<?php
    $dbservername = "localhost";
    $dbusername = "root";
    $dbpassword = "yes";
    $dbname = "uphs_events";
    
    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }    
?>