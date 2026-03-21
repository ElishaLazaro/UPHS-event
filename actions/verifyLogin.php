<?php
    include 'conn.php';

    if(isset($_POST['login_submit'])){
        $email = $_POST['email'];
        $password = $_POST['password'];

        if(!empty($email)){
            $sql = "SELECT * FROM accounts where username = ? or email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $email);

            $stmt->execute();

            $result = $stmt->get_result();

            if($result->num_rows==1){
                $user = $result->fetch_assoc();

                if(password_verify($password, $user['password'])){
                    echo 
                    '
                        <script> alert("Logged in successfully!"); </script>
                    ';

                    switch($user['ut_id']){
                        case 1: 
                            echo 
                            '
                                <script> window.location = "users/admin/html/index.php"; </script>
                            ';
                            break;
                        case 2: 
                            echo 
                            '
                                <script> window.location = "users/dean/html/organizations.php"; </script>
                            ';
                            break;
                        case 3: 
                            echo 
                            '
                                <script> window.location = "users/organization/html/org_activities.php"; </script>
                            ';
                            break;
                        case 4: 
                            echo 
                            '
                                <script> window.location = "users/admin/html/organizations.php"; </script>
                            ';
                            break;
                    }

                    $_SESSION['id'] = $user['user_id'];

                    $stmt->close();
                    $conn->close();
                }
                else{

                    $stmt->close();
                    $conn->close();

                    echo 
                    '
                    <script> alert("Password is incorrect!"); </script>
                    <script> window.location = "login.php"; </script>
                    ';                    
                }
            }
        }
        else{
            $stmt->close();
            $conn->close();
            
            echo "Username does not exist!";        
        }     
    }
?>