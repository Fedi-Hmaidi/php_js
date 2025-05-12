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

        // Success response
        echo json_encode([
            "success" => true,
            "message" => "Order placed successfully!",
            "total" => $totalPrice,
            "product_name" => $product['name'],
            "quantity" => $quantity,
            "new_stock" => $newQuantity
        ]);
        exit;

    } catch (Exception $e) {
        // Roll back transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log("Order error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
        exit;
    }
}
/*
// GET: Retrieve top-selling products
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $limit = max(1, min($limit, 20)); // Ensure limit is between 1-20

        $stmt = $pdo->prepare("
            SELECT p.name AS product_name, SUM(o.quantity) AS total_sold
            FROM orders o
            JOIN products p ON o.product_id = p.id
            GROUP BY p.name
            ORDER BY total_sold DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $topSellingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $topSellingProducts,
            "meta" => [
                "count" => count($topSellingProducts),
                "limit" => $limit
            ]
        ]);
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to retrieve top-selling products",
            "error" => $e->getMessage()
        ]);
        exit;
    }
}




// GET: Retrieve users who placed orders
// GET: Retrieve users who made orders
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['users_who_ordered'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.username, u.email
            FROM users u
            JOIN orders o ON u.id = o.user_id
        ");
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $users
        ]);
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to retrieve users",
            "error" => $e->getMessage()
        ]);
        exit;
    }
}


*/

// Handle the GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Check if the 'chart_type' query parameter is set
        $chartType = $_GET['chart_type'] ?? 'top_selling';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $limit = max(1, min($limit, 20)); // Ensure limit is between 1-20

        if (isset($_GET['users_who_ordered'])) {
            // Get users who placed orders
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id, u.username, u.email
                FROM users u
                JOIN orders o ON u.id = o.user_id
            ");
            $stmt->execute();

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => $users
            ]);
            exit;
        } else {
            // Handle chart types
            if ($chartType === 'orders_per_day') {
                // Get the total number of orders per day
                $stmt = $pdo->prepare("
                    SELECT DATE(order_date) AS order_date, COUNT(*) AS total_orders
                    FROM orders
                    GROUP BY order_date
                    ORDER BY order_date ASC
                ");
                $stmt->execute();

                // Check if there is an error with the query
                if ($stmt->errorCode() != '00000') {
                    throw new Exception("Database error: " . implode(', ', $stmt->errorInfo()));
                }

                $dailyOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "data" => $dailyOrders
                ]);
                exit;
            } else {
                // Get top-selling products (default chart type)
                $stmt = $pdo->prepare("
                    SELECT p.name AS product_name, SUM(o.quantity) AS total_sold
                    FROM orders o
                    JOIN products p ON o.product_id = p.id
                    GROUP BY p.name
                    ORDER BY total_sold DESC
                    LIMIT :limit
                ");
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();

                // Check if there is an error with the query
                if ($stmt->errorCode() != '00000') {
                    throw new Exception("Database error: " . implode(', ', $stmt->errorInfo()));
                }

                $topSellingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "data" => $topSellingProducts,
                    "meta" => [
                        "count" => count($topSellingProducts),
                        "limit" => $limit
                    ]
                ]);
                exit;
            }
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to retrieve data",
            "error" => $e->getMessage()
        ]);
        exit;
    }
}
// Unsupported request method
echo json_encode(["success" => false, "message" => "Unsupported request method"]);
exit;
