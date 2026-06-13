<?php
    	session_start();
        require_once 'Config/Config.php';
     
        if($_POST['email'] != "" || $_POST['password'] != ""){
            $email = $_POST['email'];
            $password = $_POST['password'];
            $sql = "SELECT * FROM user WHERE `email` = '$email' and `password` = '$password'";
            //$sql = "SELECT * FROM user WHERE `email` = '' OR '1=1'; -- $email' and `password` = '$password'";
            $result = mysqli_query($db,$sql);
            $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
           // $active = $row['active'];
            $count = mysqli_num_rows($result);


            if($count > 0) {
                $_SESSION['email'] = $row['email'];
                $_SESSION['id'] = $row['id'];
                $_SESSION['role'] = $row['account_type'];
                header("location: profile.php");
               
            } else{
                echo "
                <script>alert('Invalid username or password')</script>
                <script>window.location = 'login.php'</script>
                ";
            }
        }else{
            echo "
                <script>alert('Please complete the required field!')</script>
                <script>window.location = 'index.php'</script>
            ";
        }
    	
    ?>
