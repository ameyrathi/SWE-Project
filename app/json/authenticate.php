<?php
    require_once '../include/common.php';
    require_once '../include/token.php';
    $sortclass = new Sort();

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ("username"), isMissingOrEmpty ("password") ];
    $errors = array_filter($errors);

    $errors = $sortclass->sort_it($errors, "common_validation");

    if(!isEmpty($errors)){
        $result = [
            "status" => "error",
            "message" => array_values($errors)
        ];
    }
    else{
        $username = $_POST["username"];
        $password = $_POST["password"];

        $studentdao = new StudentDAO();

        if(strtolower($username) == "admin"){
            if($studentdao->adminLogin($password) == $password){
                $token = generate_token($username);
                $result = [
                    "status" => "success",
                    "token" => $token
                ];
            }
            else{
                $result = [
                    "status" => "error",
                    "message" => ["invalid password"]
                ];
            }
        }
        else{
            $result = [
                "status" => "error",
                "message" => ["invalid username"]
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>