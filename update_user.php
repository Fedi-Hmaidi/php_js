<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not authenticated"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone_number'] ?? '';
$address = $data['address'] ?? '';
$password = $data['password'] ?? null;

if (!$username || !$email || !$phone || !$address) {
    echo json_encode(["success" => false, "message" => "All fields except password are required."]);
    exit;
}

try {
    $pdo = connectToDatabase();

    // Optional password update
    if ($password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone_number = ?, address = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $email, $phone, $address, $hashedPassword, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone_number = ?, address = ? WHERE id = ?");
        $stmt->execute([$username, $email, $phone, $address, $_SESSION['user_id']]);
    }

    echo json_encode(["success" => true, "message" => "Profile updated successfully."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
