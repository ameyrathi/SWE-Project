<?php

    require_once '../include/common.php';
    require_once '../include/token.php';
    $sortclass = new Sort();

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ("token"), isMissingOrEmpty ("r")];
    $errors = array_filter($errors);
    
    if(isset($_GET["token"]) && $_GET["token"] != "") {
        if(!verify_token($_GET["token"])) { // if invalid token
            $errors[] = "invalid token";
        }
    }

    // if there are common validation errors
    if(!isEmpty($errors)) {
        $errors = $sortclass->sort_it($errors, "common_validation");

        $result = [
            "status" => "error",
            "message" => array_values($errors)
        ];
    } else { // if there are no common validation errors
        if(isset($_GET["r"])){

            $tempArr = json_decode($_GET["r"], true);
    
            $errors = [];
    
            if(!is_array($tempArr)){
                array_push($errors, "invalid format");
            }
            else{
                foreach($tempArr as $key => $value){
                    if(str_replace(' ', '' , $value) == ''){
                        array_push($errors, "blank $key");
                    }
                }
                
                if(!array_key_exists("userid", $tempArr)){
                    array_push($errors, "missing userid");
                }
            }
    
            if(!isEmpty($errors)){
                $errors = $sortclass->sort_it($errors, "common_validation");
                $result = [
                    "status" => "error",
                    "message" => $errors
                ];
            }
            else{
                $studentdao = new StudentDAO();

                $userid = $tempArr["userid"];

                //invalid userid check
                if(!$studentdao->validUser($userid)){
                    array_push($errors, "invalid userid");
                }

                if(!isEmpty($errors)){
                    sort($errors);
                    $result = [
                        "status" => "error",
                        "message" => $errors
                    ];
                }
                else{
                    $student = $studentdao->retrieve_student($userid);
                    $userid = $student[0];
                    $password = $student[1];
                    $name = $student[2];
                    $school = $student[3];
                    $edollar = $student[4];

                    $result = [
                        "status" => "success",
                        "userid" => $userid,
                        "password" => $password,
                        "name" => $name,
                        "school" => $school,
                        "edollar" => floatval($edollar)
                    ];
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);

?>