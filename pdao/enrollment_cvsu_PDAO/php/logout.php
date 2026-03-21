<?php
session_start();
include 'ConnectToDb.php';

$userId = $_GET['id'] ?? null;
$role = $_GET['role'] ?? 1;

if ($userId) {
    $conn->query("INSERT INTO activity (user_id, activity_log, activity_at, role) 
                VALUES ({$userId}, 'Account Log-Out', NOW(), {$role})");
}

$_SESSION = [];

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header("Location: ../index.php");
exit();
