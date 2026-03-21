<?php
session_start();
include 'ConnectToDb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $age      = $_POST['age'];
    $gender   = $_POST['gender'];
    $register_otp = isset($_POST['register_otp']) ? trim($_POST['register_otp']) : '';

    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
        header('Location: ../index.php');
        exit();
    }

    // Verify OTP
    if (empty($register_otp)) {
        $_SESSION['error'] = "Please enter the email verification OTP.";
        header('Location: ../index.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND otp = ? AND used = 0 ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ss", $email, $register_otp);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $_SESSION['error'] = "Invalid or used OTP.";
        header('Location: ../index.php');
        exit();
    }

    $row = $res->fetch_assoc();
    if (!empty($row['expires_at']) && strtotime($row['expires_at']) <= time()) {
        $_SESSION['error'] = "OTP has expired.";
        header('Location: ../index.php');
        exit();
    }

    // Insert new user with role = 0 (admin)
    $ins = $conn->prepare("INSERT INTO users (username, email, password, age, gender, role, is_approved, created_at) 
                           VALUES (?, ?, ?, ?, ?, 0, 0,NOW())");
    $ins->bind_param('sssis', $username, $email, $password, $age, $gender);

    if ($ins->execute()) {
        $user_id = $conn->insert_id;

        // Mark OTP as used
        $mark = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
        $mark->bind_param('i', $row['id']);
        $mark->execute();

        // Log activity
        $act_log = $conn->prepare("INSERT INTO activity (user_id, activity_log, activity_at, role) 
                                   VALUES (?, 'Account Registered', NOW(), 0)");
        $act_log->bind_param('i', $user_id);
        $act_log->execute();

        $_SESSION['success'] = "Registration successful! You can now login.";
    } else {
        $_SESSION['error'] = "Registration failed: " . $conn->error;
    }

    header('Location: ../index.php');
    exit();
}
