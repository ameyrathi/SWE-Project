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
  <a class="active" href="student_home.php?token=<?php echo $token?>">Home</a>
  <a href="student_add_bid.php?token=<?php echo $token?>">Bid</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<?php
    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $round_message = $biddingrounddao->get_round_message();
    $_SESSION["name"] = $StudentDAO->get_name($_SESSION["userid"]);

    // check if user clicked dropped bid in table's 'action' column
    if(isset($_GET['drop_bid_courseid']) && isset($_GET['drop_bid_sectionid']) && isset($_GET['drop_bid_amount'])) {
        require_once 'process_drop_bid.php';

        [$drop_bid_courseid, $drop_bid_sectionid, $drop_bid_amount] = [$_GET['drop_bid_courseid'], $_GET['drop_bid_sectionid'], $_GET['drop_bid_amount']];

        $drop_success = drop_bid($drop_bid_courseid, $drop_bid_sectionid);

        if($drop_success) {
            $balance = $StudentDAO->get_balance($_SESSION["userid"]);
            $drop_message = "<span id='success'>Your bid for $drop_bid_courseid $drop_bid_sectionid has been successfully dropped.<br>You have been refunded $$drop_bid_amount.<br>Your e-balance is now $$balance.</span>";
        } else {
            echo "ERROR - FAILED TO DROP BID. TO DEBUG";
        }
    } else {
        $balance = $StudentDAO->get_balance($_SESSION["userid"]);
    }

    // check if user clicked dropped section in table's 'action' column
    if(isset($_GET['drop_section_courseid']) && isset($_GET['drop_section_sectionid']) && isset($_GET['drop_section_amount'])) {
        require_once 'process_drop_section.php';

        [$drop_section_courseid, $drop_section_sectionid, $drop_section_amount] = [$_GET['drop_section_courseid'], $_GET['drop_section_sectionid'], $_GET['drop_section_amount']];

        $drop_success = drop_section($drop_section_courseid, $drop_section_sectionid);

        if($drop_success) {
            $balance = $StudentDAO->get_balance($_SESSION["userid"]);
            $drop_message = "<span id='success'>Your section in $drop_section_courseid $drop_section_sectionid has been successfully dropped.<br>You have been refunded $$drop_section_amount.<br>Your e-balance is now $$balance.</span>";
        } else {
            echo "ERROR - FAILED TO DROP BID. TO DEBUG";
        }
    } else {
        $balance = $StudentDAO->get_balance($_SESSION["userid"]);
    }
?>


<!-- Page content -->
<div class="content">
    <h1>Welcome to BIOS, <?php echo $_SESSION["name"]; ?>!</h1>
    <h1><?php echo $round_message;?></h1>
    <h2>Your e$ balance is $<?php echo $balance; ?>.</h2><br>

<?php
    // view results segment
    echo "<h1>Your Results:<h1>";
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
            <th>Action</th>
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
                        <td align='center'>
                            <form>
                                <input type='hidden' name='drop_bid_courseid' value=$course>
                                <input type='hidden' name='drop_bid_sectionid' value=$section>
                                <input type='hidden' name='drop_bid_amount' value=$amount>
                                <input type='hidden' name='token' value=$token>
                                <input type='submit' value='Drop Bid' id='drop_button'>
                            </form>
                        </td>
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
                    <td align='center'>
                        <form>
                            <input type='hidden' name='drop_section_courseid' value=$course>
                            <input type='hidden' name='drop_section_sectionid' value=$section>
                            <input type='hidden' name='drop_section_amount' value=$amount>
                            <input type='hidden' name='token' value=$token>
                            <input type='submit' value='Drop Section' id='drop_button'>
                        </form>
                    </td>
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
                    <td align='center'>
                        <form>
                            <input type='hidden' name='drop_bid_courseid' value=$course>
                            <input type='hidden' name='drop_bid_sectionid' value=$section>
                            <input type='hidden' name='drop_bid_amount' value=$amount>
                            <input type='hidden' name='token' value=$token>
                            <input type='submit' value='Drop Bid' id='drop_button'>
                        </form>
                    </td>
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
                    <td align='center'>
                        <form>
                            <input type='hidden' name='drop_section_courseid' value=$course>
                            <input type='hidden' name='drop_section_sectionid' value=$section>
                            <input type='hidden' name='drop_section_amount' value=$amount>
                            <input type='hidden' name='token' value=$token>
                            <input type='submit' value='Drop Section' id='drop_button'>
                        </form>
                    </td>

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
                    <td align='center'>
                        <form>
                            <input type='hidden' name='drop_section_courseid' value=$course>
                            <input type='hidden' name='drop_section_sectionid' value=$section>
                            <input type='hidden' name='drop_section_amount' value=$amount>
                            <input type='hidden' name='token' value=$token>
                            <input type='submit' value='Drop Section' id='drop_button'>
                        </form>
                    </td>

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
                    <td align='center'>
                        <form>
                            <input type='hidden' name='drop_section_courseid' value=$course>
                            <input type='hidden' name='drop_section_sectionid' value=$section>
                            <input type='hidden' name='drop_section_amount' value=$amount>
                            <input type='hidden' name='token' value=$token>
                            <input type='submit' value='Drop Section' id='drop_button'>
                        </form>
                    </td>

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
    echo "</table>";

    if(isset($drop_message)) {
        echo "<br><br>$drop_message";
    }


?>
</div>
</html>