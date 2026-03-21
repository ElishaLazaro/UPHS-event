<?php
session_start();
if (!$_SESSION['loggedin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../../php/ConnectToDb.php';

header('Content-Type: application/json');

$action = trim($_POST['action'] ?? '');

switch ($action) {

  
    case 'create':
        $date        = trim($_POST['date']        ?? '');
        $time        = trim($_POST['time']        ?? '');
        $activity    = trim($_POST['activity']    ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$date || !$time || !$activity) {
            echo json_encode(['success' => false, 'message' => 'Date, time, and activity are required.']);
            exit();
        }

        $imagePath = handleImageUpload();
        if ($imagePath === false) exit(); 
        $stmt = $conn->prepare("INSERT INTO announcements (date, time, activity, description, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $date, $time, $activity, $description, $imagePath);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Announcement created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'update':
        $id          = intval($_POST['announcement_id'] ?? 0);
        $date        = trim($_POST['date']              ?? '');
        $time        = trim($_POST['time']              ?? '');
        $activity    = trim($_POST['activity']          ?? '');
        $description = trim($_POST['description']       ?? '');

        if (!$id || !$date || !$time || !$activity) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit();
        }

        $res = $conn->query("SELECT image FROM announcements WHERE announcement_id = $id");
        if (!$res || $res->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Announcement not found.']);
            exit();
        }
        $existing  = $res->fetch_assoc();
        $imagePath = $existing['image'];

        if (!empty($_FILES['image']['name'])) {
            $newImage = handleImageUpload();
            if ($newImage === false) exit();

            if ($imagePath && file_exists('../' . $imagePath)) {
                unlink('../' . $imagePath);
            }
            $imagePath = $newImage;
        }

        $stmt = $conn->prepare("UPDATE announcements SET date=?, time=?, activity=?, description=?, image=? WHERE announcement_id=?");
        $stmt->bind_param('sssssi', $date, $time, $activity, $description, $imagePath, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Announcement updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
        break;

    
    case 'delete':
        $id = intval($_POST['announcement_id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            exit();
        }

        $res = $conn->query("SELECT image FROM announcements WHERE announcement_id = $id");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if ($row['image'] && file_exists('../' . $row['image'])) {
                unlink('../' . $row['image']);
            }
        }

        $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
        break;

  
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

$conn->close();


function handleImageUpload(): string|null|false
{
    if (empty($_FILES['image']['name'])) {
        return null;
    }

    $uploadDir = '../images/announcements/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.']);
        return false;
    }

    $filename = 'ann_' . time() . '_' . uniqid() . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
        return false;
    }

    return 'images/announcements/' . $filename;
}
