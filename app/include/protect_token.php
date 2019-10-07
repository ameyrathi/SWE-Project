<?php
    require_once "token.php";

    function token_gateway($token) {
        if(isset($_SESSION["userid"])) {
            if(verify_token($token) != $_SESSION["userid"]) {
                header("Location: login.php?error='Credentials not correct'");
            }
        } else { // if no $_SESSION["userid"], ie. not logged in
            header("Location: login.php?error='Credentials not correct'");
        }
    }
?>