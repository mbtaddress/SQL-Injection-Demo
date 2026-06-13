<?php
    	session_start();   

 define( 'SQL_INJECTION_IN_PHP', true );

require 'vendor/autoload.php';

use SqlInjection\MySQLConnection;

$pdo = ( new MySQLConnection() )->connect();
if ( $pdo === null ) {
  echo 'Whoops, could not connect to the MySQL database!';
}else{
        if(ISSET($_POST['login'])){
            if($_POST['email'] != "" || $_POST['password'] != ""){
                $email = $_POST['email'];
                $password = $_POST['password'];
                $sql = "SELECT * FROM `user` WHERE `email`=? AND `password`=? ";
                $query = $pdo->prepare($sql);
                $query->execute(array($email,$password));
                $row = $query->rowCount();
                $fetch = $query->fetch();
                if($row > 0) {
                     $_SESSION['email'] = $fetch['email'];
                     $_SESSION['id'] = $fetch['id'];
                     $_SESSION['role'] = $fetch['account_type'];
                    header("location: profile_safe.php");
                } else{
                    echo "
                    <script>alert('Invalid email or password')</script>
                    <script>window.location = 'loginsafe.php'</script>
                    ";
                }
            }else{
                echo "
                    <script>alert('Please complete the required field!')</script>
                    <script>window.location = 'profile_safe.php'</script>
                ";
            }
        }

       
    }
    ?>
