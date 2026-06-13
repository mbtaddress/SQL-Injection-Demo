<?php
    session_start();
    require_once 'Config/Config.php';
    
    if($_POST['firstname'] != "" || $_POST['username'] != "" || $_POST['password'] != ""){
        
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $age = $_POST['age'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        // md5 encrypted
        // $password = md5($_POST['password']);
        $password = $_POST['password'];
        
        $sql = "INSERT INTO `user`(firstname, lastname, age ,email, phone, password) VALUES ('$firstname', '$lastname', '$age' , '$email', $phone,'$password')";
        

        if ($db->query($sql) === TRUE) {
            $_SESSION['message']=array("text"=>"User successfully created.","alert"=>"info");
            $db = null;
            header('location:login.php');
          } else {
            echo "Error: " . $sql . "<br>" . $db->error;
          }
        
    }else{
        echo "
            <script>alert('Please fill up the required field!')</script>
            <script>window.location = 'registration.php'</script>
        ";
    }
    
    ?>