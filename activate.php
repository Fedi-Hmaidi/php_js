<?php
require_once 'db.php';

$pdo = connectToDatabase();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';
    if (!$token) {
        die("Invalid token.");
    }

    // Check if token is valid and user is not yet active
    $stmt = $pdo->prepare("SELECT id, username, token_created_at FROM users WHERE activation_token = ? AND is_active = FALSE");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Invalid or expired token.");
    }

    // Check token expiration (1 hour)
    $createdAt = new DateTime($user['token_created_at']);
    $now = new DateTime();
    $interval = $createdAt->diff($now);
    if ($interval->h > 1 || $interval->days > 0) {
        die("Activation link expired.");
    }

    // Show password form
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8" />
      <title>Set Password</title>
      <style>
        body { font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f4f4; }
        .form-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
        input { display: block; width: 100%; margin-bottom: 15px; padding: 10px; }
        button { padding: 10px; background: #007bff; color: white; border: none; width: 100%; cursor: pointer; }
        .error { color: red; }
      </style>
    </head>
    <body>
      <div class="form-container">
        <h2>Set Your Password</h2>
        <form method="POST">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
          <input type="password" name="password" placeholder="New Password" required>
          <input type="password" name="confirm_password" placeholder="Confirm Password" required>
          <button type="submit">Activate Account</button>
        </form>
      </div>
    </body>
    </html>

    <?php
    exit;
}

// --- POST logic to update password ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!$token || !$password || !$confirmPassword) {
        die("All fields are required.");
    }

    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }

    // Check if token is valid again
    $stmt = $pdo->prepare("SELECT id FROM users WHERE activation_token = ? AND is_active = FALSE");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Invalid or already activated token.");
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Update password and activate account
    $stmt = $pdo->prepare("UPDATE users SET password = ?, is_active = TRUE, activation_token = NULL, token_created_at = NULL WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);

    header("Location: login.php");
    exit;}
?>
