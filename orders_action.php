<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$pdo = connectToDatabase();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// POST: Create order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $userId = $_SESSION['user_id'];

    // Validate inputs
    if (!$productId || !$quantity) {
        echo json_encode(["success" => false, "message" => "Product ID and quantity are required"]);
        exit;
    }

    if (!is_numeric($quantity)) {
        echo json_encode(["success" => false, "message" => "Quantity must be a number"]);
        exit;
    }

    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        echo json_encode(["success" => false, "message" => "Quantity must be greater than 0"]);
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // 1. Check product availability (with row lock)
        $stmt = $pdo->prepare("SELECT id, name, unit_price, quantity FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found");
        }

        if ($product['quantity'] < $quantity) {
            throw new Exception("Not enough stock available. Only {$product['quantity']} units left.");
        }

        // Calculate total price
        $totalPrice = $product['unit_price'] * $quantity;

        // 2. Update product quantity
        $newQuantity = $product['quantity'] - $quantity;
        $updateStmt = $pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $updateStmt->execute([$newQuantity, $productId]);

        // 3. Create order record
        $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        $orderStmt->execute([$userId, $productId, $quantity, $product['unit_price'], $totalPrice]);

        // Commit transaction
        $pdo->commit();

// In the success response (before the commit):
    echo json_encode([
        "success" => true,
        "message" => "Order placed successfully!",
        "total" => $totalPrice,
        "product_name" => $product['name'],
        "quantity" => $quantity,
        "new_stock" => $newQuantity
    ]);
    } catch (Exception $e) {
        // Roll back transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log("Order error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}

// Unsupported request method
echo json_encode(["success" => false, "message" => "Unsupported request method"]);
exit;