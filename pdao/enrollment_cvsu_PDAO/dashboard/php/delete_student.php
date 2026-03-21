<?php
include '../../php/ConnectToDb.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['id'])) {
    $enrollee_id = isset($_POST['enrollee_id']) ? intval($_POST['enrollee_id']) : intval($_GET['id']);

    $sql = "SELECT photo FROM enrollees WHERE enrollee_id = $enrollee_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row['photo'] && file_exists('../' . $row['photo'])) {
        unlink('../' . $row['photo']);
    }

    $delete_sql = "DELETE FROM enrollees WHERE enrollee_id = $enrollee_id";

    if (mysqli_query($conn, $delete_sql)) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            echo json_encode(['success' => true, 'message' => 'Enrollee successfully deleted!']);
        } else {
            echo "<script>
                alert('Enrollee successfully deleted!');
                window.location.href = '../new.php';
            </script>";
        }
    } else {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            echo json_encode(['success' => false, 'message' => 'Error deleting enrollee: ' . mysqli_error($conn)]);
        } else {
            echo "<script>
                alert('Error: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');
                window.history.back();
            </script>";
        }
    }

    mysqli_close($conn);
}
