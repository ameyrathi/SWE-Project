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
        if(isset($_GET["token"])) {
            if(!verify_token($_GET["token"])) { // if invalid token
                $result = [
                    "status" => "error",
                    "message" => ["invalid token"]
                ];
            }
            else{
                $coursedao = new CourseDAO();
                $sectiondao = new SectionDAO();
                $studentdao = new StudentDAO();
                $prerequisitedao = new PrerequisiteDAO();
                $biddao = new BidDAO();
                $coursecompleteddao = new CourseCompletedDAO();
                $biddingrounddao = new BiddingRoundDAO();
                $successfuldao = new SuccessfulDAO();

                $current_round = $biddingrounddao->get_current_round();
                
                $courses = $coursedao->retrieve_all_courses();
                $students = $studentdao->retrieve_all_students();
                $sections = $sectiondao->retrieve_all_sections();
                $prerequisites = $prerequisitedao->retrieve_all_prerequisites();
                $courses_completed = $coursecompleteddao->retrieve_all_completed_courses();

                if($current_round == 0.5) { // Round 1 hasn't started
                    $bids = [];
                } elseif($current_round == 1 || $current_round == 2) {
                    $bids = $biddao->retrieve_all_bids($current_round);
                } elseif($current_round == 1.5) {
                    $bids = $biddao->retrieve_all_bids(1);
                } elseif($current_round == 2.5) {
                    $bids = $biddao->retrieve_all_bids(2);
                }

                $courseJSON = [];
                $sectionJSON = [];
                $studentJSON = [];
                $prerequisiteJSON = [];
                $bidJSON = [];
                $completed_courseJSON = [];
                $section_student = []; //students who have successfully won a bid for a section (in previous round)

                for($i=0; $i<count($courses); $i++){
                    $course = $courses[$i][0];
                    $school = $courses[$i][1];
                    $title = $courses[$i][2];
                    $description = utf8_encode($courses[$i][3]);
                    $examdate = $courses[$i][4];
                    $examstart = $courses[$i][5];
                    $examend = $courses[$i][6];

                    $temp_arr = [
                        "course" => $course,
                        "school" => $school,
                        "title" => $title,
                        "description" => $description,
                        "examdate" => $examdate,
                        "examstart" => $examstart,
                        "examend" => $examend
                    ];

                    array_push($courseJSON, $temp_arr);
                }

                for($i=0; $i<count($sections); $i++){
                    $course = $sections[$i][0];
                    $section = $sections[$i][1];
                    $day = $sections[$i][2];
                    $start = $sections[$i][3];
                    $end = $sections[$i][4];
                    $instructor = $sections[$i][5];
                    $venue = $sections[$i][6];
                    $size = $sections[$i][7];

                    $temp_arr = [
                        "course" => $course,
                        "section" => $section,
                        "day" => $day,
                        "start" => $start,
                        "end" => $end,
                        "instructor" => $instructor,
                        "venue" => $venue,
                        "size" => $size
                    ];

                    array_push($sectionJSON, $temp_arr);
                }

                for($i=0; $i<count($students); $i++){
                    $userid = $students[$i][0];
                    $password = $students[$i][1];
                    $name = $students[$i][2];
                    $school = $students[$i][3];
                    $edollar = $students[$i][4];

                    $temp_arr = [
                        "userid" => $userid,
                        "password" => $password,
                        "name" => $name,
                        "school" => $school,
                        "edollar" => $edollar
                    ];

                    array_push($studentJSON, $temp_arr);
                }

                for($i=0; $i<count($prerequisites); $i++){
                    $course = $prerequisites[$i][0];
                    $prerequisite = $prerequisites[$i][1];

                    $temp_arr = [
                        "course" => $course,
                        "prerequisite" => $prerequisite
                    ];

                    array_push($prerequisiteJSON, $temp_arr);
                }

                for($i=0; $i<count($bids); $i++){
                    $userid = $bids[$i][0];
                    $amount = $bids[$i][1];
                    $course = $bids[$i][2];
                    $section = $bids[$i][3];

                    $temp_arr = [
                        "userid" => $userid,
                        "amount" => $amount,
                        "course" => $course,
                        "section" => $section
                    ];

                    array_push($bidJSON, $temp_arr);
                }

                for($i=0; $i<count($courses_completed); $i++){
                    $userid = $courses_completed[$i][0];
                    $course = $courses_completed[$i][1];

                    $temp_arr = [
                        "userid" => $userid,
                        "course" => $course
                    ];

                    array_push($completed_courseJSON, $temp_arr);
                }

                $sortclass = new Sort();
                $courseJSON = $sortclass->sort_it($courseJSON, "course");
                $sectionJSON = $sortclass->sort_it($sectionJSON, "section");
                $studentJSON = $sortclass->sort_it($studentJSON, "student");
                $prerequisiteJSON = $sortclass->sort_it($prerequisiteJSON, "prerequisite");
                $bidJSON = $sortclass->sort_it($bidJSON, "bid");
                $completed_courseJSON = $sortclass->sort_it($completed_courseJSON, "course_completed");

                if($current_round == 1.5 || $current_round == 2 || $current_round == 2.5){

                    if($current_round == 1.5 || $current_round == 2) {
                        $successful_bids = $successfuldao->retrieve_all_bids(1);
                    } else {
                        $successful_bids = $successfuldao->retrieve_all_bids(2);
                    }

                    for($i=0; $i<count($successful_bids); $i++){
                        $userid = $successful_bids[$i][0];
                        $amount = $successful_bids[$i][1];
                        $course = $successful_bids[$i][2];
                        $section = $successful_bids[$i][3];
    
                        $temp_arr = [
                            "userid" => $userid,
                            "amount" => $amount,
                            "course" => $course,
                            "section" => $section
                        ];
    
                        array_push($section_student, $temp_arr);
                    }

                    $section_student = $sortclass->sort_it($section_student, "course_completed");

                    $result = [
                        "status" => "success",
                        "courses" => $courseJSON,
                        "section" => $sectionJSON,
                        "students" => $studentJSON,
                        "prerequisite" => $prerequisiteJSON,
                        "bid" => $bidJSON,
                        "completed-course" => $completed_courseJSON,
                        "section-student" => $section_student
                    ];
                }
                else{
                    $result = [
                        "status" => "success",
                        "courses" => $courseJSON,
                        "section" => $sectionJSON,
                        "students" => $studentJSON,
                        "prerequisite" => $prerequisiteJSON,
                        "bid" => $bidJSON,
                        "completed-course" => $completed_courseJSON
                    ];
                
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