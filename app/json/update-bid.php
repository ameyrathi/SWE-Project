<?php

    require_once '../include/common.php';
    require_once '../include/token.php';
    require_once '../process_add_bid.php';

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
                        $biddao = new BidDAO();
                        $prerequisitedao = new PrerequisiteDAO();
                        $coursecompleteddao = new CourseCompletedDAO();
                        $biddingrounddao = new BiddingRoundDAO();
                        $successfuldao = new SuccessfulDAO();
            
                        $userid = $tempArr["userid"];
                        $amount = $tempArr["amount"];
                        $course = $tempArr["course"];
                        $section = $tempArr["section"];

                        $round = $biddingrounddao->get_round();
                        $status = $biddingrounddao->get_status();

                        //invalid userid check
                        if(!$studentdao->validUser($userid)){
                            array_push($errors, "invalid userid");
                        }
            
                        //invalid amount
                        if(is_numeric($amount)){
                            if((int)$amount < 10 || strlen(substr(strrchr($amount, "."), 1)) > 2){
                                array_push($errors, "invalid amount");
                            }
                        }
                        else{
                            array_push($errors, "invalid amount");
                        }
                        
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
            
                            if($biddao->bid_already_exists($userid, $course, $section, $round)){
                                $studentdao->add_balance($userid, $amount);
                                if($amount > $studentdao->get_balance($userid)){
                                    array_push($errors, "insufficient e$");
                                }
            
                                //bid too low
                                if($round == 2){
                                    $min_bid = process_min_bid($course, $section);
                                    if($amount < $min_bid){
                                        array_push($errors, "bid too low");
                                    }
                                }
                            }

                            $pending_bidded_sections = $biddao->get_pending_bids_and_amount($userid, $round);

                            //class timetable clash
                            //exam timetable clash
                            if(!$biddao->bid_already_exists($userid, $course, $section, $round)){
                                $no_clash_check_success = true;
                                if($sectiondao->is_valid_section($course, $section)){
                                    $bidding_class = $sectiondao->get_class_day_start_end($course, $section);
                                    foreach($pending_bidded_sections as $this_list) {
                                        $existing_courseid = $this_list[0];
                                        $existing_section = $this_list[1];
                        
                                        $existing_class = $sectiondao->get_class_day_start_end($existing_courseid, $existing_section);
                                        $class_clash_check = dont_clash($bidding_class[0], $bidding_class[1], $bidding_class[2], $existing_class[0], $existing_class[1], $existing_class[2]);
                        
                                        $bidding_exam = $coursedao->get_exam_date_start_end($course);
                                        $existing_exam = $coursedao->get_exam_date_start_end($existing_courseid);
                                        $exam_clash_check = dont_clash($bidding_exam[0], $bidding_exam[1], $bidding_exam[2], $existing_exam[0], $existing_exam[1], $existing_exam[2]);
                        
                                        $no_clash_check_success = $class_clash_check && $exam_clash_check;
                        
                                        if(!$no_clash_check_success) {
                                            $no_clash_check_success = false;
                                            if(!$class_clash_check) {
                                                array_push($errors, "class timetable clash");
                                            }
                                            if(!$exam_clash_check) {
                                                array_push($errors, "exam timetable clash");
                                            }
                                        }
                                    }
                                }
                            }     
            
                            //incomplete prerequisites
                            $prerequisites_needed = $prerequisitedao->get_prerequisite_courses($course);
                            $student_completed_courses = $coursecompleteddao->get_completed_courses($userid);
                            foreach($prerequisites_needed as $this_prerequisite) {
                                if(!in_array($this_prerequisite, $student_completed_courses)) {
                                    array_push($errors, "incomplete prerequisites");
                                }
                            }
            
                            //round ended
                            if($status != "Ongoing"){
                                array_push($errors, "round ended");
                            }

                            //course completed
                            if(in_array($course, $student_completed_courses)){
                                array_push($errors, "course completed");
                            }
            
                            //course enrolled
                            if($round == 2){
                                $enrolled = $successfuldao->check_success($userid, $course, $section, 1);
                                if($enrolled){
                                    array_push($errors, "course enrolled");
                                }
                            }
            
                            //section limit reached
                            if(!$max_course_check_success = count($pending_bidded_sections) < 5) {
                                array_push($errors, "section limit reached");
                            }
            
                            //not own school course
                            if($round != 2){
                                if($coursedao->get_school($course) != $studentdao->get_school($userid)) {
                                    array_push($errors, "not own school course");
                                }
                            }
            
                            //no vacancy
                            if($round == 2){
                                $seats = $sectionresultsdao->get_available_seats($course, $section);
                                if($seats < 1){
                                    array_push($errors, "no vacancy");
                                }
                            }
                            
            
                            if(!isEmpty($errors)){
                                $result =[
                                    "status" => "error",
                                    "message" => $errors
                                ];
                            }
                            else{
                                if($biddao->bid_already_exists($userid, $course, $section, $round)){
                                    $success = $biddao->update_bid($userid, $amount, $course, $section, $round);
                                    if($success){
                                        $studentdao->deduct_balance($userid, $amount);
                                        $result = [
                                            "status" => "success"
                                        ];
                                    }
                                }
                                else{
                                    $success = $biddao->add_bid($userid, $amount, $course, $section, $round);
                                    if($success){
                                        $studentdao->deduct_balance($userid, $amount);
                                        $result =[
                                            "status" => "success"
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
    
    function process_min_bid($course, $section) {
        $successfuldao = new SuccessfulDAO();
        $biddao = new BidDAO();
        $sectionresultsdao = new SectionResultsDAO();
    
    
        $current_min_bid = $sectionresultsdao->get_min_bid($course, $section);
        $current_available_seats = $sectionresultsdao->get_available_seats($course, $section);
    
        $round1_successful_bids = $successfuldao->retrieve_sort_this_section_bids($course, $section, 1);
        $round2_pending_bids = $biddao->retrieve_sort_this_section_bids($course, $section, 2);
    
        $num_round2_pending_bids = count($round2_pending_bids);
    
        // echo "<h2>Round 2 list of bids:</h2>";
        // var_dump($round2_pending_bids);
    
        // echo "<br>";
    
        // echo "Round 2 bids: $num_round2_pending_bids<br>";
        // echo "Round 2 available seats: $current_available_seats<br>";
    
        // Case 1: If there are less than N bids for the section (where N is the total available seats)
        // The minimum bid value remains the same
        if($num_round2_pending_bids < $current_available_seats) { // if num of round 2 bids < round 2 available seats
            foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
            }
    
            return $current_min_bid;
    
        } elseif($num_round2_pending_bids == $current_available_seats) { // if num of round 2 bids = round 2 available seats
            $clearing_price = $round2_pending_bids[$num_round2_pending_bids-1][1];
    
            $num_clearing_price_bids = 0;
    
            foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                if($this_amount == $clearing_price) {
                    $num_clearing_price_bids++;
                }
                if($num_clearing_price_bids > 1) {
                    break;
                }
            }
    
            if($num_clearing_price_bids == 1) { // only 1 bid at clearing price, so all succeed
                foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                    $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
                }
            } else { // if more than 1 bid at clearing price, aka all clearing price bids fail
                foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                    if($this_amount == $clearing_price) {
                        $biddao->update_round2_bid_status($this_userid, $course, "Pending, fail");
                    } else {
                        $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
                    }
                }
            }
    
            // return new minimum bid
            if($current_min_bid < ($clearing_price+1)) {
                $new_min_bid = $clearing_price + 1;
                $sectionresultsdao->update_min_bid($course, $section, $new_min_bid);
                return $new_min_bid;
            } else {
                return $current_min_bid;
            }
    
        } else { // if num of round 2 bids > round 2 available seats
            $clearing_price = $round2_pending_bids[$current_available_seats-1][1];
    
            $num_clearing_price_bids = 0;
    
            foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                if($this_amount == $clearing_price) {
                    $num_clearing_price_bids++;
                }
                if($num_clearing_price_bids > 1) {
                    break;
                }
            }
    
            if($num_clearing_price_bids == 1) { // only 1 bid at clearing price, so all within capacity succeed
                foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                    if($this_amount >= $clearing_price) {
                        $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
                    } else {
                        $biddao->update_round2_bid_status($this_userid, $course, "Pending, fail");
                    }
                }
            } else { // if more than 1 bid at clearing price, aka all clearing price bids fail
                foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                    if($this_amount > $clearing_price) {
                        $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
                    } else {
                        $biddao->update_round2_bid_status($this_userid, $course, "Pending, fail");
                    }
                }
            }
    
            if($current_min_bid < ($clearing_price+1)) {
                $new_min_bid = $clearing_price + 1;
                $sectionresultsdao->update_min_bid($course, $section, $new_min_bid);
                return $new_min_bid;
            } else {
                return $current_min_bid;
            }
        }
    }

?>