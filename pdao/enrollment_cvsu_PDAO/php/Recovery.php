<?php
session_start();
include 'ConnectToDb.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // This handler expects the reset form submission (name="reset" on button).
    if (!isset($_POST['reset'])) {
        $_SESSION['error'] = "Invalid request.";
        header('Location: ../index.php');
        exit();
    }

    $email = isset($_POST['recovery_email']) ? trim($_POST['recovery_email']) : '';
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
    $new_password = isset($_POST['recovery_newpass']) ? $_POST['recovery_newpass'] : '';
    $confirm = isset($_POST['confirm_recovery']) ? $_POST['confirm_recovery'] : '';

    if (empty($email) || empty($otp) || empty($new_password) || empty($confirm)) {
        $_SESSION['error'] = "All fields are required!";
        header('Location: ../index.php');
        exit();
    }

    if ($new_password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header('Location: ../index.php');
        exit();
    }

    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header('Location: ../index.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND otp = ? AND used = 0 ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $_SESSION['error'] = "Invalid or used OTP.";
        header('Location: ../index.php');
        exit();
    }

    $row = $res->fetch_assoc();

    if (!empty($row['expires_at'])) {
        $expires_ts = strtotime($row['expires_at']);
        if ($expires_ts <= time()) {
            $_SESSION['error'] = "Invalid or expired OTP.";
            header('Location: ../index.php');
            exit();
        }
    }

    // Fetch user
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $userRes = $user_stmt->get_result();

    if ($userRes->num_rows === 0) {
        $_SESSION['error'] = "Email not found.";
        header('Location: ../index.php');
        exit();
    }

    $user = $userRes->fetch_assoc();

    // Hash the new password before storing
    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $update_stmt->bind_param("si", $hashed, $user['user_id']);

    if ($update_stmt->execute()) {
        // mark otp as used
        $mark_stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
        $mark_stmt->bind_param("i", $row['id']);
        $mark_stmt->execute();

        $activity_stmt = $conn->prepare("INSERT INTO activity (user_id, activity_log, activity_at, role) VALUES (?, ?, NOW(), ?)");
        $activity_log = "Recovered Password!";
        $activity_stmt->bind_param("isi", $user['user_id'], $activity_log, $user['role']);
        $activity_stmt->execute();

        $_SESSION['success'] = "Recovery successful! You can now login.";
    } else {
        $_SESSION['error'] = "Failed to update password. Please try again.";
    }

    header('Location: ../index.php');
    exit();
}
