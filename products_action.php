<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

// Cloudinary configuration
Configuration::instance([
    'cloud' => [
        'cloud_name' => 'dfloifjsv',
        'api_key'    => '354645883935473',
        'api_secret' => 'YkQPvXhjpH_3vSb_d5A2c17lj6s'
    ],
    'url' => [
        'secure' => true
    ]
]);

$cloudinary = new Cloudinary();
$pdo = connectToDatabase();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Handle GET request (fetch products)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if a specific product ID is requested
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            echo json_encode(["success" => true, "data" => $product]);
        } else {
            echo json_encode(["success" => false, "message" => "Product not found"]);
        }
        exit;
    }

    // Handle search parameter
    $search = $_GET['search'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%"]);
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// Handle POST request (create new product)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $unit_price = floatval($_POST['unit_price'] ?? 0);

    if (!$name || $quantity < 0 || $unit_price < 0) {
        echo json_encode(["success" => false, "message" => "Name, quantity, and price are required and must be valid."]);
        exit;
    }

    try {
        // Handle image upload to Cloudinary if present
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadApi = new UploadApi();
            $result = $uploadApi->upload($_FILES['image']['tmp_name'], [
                'folder' => 'products/',
                'resource_type' => 'image'
            ]);

            if (isset($result['secure_url'])) {
                $image_url = $result['secure_url'];
            }
        }

        // Insert new product
        $stmt = $pdo->prepare("INSERT INTO products (name, description, quantity, unit_price, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $quantity, $unit_price, $image_url]);

        echo json_encode(["success" => true, "message" => "Product added successfully."]);
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        error_log($errorMessage);
        echo json_encode(["success" => false, "message" => "Failed to add product. Please try again later."]);
    }
    exit;
}

// Handle PUT request (update existing product)
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || (isset($_POST['_method']) && $_POST['_method'] === 'PUT')) {
    // Support for form-data PUT method via POST with _method field
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
        $putData = $_POST;
        $files = $_FILES;
    } else {
        parse_str(file_get_contents("php://input"), $putData);
        $files = null;
    }

    $id = $putData['id'] ?? '';
    $name = trim($putData['name'] ?? '');
    $description = trim($putData['description'] ?? '');
    $quantity = intval($putData['quantity'] ?? 0);
    $unit_price = floatval($putData['unit_price'] ?? 0);

    if (!$id || !$name || $quantity < 0 || $unit_price < 0) {
        echo json_encode(["success" => false, "message" => "All fields are required and must be valid."]);
        exit;
    }

    try {
        // Handle image upload if present
        $image_url = null;
        $image_clause = "";
        $params = [$name, $description, $quantity, $unit_price];

        if ($files && isset($files['image']) && $files['image']['error'] == 0) {
            $uploadApi = new UploadApi();
            $result = $uploadApi->upload($files['image']['tmp_name'], [
                'folder' => 'products/',
                'resource_type' => 'image'
            ]);

            if (isset($result['secure_url'])) {
                $image_url = $result['secure_url'];
                $image_clause = ", image_url = ?";
                $params[] = $image_url;
            }
        }

        // Add ID to params array
        $params[] = $id;

        // Update product
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, quantity = ?, unit_price = ?" . $image_clause . " WHERE id = ?");
        $stmt->execute($params);

        echo json_encode(["success" => true, "message" => "Product updated successfully."]);
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        error_log($errorMessage);
        echo json_encode(["success" => false, "message" => "Failed to update product. Please try again later."]);
    }
    exit;
}

// Handle DELETE request (delete a product)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] === 'DELETE')) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
        $deleteData = $_POST;
    } else {
        parse_str(file_get_contents("php://input"), $deleteData);
    }

    $id = $deleteData['id'] ?? '';

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Product ID is required."]);
        exit;
    }

    try {
        // Fetch the current image URL (optional: to delete from Cloudinary later)
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_image_url = $product['image_url'] ?? null;

        // Delete the product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        // Optional: Delete the image from Cloudinary
        // This would require additional Cloudinary API implementation

        echo json_encode(["success" => true, "message" => "Product deleted successfully."]);
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        error_log($errorMessage);
        echo json_encode(["success" => false, "message" => "Failed to delete product. Please try again later."]);
    }
    exit;
}

// If we reach here, it means the request method is not supported
echo json_encode(["success" => false, "message" => "Request method not supported"]);
exit;
?>