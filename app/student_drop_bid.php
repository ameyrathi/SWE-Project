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
  <a class="active" href="student_drop_bid.php?token=<?php echo $token?>">Drop Bid</a>
  <a href="student_view_results.php?token=<?php echo $token?>">View Results</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<div class="content">

<?php

    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $current_round = $biddingrounddao->checkBiddingRound();

    if($current_round == null) {
        echo "<h2>Round 1 has not started.</h2>";
    } else {
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
        $list_of_bids = $BidDAO->get_bids_by_student($current_round);

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
                $drop_success = $BidDAO->drop_bid($drop_courseid, $current_round) && $StudentDAO->add_balance($this_amount);
                $new_balance = $StudentDAO->get_balance();
                if($drop_success) {
                    echo "<strong>Your bid for $drop_courseid $drop_section has been successfully dropped.<br>";
                    echo "You have been refunded $$this_amount. Your current e$ balance is $$new_balance.</strong>";
                }
            } else {
                echo "<strong>Error:</strong><br><br>";
                echo "$drop_courseid $drop_section is not a course you have bidded for.";
            }
        }
    }




?>

</p>


</div>
</html>