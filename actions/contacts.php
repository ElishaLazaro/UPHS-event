<?php

declare(strict_types=1);

/**
 * Public contact form handler: saves inquiry, emails school inbox, redirects with flash.
 */

require __DIR__ . '/conn.php';
require_once __DIR__ . '/contact_inquiry_mail.php';

function contact_table_has_column(mysqli $conn, string $column): bool
{
    $col = $conn->real_escape_string($column);
    $r = $conn->query("SHOW COLUMNS FROM `contact` LIKE '{$col}'");

    return $r && $r->num_rows > 0;
}

if (!isset($_POST['submitContact'])) {
    header('Location: ../index.php');
    exit;
}

$name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';

if ($name === '' || $email === '' || $message === '') {
    header('Location: ../index.php?contact=invalid#contact-section');
    exit;
}

if (strlen($name) > 200 || strlen($email) > 200 || strlen($message) > 8000) {
    header('Location: ../index.php?contact=invalid#contact-section');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../index.php?contact=invalid#contact-section');
    exit;
}

$recipients = contact_resolve_inbox_recipients($conn);
if ($recipients === []) {
    header('Location: ../index.php?contact=not_configured#contact-section');
    exit;
}

$hasIsRead = contact_table_has_column($conn, 'is_read');

if ($hasIsRead) {
    $sql = 'INSERT INTO `contact` (`name`, `email`, `message`, `is_read`) VALUES (?,?,?,0)';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header('Location: ../index.php?contact=error#contact-section');
        exit;
    }
    $stmt->bind_param('sss', $name, $email, $message);
} else {
    $sql = 'INSERT INTO `contact` (`name`, `email`, `message`) VALUES (?,?,?)';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header('Location: ../index.php?contact=error#contact-section');
        exit;
    }
    $stmt->bind_param('sss', $name, $email, $message);
}

if (!$stmt->execute()) {
    $stmt->close();
    header('Location: ../index.php?contact=error#contact-section');
    exit;
}
$stmt->close();

$fromEnvelope = $recipients[0];
$mailOk = true;
foreach ($recipients as $to) {
    if (!contact_send_inquiry_email($to, $name, $email, $message, $fromEnvelope)) {
        $mailOk = false;
    }
}

if ($mailOk) {
    header('Location: ../index.php?contact=success#contact-section');
} else {
    // Saved to database; mail may be disabled on localhost (configure sendmail/SMTP for production).
    header('Location: ../index.php?contact=saved_no_mail#contact-section');
}
exit;
