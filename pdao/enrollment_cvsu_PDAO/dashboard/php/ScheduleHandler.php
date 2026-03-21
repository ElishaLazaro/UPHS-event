<?php
session_start();
if (!$_SESSION['loggedin']) {
    header('Location: ../index.php');
    exit();
}

include '../../php/ConnectToDb.php';

$title       = trim($_POST['title'] ?? '');
$event_date  = trim($_POST['event_date'] ?? '');
$event_time  = trim($_POST['event_time'] ?? '') ?: null;
$description = trim($_POST['description'] ?? '') ?: null;
$admin_id    = $_SESSION['user_id'] ?? null;

if ($title && $event_date) {
    $stmt = $conn->prepare("INSERT INTO schedule (admin_id, title, event_date, event_time, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $admin_id, $title, $event_date, $event_time, $description);
    $stmt->execute();
    $stmt->close();

    $month = intval(date('n', strtotime($event_date)));
    $year  = intval(date('Y', strtotime($event_date)));
    header("Location: ../schedule.php?month=$month&year=$year&saved=1");
} else {
    header("Location: ../schedule.php");
}

$conn->close();
exit();
