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
if($username == "admin"){
    if($admin = $StudentDAO->adminLogin($password)){
        header("Location: admin_home.php");
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
        header("Location: student_home.php");
    }
    else{
        $_SESSION["errors"]="Invalid Password";
        header("Location: login.php");
    }
}


?>