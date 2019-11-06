<html>
<link rel="stylesheet" href="stylesheet.css"/>

<?php
    require_once 'include/common.php';
    require_once 'include/protect_token.php';
    require_once 'process_add_bid.php';

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
  <a href="sign_out.php?token=<?php echo $token?>">Sign Out</a>
</div>


<div class='content'>
<?php 
    $coursedao = new CourseDAO();
    $studentdao = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $biddao = new BidDAO();
    $sectionresultsdao = new SectionResultsDAO();
    $sectiondao = new SectionDAO();

    $current_round = $biddingrounddao->get_current_round();
    $round_message = $biddingrounddao->get_round_message();

    if($current_round == 0.5 || $current_round == 1.5 || $current_round == 2.5) {
        echo "<h1>$round_message</h1>";
    } elseif($current_round == 1 || $current_round == 2) { // round 1 ongoing or round 2 ongoing
        $searched_course = "";
        $bid_course = "";
        $bid_section = "";
        $bid_amount = "";

        if(isset($_GET["searched_course"])) {
            $searched_course = strtoupper($_GET["searched_course"]);
        }
        
        if(isset($_GET["bid_course"])) {
            $bid_course = strtoupper($_GET["bid_course"]);
        }
        if(isset($_GET["bid_section"])) {
            $bid_section = strtoupper($_GET["bid_section"]);
        }
        if(isset($_GET["bid_amount"])) {
            $bid_amount = $_GET["bid_amount"];
        }

        echo "<h1>Current bidding round: $current_round<br><br></h1>";


        $list_of_all_courses = $coursedao->get_codes_and_titles();
        $sortclass = new Sort();
        $list_of_all_courses = $sortclass->sort_it($list_of_all_courses,"search_bid_course");


        echo "
        <form method='get'>
        <input type='hidden' name='token' value=$token>

        <strong>Enter Course Code: </strong>
        <input type='text' name='searched_course' value=$searched_course>

        <br><br>
        <input type='submit' name='submit_search' value='Search Sections'>
        </form><br>";

        if($searched_course != "") {
            if($coursedao->get_course($searched_course) == false) {
                echo "<span id='error'>Course $searched_course does not exist.</span><br>"; 
            } else { // course exists
                echo "
                <table id='section_search'>
                <tr>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Lesson Time</th>
                    <th>Instructor</th>
                ";

                if($current_round == 2) {
                    echo "
                    <th>Vacancy</th>
                    <th>Min Bid</th>
                    ";
                }
                
                echo "
                <th>Action</th>
                </tr>
                ";

                $section_details = $sectiondao->get_course_sections_times($searched_course);
                $course_name = $coursedao->get_course_title($searched_course);
                $course_concat = "$searched_course $course_name";

                $days_of_week = ['1'=>'Mon', '2'=>'Tue', '3'=>'Wed', '4'=>'Thu', '5'=>'Fri', '6'=>'Sat', '7'=>'Sun'];

                foreach($section_details as [$this_section, $this_day, $this_start, $this_end, $this_instructor]) {
                    $this_day = $days_of_week[$this_day];
                    $this_start = date("H:i", strtotime($this_start));
                    $this_end = date("H:i", strtotime($this_end));
                    if($current_round == 2) {
                        $min_bid = $sectionresultsdao->get_min_bid($searched_course, $this_section);
                        $vacancy = $sectionresultsdao->get_available_seats($searched_course, $this_section);
                    }

                    echo "
                    <tr>
                        <td>$course_concat</td>
                        <td>$this_section</td>
                        <td>$this_day $this_start-$this_end</td>
                        <td>$this_instructor</td>
                    ";

                    if($current_round == 2) {
                        echo "
                        <td>$vacancy</td>
                        <td>$min_bid</td>
                        ";
                    }

                    echo "
                        <td>
                            <form id='drop_form'>
                                <input type='hidden' name='token' value=$token>
                                <input type='hidden' name='searched_course' value=$searched_course>
                                <input type='hidden' name='bid_course' value=$searched_course>
                                <input type='hidden' name='bid_section' value=$this_section>
                                Amount: <input type='text' name='bid_amount' size=1px>
                                <input type='submit' value='Bid' id='submit_button'>
                            </form>
                        </td>
                    </tr>
                    ";
                }

                echo "
                </table><br>
                ";
            }
        }

        if(isset($_GET['bid_amount'])) { // if user submitted a bid
            if(trim($_GET['bid_amount']) == "") {
                echo "<span id='error'>Please enter a bid amount.</span><br>";
            } else {
                if($current_round == 1) {
                    $bid_check_success = round1_bid_check($bid_amount, $bid_course, $bid_section);
                    if($bid_check_success == "success") {
                        $balance = $studentdao->get_balance($_SESSION["userid"]);
                        echo "You have successfully bidded $$bid_amount for $bid_course $bid_section.<br>
                            Your current balance is $$balance.";
                    } else { // return errors
                        echo "<strong><span id='error'>Errors:</span></strong><br>";
                        $error_counter = 1;
                        foreach($bid_check_success as $error) {
                            echo "<span id='error'>$error_counter. $error</span><br>";
                            $error_counter++;
                        }
                    }
                } elseif($current_round == 2) {
                    $bid_check_success = round2_bid_check($bid_amount, $bid_course, $bid_section);
                    if($bid_check_success == "success") {
                        $balance = $studentdao->get_balance($_SESSION["userid"]);
                        echo "You have successfully bidded $$bid_amount for $bid_course $bid_section.<br>
                            Your current balance is $$balance.<br>";                        
                    } else { // return errors
                        echo "<strong><span id='error'>Errors:</span></strong><br>";
                        $error_counter = 1;
                        foreach($bid_check_success as $error) {
                            echo "<span id='error'>$error_counter. $error</span><br>";
                            $error_counter++;
                        }
                    }
                }
            }
        }
    }

?>

</div>
</html>