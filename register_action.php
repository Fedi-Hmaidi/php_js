<?php
require_once 'db.php';
require_once 'vendor/autoload.php';

$pdo = connectToDatabase();

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone_number'] ?? '';
$address = $_POST['address'] ?? '';

if (!$username || !$email || !$phone || !$address) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(["success" => false, "message" => "Email already registered."]);
        exit;
    }

    // Generate token
    $token = bin2hex(random_bytes(32));
    $createdAt = date('Y-m-d H:i:s');

    // Save user with inactive status
    $stmt = $pdo->prepare("INSERT INTO users (username, email, phone_number, address, is_active, activation_token, token_created_at,password)
                            VALUES (?, ?, ?, ?, FALSE, ?, ?, null)");
    $stmt->execute([$username, $email, $phone, $address, $token, $createdAt]);

    // Send email using PHPMailer
    $activationLink = "http://localhost:8000/activate.php?token=$token"; // Adjust URL as needed

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Example for Gmail SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'fedihmaidi9@gmail.com'; // Your Gmail address
    $mail->Password = 'rxbpczpjxbcweota'; // Your Gmail password or app password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('no-reply@yourdomain.com', 'Your Company');
    $mail->addAddress($email, $username);
    $mail->Subject = "Activate your account";
    $mail->Body    = "Hi $username,\n\nClick the link below to activate your account and set your password:\n$activationLink\n\nIf you didn’t register, ignore this email.";

    if ($mail->send()) {
        echo json_encode(["success" => true, "message" => "Activation link sent to your email."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to send activation email."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
