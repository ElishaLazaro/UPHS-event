<?php
    session_start();
    require 'conn.php';

    $user_id = $_SESSION['id'];

    if(isset($_POST['submit_event'])){
        $event_name = $_POST['event_name'];
        $activity = $_POST['activity'];
        $venue = $_POST['venue'];
        $date_start = $_POST['date_start'];
        $date_end = $_POST['date_end'];
        $time_start = $_POST['time_start'];
        $time_end = $_POST['time_end'];
        $status = 1; // Changed from 3 to 1 to match your WHERE clause (event_status = 1)

        // Handle image upload
        $event_image = null;
        $upload_dir = '../uploads/events/'; // Adjust this path as needed
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if(isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['event_image']['type'];
            
            if(in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'event_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if(move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                    $event_image = $new_filename; // Store only filename in database
                } else {
                    echo '<script>alert("Error uploading image!");</script>';
                    echo '<script>window.location = "../users/organization/html/events.php";</script>';
                    exit();
                }
            } else {
                echo '<script>alert("Invalid image type. Please upload JPEG, PNG, GIF, or WEBP.");</script>';
                echo '<script>window.location = "../users/organization/html/events.php";</script>';
                exit();
            }
        } else {
            echo '<script>alert("Please select an image to upload!");</script>';
            echo '<script>window.location = "../users/organization/html/events.php";</script>';
            exit();
        }

        // Insert into database
        $sql = "INSERT INTO events (u_id, event_name, activity, venue, date_start, date_end, time_start, time_end, event_image, event_status)
                VALUES (?,?,?,?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssi", $user_id, $event_name, $activity, $venue, $date_start, $date_end, $time_start, $time_end, $event_image, $status);
        
        if($stmt->execute()) {
            echo '<script>alert("New event added successfully!");</script>';
            echo '<script>window.location = "../users/admin/html/events.php";</script>';
        } else {
            echo '<script>alert("Error adding event: ' . $conn->error . '");</script>';
            echo '<script>window.location = "../users/admin/html/events.php";</script>';
        }

        $stmt->close();
        $conn->close();
    } else {
        header("Location: ../users/organization/html/events.php");
        exit();
    }
?>