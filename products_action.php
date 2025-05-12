<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;

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

// Create API instances
$uploadApi = new UploadApi();
$adminApi = new AdminApi();

$pdo = connectToDatabase();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// GET: Fetch product(s)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($product ? ["success" => true, "data" => $product] : ["success" => false, "message" => "Product not found"]);
        exit;
    }

    $search = $_GET['search'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%"]);
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}
// POST: Create product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['_method'] ?? '';

    if ($method === 'PUT') {
        handleUpdateProduct($_POST, $_FILES);
        exit;
    } else if ($method === 'DELETE') {
        handleDeleteProduct($_POST);
        exit;
    }

    // Standard POST handling
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $unit_price = floatval($_POST['unit_price'] ?? 0);
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;

    // Validate input
    if (!$name || $quantity < 0 || $unit_price < 0 || $category_id === null || $category_id <= 0) {
        echo json_encode(["success" => false, "message" => "Name, quantity, unit price, and category_id are required and must be valid."]);
        exit;
    }

    try {
        // Process file upload
        $image_url = null;
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === 0) {
            $result = $uploadApi->upload($_FILES['image']['tmp_name'], [
                'folder' => 'products/',
                'resource_type' => 'image'
            ]);
            $image_url = $result['secure_url'] ?? null;
        }

        // Insert into DB (now includes category_id)
        $stmt = $pdo->prepare("INSERT INTO products (name, description, quantity, unit_price, image_url, category_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $quantity, $unit_price, $image_url, $category_id]);

        echo json_encode([
            "success" => true,
            "message" => "Product added successfully.",
            "product_id" => $pdo->lastInsertId()
        ]);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Error adding product: " . $e->getMessage()]);
    }
    exit;
}

// PUT: Update product
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $putData);
    handleUpdateProduct($putData, null);
    exit;
}

// DELETE: Remove product
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $deleteData);
    handleDeleteProduct($deleteData);
    exit;
}

// Unsupported request
echo json_encode(["success" => false, "message" => "Unsupported request method."]);
exit;

/**
 * Handle product update
 *
 * @param array $data Request data
 * @param array|null $files Files data if using POST
 * @return void
 */
function handleUpdateProduct($data, $files = null) {
    global $pdo, $uploadApi;

    $id = $data['id'] ?? '';
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : -1;
    $unit_price = isset($data['unit_price']) ? floatval($data['unit_price']) : -1;

    // Validate required fields
    if (!$id || !$name || $quantity < 0 || $unit_price < 0) {
        echo json_encode(["success" => false, "message" => "Invalid product data. All fields are required."]);
        exit;
    }

    try {
        // First check if product exists
        $checkStmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $checkStmt->execute([$id]);
        $product = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(["success" => false, "message" => "Product not found."]);
            exit;
        }

        $image_clause = '';
        $params = [$name, $description, $quantity, $unit_price];
        $old_image_url = $product['image_url'] ?? null;

        // Handle image upload for POST method
        if ($files && isset($files['image']) && $files['image']['error'] === 0 && !empty($files['image']['tmp_name'])) {
            $result = $uploadApi->upload($files['image']['tmp_name'], [
                'folder' => 'products/',
                'resource_type' => 'image'
            ]);

            if (!empty($result['secure_url'])) {
                $image_clause = ", image_url = ?";
                $params[] = $result['secure_url'];

                // We have a new image, so we should clean up the old one later
                // (this would happen in a separate function if we were properly
                // tracking public_ids)
            }
        }

        $params[] = $id;
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, quantity = ?, unit_price = ?" . $image_clause . " WHERE id = ?");
        $stmt->execute($params);

        echo json_encode([
            "success" => true,
            "message" => "Product updated successfully.",
            "affected_rows" => $stmt->rowCount()
        ]);
    } catch (Exception $e) {
        error_log("Error updating product: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Error updating product: " . $e->getMessage()]);
    }
}

/**
 * Handle product deletion
 *
 * @param array $data Request data
 * @return void
 */
function handleDeleteProduct($data) {
    global $pdo, $adminApi;

    $id = $data['id'] ?? '';

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Product ID is required."]);
        exit;
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // First get the product to check if it exists and get image info
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(["success" => false, "message" => "Product not found."]);
            $pdo->rollBack();
            exit;
        }

        // Delete the product from the database
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        // Check if deletion was successful
        if ($stmt->rowCount() === 0) {
            echo json_encode(["success" => false, "message" => "Failed to delete product."]);
            $pdo->rollBack();
            exit;
        }

        // Commit transaction
        $pdo->commit();

        // Attempt to clean up Cloudinary image if it exists
        // This would be more robust if we stored public_ids in the database
        if (!empty($product['image_url'])) {
            // Note: This is a best effort cleanup. The URL doesn't contain the public_id in a reliable way,
            // so a proper implementation would store the public_id in the database.
            try {
                $urlParts = parse_url($product['image_url']);
                $pathParts = explode('/', trim($urlParts['path'], '/'));

                // Remove the version number and file extension
                $publicIdParts = [];
                $captureStarted = false;

                foreach ($pathParts as $part) {
                    // Skip until we find "products" folder
                    if ($part === 'products') {
                        $captureStarted = true;
                        $publicIdParts[] = $part;
                        continue;
                    }

                    if ($captureStarted) {
                        // Add each part except the last one (filename with extension)
                        if (next($pathParts) !== false) {
                            $publicIdParts[] = $part;
                        } else {
                            // For the last part, remove the file extension
                            $publicIdParts[] = pathinfo($part, PATHINFO_FILENAME);
                        }
                    }
                }

                $publicId = implode('/', $publicIdParts);

                if (!empty($publicId)) {
                    // Best effort to delete the image
                    $adminApi->deleteAssets([$publicId], ['resource_type' => 'image']);
                }
            } catch (Exception $e) {
                // Log but don't fail if image deletion fails
                error_log("Failed to delete Cloudinary image: " . $e->getMessage());
            }
        }

        echo json_encode([
            "success" => true,
            "message" => "Product deleted successfully."
        ]);
    } catch (Exception $e) {
        // Roll back transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log("Error deleting product: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Error deleting product: " . $e->getMessage()]);
    }
}
?>