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
                        $coursedao = new CourseDAO();
                        $sectiondao = new SectionDAO();
                        $biddao = new BidDAO();
                        $biddingrounddao = new BiddingRoundDAO();
                        $successfuldao = new SuccessfulDAO();
                        $unsuccessfuldao = new UnsuccessfulDAO();
        
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
        
                        if(!isEmpty($errors)){
                            $result =[
                                "status" => "error",
                                "message" => $errors
                            ];
                        }
                        else{
        
                            $success = [];
        
                            if($status == "Ongoing"){
                                $bids = $biddao->retrieve_course_section_bids($course, $section, $round);
                                $sortclass = new Sort();
        
                                $bids = $sortclass->sort_it($bids, "bid_dump");
                                for($i=0; $i<count($bids); $i++){
                                    $userid = $bids[$i][0];
                                    $amount = $bids[$i][1];
        
                                    $res = [
                                        "row" => $i+1,
                                        "userid" => $userid,
                                        "amount" => $amount,
                                        "result" => "-"
                                    ];
        
                                    array_push($success, $res);
                                }
        
                                $result =[
                                    "status" => "success",
                                    "bids" => $success
                                ];
                            }
                            else if($status == "Ended"){
        
                                $successful_bids = $successfuldao->retrieve_successful_bids($course, $section, $round);
        
                                for($i=0; $i<count($successful_bids); $i++){
                                    array_push($successful_bids[$i], "in");
                                }
        
                                $unsuccessful_bids = $unsuccessfuldao->retrieve_unsuccessful_bids($course, $section, $round);
        
                                for($i=0; $i<count($unsuccessful_bids); $i++){
                                    array_push($unsuccessful_bids[$i], "out");
                                }
        
                                $bids = array_merge($successful_bids, $unsuccessful_bids);
                                $sortclass = new Sort();
                                
                                $bids = $sortclass->sort_it($bids, "bid_dump");
                                for($i=0; $i<count($bids); $i++){
                                    $userid = $bids[$i][0];
                                    $amount = $bids[$i][1];
                                    $result = $bids[$i][4];
        
                                    $res = [
                                        "row" => $i+1,
                                        "userid" => $userid,
                                        "amount" => $amount,
                                        "result" => $result
                                    ];
        
                                    array_push($success, $res);
                                }
        
                                $result =[
                                    "status" => "success",
                                    "bids" => $success
                                ];
                            }
                            else{
                                if($status == "Not Started"){
                                    if($round == 1){
                                        $result =[
                                            "status" => "error",
                                            "message" => ["no active round"]
                                        ];
                                    }
                                    else{
                                        $successful_bids = $successfuldao->retrieve_successful_bids($course, $section, 1);
        
                                        for($i=0; $i<count($successful_bids); $i++){
                                            array_push($successful_bids[$i], "in");
                                        }
                
                                        $unsuccessful_bids = $unsuccessfuldao->retrieve_unsuccessful_bids($course, $section, 1);
                
                                        for($i=0; $i<count($unsuccessful_bids); $i++){
                                            array_push($unsuccessful_bids[$i], "out");
                                        }
                
                                        $bids = array_merge($successful_bids, $unsuccessful_bids);
                                        $sortclass = new Sort();
                                        
                                        $bids = $sortclass->sort_it($bids, "bid_dump");
                                        for($i=0; $i<count($bids); $i++){
                                            $userid = $bids[$i][0];
                                            $amount = $bids[$i][1];
                                            $result = $bids[$i][4];
                
                                            $res = [
                                                "row" => $i+1,
                                                "userid" => $userid,
                                                "amount" => $amount,
                                                "result" => $result
                                            ];
                
                                            array_push($success, $res);
                                        }
                
                                        $result =[
                                            "status" => "success",
                                            "bids" => $success
                                        ];
                                    }
                                }
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