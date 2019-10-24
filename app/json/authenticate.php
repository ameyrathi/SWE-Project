<?php
    require_once '../include/common.php';
    require_once '../include/token.php';

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ("username"), 
    isMissingOrEmpty ("password") ];
    $errors = array_filter($errors);

    if (!isEmpty($errors)) {
        $result = [
            "status" => "error",
            "message" => array_values($errors)
            ];
    } else {
        $username = $_POST["username"];
        $password = $_POST["password"];

        $StudentDAO = new StudentDAO();

        if(($StudentDAO->validUser($username)) == true) { // if username exists in database
            if($StudentDAO->getPassword($username) == $password) { // if password is correct
                $token = generate_token($username);
                $result = [
                    "status" => "success",
                    "token" => $token
                ];
            } else { // if username is correct but password is wrong
                $result = [
                    "status" => "error",
                    "message" => ["invalid password"]
                    ];
            }
        } else { // if username not in database
            $result = [
                "status" => "error",
                "message" => ["invalid username"]
                ];
        }
    }

    if(isset($result["message"])) {
        sort($result["message"]);
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>