<?php

    require_once '../include/common.php';
    require_once '../include/token.php';

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ("r")];
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
                        $coursedao = new CourseDAO();
                        $sectiondao = new SectionDAO();
                        $biddao = new BidDAO();
                        $biddingrounddao = new BiddingRoundDAO();
                        $successfuldao = new SuccessfulDAO();
        
                        $course = $tempArr["course"];
                        $section = $tempArr["section"];
        
                        $round = $biddingrounddao->get_current_round();
        
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
        
                        if(!isEmpty($errors)){
                            $result =[
                                "status" => "error",
                                "message" => $errors
                            ];
                        }
                        else{
                            $success = [];
        
                            if($round == 1.5 || $round == 2 ){
                                $bids = $successfuldao->retrieve_successful_bids($course, $section, 1);
                                $sortclass = new Sort();
                                
                                $bids = $sortclass->sort_it($bids, "section_dump");
                                for($i=0; $i<count($bids); $i++){
                                    $userid = $bids[$i][0];
                                    $amount = $bids[$i][1];
        
                                    $res = [
                                        "userid" => $userid,
                                        "amount" => $amount,
                                    ];
        
                                    array_push($success, $res);
                                }
        
                                $result =[
                                    "status" => "success",
                                    "students" => $success
                                ];
                            }
                            else if($round == 2.5){
                                $round1_bids = $successfuldao->retrieve_successful_bids($course, $section, 1);
                                $round2_bids = $successfuldao->retrieve_successful_bids($course, $section, 2);
                                $bids = array_merge($round1_bids, $round2_bids);
                                $sortclass = new Sort();
                                
                                $bids = $sortclass->sort_it($bids, "section_dump");
                                for($i=0; $i<count($bids); $i++){
                                    $userid = $bids[$i][0];
                                    $amount = $bids[$i][1];
        
                                    $res = [
                                        "userid" => $userid,
                                        "amount" => $amount,
                                    ];
        
                                    array_push($success, $res);
                                }
        
                                $result =[
                                    "status" => "success",
                                    "students" => $success
                                ];
                            }
                            else{
                                if($round == 1){
                                    $result =[
                                        "status" => "error",
                                        "message" => ["round 1 ongoing"]
                                    ];
                                }
                                else{
                                    if($round == 0.5){
                                        $result =[
                                            "status" => "error",
                                            "message" => ["round 1 not started"]
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>