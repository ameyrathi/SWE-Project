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

    require_once 'process_min_bid.php';
?>

<!-- The sidebar -->
<div class="sidebar">
  <a href="student_home.php?token=<?php echo $token?>">Home</a>
  <a href="student_add_bid.php?token=<?php echo $token?>">Bid</a>
  <a class="active" href="student_drop_bid.php?token=<?php echo $token?>">Drop Bid</a>
  <a href="student_drop_section.php?token=<?php echo $token?>">Drop Section</a>
  <a href="student_view_results.php?token=<?php echo $token?>">View Results</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<div class="content">

<?php

    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $current_round = $biddingrounddao->get_current_round();
    $round_message = $biddingrounddao->get_round_message();

    if($current_round == 0.5 || $current_round == 1.5 || $current_round == 2.5) {
        echo "<h1>$round_message</h1>";
    } elseif($current_round == 1 || $current_round == 2) {
        $drop_courseid = "";
        $drop_section = "";

        if(isset($_GET["drop_courseid"])) {
            $drop_courseid = strtoupper($_GET["drop_courseid"]);
        }
        if(isset($_GET["drop_section"])) {
            $drop_section = strtoupper($_GET["drop_section"]);
        }
        
        echo "
        <h1>Current bidding round: $current_round<br><br></h1>
        <form>
            <input type='hidden' name='token' value=$token>

            Course: <input type='text' name='drop_courseid' value=$drop_courseid><br><br>
            Section: <input type='text' name='drop_section' value=$drop_section><br><br>
        <input type='submit'/>
        </form><br>
        ";

        $BidDAO = new BidDAO();
        $list_of_bids = $BidDAO->get_pending_bids_and_amount($_SESSION["userid"], $current_round);

        $bid_valid = false;

        if($drop_courseid != "" && $drop_section != "") {
            foreach($list_of_bids as $this_list) {
                $this_courseid = $this_list[0];
                $this_section = $this_list[1];
                if($drop_courseid == $this_courseid && $drop_section == $this_section) {
                    $bid_valid = true;
                    $this_amount = $this_list[2];
                    break;
                }
            }
            if($bid_valid) {
                $BidDAO = new BidDAO();
                $StudentDAO = new StudentDAO();
                $drop_success = $BidDAO->drop_bid($_SESSION["userid"], $drop_courseid, $current_round) && $StudentDAO->add_balance($_SESSION["userid"], $this_amount);
                $new_balance = $StudentDAO->get_balance($_SESSION["userid"]);
                if($drop_success) {
                    echo "<strong>Your bid for $drop_courseid $drop_section has been successfully dropped.<br>";
                    echo "You have been refunded $$this_amount. Your current e$ balance is $$new_balance.</strong>";

                    if($current_round == 2) {
                        process_min_bid($drop_courseid, $drop_section);
                    }
                }
            } else {
                echo "<strong><span id='error'>Error:</span></strong><br><br>";
                echo "<span id='error'>$drop_courseid $drop_section is not a course you have bidded for.</span>";
            }
        }
    }




?>

</p>


</div>
</html>