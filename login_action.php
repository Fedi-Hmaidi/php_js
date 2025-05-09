<?php
session_start();
require_once 'db.php';
require_once 'vendor/autoload.php';

use \Firebase\JWT\JWT;

$pdo = connectToDatabase();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $key = bin2hex(random_bytes(32));
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;
        $payload = array(
            "username" => $user['username'],
            "id" => $user['id'],
            "iat" => $issuedAt,
            "exp" => $expirationTime
        );

        $jwt = JWT::encode($payload, $key, 'HS256');

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode(["success" => true, "token" => $jwt]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Invalid username or password."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
