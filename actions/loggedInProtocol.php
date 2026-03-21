<?php
    function keepLogged($ut_id){
        switch($ut_id){
            case 1:
                header("Location: users/admin/html/index.php"); 
                break;
            case 2: 
                header("Location: users/dean/html/organizations.php"); 
                break;
            case 3: 
                header("Location: users/organization/html/org_activities.php"); 
                break;
            case 4: 
                header("Location: users/student/html/index.php"); 
                break;
            default:
                session_destroy();
                echo '
                    <script>
                        alert("Hi!");
                    </script>';
                break;
        }
    }

    require 'conn.php';

    if(isset($_SESSION['id'])){
        $id = $_SESSION['id'];
    
        $sql = 
        "SELECT ut_id

        FROM accounts

        WHERE user_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt -> bind_param("i", $id);
        $stmt -> execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $ut_id = $user['ut_id'];

        keepLogged($ut_id);

        $stmt -> close();
        $conn -> close();
    }
?>