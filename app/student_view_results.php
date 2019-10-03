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
  <a href="student_drop_bid.php?token=<?php echo $token?>">Drop Bid</a>
  <a class="active" href="student_view_results.php?token=<?php echo $token?>">View Results</a>
  <a href="sign_out.php?token=<?php echo $token?>">Sign Out</a>
</div>


<div class="content">

<?php

    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $current_round = $biddingrounddao->checkBiddingRound();

    if($current_round == null) {
        echo "<h2>Round 1 has not started.</h2>";
    } else {
        $biddao = new BidDAO();
        $successfuldao = new SuccessfulDAO();

        echo "
        <table id='view_results'>
        <tr>
            <th>Course</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Status</th>
        </tr>
        ";

        if($current_round == 1) {
            $round1_bids = $biddao->get_bids_by_student(1);
            foreach($round1_bids as $this_bid) {
                [$course, $section, $amount] = $this_bid;
                echo "<tr>
                        <td>$course</td>
                        <td>$section</td>
                        <td>$amount</td>
                        <td>Pending</td>
                    </tr>";  
            }      
        } elseif($current_round == 2) {
            $round1_bids = $biddao->get_bids_by_student(1);
            $round2_bids = $biddao->get_bids_by_student(2);

            $pending_courses = [];

            foreach($round2_bids as $this_bid) {
                [$course, $section, $amount] = $this_bid;

                echo "<tr>
                <td>$course</td>
                <td>$section</td>
                <td>$amount</td>
                <td>Pending</td>
                </tr>";

                array_push($pending_courses, $course);
            }

            foreach($round1_bids as $this_bid) {
                [$course, $section, $amount] = $this_bid;

                if(!in_array($course, $pending_courses)) {
                    echo "<tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>";
    
                    if($successfuldao->check_success($_SESSION["userid"], $course, $section, 1) != false) {
                        echo "<td>Successful</td>";
                    } else {
                        echo "<td>Unsuccessful</td>";
                    }
    
                    echo "</tr>";
                }
            }
        }
    } 
?>

</div>