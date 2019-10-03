<html>
<link rel="stylesheet" href="stylesheet.css"/>

<?php
    require_once 'include/common.php';
    require_once 'include/protect_token.php';

    if(isset($_GET["token"])) {
        $token = $_GET["token"];
    } else {
        $token = "";
    }

    token_gateway($token);
?>

<!-- The sidebar -->

<div class="sidebar">
  <a href="student_home.php?token=<?php echo $token?>">Home</a>
  <a class="active" href="student_add_bid.php?token=<?php echo $token?>">Bid</a>
  <a href="student_drop_bid.php?token=<?php echo $token?>">Drop Bid</a>
  <a href="student_drop_section.php?token=<?php echo $token?>">Drop Section</a>
  <a href="student_view_results.php?token=<?php echo $token?>">View Results</a>
  <a href="sign_out.php?token=<?php echo $token?>">Sign Out</a>
</div>


<div class='content'>
<?php 
    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $current_round = $biddingrounddao->checkBiddingRound();

    if($current_round == null) {
        echo "<h2>Round 1 has not started.</h2>";
    } else {

        $bid_courseid = "";
        $bid_section = "";
        $bid_amount = "";

        if(isset($_GET["bid_courseid"])) {
            $bid_courseid = strtoupper($_GET["bid_courseid"]);
        }
        if(isset($_GET["bid_section"])) {
            $bid_section = strtoupper($_GET["bid_section"]);
        }
        if(isset($_GET["bid_amount"])) {
            $bid_amount = $_GET["bid_amount"];
        }

        echo "
        <h1>Current bidding round: $current_round<br><br></h1>
        <form>
            <input type='hidden' name='token' value=$token>

            Course: <input type='text' name='bid_courseid' value=$bid_courseid><br><br>
            Section: <input type='text' name='bid_section' value=$bid_section><br><br>
            Bid Amount: <input type='text' name='bid_amount' value=$bid_amount><br><br>
            <input type='submit'/>
        </form>
        <br>
        ";

        $StudentDAO = new StudentDAO();
        $_SESSION["balance"] = $StudentDAO->get_balance($_SESSION["userid"]);

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


        function bid_check($amount, $courseid, $section, $current_round) {
        /**
         * checks if bid is valid
         * @param string $amount bid amount
         * @param string $courseid course id of desired course
         * @param string $section section of desired course
         * @return boolean true if events don't clash, false if events clash
         */

            $CourseDAO = new CourseDAO();
            $SectionDAO = new SectionDAO();
            $BidDAO = new BidDAO();
            $PrerequisiteDAO = new PrerequisiteDAO();
            $CourseCompletedDAO = new CourseCompletedDAO();
            $StudentDAO = new StudentDAO();

            $errors = [];
            $is_valid_section = true;

            if(!$SectionDAO->is_valid_section($courseid, $section)) {
                $is_valid_section = false;
                array_push($errors, "$courseid $section does not exist.");
            }

            // "For bidding round 1, the student can only bid for courses offered by his/her own school."
            if($current_round == 1) {
                if($CourseDAO->get_school($courseid) != $StudentDAO->get_school($_SESSION["userid"])) {
                    array_push($errors, "$courseid is not offered by your school.");
                }
            }

            $list_of_bidded_courses = $BidDAO->get_bidded_courses($_SESSION["userid"], $current_round);

            if (!($enough_balance_check_success = $_SESSION["balance"] >= $amount)) {
                $amount_shortage = $amount - $_SESSION["balance"];
                array_push($errors, "You are short of $$amount_shortage.");
            }

            if(!$max_course_check_success = count($list_of_bidded_courses) < 5) {
                array_push($errors, "You have already bidded for 5 modules.");
            }

            
            $one_section_check_success = true; # assume student didn't bid for this course already
            foreach($list_of_bidded_courses as $this_list) {
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

            $bidding_class = $SectionDAO->get_class_day_start_end($courseid, $section); # desired class day + time
            $no_clash_check_success = true; # assume no clash first

            if($is_valid_section) {
                foreach($list_of_bidded_courses as $this_list) {
                    $existing_courseid = $this_list[0];
                    $existing_section = $this_list[1];

                    $existing_class = $SectionDAO->get_class_day_start_end($existing_courseid, $existing_section);
                    $class_clash_check = dont_clash($bidding_class[0], $bidding_class[1], $bidding_class[2], $existing_class[0], $existing_class[1], $existing_class[2]);

                    $bidding_exam = $CourseDAO->get_exam_date_start_end($courseid);
                    $existing_exam = $CourseDAO->get_exam_date_start_end($existing_courseid);
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
            }


            $prerequisite_courses = $PrerequisiteDAO->get_prerequisite_courses($courseid);
            $completed_courses = $CourseCompletedDAO->get_completed_courses($_SESSION["userid"]);

            $prerequisite_check_success = true; # assume fulfill prerequisites first

            foreach($prerequisite_courses as $this_prerequisite) {
                if(!in_array($this_prerequisite, $completed_courses)) {
                    $prerequisite_check_success = false;
                    array_push($errors, "You have not completed prerequisite course: $this_prerequisite.");
                }
            }

            
            if(!empty($errors)) {
                $error_count = 1;
                echo "<strong><span id='error'>Errors:</span></strong><br><br>";
                foreach($errors as $error) {
                    echo "<span id='error'>$error_count. $error</span><br>";
                    $error_count++;
                }
            } else {
                if($add_bid_success = ($BidDAO->add_bid($_SESSION["userid"], $amount, $courseid, $section, $current_round)) && $StudentDAO->deduct_balance($_SESSION["userid"], $amount)) {
                    echo "<strong>Congratulations! Your bid of $$amount for $courseid $section has been submitted.<br>";
                    $balance = $StudentDAO->get_balance($_SESSION["userid"]);
                    echo "Your balance e-$ is $$balance.<strong>";
                    return true;
                } else {
                    echo "CRITERIA SATISFIED BUT FAIL TO ADD BID TO DATABASE - TO DEBUG";
                }
            }
            return false;
        }

        echo "<p>";
        
        if(!in_array("", [$bid_amount, $bid_courseid, $bid_section])) {
            bid_check($bid_amount, $bid_courseid, $bid_section, $current_round);
        }

        echo "</p>";
    }

?>

</div>
</html>