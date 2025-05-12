<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$pdo = connectToDatabase();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Handle GET request (fetch categories)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $stmt = $pdo->prepare("SELECT name, (SELECT COUNT(*) FROM products WHERE category_id = categories.id) AS product_count FROM categories WHERE name LIKE ?");
    $stmt->execute(["%$search%"]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Handle POST request (create new category)// Handle POST request (create new category)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$name || !$description) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    try {
        // Modified query to NOT specify the ID value, allowing the database to auto-increment
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);

        echo json_encode(["success" => true, "message" => "Category added successfully."]);
    } catch (PDOException $e) {
        // Proper error handling
        $errorMessage = "Database error: " . $e->getMessage();
        error_log($errorMessage);
        echo json_encode(["success" => false, "message" => "Failed to add category. Please try again later."]);
    }
    exit;
}
// Handle PUT request (update existing category)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $putData);
    $id = $putData['id'] ?? '';
    $name = trim($putData['name'] ?? '');
    $description = trim($putData['description'] ?? '');

    if (!$id || !$name || !$description) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $description, $id]);

    echo json_encode(["success" => true, "message" => "Category updated successfully."]);
    exit;
}

// Handle DELETE request (delete a category)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $deleteData);
    $id = $deleteData['id'] ?? '';

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Category ID is required."]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["success" => true, "message" => "Category deleted successfully."]);
    exit;
}
?>


