<?php
    require_once "token.php";

    function token_gateway($token) {
        if(verify_token($token) != $_SESSION["userid"]) {
            header("Location: login.php?error='Credentials not correct'");
        }
    }
?>