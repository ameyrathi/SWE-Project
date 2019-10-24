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
                        $coursedao = new CourseDAO();
                        $sectiondao = new SectionDAO();
                        $biddao = new BidDAO();
                        $biddingrounddao = new BiddingRoundDAO();
                        $successfuldao = new SuccessfulDAO();
        
                        $userid = $tempArr["userid"];
                        $course = $tempArr["course"];
                        $section = $tempArr["section"];
        
                        $status = $biddingrounddao->get_status();
                        $round = $biddingrounddao->get_round();
        
                        //invalid course check
                        if(!$coursedao->get_course($course)){
                            array_push($errors, "invalid course");
                        }
        
                        //invalid section check
                        if($coursedao->get_course($course)){
                            if(!$sectiondao->is_valid_section($course, $section)){
                                array_push($errors, "invalid section");
                            }
                        }
        
                        //invalid userid check
                        if(!$studentdao->validUser($userid)){
                            array_push($errors, "invalid userid");
                        }
        
                        //round not active
                        if($round == 2){
                            if($status != "Ongoing"){
                                array_push($errors, "round not active");
                            }
                        }
                        else{
                            array_push($errors, "round not active");
                        }
        
                        if(!isEmpty($errors)){
                            $result =[
                                "status" => "error",
                                "message" => $errors
                            ];
                            sort($result["message"]);
                        }
                        else{
                            if($successfuldao->check_success($userid, $course, $section, 1)){
                                $amount = $successfuldao->get_specific_bid($userid, $course, $section, 1);
                                $success = $successfuldao->drop_section($userid, $course, $section);
                                if($success){
                                    $studentdao->add_balance($userid, $amount[1]);
                                    $result = [
                                        "status" => "success"
                                    ];
                                }
                            }
                            else{
                                $result =[
                                    "status" => "error",
                                    "message" => ["no such enrollment found"]
                                ];
                            }
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