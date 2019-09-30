<?php 
session_start();

if(isset($_SESSION["userid_attempt"])) {
    $userid_attempt = $_SESSION["userid_attempt"];
} else {
    $userid_attempt = "";
}

?>
<html>
<link rel="stylesheet" href="stylesheet.css"/>
<body>
<!-- need to enter action -->
<h1>Welcome to BIOS!</h1>
<form method="post" action="process_login.php">
    <table id="login"> 
        <tr>
            <td> Username: </td>
            <td> <input type="text" name="username" value=<?php echo $userid_attempt;?>> </td>
        </tr>
        <tr>
            <td> Password:</td>
            <td> <input type="password" name="password"></td>
        </tr>
    </table>
    <br>
    <input type="submit" value="Login">
</form>

<p id="error">
<?php
if(isset($_SESSION["errors"])){
    echo "Login failed. {$_SESSION['errors']}.";
    $_SESSION=[];
}
?>
</p>
</body>

</html>