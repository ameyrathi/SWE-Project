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
  <a href="student_drop_bid.php?token=<?php echo $token?>">Drop Bid</a>
  <a href="student_drop_section.php?token=<?php echo $token?>">Drop Section</a>
  <a href="student_view_results.php?token=<?php echo $token?>">View Results</a>
  <a href="sign_out.php?token=<?php echo $token?>">Sign Out</a>
</div>


<div class='content'>
<?php 
    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $biddao = new BidDAO();
    $sectionresultsdao = new SectionResultsDAO();

    $current_round = $biddingrounddao->get_current_round();
    $round_message = $biddingrounddao->get_round_message();

    if($current_round == 0.5 || $current_round == 1.5 || $current_round == 2.5) {
        echo "<h1>$round_message</h1>";
    } elseif($current_round == 1 || $current_round == 2) { // round 1 ongoing or round 2 ongoing
        $bid_course = "";
        $bid_section = "";
        $bid_amount = "";

        if(isset($_GET["bid_courseid"])) {
            $bid_course = strtoupper($_GET["bid_courseid"]);
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

            Course: <input type='text' name='bid_courseid' value=$bid_course><br><br>
            Section: <input type='text' name='bid_section' value=$bid_section><br><br>
            Bid Amount: <input type='text' name='bid_amount' value=$bid_amount><br><br>
            <input type='submit' name='submit' value='Submit'>
        </form>
        <br>
        ";

        $StudentDAO = new StudentDAO();

               
        if(!in_array("", [$bid_amount, $bid_course, $bid_section])) {
            if($current_round == 1) {
                $bid_check_success = round1_bid_check($bid_amount, $bid_course, $bid_section);
                if($bid_check_success == "success") {
                    $balance = $StudentDAO->get_balance($_SESSION["userid"]);
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
                    $balance = $StudentDAO->get_balance($_SESSION["userid"]);
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
        } else {
            if(isset($_GET['submit'])) { // if user had submitted form
                $empty_errors = [];
                if(empty($_GET['bid_courseid'])) {
                    $empty_errors[] = "Please enter a course ID.";
                }
                if(empty($_GET['bid_section'])) {
                    $empty_errors[] = "Please enter a section ID.";
                }
                if(empty($_GET['bid_amount'])) {
                    $empty_errors[] = "Please enter an amount.";
                }

                $empty_error_counter = 1;   
                echo "<strong><span id='error'>Errors:</span></strong><br>";

                foreach($empty_errors as $this_error) {
                    echo "<span id='error'>$empty_error_counter. $this_error<br></span>";
                    $empty_error_counter ++;
                }
            }
        }

        $round2_pending_bids = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 2);

        if($round2_pending_bids != []) {
            echo "<br><strong>Real-time Bid Information:</strong><br><br>";
            echo "
            <table id='real_time_bids'>
            <tr>
                <th>Course</th>
                <th>Section</th>
                <th>Available Seats</th>
                <th>Minimum Bid</th>
                <th>Your Bid</th>
                <th>Bid Status</th>
            </tr>
            ";

            foreach($round2_pending_bids as $this_bid) {
                [$this_course, $this_section, $this_amount] = $this_bid;

                $total_available_seats = $sectionresultsdao->get_available_seats($this_course, $this_section);
                $min_bid = $sectionresultsdao->get_min_bid($this_course, $this_section);
                $bid_status = $biddao->get_round2_bid_status($_SESSION["userid"], $this_course);

                echo "
                <tr>
                    <td>$this_course</td>
                    <td>$this_section</td>
                    <td>$total_available_seats</td>
                    <td>$$min_bid</td>
                    <td>$$this_amount</td>
                    <td>$bid_status</td>
                </tr>
                ";
            }
        }
    }

?>

</div>
</html>