<?php

    require_once '../include/common.php';
    require_once '../include/token.php';
    require_once 'json_process_min_bid.php';
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
                $biddao = new BidDAO();
                $biddingrounddao = new BiddingRoundDAO();
                $successfuldao = new SuccessfulDAO();
                $unsuccessfuldao = new UnsuccessfulDAO();
                $sectionresultsdao = new SectionResultsDAO();

                $course = $tempArr["course"];
                $section = $tempArr["section"];

                $status = $biddingrounddao->get_status();
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
                    sort($errors);
                    $result = [
                        "status" => "error",
                        "message" => $errors
                    ];
                }
                else{
                    if($round == 1){
                        $vacancy = $sectiondao->get_size($course, $section);
                        $bids = $biddao->retrieve_course_section_bids($course, $section, 1);
                        $sortclass = new Sort();

                        if(count($bids) === 0){
                            $min_bid_amount = 10;
                        }
                        else{
                            $bids_sorted_by_amount = $sortclass->sort_it($bids, "min_bid");

                            if(count($bids) > $vacancy){
                                $new_bids = array_slice($bids_sorted_by_amount, 0, $vacancy);
                                $min_bid_amount = end($new_bids)[1];
                            }
                            else{
                                $min_bid_amount = end($bids_sorted_by_amount)[1];
                            }
                        }

                        $students = [];

                        for($i=0; $i<count($bids); $i++){
                            $userid = $bids[$i][0];
                            $amount = $bids[$i][1];

                            $temp = [
                                "userid" => $userid,
                                "amount" => floatval($amount),
                                "balance" => floatval($studentdao->get_balance($userid)),
                                "status" => "pending"
                            ];

                            array_push($students, $temp);
                        }

                        $students = $sortclass->sort_it($students, "bid_status");

                        $result = [
                            "status" => "success",
                            "vacancy" => $vacancy,
                            "min-bid-amount" => floatval($min_bid_amount),
                            "students" => $students
                        ];
                    }
                    elseif($round == 1.5){
                        $vacancy = $sectionresultsdao->get_available_seats($course, $section);

                        $size = $sectiondao->get_size($course, $section);
                        $bids = $biddao->retrieve_course_section_bids($course, $section, 1);
                        $successful_bids = $successfuldao->retrieve_sort_this_section_bids($course, $section, 1);
                        $unsuccessful_bids = $unsuccessfuldao->retrieve_unsuccessful_bids($course, $section, 1);
                        $sortclass = new Sort();

                        if(count($bids) === 0 || count($successful_bids) === 0){
                            $min_bid_amount = 10;
                        }
                        else{
                            $min_bid_amount = end($successful_bids)[1];
                        }

                        $students = [];

                        for($i=0; $i<count($successful_bids); $i++){
                            $userid = $successful_bids[$i][0];
                            $amount = $successful_bids[$i][1];

                            $temp = [
                                "userid" => $userid,
                                "amount" => floatval($amount),
                                "balance" => floatval($studentdao->get_balance($userid)),
                                "status" => "success"
                            ];

                            array_push($students, $temp);
                        }

                        for($i=0; $i<count($unsuccessful_bids); $i++){
                            $userid = $unsuccessful_bids[$i][0];
                            $amount = $unsuccessful_bids[$i][1];

                            $temp = [
                                "userid" => $userid,
                                "amount" => floatval($amount),
                                "balance" => floatval($studentdao->get_balance($userid)),
                                "status" => "fail"
                            ];

                            array_push($students, $temp);
                        }

                        $students = $sortclass->sort_it($students, "bid_status");

                        $result = [
                            "status" => "success",
                            "vacancy" => $vacancy,
                            "min-bid-amount" => floatval($min_bid_amount),
                            "students" => $students
                        ];
                    }
                    elseif($round == 2){
                        $vacancy = $sectionresultsdao->get_available_seats($course, $section);
                        $sortclass = new Sort();
                        $bids = $biddao->retrieve_course_section_bids($course, $section, 2);

                        $min_bid_amount = process_min_bid($course, $section);
                        
                        $students = [];

                        for($i=0; $i<count($bids); $i++){
                            $userid = $bids[$i][0];
                            $amount = $bids[$i][1];
                            $status = $biddao->get_round2_bid_status($userid, $course);

                            if($status == "Pending, successful") {
                                $status = "success";
                            } elseif($status == "Pending, fail") {
                                $status = "fail";
                            } else {
                                $status = "ERROR ROUND 2 STATUS, TO DEBUG";
                            }

                            $temp = [
                                "userid" => $userid,
                                "amount" => floatval($amount),
                                "balance" => floatval($studentdao->get_balance($userid)),
                                "status" => $status
                            ];

                            array_push($students, $temp);
                        }

                        $students = $sortclass->sort_it($students, "bid_status");

                        $result = [
                            "status" => "success",
                            "vacancy" => $vacancy,
                            "min-bid-amount" => floatval($min_bid_amount),
                            "students" => $students
                        ];
                    }
                    else{
                        if($round == 2.5){
                            $num_round1_successful_bids = count($successfuldao->retrieve_successful_bids($course, $section, 1));

                            $num_round2_successful_bids = count($successfuldao->retrieve_successful_bids($course, $section, 2));

                            $vacancy = $sectiondao->get_size($course, $section) - ($num_round1_successful_bids + $num_round2_successful_bids);

                            $sortclass = new Sort();
                            $round1_bids = $successfuldao->retrieve_successful_bids($course, $section, 1);
                            $round2_bids = $successfuldao->retrieve_successful_bids($course, $section, 2);
                            $bids = array_merge($round1_bids, $round2_bids);

                            $round2_bids_sorted_by_amount = $sortclass->sort_it($round2_bids, "min_bid");

                            if(count($round2_bids) === 0){
                                $min_bid_amount = 10;
                            }
                            else{
                                $min_bid_amount = end($round2_bids_sorted_by_amount)[1];
                            }

                            $students = [];

                            for($i=0; $i<count($bids); $i++){
                                $userid = $bids[$i][0];
                                $amount = $bids[$i][1];

                                $temp = [
                                    "userid" => $userid,
                                    "amount" => floatval($amount),
                                    "balance" => floatval($studentdao->get_balance($userid)),
                                    "status" => "success"
                                ];

                                array_push($students, $temp);
                            }

                            $students = $sortclass->sort_it($students, "bid_status");

                            $result = [
                                "status" => "success",
                                "vacancy" => $vacancy,
                                "min-bid-amount" => floatval($min_bid_amount),
                                "students" => $students
                            ];
                        }
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
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);

?>