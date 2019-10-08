<?php

    function dont_clash($date1, $start_time1, $end_time1, $date2, $start_time2, $end_time2) {
        /**
         * checks if two classes/exams clash
         * @param string $date1 date of first event
         * @param string $start_time1 start time of first event
         * @param string $end_time1 end time of first event
         * @param string $date2 date of second event
         * @param string $start_time2 start time of second event
         * @param string $end_time2 end time of second event
         * @return boolean true if events don't clash, false if events clash
         */

            $start_time1 = strtotime($start_time1);
            $end_time1 = strtotime($end_time1);
            $start_time2 = strtotime($start_time2);
            $end_time2 = strtotime($end_time2);

            if($date1 == $date2) {
                $dont_clash = ($end_time1 <= $start_time2) || ($start_time1 >= $end_time2);
            } else {
                $dont_clash = true;
            }

            return $dont_clash;
        }


    function round1_bid_check($amount, $courseid, $section) {

        $coursedao = new CourseDAO();
        $sectiondao = new SectionDAO();
        $biddao = new BidDAO();
        $prerequisitedao = new PrerequisiteDAO();
        $coursecompleteddao = new CourseCompletedDAO();
        $studentdao = new StudentDAO();

        $errors = [];
        $is_valid_section = true;
        $balance = $studentdao->get_balance($_SESSION["userid"]);

        if(!$sectiondao->is_valid_section($courseid, $section)) {
            $is_valid_section = false;
            array_push($errors, "$courseid $section does not exist.");
        }

        if($amount < 10) {
            array_push($errors, "Minimum bid cannot be less than $10.");
        }

        if($is_valid_section) {

            $courses_completed = $coursecompleteddao->get_completed_courses($_SESSION["userid"]);
            foreach($courses_completed as $course_completed) {
                if($course_completed == $courseid) {
                    $errors[] = "You have already completed course $courseid.";
                    break;
                }
            }

            // "For bidding round 1, the student can only bid for courses offered by his/her own school."
            if($coursedao->get_school($courseid) != $studentdao->get_school($_SESSION["userid"])) {
                array_push($errors, "$courseid is not offered by your school.");
            }

            $pending_bidded_sections = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 1);

            if (!($enough_balance_check_success = $balance >= $amount)) {
                $amount_shortage = $amount - $balance;
                array_push($errors, "You are short of $$amount_shortage.");
            }

            if(!$max_course_check_success = count($pending_bidded_sections) < 5) {
                array_push($errors, "You have already bidded for 5 modules.");
            }

            
            $one_section_check_success = true; # assume student didn't bid for this course already
            foreach($pending_bidded_sections as $this_list) {
                $existing_courseid = $this_list[0];
                $existing_section = $this_list[1];
                if($existing_courseid == $courseid) {
                    $one_section_check_success = false;
                    if($existing_section != $section) {
                        array_push($errors, "You have already bidded for another section ($existing_section) of this course.");
                    } else {
                        array_push($errors, "You have already bidded for this specific section of this course.");
                    }
                }
            }

            $bidding_class = $sectiondao->get_class_day_start_end($courseid, $section); # desired class day + time
            $no_clash_check_success = true; # assume no clash first

            foreach($pending_bidded_sections as $this_list) {
                $existing_courseid = $this_list[0];
                $existing_section = $this_list[1];

                $existing_class = $sectiondao->get_class_day_start_end($existing_courseid, $existing_section);
                $class_clash_check = dont_clash($bidding_class[0], $bidding_class[1], $bidding_class[2], $existing_class[0], $existing_class[1], $existing_class[2]);

                $bidding_exam = $coursedao->get_exam_date_start_end($courseid);
                $existing_exam = $coursedao->get_exam_date_start_end($existing_courseid);
                $exam_clash_check = dont_clash($bidding_exam[0], $bidding_exam[1], $bidding_exam[2], $existing_exam[0], $existing_exam[1], $existing_exam[2]);

                $no_clash_check_success = $class_clash_check && $exam_clash_check;

                if(!$no_clash_check_success) {
                    $no_clash_check_success = false;
                    if(!$class_clash_check) {
                        array_push($errors, "Class of desired section ($courseid, $section) clashes with existing section ($existing_courseid, $existing_section).");
                    }
                    if(!$exam_clash_check) {
                        array_push($errors, "Exam of desired section ($courseid, $section) clashes with existing section ($existing_courseid, $existing_section).");
                    }
                }           
            } 

            $prerequisite_courses = $prerequisitedao->get_prerequisite_courses($courseid);
            $completed_courses = $coursecompleteddao->get_completed_courses($_SESSION["userid"]);

            $prerequisite_check_success = true; # assume fulfill prerequisites first

            foreach($prerequisite_courses as $this_prerequisite) {
                if(!in_array($this_prerequisite, $completed_courses)) {
                    $prerequisite_check_success = false;
                    array_push($errors, "You have not completed prerequisite course: $this_prerequisite.");
                }
            }
        }

        if(empty($errors)) {
            if($add_bid_success = ($biddao->add_bid($_SESSION["userid"], $amount, $courseid, $section, 1)) && $studentdao->deduct_balance($_SESSION["userid"], $amount)) {
                return "success";
            }
        }
        return $errors;
    }

    function round2_bid_check($amount, $courseid, $section) {
        require_once 'process_min_bid.php';

        $coursedao = new CourseDAO();
        $sectiondao = new SectionDAO();
        $biddao = new BidDAO();
        $prerequisitedao = new PrerequisiteDAO();
        $coursecompleteddao = new CourseCompletedDAO();
        $studentdao = new StudentDAO();
        $successfuldao = new SuccessfulDAO();
        $sectionresultsdao = new SectionResultsDAO();

        $errors = [];
        $is_valid_section = true;
        $balance = $studentdao->get_balance($_SESSION["userid"]);

        // invalid course / section check
        if(!$sectiondao->is_valid_section($courseid, $section)) {
            $is_valid_section = false;
            array_push($errors, "$courseid $section does not exist.");
        }

        if($is_valid_section) {

            // course already completed check
            $courses_completed = $coursecompleteddao->get_completed_courses($_SESSION["userid"]);
            foreach($courses_completed as $course_completed) {
                if($course_completed == $courseid) {
                    $errors[] = "You have already completed course $courseid.";
                    break;
                }
            }

            $pending_bidded_sections = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 2);

            // insufficient balance check
            if (!($enough_balance_check_success = $balance >= $amount)) {
                $amount_shortage = $amount - $balance;
                array_push($errors, "You are short of $$amount_shortage.");
            }
            
            $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

            // 5 sections limit check
            $total_num_bids = count($pending_bidded_sections) + count($round1_successful_bids);
            if($total_num_bids == 5) {
                array_push($errors, "You have already bidded for 5 modules.");
            }
            
            // check if course / section is already in ROUND 2 PENDING bids
            foreach($pending_bidded_sections as $this_list) {
                $existing_courseid = $this_list[0];
                $existing_section = $this_list[1];
                if($existing_courseid == $courseid) {
                    if($existing_section != $section) {
                        array_push($errors, "You have already bidded for another section ($existing_section) of this course.");
                    } else {
                        array_push($errors, "You have already bidded for this specific section of this course.");
                    }
                }
            }

            // check if course / section is already in ROUND 1 SUCCESSFUL bids
            foreach($round1_successful_bids as [$this_course, $this_section, $this_amount]) {
                if($this_course == $courseid) {
                    if($this_section != $section) {
                        array_push($errors, "You have already successfully bidded for section ($this_section) of this course.");
                    } else {
                        array_push($errors, "You have already successfully bidded for this specific section of this course.");
                    }
                }
            }

            $bidding_class = $sectiondao->get_class_day_start_end($courseid, $section); # desired class day + time
            $no_clash_check_success = true; # assume no clash first

            // check clash with ROUND 2 PENDING bids
            foreach($pending_bidded_sections as $this_list) {
                $existing_courseid = $this_list[0];
                $existing_section = $this_list[1];

                $existing_class = $sectiondao->get_class_day_start_end($existing_courseid, $existing_section);
                $class_clash_check = dont_clash($bidding_class[0], $bidding_class[1], $bidding_class[2], $existing_class[0], $existing_class[1], $existing_class[2]);

                $bidding_exam = $coursedao->get_exam_date_start_end($courseid);
                $existing_exam = $coursedao->get_exam_date_start_end($existing_courseid);
                $exam_clash_check = dont_clash($bidding_exam[0], $bidding_exam[1], $bidding_exam[2], $existing_exam[0], $existing_exam[1], $existing_exam[2]);

                $no_clash_check_success = $class_clash_check && $exam_clash_check;

                if(!$no_clash_check_success) {
                    $no_clash_check_success = false;
                    if(!$class_clash_check) {
                        array_push($errors, "Class of desired section ($courseid, $section) clashes with existing section ($existing_courseid, $existing_section).");
                    }
                    if(!$exam_clash_check) {
                        array_push($errors, "Exam of desired section ($courseid, $section) clashes with existing section ($existing_courseid, $existing_section).");
                    }
                }           
            } 

            // check clash with ROUND 1 SUCCESSFUL bids
            foreach($round1_successful_bids as [$existing_courseid, $existing_section, $this_amount]) {

                $existing_class = $sectiondao->get_class_day_start_end($existing_courseid, $existing_section);
                $class_clash_check = dont_clash($bidding_class[0], $bidding_class[1], $bidding_class[2], $existing_class[0], $existing_class[1], $existing_class[2]);

                $bidding_exam = $coursedao->get_exam_date_start_end($courseid);
                $existing_exam = $coursedao->get_exam_date_start_end($existing_courseid);
                $exam_clash_check = dont_clash($bidding_exam[0], $bidding_exam[1], $bidding_exam[2], $existing_exam[0], $existing_exam[1], $existing_exam[2]);

                $no_clash_check_success = $class_clash_check && $exam_clash_check;

                if(!$no_clash_check_success) {
                    $no_clash_check_success = false;
                    if(!$class_clash_check) {
                        array_push($errors, "Class of desired section ($courseid, $section) clashes with existing section ($existing_courseid, $existing_section).");
                    }
                    if(!$exam_clash_check) {
                        array_push($errors, "Exam of desired section ($courseid, $section) clashes with existing section ($existing_courseid, $existing_section).");
                    }
                }           
            } 

            $prerequisite_courses = $prerequisitedao->get_prerequisite_courses($courseid);
            $completed_courses = $coursecompleteddao->get_completed_courses($_SESSION["userid"]);

            $prerequisite_check_success = true; # assume fulfill prerequisites first

            foreach($prerequisite_courses as $this_prerequisite) {
                if(!in_array($this_prerequisite, $completed_courses)) {
                    $prerequisite_check_success = false;
                    array_push($errors, "You have not completed prerequisite course: $this_prerequisite.");
                }
            }

            $min_bid = $sectionresultsdao->get_min_bid($courseid, $section);
            if($amount < $min_bid) {
                $errors[] = "Please bid higher than the minimum bid of $$min_bid.";
            }
        }

        if(empty($errors)) {
            if($add_bid_success = ($biddao->add_bid($_SESSION["userid"], $amount, $courseid, $section, 2)) && $studentdao->deduct_balance($_SESSION["userid"], $amount)) {
                $min_bid = process_min_bid($courseid, $section);
                return "success";
            }
        }
        return $errors;
    }

         

    

?>