<?php
session_start();
include 'ConnectToDb.php';

$redirectUrl = '../index.php';

if (isset($_GET['role']) && $_GET['role'] === 'admin') {
    $redirectUrl = '../login/index.php';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['login-email']) && isset($_POST['login-password'])) {

        $email = $_POST['login-email'];
        $password = $_POST['login-password'];

        if (strlen($password) < 8) {
            $_SESSION['error'] = "Password must be at least 8 characters long!";
            header('Location: ' . $redirectUrl);
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $userResult = $stmt->get_result();

        if ($userResult->num_rows > 0) {

            $user = $userResult->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                if (isset($user['is_approved']) && $user['is_approved'] != 1) {
                    $_SESSION['error'] = "Your account has not been approved by an admin yet. Please wait for approval.";
                    header('Location: ' . $redirectUrl);
                    exit();
                }

                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['username']   = $user['username'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['loggedin']   = true;
                $_SESSION['test_taken'] = $user['test_taken'];

                $conn->query("
                    INSERT INTO activity (user_id, activity_log, activity_at, role) 
                    VALUES ('{$user['user_id']}', 'Account Log-In', NOW(), {$user['role']})
                ");

                if ($user['role'] == 0 || $user['role'] == 2) {
                    header('Location: ../dashboard/index.php');
                } else {
                    header('Location: ' . $redirectUrl);
                }
                exit();
            } else {
                $_SESSION['error'] = "Invalid password!";
                header('Location: ' . $redirectUrl);
                exit();
            }
        } else {
            $_SESSION['error'] = "Email not found!";
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
}
