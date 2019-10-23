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
                    }
                    else{
            
                        $studentdao = new StudentDAO();
                        $coursedao = new CourseDAO();
                        $sectiondao = new SectionDAO();
                        $biddingrounddao = new BiddingRoundDAO();
                        $biddao = new BidDAO();
            
                        $userid = $tempArr["userid"];
                        $course = $tempArr["course"];
                        $section = $tempArr["section"];
                        
                        $status = $biddingrounddao->get_status();
                        $round = $biddingrounddao->get_round();
            
                        //invalid course check
                        if(!$coursedao->get_course($course)){
                            array_push($errors, "invalid course");
                        }
            
                        //invalid userid check
                        if(!$studentdao->validUser($userid)){
                            array_push($errors, "invalid userid");
                        }
            
                        //invalid section check
                        if($coursedao->get_course($course)){
                            if(!$sectiondao->is_valid_section($course, $section)){
                                array_push($errors, "invalid section");
                            }
                        }
            
                        //round ended
                        if($status != "Ongoing"){
                            array_push($errors, "round ended");
                        }
            
                        //no such bid
                        if($status == "Ongoing"){
                            if($studentdao->validUser($userid)){
                                if($coursedao->get_course($course)){
                                    if($sectiondao->is_valid_section($course, $section)){
                                        if(!$biddao->bid_already_exists($userid, $course, $section, 1)){
                                            array_push($errors, "no such bid");
                                        }
                                    }
                                }
                            }
                        }
            
                        if(!isEmpty($errors)){
                            $result = [
                                "status" => "error",
                                "message" => $errors
                            ];
                        }
                        else{
                            $amount = $biddao->bid_already_exists($userid, $course, $section, $round)[1];
                            $success = $biddao->drop_bid($userid, $course, $round);
                            if($success){
                                $studentdao->add_balance($userid, $amount);
                                $result = [
                                    "status" => "success"
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