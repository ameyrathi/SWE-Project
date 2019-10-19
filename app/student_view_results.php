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
  <a href="sign_out.php?token=<?php echo $token?>">Sign Out</a>
</div>


<div class="content">

<?php

    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $current_round = $biddingrounddao->get_current_round();
    $round_message = $biddingrounddao->get_round_message();

    if($current_round == 0.5) {
        echo "<h1>$round_message</h1>";
    } else {
        $biddao = new BidDAO();
        $successfuldao = new SuccessfulDAO();
        $unsuccessfuldao = new UnsuccessfulDAO();

        echo "
        <table id='view_results'>
        <tr>
            <th>Course</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Status</th>
        </tr>
        ";

        if($current_round == 1) { // round 1 ongoing
            $round1_bids = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 1);
            foreach($round1_bids as $this_bid) {
                [$course, $section, $amount] = $this_bid;
                echo "<tr>
                        <td>$course</td>
                        <td>$section</td>
                        <td>$amount</td>
                        <td>Pending</td>
                    </tr>";  
            }     
        } elseif($current_round == 1.5) { // round 1 ended, round 2 hasn't started

            $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

            $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

            foreach($round1_successful_bids as [$course, $section, $amount]) {
                echo 
                "
                <tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>
                    <td>Successful (Round 1)</td>
                </tr>
                ";
            }

            foreach($round1_unsuccessful_bids as [$course, $section, $amount]) {
                echo 
                "
                <tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>
                    <td>Unsuccessful (Round 1)</td>
                </tr>
                ";
            }
        } elseif($current_round == 2) { // round 2 ongoing
            $round2_pending_bids = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 2);

            $round2_pending_courses = array_column($round2_pending_bids, 0);

            $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

            $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

            foreach($round2_pending_bids as [$course, $section, $amount]) {
                echo 
                "
                <tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>
                    <td>Pending</td>
                </tr>
                ";
            }

            foreach($round1_successful_bids as [$course, $section, $amount]) {
                echo 
                "
                <tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>
                    <td>Successful (Round 1)</td>
                </tr>
                ";
            }

            foreach($round1_unsuccessful_bids as [$course, $section, $amount]) {
                if(!in_array($course, $round2_pending_courses)) {
                    echo 
                    "
                    <tr>
                        <td>$course</td>
                        <td>$section</td>
                        <td>$amount</td>
                        <td>Unsuccessful (Round 1)</td>
                    </tr>
                    ";
                }
            }     
        } elseif($current_round == 2.5) {
            $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

            $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

            $round2_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 2);

            $round2_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 2);

            $round2_successful_courses = array_column($round2_successful_bids, 0);
            $round2_unsuccessful_courses = array_column($round2_unsuccessful_bids, 0);


            foreach($round1_successful_bids as [$course, $section, $amount]) {
                echo 
                "
                <tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>
                    <td>Successful (Round 1)</td>
                </tr>
                ";
            }

            foreach($round2_successful_bids as [$course, $section, $amount]) {
                echo 
                "
                <tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>
                    <td>Successful (Round 2)</td>
                </tr>
                ";
            }

            foreach($round2_unsuccessful_bids as [$course, $section, $amount]) {
                if(!in_array($course, $round2_unsuccessful_bids)) {
                    echo 
                    "
                    <tr>
                        <td>$course</td>
                        <td>$section</td>
                        <td>$amount</td>
                        <td>Unsuccessful (Round 2)</td>
                    </tr>
                    ";
                }
            }    

            foreach($round1_unsuccessful_bids as [$course, $section, $amount]) {
                if(!in_array($course, $round2_successful_courses) && !in_array($course, $round2_unsuccessful_courses)) {
                    echo 
                    "
                    <tr>
                        <td>$course</td>
                        <td>$section</td>
                        <td>$amount</td>
                        <td>Unsuccessful (Round 1)</td>
                    </tr>
                    ";
                }
            }     
        }
    }
?>

</table>
</div>