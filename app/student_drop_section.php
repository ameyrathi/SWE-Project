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
  <a href="student_add_bid.php?token=<?php echo $token?>">Bid</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<div class="content">

<?php
    require_once 'process_min_bid.php';
    
    $studentdao = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $successfuldao = new SuccessfulDAO();
    $sectionresultsdao = new SectionResultsDAO();
    $biddao = new BidDAO();

    $current_round = $biddingrounddao->get_current_round();
    $round_message = $biddingrounddao->get_round_message();


    if($current_round == 0.5 || $current_round == 1 || $current_round == 2.5) {
        echo "<h1>$round_message</h1>";
    } elseif($current_round == 1.5 || $current_round == 2) { // round 1 ended, round 2 not started OR round 2 ongoing
        $drop_courseid = "";
        $drop_section = "";

        if(isset($_GET["drop_courseid"])) {
            $drop_courseid = strtoupper($_GET["drop_courseid"]);
        }
        if(isset($_GET["drop_section"])) {
            $drop_section = strtoupper($_GET["drop_section"]);
        }

        echo "
        <h1>Drop a section:<br><br></h1>
        <form>
            <input type='hidden' name='token' value=$token>

            Course: <input type='text' name='drop_courseid' value=$drop_courseid><br><br>
            Section: <input type='text' name='drop_section' value=$drop_section><br><br>
        <input type='submit' name='submit' value='Submit'>
        </form><br>
        ";

        if($drop_courseid != "" && $drop_section != "") {
            $successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);
            $drop_valid = false;

            foreach($successful_bids as $idx => [$successful_course, $successful_section, $successful_amount]) {
                if($successful_course == $drop_courseid && $successful_section == $drop_section) {
                    $drop_valid = true;
                    break;
                }
            }

            if($drop_valid) { // must also delete from round1_bid (else view results will show as unsuccessful)
                $drop_success = $successfuldao->drop_section($_SESSION["userid"], $drop_courseid, $drop_section) && $biddao->drop_bid($_SESSION["userid"], $drop_courseid, 1) && $sectionresultsdao->add_one_seat($drop_courseid, $drop_section) && process_min_bid($drop_courseid, $drop_section);
                
                if($drop_success) {
                    $refund_success = $studentdao->add_balance($_SESSION["userid"], $successful_amount);
                    if($refund_success) {
                        $new_balance = $studentdao->get_balance($_SESSION["userid"]);
                        echo "<strong>You have successfully dropped $drop_courseid $drop_section.<br>";
                        echo "You have been refunded $$successful_amount. Your current e$ balance is $$new_balance.</strong>";
                    }
                }
            } else { // if section student wants to drop isn't in successful tables
                echo "<strong><span id='error'>Error:</span></strong><br><br>";
                echo "<span id='error'>$drop_courseid $drop_section is not a course you have bidded for.</span>";
            }
        } else {
            if(isset($_GET['submit'])) { // if user had submitted form
                $empty_errors = [];
                if(empty($_GET['drop_courseid'])) {
                    $empty_errors[] = "Please enter a course ID.";
                }
                if(empty($_GET['drop_section'])) {
                    $empty_errors[] = "Please enter a section ID.";
                }

                $empty_error_counter = 1;   
                echo "<strong><span id='error'>Errors:</span></strong><br>";

                foreach($empty_errors as $this_error) {
                    echo "<span id='error'>$empty_error_counter. $this_error<br></span>";
                    $empty_error_counter ++;
                }
            }
        }
    }
?>

</div>
</html>