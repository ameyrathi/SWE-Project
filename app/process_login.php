<?php
require_once 'include/common.php';
require_once 'include/protect.php';


$username = $_POST["username"];
$password = $_POST["password"];

$_SESSION["userid_attempt"] = $username;

if(empty($username) && empty($password)){
    $_SESSION["errors"] = "Enter username and password";
    header("Location: login.php");
    die;
}
if(empty($username)){
    $_SESSION["errors"] = "Enter username";
    header("Location: login.php");
    die;
}
if(empty($password)){
    $_SESSION["errors"] = "Enter password";
    header("Location: login.php");
    die;
}


$StudentDAO = new StudentDAO();
if(strtolower($username) == "admin"){
    if($admin = $StudentDAO->adminLogin($password)){
        $_SESSION["userid"] = $username;
        $token = generate_token($username);
        header("Location: admin_home.php?token=$token");
    }
    else{
        $_SESSION["errors"]="Incorrect Password";
        header("Location: login.php");
    }
}
else{
    $userValid = $StudentDAO->validUser($username);
    if($userValid!=1){
        $_SESSION["errors"] = "Invalid Username";
        header('Location: login.php');
        die;
    }
    $db_password = $StudentDAO->getPassword($username);
    if($password == $db_password){
        $_SESSION["userid"] = $username;
        $token = generate_token($username);
        header("Location: student_home.php?token=$token");
    }
    else{
        $_SESSION["errors"]="Invalid Password";
        header("Location: login.php");
    }
}


?>