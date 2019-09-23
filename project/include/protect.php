<?php
require_once 'token.php';
require_once 'common.php';

$username = '';
if  (isset($_SESSION['userid'])) {
	$username = $_SESSION['userid'];
} else { // user not logged in. session variable not found
    header("Location: login.php?error='Credentials not correct'");
}

# check if the username session variable has been set 
# send user back to the login page with the appropriate message if it was not

# add your code here 

?>