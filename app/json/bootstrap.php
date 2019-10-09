<?php
    require_once '../include/common.php';
    require_once '../include/token.php';

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ("token")];
    $errors = array_filter($errors);

    if (!isEmpty($errors)) { // if missing or empty token
        $result = [
            "status" => "error",
            "message" => array_values($errors)
            ];
    }
    else{
        if(isset($_POST["token"])) {
            if(!verify_token($_POST["token"])) { // if invalid token
                $result = [
                    "status" => "error",
                    "message" => ["invalid token"]
                    ];
            }
            else{
    
                $json_errors = [];
                $num_record_loaded = [];
    
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
            
                $json_errors = [];
            
                # check file size
                if ($_FILES["bootstrap-file"]["size"] <= 0) {
                    $result = [
                        "status" => "error",
                        "message" => "input files not found"
                    ];
            
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
                            $result = [
                                "status" => "error",
                                "message" => "input files not found"
                            ];
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
                        }
                        else {
                            # create DAOs
                            $biddao = new BidDAO();
                            $coursedao = new CourseDAO();
                            $coursecompleteddao = new CourseCompletedDAO();
                            $prerequisitedao = new PrerequisiteDAO();
                            $sectiondao = new SectionDAO();
                            $studentdao = new StudentDAO();
            
                            $biddao->removeAll();
                            $coursedao->removeAll();
                            $coursecompleteddao->removeAll();
                            $prerequisitedao->removeAll();
                            $sectiondao->removeAll();
                            $studentdao->removeAll();
            
                            // student.csv
                            $student_headers_list = fgetcsv($student_file); # skip header
                            $student_row_count = 1;
            
                            while(($student_row = fgetcsv($student_file)) != false) { # we want to insert these values into the database
            
                                $student_row_errors = [];
            
                                // blank field(s) check
                                for($i=0; $i<count($student_row); $i++) {
                                    if(rtrim($student_row[$i]) === '' ) {
                                        array_push($student_row_errors, "blank $student_headers_list[$i]");
                                    }
                                }
            
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
            
                                if(empty($student_row_errors)) {
                                    $success = $studentdao->add_student($userid, $password, $name, $school, $edollar);
                                    if($success) {
                                        $student_processed++;
                                    }
                                } else {
                                    $error = [
                                        "file" => "student.csv",
                                        "line" => $student_row_count,
                                        "message" => $student_row_errors
                                    ];
                                    array_push($json_errors, $error);
                                }
                                $student_row_count++;
                            }
                            array_push($num_record_loaded, array('student.csv' => $student_processed));
                            fclose($student_file);
                            unlink($student_path);
            
                            // course.csv
                            $course_headers_list = fgetcsv($course_file); # skip header
                            $course_row_count = 1;
            
                            while(($course_row = fgetcsv($course_file)) != false) {
            
                                $course_row_errors = [];
            
                                // blank field(s) check
                                for($i=0; $i<count($course_row); $i++) {
                                    if(rtrim($course_row[$i]) === '' ) {
                                        array_push($course_row_errors, "blank $course_headers_list[$i]");
                                    }
                                }
            
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
            
                                    if($enddate < $startdate){
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
            
            
                                if(empty($course_row_errors)) {
                                    $success = $coursedao->add_course($course, $school, $title, $description, $exam_date, $exam_start, $exam_end);
                                    if($success) {
                                        $course_processed++;
                                    }
                                } else {
                                    $error = [
                                        "file" => "course.csv",
                                        "line" => $course_row_count,
                                        "message" => $course_row_errors
                                    ];
                                    array_push($json_errors, $error);
                                }
                                $course_row_count++;
                            }
                            array_push($num_record_loaded, array('course.csv' => $course_processed));
                            fclose($course_file);
                            unlink($course_path);
            
                            //section.csv
                            $section_headers_list = fgetcsv($section_file); # skip header
                            $section_row_count = 1;
            
                            while(($section_row = fgetcsv($section_file)) != false) {
            
                                $section_row_errors = [];
            
                                // blank field(s) check
                                for($i=0; $i<count($section_row); $i++) {
                                    if(rtrim($section_row[$i]) == '') {
                                        array_push($section_row_errors, "blank $section_headers_list[$i]");
                                    }
                                }
            
                                [$course, $section, $day, $start, $end, $instructor, $venue, $size] = $section_row;
            
                                // invalid course check
                                if($coursedao->get_course($course) != TRUE){
                                    array_push($section_row_errors, "invalid course");
                                }
            
                                // invalid section check
                                if($coursedao->get_course($course) == TRUE){
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
        
                                    if($enddate < $startdate){
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
                                    if((int)$size < 0){
                                        array_push($section_row_errors, "invalid size");
                                    }
                                }
                                else{
                                    array_push($section_row_errors, "invalid size");
                                }
            
                                if(empty($section_row_errors)) {
                                    $success = $sectiondao->add_section($course, $section, $day, $start, $end, $instructor, $venue, $size);
                                    if($success) {
                                        $section_processed++;
                                    }
                                } else {
                                    $error = [
                                        "file" => "section.csv",
                                        "line" => $section_row_count,
                                        "message" => $section_row_errors
                                    ];
                                    array_push($json_errors, $error);
                                }
                                $section_row_count++;
                            }
                            array_push($num_record_loaded, array('section.csv' => $section_processed));
                            fclose($section_file);
                            unlink($section_path);
            
                            //prerequisite.csv
                            $prerequisite_headers_list = fgetcsv($prerequisite_file); # skip header
                            $prerequisite_row_count = 1;
            
                            while(($prerequisite_row = fgetcsv($prerequisite_file)) != false) {
            
                                $prerequisite_row_errors = [];
            
                                // blank field(s) check
                                for($i=0; $i<count($prerequisite_row); $i++) {
                                    if(rtrim($prerequisite_row[$i]) == '') {
                                        array_push($prerequisite_row_errors, "blank $prerequisite_headers_list[$i]");
                                    }
                                }
            
                                [$course, $prerequisite] = $prerequisite_row;
            
                                //invalid course check
                                if($coursedao->get_course($course) != TRUE){
                                    array_push($prerequisite_row_errors, "invalid course");
                                }
            
                                //invalid prerequisite check
                                if($coursedao->get_course($prerequisite) != TRUE){
                                    array_push($prerequisite_row_errors, "invalid prerequisite");
                                }
            
                                if(empty($prerequisite_row_errors)) {
                                    $success = $prerequisitedao->add_prerequisite($course, $prerequisite);
                                    if($success) {
                                        $prerequisite_processed++;
                                    }
                                } else {
                                    $error = [
                                        "file" => "prerequisite.csv",
                                        "line" => $prerequisite_row_count,
                                        "message" => $prerequisite_row_errors
                                    ];
                                    array_push($json_errors, $error);
                                }
                                $prerequisite_row_count++;
                            }
                            array_push($num_record_loaded, array('prerequisite.csv' => $prerequisite_processed));
                            fclose($prerequisite_file);
                            unlink($prerequisite_path);
            
                            //course_completed.csv
                            $course_completed_list = fgetcsv($course_completed_file); # skip header
                            $course_completed_row_count = 1;
            
                            while(($course_completed_row = fgetcsv($course_completed_file)) != false) {
            
                                $course_completed_row_errors = [];
            
                                // blank field(s) check
                                for($i=0; $i<count($course_completed_row); $i++) {
                                    if(rtrim($course_completed_row[$i]) == '') {
                                        array_push($course_completed_row_errors, "blank $course_completed_headers_list[$i]");
                                    }
                                }
            
                                [$userid, $code] = $course_completed_row;
            
                                //invalid userid check
                                if(!$studentdao->validUser($userid)){
                                    array_push($course_completed_row_errors, "invalid userid");
                                }
            
                                //invalid course check
                                if(!$coursedao->get_course($code)){
                                    array_push($course_completed_row_errors, "invalid course");
                                }
    
                                //prerequisite fulfilled check
                                $prerequisites_needed = $prerequisitedao->get_prerequisite_courses($code);
                                $student_completed_courses = $coursecompleteddao->get_completed_courses($userid);
                                foreach($prerequisites_needed as $this_prerequisite) {
                                    if(!in_array($this_prerequisite, $student_completed_courses)) {
                                        array_push($course_completed_row_errors, "invalid course completed");
                                    }
                                }
            
                                if(empty($course_completed_row_errors)) {
                                    $success = $coursecompleteddao->add_course_completed($userid, $code);
                                    if($success) {
                                        $course_completed_processed++;
                                    }
                                } else {
                                    $error = [
                                        "file" => "course_completed.csv",
                                        "line" => $course_completed_row_count,
                                        "message" => $course_completed_row_errors
                                    ];
                                    array_push($json_errors, $error);
                                }
                                $course_completed_row_count++;
            
                            }
                            array_push($num_record_loaded, array('course_completed.csv' => $course_completed_processed));
                            fclose($course_completed_file);
                            unlink($course_completed_path);
            
                            //bid.csv
                            $bid_headers_list = fgetcsv($bid_file); # skip header
                            $bid_row_count = 1;
            
                            while(($bid_row = fgetcsv($bid_file)) != false) {
            
                                $bid_row_errors = [];
            
                                // blank field(s) check
                                for($i=0; $i<count($bid_row); $i++) {
                                    if(rtrim($bid_row[$i]) == '') {
                                        array_push($bid_row_errors, "blank $bid_headers_list[$i]");
                                    }
                                }
            
                                [$userid, $amount, $code, $section] = $bid_row;
            
                                //invalid userid check
                                if(!$studentdao->validUser($userid)){
                                    array_push($bid_row_errors, "invalid userid");
                                }
            
                                //invalid amount check
                                if(is_numeric($amount)){
                                    if((int)$amount < 10 || strlen(substr(strrchr($amount, "."), 1)) > 2){
                                        array_push($bid_row_errors, "invalid amount");
                                    }
                                }
                                else{
                                    array_push($bid_row_errors, "invalid amount");
                                }
            
                                //invalid course check
                                if(!$coursedao->get_course($code)){
                                    array_push($bid_row_errors, "invalid course");
                                }
            
                                //invalid section check
                                if($coursedao->get_course($code)){
                                    if(!$sectiondao->is_valid_section($code, $section)){
                                        array_push($bid_row_errors, "invalid section");
                                    }
                                }

                                $biddingrounddao = new BiddingRoundDAO();
                                //not own school course
                                if($biddingrounddao->get_current_round() == 0.5){
                                    if($coursedao->get_school($code) != $studentdao->get_school($userid)) {
                                        array_push($bid_row_errors, "not own school course");
                                    }
    
                                //section limit reached
                                $pending_bidded_sections = $biddao->get_pending_bids_and_amount($userid, 1);
                                if(!$max_course_check_success = count($pending_bidded_sections) < 5) {
                                    array_push($errors, "section limit reached");
                                }
    
                                //class timetable clash
                                //exam timetable clash
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
                                                array_push($errors, "class timetable clash");
                                            }
                                            if(!$exam_clash_check) {
                                                array_push($errors, "exam timetable clash");
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
                                    }
                                }
    
                                //student has already completed this course
                                if(in_array($code, $student_completed_courses)){
                                    array_push($bid_row_errors, "course completed");
                                }
    
                                //not enough e-dollar
                                if($amount > $studentdao->get_balance($userid)){
                                    array_push($bid_row_errors, "not enough e-dollar");
                                }
            
                                if(empty($bid_row_errors)) {
                                    if($biddao->bid_already_exists($userid, $code, $section, 1)) {
                                        $success = $biddao->update_bid_for_bootstrap($userid, $amount, $code, $section);
                                    } else {
                                        $success = $biddao->add_bid($userid, $amount, $code, $section, 1);
                                    }
                                    if($success) {
                                        $bid_processed++;
                                        $studentdao->deduct_balance($userid, $amount);
                                    } 
                                } else {
                                    $error = [
                                        "file" => "bid.csv",
                                        "line" => $bid_row_count,
                                        "message" => $bid_row_errors
                                    ];
                                    array_push($json_errors, $error);
                                }
                                $bid_row_count++;
                            }
                            array_push($num_record_loaded, array('bid.csv' => $bid_processed));
                            fclose($bid_file);
                            unlink($bid_path);
            
                            if(!isEmpty($json_errors)){
                                $sortclass = new Sort();
                                $json_errors = $sortclass->sort_it($json_errors,"file");
                                $num_record_loaded = $sortclass->sort_it($num_record_loaded, "filename");
                                
                                $result = [
                                        "status" => "error",
                                        "num-record-loaded" => $num_record_loaded,
                                        "error" => $json_errors
                                    ];
                            }
                            else{
                                $sortclass = new Sort();
                                $num_record_loaded = $sortclass->sort_it($num_record_loaded, "filename");
                                $result = [
                                        "status" => "success",
                                        "num-record-loaded" => $num_record_loaded
                                    ];
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