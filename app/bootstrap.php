<?php

require_once 'include/common.php';
require_once 'process_add_bid.php';

function doBootstrap() {

    $errors = array();
    $num_record_loaded = array();

    $zip_file = $_FILES["bootstrap-file"]["tmp_name"];

    # Get temp dir on system for uploading
    $temp_dir = sys_get_temp_dir();

    # keep track of number of lines successfully processed for each file
    $bid_processed = 0;
    $course_processed = 0;
    $course_completed_processed = 0;
    $prerequisite_processed = 0;
    $section_processed = 0;
    $student_processed = 0;

    # check file size
    if ($_FILES["bootstrap-file"]["size"] <= 0) {
        $errors[] = "input files not found";
    } else {

        $zip = new ZipArchive; # class that processes zip files
        $res = $zip->open($zip_file);

        if ($res === TRUE) {
            $zip->extractTo($temp_dir);
            $zip->close(); # must close all zip files + delete temporary files

            $bid_path = "$temp_dir/bid.csv";
            $course_path = "$temp_dir/course.csv";
            $course_completed_path = "$temp_dir/course_completed.csv";
            $prerequisite_path = "$temp_dir/prerequisite.csv";
            $section_path = "$temp_dir/section.csv";
            $student_path = "$temp_dir/student.csv";

            $bid_file = @fopen($bid_path, "r");
            $course_file = @fopen($course_path, "r");
            $course_completed_file = @fopen($course_completed_path, "r");
            $prerequisite_file = @fopen($prerequisite_path, "r");
            $section_file = @fopen($section_path, "r");
            $student_file = @fopen($student_path, "r");

            if (empty($bid_file) || empty($course_file) || empty($course_completed_file) || empty($prerequisite_file) || empty($section_file) || empty($student_file)) {
                $errors[] = "input files not found";
                if (!empty($bid_file)){
                    fclose($bid_file);
                    @unlink($bid_path);
                }

                if (!empty($course_file)) {
                    fclose($course_file);
                    @unlink($course_path);
                }

                if (!empty($course_completed_file)) {
                    fclose($course_completed_file);
                    @unlink($course_completed_path);
                }

                if (!empty($prerequisite_file)) {
                    fclose($prerequisite_file);
                    @unlink($prerequisite_path);
                }

                if (!empty($section_file)) {
                    fclose($section_file);
                    @unlink($section_path);
                }

                if (!empty($student_file)) {
                    fclose($student_file);
                    @unlink($student_path);
                }
            } else {
                # create DAOs
                $biddao = new BidDAO();
                $coursedao = new CourseDAO();
                $coursecompleteddao = new CourseCompletedDAO();
                $prerequisitedao = new PrerequisiteDAO();
                $sectiondao = new SectionDAO();
                $studentdao = new StudentDAO();
                $biddingrounddao = new BiddingRoundDAO();
                $successfuldao = new SuccessfulDAO();
                $unsuccessfuldao = new UnsuccessfulDAO();
                $sectionresultsdao = new SectionResultsDAO();
                
                # truncate current SQL tables
                $biddao->removeAll();
                $coursedao->removeAll();
                $coursecompleteddao->removeAll();
                $prerequisitedao->removeAll();
                $sectiondao->removeAll();
                $studentdao->removeAll();
                $successfuldao->removeAll();
                $unsuccessfuldao->removeAll();
                $sectionresultsdao->removeAll();


                // student.csv
                $student_headers_list = fgetcsv($student_file); # skip header
                $student_row_count = 2;

                while(($student_row = fgetcsv($student_file)) != false) { # we want to insert these values into the database
                    $student_row = mb_convert_encoding($student_row,"UTF-8");

                    $student_row_errors = [];

                    $student_row = array_map('trim', $student_row);

                    // blank field(s) check
                    for($i=0; $i<count($student_row); $i++) {
                        if($student_row[$i] == '' ) {
                            array_push($student_row_errors, "blank $student_headers_list[$i]");
                        }
                    }

                    if($student_row_errors == []) { // if common validations pass

                        // then perform file specific validations

                        [$userid, $password, $name, $school, $edollar] = $student_row;
    
                        // invalid userid check
                        if(strlen($userid) > 128) {
                            array_push($student_row_errors, "invalid userid");
                        }
    
                        // duplicate userid check
                        if($studentdao->validUser($userid)) { // if userid already exists
                            array_push($student_row_errors, "duplicate userid");
                        }
    
                        // invalid e-dollar check
                        if(is_numeric($edollar)) {
                            if($edollar < 0){
                                array_push($student_row_errors, "invalid edollar");
                            }
                            else{
                                if(strpos($edollar , ".") != false) {
                                    $decimal_places = strlen(substr(strrchr($edollar, "."), 1));
                                    if($decimal_places > 2) {
                                        array_push($student_row_errors, "invalid edollar");
                                    }
                                }
                            }
                        }
                        else{
                            array_push($student_row_errors, "invalid edollar");
                        }
    
                        // invalid password check
                        if(strlen($password) > 128) {
                            array_push($student_row_errors, "invalid password");
                        }
    
                        // invalid name check
                        if(strlen($name) > 100) {
                            array_push($student_row_errors, "invalid name");
                        }
                    }

                    if(empty($student_row_errors)) {
                        $success = $studentdao->add_student($userid, $password, $name, $school, $edollar);
                        if($success) {
                            $student_processed++;
                        }
                    } else {
                        $student_row_error_string = implode(", ", $student_row_errors);
                        array_push($errors, "Table: student, Row: $student_row_count, $student_row_error_string");
                    }
                    $student_row_count++;
                }
                array_push($num_record_loaded, "student.csv: $student_processed row(s) processed");
                fclose($student_file);
                unlink($student_path);

                // course.csv
                $course_headers_list = fgetcsv($course_file); # skip header
                $course_row_count = 2;

                while(($course_row = fgetcsv($course_file)) != false) {
                    $course_row = mb_convert_encoding($course_row,"UTF-8");
                    $course_row_errors = [];

                    $course_row = array_map('trim', $course_row);

                    // blank field(s) check
                    for($i=0; $i<count($course_row); $i++) {
                        if($course_row[$i] == '') {
                            array_push($course_row_errors, "blank $course_headers_list[$i]");
                        }
                    }

                    if($course_row_errors == []) { // if common validations pass

                        // then perform file specific validations

                        [$course, $school, $title, $description, $exam_date, $exam_start, $exam_end] = $course_row;
    
                        // invalid exam date check
                        if(!validateDate($exam_date)){
                            array_push($course_row_errors, "invalid exam date");
                        }
    
                        // invalid exam start check
                        if(!isValidTime($exam_start)) {
                            array_push($course_row_errors, "invalid exam start");
                        }
    
                        //invalid exam end check
                        if(!isValidTime($exam_end)) {
                            array_push($course_row_errors, "invalid exam end");
                        }
    
                        if(isValidTime($exam_end)){
                            $startdate = strtotime($exam_start);
                            $enddate = strtotime($exam_end);
    
                            if($enddate <= $startdate){
                                array_push($course_row_errors, "invalid exam end");
                            }
                        }
    
                        //invalid title check
                        if(strlen($title) > 100){
                            array_push($course_row_errors, "invalid title");
                        }
    
                        //invalid description check
                        if(strlen($description) > 1000){
                            array_push($course_row_errors, "invalid description");
                        }
                    }


                    if(empty($course_row_errors)) {
                        $success = $coursedao->add_course($course, $school, $title, $description, $exam_date, $exam_start, $exam_end);
                        if($success) {
                            $course_processed++;
                        }
                    } else {
                        $course_row_error_string = implode(", ", $course_row_errors);
                        array_push($errors, "Table: course, Row: $course_row_count, $course_row_error_string");
                    }
                    $course_row_count++;
                }
                array_push($num_record_loaded, "course.csv: $course_processed row(s) processed");
                fclose($course_file);
                unlink($course_path);

                //section.csv
                $section_headers_list = fgetcsv($section_file); # skip header
                $section_row_count = 2;

                while(($section_row = fgetcsv($section_file)) != false) {
                    $section_row = mb_convert_encoding($section_row,"UTF-8");
                    $section_row_errors = [];

                    $section_row = array_map('trim', $section_row);

                    // blank field(s) check
                    for($i=0; $i<count($section_row); $i++) {
                        if($section_row[$i] == '') {
                            array_push($section_row_errors, "blank $section_headers_list[$i]");
                        }
                    }

                    if($section_row_errors == []) { // if common validations pass

                        // then perform file specific validations

                        [$course, $section, $day, $start, $end, $instructor, $venue, $size] = $section_row;

                        $is_valid_course = false;
    
                        // invalid course check
                        if($coursedao->get_course($course) == false){
                            array_push($section_row_errors, "invalid course");
                        } else {
                            $is_valid_course = true;
                        }
    
                        // invalid section check
                        if($is_valid_course){
                            $section_array = explode('S',$section);
                            if(count($section_array) == 2){
                                if($section_array[0] == ''){
                                    if(!is_numeric($section_array[1])){
                                        array_push($section_row_errors, "invalid section");
                                    }
                                    else{
                                        if((int)$section_array[1] <= 0 || (int)$section_array[1] > 99){
                                            array_push($section_row_errors, "invalid section");
                                        }
                                    }
                                }
                                else{
                                    array_push($section_row_errors, "invalid section");
                                }
                        }
                            else{
                                array_push($section_row_errors, "invalid section");
                            }
                        }
    
                        // invalid day check
                        if(is_numeric($day)){
                            if((int)$day < 1 || (int)$day > 7){
                                array_push($section_row_errors, "invalid day");
                            }
                        }
                        else{
                            array_push($section_row_errors, "invalid day");
                        }
    
                        // invalid start time check
                        if(!isValidTime($start)) {
                            array_push($section_row_errors, "invalid start");
                        }
    
                        // invalid end time check
                        if(!isValidTime($end)) {
                            array_push($section_row_errors, "invalid end");
                        }

                        if(isValidTime($end)){
                            $startdate = strtotime($start);
                            $enddate = strtotime($end);

                            if($enddate <= $startdate){
                                array_push($section_row_errors, "invalid end");
                            }
                        }
    
                        // invalid instructor check
                        if(strlen($instructor) > 100){
                            array_push($section_row_errors, "invalid instructor");
                        }
    
                        // invalid venue check
                        if(strlen($venue) > 100){
                            array_push($section_row_errors, "invalid venue");
                        }
    
                        // invalid size check
                        if(is_numeric($size)){
                            if((int)$size <= 0){
                                array_push($section_row_errors, "invalid size");
                            }
                        }
                        else{
                            array_push($section_row_errors, "invalid size");
                        }
                    }

                    if(empty($section_row_errors)) {
                        $success = $sectiondao->add_section($course, $section, $day, $start, $end, $instructor, $venue, $size);
                        if($success) {
                            $section_processed++;
                        }
                    } else {
                        $section_row_error_string = implode(", ", $section_row_errors);
                        array_push($errors, "Table: section, Row: $section_row_count, $section_row_error_string");
                    }
                    $section_row_count++;
                }
                array_push($num_record_loaded, "section.csv: $section_processed row(s) processed");
                fclose($section_file);
                unlink($section_path);

                //prerequisite.csv
                $prerequisite_headers_list = fgetcsv($prerequisite_file); # skip header
                $prerequisite_row_count = 2;

                while(($prerequisite_row = fgetcsv($prerequisite_file)) != false) {
                    $prerequisite_row = mb_convert_encoding($prerequisite_row,"UTF-8");
                    $prerequisite_row_errors = [];

                    $prerequisite_row = array_map('trim', $prerequisite_row);

                    // blank field(s) check
                    for($i=0; $i<count($prerequisite_row); $i++) {
                        if($prerequisite_row[$i] == '') {
                            array_push($prerequisite_row_errors, "blank $prerequisite_headers_list[$i]");
                        }
                    }

                    if($prerequisite_row_errors == []) { // if common validations pass

                        // then perform file specific validations

                        [$course, $prerequisite] = $prerequisite_row;
    
                        //invalid course check
                        if($coursedao->get_course($course) == false){
                            array_push($prerequisite_row_errors, "invalid course");
                        }
    
                        //invalid prerequisite check
                        if($coursedao->get_course($prerequisite) == false){
                            array_push($prerequisite_row_errors, "invalid prerequisite");
                        }
                    }

                    if(empty($prerequisite_row_errors)) {
                        $success = $prerequisitedao->add_prerequisite($course, $prerequisite);
                        if($success) {
                            $prerequisite_processed++;
                        }
                    } else {
                        $prerequisite_row_error_string = implode(", ", $prerequisite_row_errors);
                        array_push($errors, "Table: prerequisite, Row: $prerequisite_row_count, $prerequisite_row_error_string");
                    }
                    $prerequisite_row_count++;
                }
                array_push($num_record_loaded, "prerequisite.csv: $prerequisite_processed row(s) processed");
                fclose($prerequisite_file);
                unlink($prerequisite_path);

                //course_completed.csv
                $course_completed_list = fgetcsv($course_completed_file); # skip header
                $course_completed_row_count = 2;

                while(($course_completed_row = fgetcsv($course_completed_file)) != false) {
                    $course_completed_row = mb_convert_encoding($course_completed_row,"UTF-8");
                    $course_completed_row_errors = [];

                    $course_completed_row = array_map('trim', $course_completed_row);

                    // blank field(s) check
                    for($i=0; $i<count($course_completed_row); $i++) {
                        if($course_completed_row[$i] == '') {
                            array_push($course_completed_row_errors, "blank $course_completed_headers_list[$i]");
                        }
                    }

                    if($course_completed_row_errors == []) { // if common validations pass

                        // then perform file specific validations

                        [$userid, $code] = $course_completed_row;
    
                        //invalid userid check
                        if(!$studentdao->validUser($userid)){
                            array_push($course_completed_row_errors, "invalid userid");
                        }
    
                        //invalid course check
                        if($coursedao->get_course($code) == false){
                            array_push($course_completed_row_errors, "invalid course");
                        }

                        //prerequisite fulfilled check
                        $prerequisites_needed = $prerequisitedao->get_prerequisite_courses($code);
                        $student_completed_courses = $coursecompleteddao->get_completed_courses($userid);
                        foreach($prerequisites_needed as $this_prerequisite) {
                            if(!in_array($this_prerequisite, $student_completed_courses)) {
                                array_push($course_completed_row_errors, "invalid course completed");
                                break;
                            }
                        }
                    }

                    if(empty($course_completed_row_errors)) {
                        $success = $coursecompleteddao->add_course_completed($userid, $code);
                        if($success) {
                            $course_completed_processed++;
                        }
                    } else {
                        $course_completed_row_error_string = implode(", ", $course_completed_row_errors);
                        array_push($errors, "Table: course_completed, Row: $course_completed_row_count, $course_completed_row_error_string");
                    }
                    $course_completed_row_count++;

                }
                array_push($num_record_loaded, "course_completed.csv: $course_completed_processed row(s) processed");
                fclose($course_completed_file);
                unlink($course_completed_path);

                //bid.csv
                $bid_headers_list = fgetcsv($bid_file); # skip header
                $bid_row_count = 2;

                while(($bid_row = fgetcsv($bid_file)) != false) {
                    $bid_row = mb_convert_encoding($bid_row,"UTF-8");
                    $bid_row_errors = [];

                    $bid_row = array_map('trim', $bid_row);

                    // blank field(s) check
                    for($i=0; $i<count($bid_row); $i++) {
                        if($bid_row[$i] == '') {
                            array_push($bid_row_errors, "blank $bid_headers_list[$i]");
                        }
                    }

                    if($bid_row_errors == []) { // if common validations pass

                        // then perform file specific validations

                        [$userid, $amount, $code, $section] = $bid_row;

                        $preliminary_checks = 4;
    
                        //invalid userid check
                        if(!$studentdao->validUser($userid)){
                            array_push($bid_row_errors, "invalid userid");
                            $preliminary_checks--;
                        }
    
                        //invalid amount check
                        if(is_numeric($amount)){
                            if((int)$amount < 10 || strlen(substr(strrchr($amount, "."), 1)) > 2){
                                array_push($bid_row_errors, "invalid amount");
                                $preliminary_checks--;
                            }
                        }
                        else{
                            array_push($bid_row_errors, "invalid amount");
                            $preliminary_checks--;
                        }
    
                        //invalid course check
                        if($coursedao->get_course($code) == false){
                            array_push($bid_row_errors, "invalid course");
                            $preliminary_checks--;
                        }
    
                        //invalid section check
                        if($coursedao->get_course($code) != false){
                            if(!$sectiondao->is_valid_section($code, $section)){
                                array_push($bid_row_errors, "invalid section");
                                $preliminary_checks--;
                            }
                        }

                        if($preliminary_checks == 4) { // if all 4 preliminary checks passed

                            // then conduct logic validations

                            $pending_bidded_sections = $biddao->get_pending_bids_and_amount($userid, 1);

                            //not own school course
                            if($coursedao->get_school($code) != $studentdao->get_school($userid)) {
                                array_push($bid_row_errors, "not own school course");
                            }
                            
                            //class timetable clash
                            //exam timetable clash
                            if(!$biddao->bid_already_exists($userid, $code, $section, 1)){
                                $no_clash_check_success = true;
                                if($sectiondao->is_valid_section($code, $section)){
                                    $bidding_class = $sectiondao->get_class_day_start_end($code, $section);
                                    foreach($pending_bidded_sections as $this_list) {
                                        $existing_courseid = $this_list[0];
                                        $existing_section = $this_list[1];
                        
                                        $existing_class = $sectiondao->get_class_day_start_end($existing_courseid, $existing_section);
                                        $class_clash_check = dont_clash($bidding_class[0], $bidding_class[1], $bidding_class[2], $existing_class[0], $existing_class[1], $existing_class[2]);
                        
                                        $bidding_exam = $coursedao->get_exam_date_start_end($code);
                                        $existing_exam = $coursedao->get_exam_date_start_end($existing_courseid);
                                        $exam_clash_check = dont_clash($bidding_exam[0], $bidding_exam[1], $bidding_exam[2], $existing_exam[0], $existing_exam[1], $existing_exam[2]);
                        
                                        $no_clash_check_success = $class_clash_check && $exam_clash_check;
                        
                                        if(!$no_clash_check_success) {
                                            $no_clash_check_success = false;
                                            if(!$class_clash_check) {
                                                array_push($bid_row_errors, "class timetable clash");
                                                break;
                                            }
                                            if(!$exam_clash_check) {
                                                array_push($bid_row_errors, "exam timetable clash");
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            //incomplete prerequisites
                            $prerequisites_needed = $prerequisitedao->get_prerequisite_courses($code);
                            $student_completed_courses = $coursecompleteddao->get_completed_courses($userid);
                            foreach($prerequisites_needed as $this_prerequisite) {
                                if(!in_array($this_prerequisite, $student_completed_courses)) {
                                    array_push($bid_row_errors, "incomplete prerequisites");
                                    break;
                                }
                            }

                            //student has already completed this course
                            if(in_array($code, $student_completed_courses)){
                                array_push($bid_row_errors, "course completed");
                            }

                            //section limit reached
                            $pending_bidded_sections = $biddao->get_pending_bids_and_amount($userid, 1);
                            if(count($pending_bidded_sections) == 5) {
                                array_push($bid_row_errors, "section limit reached");
                            }

                            //not enough e-dollar
                            if($biddao->bid_already_exists($userid, $code, $section, 1)){
                                $updated_amount = $studentdao->get_balance($userid) + $biddao->get_amount($userid, $code, $section, 1);
                                if($amount > $updated_amount){
                                    array_push($bid_row_errors, "not enough e-dollar");
                                }
                            }
                            else{
                                if($amount > $studentdao->get_balance($userid)){
                                    array_push($bid_row_errors, "not enough e-dollar");
                                }
                            }
                        }
                    }

                    if(empty($bid_row_errors)) { // if no error in this row, aka add/update bid
                        $bid_already_exists = false;

                        $pending_bidded_sections = $biddao->get_pending_bids_and_amount($userid, 1);
                        foreach($pending_bidded_sections as [$existing_course, $existing_section, $existing_amount]) {
                            if($existing_course == $code && $existing_section == $section) { // if bid for this course+section already exists
                                $update_bid_success = $biddao->update_bid_for_bootstrap($userid, $amount, $code, $section);

                                $refund_balance_success = $studentdao->add_balance($userid, $existing_amount); // refund $ for existing bid

                                $deduct_balance_success = $studentdao->deduct_balance($userid, $amount); // deduct $ for new bid

                                $bid_already_exists = true;
                                $success = $update_bid_success && $refund_balance_success && $deduct_balance_success;
                                break;
                            }
                        }

                        if($bid_already_exists == false) {
                            $add_bid_success = $biddao->add_bid($userid, $amount, $code, $section, 1);
                            $deduct_balance_success = $studentdao->deduct_balance($userid, $amount);

                            $success = $add_bid_success && $deduct_balance_success;
                        }

                        if($success) {
                            $bid_processed++;
                        } 

                    } else { // if there is error(s) in this row, aka don't add/update bid
                        $bid_row_error_string = implode(", ", $bid_row_errors);
                        array_push($errors, "Table: bid, Row: $bid_row_count, $bid_row_error_string");
                    }
                    
                    $bid_row_count++;
                }

                array_push($num_record_loaded, "bid.csv: $bid_processed row(s) processed");
                fclose($bid_file);
                unlink($bid_path);
            }
        } else {
            $errors[] = "input files not found";
        }
    }

    if(!in_array("input files not found", $errors)){
        $biddingrounddao = new BiddingRoundDAO();
        $biddingrounddao->start_round(1);
        return [$num_record_loaded, $errors];
    }
    return "failed";
}

// doBootstrap();
?>