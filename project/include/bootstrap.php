<?php
require_once 'common.php';

function doBootstrap() {		

    $errors = array();
    $num_record_loaded = array();

	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
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
				$connection_manager = new connection_manager();
                $conn = $connection_manager->connect();

                # create DAOs
                $biddao = new BidDAO();
                $coursedao = new CourseDAO();
                $coursecompleteddao = new CourseCompletedDAO();
                $prerequisitedao = new PrerequisiteDAO();
                $sectiondao = new SectionDAO();
                $studentdao = new StudentDAO();
                
                # truncate current SQL tables
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
                        if(empty($student_row[$i])) {
                            array_push($student_row_errors, "blank $student_headers_list[$i]");
                        }
                    }

                    if(count($student_row_errors) == 0) {
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
                            if(strpos($edollar , ".") != false) {
                                $decimal_places = strlen(substr(strrchr($edollar, "."), 1));
                                if($decimal_places > 2) {
                                    array_push($student_row_errors, "invalid edollar");
                                }
                            }
                        }

                        // invalid password check
                        if(strlen($password) > 128) {
                            array_push($student_row_errors, "invalid password");
                        }

                        // invalid name check
                        if(strlen($name) > 128) {
                            array_push($student_row_errors, "invalid name");
                        }
                    }
                    
                    if(empty($student_row_errors)) {
                        $success = $studentdao->add_student($userid, $password, $name, $school, $edollar);
                        if($success) {
                            $student_processed++;
                        } else {
                            echo "STUDENT ROW VALID BUT FAILED TO ADD - DEBUG";
                        }
                    } else {
                        $student_row_error_string = implode(", ", $student_row_errors);
                        array_push($errors, "Table: student, Row: $student_row_count, $student_row_error_string");
                    }
                    $student_row_count++;
                }
                array_push($num_record_loaded, "student.csv: $student_processed row(s) processed");
                fclose($student_file);

                // course.csv
                $course_headers_list = fgetcsv($course_file); # skip header
                $course_row_count = 1;

                while(($course_row = fgetcsv($course_file)) != false) {

                    $course_row_errors = [];

                    // blank field(s) check
                    for($i=0; $i<count($course_row); $i++) {
                        if(empty($course_row[$i])) {
                            array_push($course_row_errors, "blank $course_headers_list[$i]");
                        }
                    }

                    if(count($course_row_errors) == 0) {
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
                        if(strlen($description > 1000)){
                            array_push($course_row_errors, "invalid description");
                        }
                    }

                    if(empty($course_row_errors)) {
                        $success = $coursedao->add_course($course, $school, $title, $description, $exam_date, $exam_start, $exam_end);
                        if($success) {
                            $course_processed++;
                        } else {
                            echo "COURSE ROW VALID BUT FAILED TO ADD - DEBUG";
                        }
                    } else {
                        $course_row_error_string = implode(", ", $course_row_errors);
                        array_push($errors, "Table: course, Row: $course_row_count, $course_row_error_string");
                    }
                    $course_row_count++;
                }
                array_push($num_record_loaded, "course.csv: $course_processed row(s) processed");
                fclose($course_file);

                //section.csv
                $section_headers_list = fgetcsv($section_file); # skip header
                $section_row_count = 1;

                while(($section_row = fgetcsv($section_file)) != false) {

                    $section_row_errors = [];

                    // blank field(s) check
                    for($i=0; $i<count($section_row); $i++) {
                        if(empty($section_row[$i])) {
                            array_push($section_row_errors, "blank $section_headers_list[$i]");
                        }
                    }

                    if(count($section_row_errors) == 0) {
                        [$course, $section, $day, $start, $end, $instructor, $venue, $size] = $section_row;

                        // invalid course check
                        if($coursedao->get_course($course) != TRUE){
                            array_push($section_row_errors, "invalid course");
                        }

                        // invalid section check
                        #$section_array = explode('S',$section);
                        #if(count($section_array) != 2 || !is_int($section_array[1]) || (int)$section_array[1] < 0 || (int)$section_array[1] > 99){
                        #    array_push($section_row_errors, "invalid section");
                        #}

                        // invalid day check
                        if((int)$day < 1 || (int)$day > 7){
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

                        // invalid instructor check
                        if(strlen($instructor) > 100){
                            array_push($section_row_errors, "invalid instructor");
                        }

                        // invalid venue check
                        if(strlen($venue) > 100){
                            array_push($section_row_errors, "invalid venue");
                        }

                        // invalid size check
                        if((int)$size < 0){
                            array_push($section_row_errors, "invalid size");
                        }
                    }

                    if(empty($section_row_errors)) {
                        $success = $sectiondao->add_section($course, $section, $day, $start, $end, $instructor, $venue, $size);
                        if($success) {
                            $section_processed++;
                        } else {
                            echo "SECTION ROW VALID BUT FAILED TO ADD - DEBUG";
                        }
                    } else {
                        $section_row_error_string = implode(", ", $section_row_errors);
                        array_push($errors, "Table: section, Row: $section_row_count, $section_row_error_string");
                    }
                    $section_row_count++;
                }
                array_push($num_record_loaded, "section.csv: $section_processed row(s) processed");
                fclose($section_file);

                //prerequisite.csv
                $prerequisite_headers_list = fgetcsv($prerequisite_file); # skip header
                $prerequisite_row_count = 1;

                while(($prerequisite_row = fgetcsv($prerequisite_file)) != false) {

                    $prerequisite_row_errors = [];

                    // blank field(s) check
                    for($i=0; $i<count($prerequisite_row); $i++) {
                        if(empty($prerequisite_row[$i])) {
                            array_push($prerequisite_row_errors, "blank $prerequisite_headers_list[$i]");
                        }
                    }

                    if(count($prerequisite_row_errors) == 0) {
                        [$course, $prerequisite] = $prerequisite_row;

                        //invalid course check
                        if($coursedao->get_course($course) != TRUE){
                            array_push($prerequisite_row_errors, "invalid course");
                        }

                        //invalid prerequisite check
                        if($coursedao->get_course($prerequisite) != TRUE){
                            array_push($prerequisite_row_errors, "invalid prerequisite");
                        }
                    }
                    
                    if(empty($prerequisite_row_errors)) {
                        $success = $prerequisitedao->add_prerequisite($course, $prerequisite);
                        if($success) {
                            $prerequisite_processed++;
                        } else {
                            echo "PREREQUISITE ROW VALID BUT FAILED TO ADD - DEBUG";
                        }
                    } else {
                        $prerequisite_row_error_string = implode(", ", $prerequisite_row_errors);
                        array_push($errors, "Table: prerequisite, Row: $prerequisite_row_count, $prerequisite_row_error_string");
                    }
                    $prerequisite_row_count++;
                }
                array_push($num_record_loaded, "prerequisite.csv: $prerequisite_processed row(s) processed");
                fclose($prerequisite_file);

                //course_completed.csv
                $course_completed_list = fgetcsv($course_completed_file); # skip header
                $course_completed_row_count = 1;

                while(($course_completed_row = fgetcsv($course_completed_file)) != false) {

                    $course_completed_row_errors = [];

                    // blank field(s) check
                    for($i=0; $i<count($course_completed_row); $i++) {
                        if(empty($course_completed_row[$i])) {
                            array_push($course_completed_row_errors, "blank $course_completed_headers_list[$i]");
                        }
                    }

                    if(count($course_completed_row_errors) == 0) {
                        [$userid, $code] = $course_completed_row;

                        //invalid userid check
                        if(!$studentdao->validUser($userid)){
                            array_push($course_completed_row_errors, "invalid userid");
                        }

                        //invalid course check
                        if(!$coursedao->get_course($course)){
                            array_push($course_completed_row_errors, "invalid course");
                        }
                    }

                    if(empty($course_completed_row_errors)) {
                        $success = $coursecompleteddao->add_course_completed($userid, $code);
                        if($success) {
                            $course_completed_processed++;
                        } else {
                            echo "COURSE_COMPLETTED ROW VALID BUT FAILED TO ADD - DEBUG";
                        }
                    } else {
                        $course_completed_row_error_string = implode(", ", $course_completed_row_errors);
                        array_push($errors, "Table: course_completed, Row: $course_completed_row_count, $course_completed_row_error_string");
                    }
                    $course_completed_row_count++;

                }
                array_push($num_record_loaded, "course_completed.csv: $course_completed_processed row(s) processed");
                fclose($course_completed_file);

                //bid.csv
                $bid_headers_list = fgetcsv($bid_file); # skip header
                $bid_row_count = 1;

                while(($bid_row = fgetcsv($bid_file)) != false) {

                    $bid_row_errors = [];

                    // blank field(s) check
                    for($i=0; $i<count($bid_row); $i++) {
                        if(empty($bid_row[$i])) {
                            array_push($bid_row_errors, "blank $bid_headers_list[$i]");
                        }
                    }

                    if(count($bid_row_errors) == 0) {
                        [$userid, $amount, $code, $section] = $bid_row;

                        //invalid userid check
                        if(!$studentdao->validUser($userid)){
                            array_push($bid_row_errors, "invalid userid");
                        }

                        //invalid amount check
                        if((int)$amount < 10 || strlen(substr(strrchr($amount, "."), 1)) > 2){
                            array_push($bid_row_errors, "invalid amount");
                        }

                        //invalid course check
                        if(!$coursedao->get_course($course)){
                            array_push($bid_row_errors, "invalid course");
                        }

                        //invalid section check
                        if(!$sectiondao->is_valid_section($code, $section)){
                            array_push($bid_row_errors, "invalid section");
                        }
                    }

                    if(empty($bid_row_errors)) {
                        $success = $biddao->add_bid_for_bootstrap($userid, $amount, $code, $section);
                        if($success) {
                            $bid_processed++;
                        } else {
                            echo "BID ROW VALID BUT FAILED TO ADD - DEBUG";
                        }
                    } else {
                        $bid_row_error_string = implode(", ", $bid_row_errors);
                        array_push($errors, "Table: bid, Row: $bid_row_count, $bid_row_error_string");
                    }
                    $bid_row_count++;
                }
                array_push($num_record_loaded, "bid.csv: $bid_processed row(s) processed");
                fclose($bid_file);
                
            }
        }
    }

    if(!in_array("input files not found", $errors)){

        $biddingrounddao = new BiddingRoundDAO();
        $biddingrounddao->addBiddingRound(1);
    }

    return [$num_record_loaded, $errors];
}


	# Sample code for returning JSON format errors. remember this is only for the JSON API. Humans should not get JSON errors.

	// if (!isEmpty($errors))
	// {	
	// 	$sortclass = new Sort();
	// 	$errors = $sortclass->sort_it($errors,"bootstrap");
	// 	$result = [ 
	// 		"status" => "error",
	// 		"messages" => $errors
	// 	];
	// }

	// else
	// {	
	// 	$result = [ 
	// 		"status" => "success",
	// 		"num-record-loaded" => [
	// 			"pokemon.csv" => $pokemon_processed,
	// 			"pokemon_type.csv" => $pokemon_type_processed,
	// 			"user.csv" => $User_processed
	// 		]
	// 	];
    // }
    
	// header('Content-Type: application/json');
    // echo json_encode($result, JSON_PRETTY_PRINT);

?>