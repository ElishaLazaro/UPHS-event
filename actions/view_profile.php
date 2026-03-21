<?php
    session_start();
    require 'conn.php';

    $user_id = $_SESSION['id'];

    $sql = 
    "SELECT 
         f_name
       , m_name 
       , l_name
       , username
    
    FROM accounts

    WHERE user_id = ?;
    ";

    $stmt = $conn->prepare($sql);
    $stmt -> bind_param("i", $user_id);
    $stmt -> execute();

    $result = $stmt->get_result();

    $row = mysqli_fetch_assoc($result);

    $f_name = $row['f_name'];
    $m_name = $row['m_name'];
    $l_name = $row['l_name'];
    $username = $row['username'];

?>