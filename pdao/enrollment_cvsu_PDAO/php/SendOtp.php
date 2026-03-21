<?php
session_start();
include 'ConnectToDb.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../index.php');
    exit();
}

$email = isset($_POST['recovery_email']) ? trim($_POST['recovery_email']) : '';
$action = isset($_POST['action']) ? trim($_POST['action']) : 'recovery'; // 'register' or 'recovery'

if (empty($email)) {
    $_SESSION['error'] = 'Email is required to send OTP.';
    header('Location: ../index.php');
    exit();
}

// Check email depending on action
if ($action === 'register') {
    // For registration, email must NOT already exist
    $u = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $u->bind_param('s', $email);
    $u->execute();
    $ur = $u->get_result();
    if ($ur->num_rows > 0) {
        $_SESSION['error'] = 'Email already registered.';
        // If AJAX, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Email already registered.']);
            exit();
        }
        header('Location: ../index.php');
        exit();
    }
} else {
    // For recovery (default), email must exist
    $u = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $u->bind_param('s', $email);
    $u->execute();
    $ur = $u->get_result();
    if ($ur->num_rows === 0) {
        $_SESSION['error'] = 'Email not found.';
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Email not found.']);
            exit();
        }
        header('Location: ../index.php');
        exit();
    }
}

// Ensure password_resets table exists
$create_sql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(20) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create_sql);

// Generate 6-digit OTP
$otp = strval(rand(100000, 999999));
$expires_at = date('Y-m-d H:i:s', time() + 15 * 60); // 15 minutes

$ins = $conn->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)");
$ins->bind_param('sss', $email, $otp, $expires_at);
if (!$ins->execute()) {
    $_SESSION['error'] = 'Failed to generate OTP. Please try again.';
    header('Location: ../index.php');
    exit();
}

// Try to send via PHPMailer. Require composer dependency phpmailer/phpmailer.
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // SMTP settings - UPDATE these to your SMTP server credentials
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'EMAIL HERE';
    $mail->Password = 'APP PASSWORD HERE';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('no-reply@example.com', 'Password Recovery');
    $mail->addAddress($email);

    $mail->isHTML(true);
    if ($action === 'register') {
        $mail->Subject = 'Email verification code';
        $mail->Body = "<p>Thank you for registering. Your email verification code is: <strong>{$otp}</strong></p><p>This code expires in 15 minutes. Do not share this code with anyone.</p>";
    } else {
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "<p>Your password recovery OTP is: <strong>{$otp}</strong></p><p>This code expires in 15 minutes.</p>";
    }

    $mail->send();
    $message = 'OTP sent to your email. It expires in 15 minutes.';
    $success = true;
} catch (Exception $e) {
    // PHPMailer failed; still keep OTP in DB so manual delivery possible
    $message = 'Failed to send OTP email: ' . $mail->ErrorInfo;
    $success = false;
}

// If AJAX request, return JSON; otherwise set session and redirect
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
    header('Content-Type: application/json');
    // Only include the OTP in the JSON when on localhost for testing convenience
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $includeOtp = ($remote === '127.0.0.1' || $remote === '::1' || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
    $resp = [
        'success' => $success,
        'message' => $message,
    ];
    if ($includeOtp) {
        $resp['otp'] = $otp;
    }
    echo json_encode($resp);
    exit();
} else {
    if ($success) {
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = $message;
    }
    header('Location: ../index.php');
    exit();
}
