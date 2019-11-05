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

                if(!array_key_exists("course", $tempArr)){
                    array_push($errors, "missing course");
                }

                if(!array_key_exists("section", $tempArr)){
                    array_push($errors, "missing section");
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
                if(isEmpty($errors)) {
                    if(!$biddao->bid_already_exists($userid, $course, $section, $round)){
                        array_push($errors, "no such bid");
                    }
                }
    
                if(!isEmpty($errors)){
                    sort($errors);
                    $result = [
                        "status" => "error",
                        "message" => $errors
                    ];
                }
                else{
                    $amount = $biddao->get_amount($userid, $course, $section, $round);
                    $success = $biddao->drop_bid($userid, $course, $round) && $studentdao->add_balance($userid, $amount);

                    if($success){
                        $result = [
                            "status" => "success"
                        ];
                    }
                }
            }
        }
        else{
            $result = [
                "status" => "error",
                "message" => ["HTTP REQUEST NOT FOUND"]
            ];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>