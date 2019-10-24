<?php

    require_once '../include/common.php';
    require_once '../include/token.php';

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ("token"), isMissingOrEmpty ("r")];
    $errors = array_filter($errors);

    if (!isEmpty($errors)) { // if missing or empty token
        $result = [
            "status" => "error",
            "message" => array_values($errors)
        ];
        sort($result["message"]);
    }
    else{
        if(isset($_GET["token"])) {
            if(!verify_token($_GET["token"])) { // if invalid token
                $result = [
                    "status" => "error",
                    "message" => ["invalid token"]
                ];
            }
            else{
                if(isset($_GET["r"])){

                    $tempArr = json_decode($_GET["r"], true);
            
                    $errors = [];
            
                    foreach($tempArr as $key => $value){
                        if(str_replace(' ', '' , $value) == ''){
                            array_push($errors, "blank $key");
                        }
                    }
            
                    if(!isEmpty($errors)){
                        $result =[
                            "status" => "error",
                            "message" => $errors
                        ];
                        sort($result["message"]);
                    }
                    else{
                        $studentdao = new StudentDAO();
        
                        $userid = $tempArr["userid"];
        
                        //invalid userid check
                        if(!$studentdao->validUser($userid)){
                            array_push($errors, "invalid userid");
                        }
        
                        if(!isEmpty($errors)){
                            $result =[
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
                                "edollar" => $edollar
                            ];
                        }
                    }
                }
            }
        }
        else{
            $result =[
                "status" => "error",
                "message" => ["HTTP REQUEST NOT FOUND"]
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>