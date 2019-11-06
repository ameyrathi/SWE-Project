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
    $sectionresultsdao = new SectionResultsDAO();
    $round_message = $biddingrounddao->get_round_message();
    $_SESSION["name"] = $StudentDAO->get_name($_SESSION["userid"]);

    // check if user clicked dropped bid in table's 'action' column
    if(isset($_GET['drop_bid_courseid']) && isset($_GET['drop_bid_sectionid']) && isset($_GET['drop_bid_amount'])) {
        require_once 'process_drop_bid.php';

        [$drop_bid_courseid, $drop_bid_sectionid, $drop_bid_amount] = [$_GET['drop_bid_courseid'], $_GET['drop_bid_sectionid'], $_GET['drop_bid_amount']];

        $drop_success = drop_bid($drop_bid_courseid, $drop_bid_sectionid);

        if($drop_success) {
            $balance = $StudentDAO->get_balance($_SESSION["userid"]);
            $drop_message = "<span id='success'>Your bid for $drop_bid_courseid $drop_bid_sectionid has been successfully dropped.<br>You have been refunded $$drop_bid_amount.<br>Your e-balance is now $$balance.</span><br>";
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
            $drop_message = "<span id='success'>Your section in $drop_section_courseid $drop_section_sectionid has been successfully dropped.<br>You have been refunded $$drop_section_amount.<br>Your e-balance is now $$balance.</span><br>";
        } else {
            echo "ERROR - FAILED TO DROP BID. TO DEBUG";
        }
    } else {
        $balance = $StudentDAO->get_balance($_SESSION["userid"]);
    }
?>


<!-- Page content -->
<div class="content">
    <h1>Welcome to BIOS, <?php echo $_SESSION["name"]; ?>! 
    Your e$ balance is $<?php echo $balance; ?>.</h1><br>

<?php

    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $biddao = new BidDAO();
    $successfuldao = new SuccessfulDAO();
    $unsuccessfuldao = new UnsuccessfulDAO();
    $current_round = $biddingrounddao->get_current_round();
    $round_message = $biddingrounddao->get_round_message();

    // round 2: real-time results
    if($current_round == 2) {

        echo "<strong>Pending Bids:</strong><br><br>";

        echo "
        <table id='view_results'>
        <tr>
            <th>Course</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Minimum Bid</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        ";

        $round2_pending_bids = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 2);

            $round2_pending_courses = array_column($round2_pending_bids, 0);

            foreach($round2_pending_bids as [$course, $section, $amount]) {
                $min_bid = $sectionresultsdao->get_min_bid($course, $section);
                $status = $biddao->get_round2_bid_status($_SESSION["userid"], $course);

                echo 
                "
                <tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>
                    <td>$min_bid</td>
                    <td>$status</td>
                    <td align='center'>
                        <form id='drop_form'>
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

        echo "</table><br>";

        $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

        $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

        echo "<strong>Round 1 Results:</strong><br><br>";

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

        foreach($round1_successful_bids as [$course, $section, $amount]) {
            echo 
            "
            <tr>
                <td>$course</td>
                <td>$section</td>
                <td>$amount</td>
                <td>Successful (Round 1)</td>
                <td align='center'>
                    <form id='drop_form'>
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
                    <td></td>
                </tr>
                ";
            }
        }     
        echo "</table>";

    } elseif($current_round == 0.5) {
        echo "<strong>$round_message</strong>";

    } elseif($current_round == 1) {
        echo "<strong>Pending Bids:</strong><br><br>";

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

        $round1_bids = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 1);
        foreach($round1_bids as $this_bid) {
            [$course, $section, $amount] = $this_bid;
            echo "<tr>
                    <td>$course</td>
                    <td>$section</td>
                    <td>$amount</td>
                    <td>Pending</td>
                    <td align='center'>
                        <form id='drop_form'>
                            <input type='hidden' name='drop_bid_courseid' value=$course>
                            <input type='hidden' name='drop_bid_sectionid' value=$section>
                            <input type='hidden' name='drop_bid_amount' value=$amount>
                            <input type='hidden' name='token' value=$token>
                            <input type='submit' value='Drop Bid' id='drop_button'>
                        </form>
                    </td>
                </tr>";  
        }

        echo "</table>";
    } elseif($current_round == 1.5) {
        $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

        $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

        echo "<strong>Round 1 Results:</strong><br><br>";

        echo "
        <table id='view_results'>
        <tr>
            <th>Course</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Status</th>
        </tr>
        ";

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

            echo "</table>";
        }
    } elseif($current_round == 2.5) {
        $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

        $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

        $round2_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 2);

        $round2_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 2);

        $round2_successful_courses = array_column($round2_successful_bids, 0);
        $round2_unsuccessful_courses = array_column($round2_unsuccessful_bids, 0);

        echo "<strong>Bidding Results:</strong><br><br>";

        echo "
        <table id='view_results'>
        <tr>
            <th>Course</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Status</th>
        </tr>
        ";

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

        echo "</table>";
    }

    // echo "<br><br>";
    // // view results segment
    // echo "<strong>Your Results:</strong><br><br>";
    // $StudentDAO = new StudentDAO();
    // $biddingrounddao = new BiddingRoundDAO();
    // $current_round = $biddingrounddao->get_current_round();
    // $round_message = $biddingrounddao->get_round_message();

    // if($current_round == 0.5) {
    //     echo "<h1>$round_message</h1>";
    // } else {
    //     $biddao = new BidDAO();
    //     $successfuldao = new SuccessfulDAO();
    //     $unsuccessfuldao = new UnsuccessfulDAO();

    //     echo "
    //     <table id='view_results'>
    //     <tr>
    //         <th>Course</th>
    //         <th>Section</th>
    //         <th>Bid Amount</th>
    //         <th>Status</th>
    //         <th>Action</th>
    //     </tr>
    //     ";

    //     if($current_round == 1) { // round 1 ongoing
    //         $round1_bids = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 1);
    //         foreach($round1_bids as $this_bid) {
    //             [$course, $section, $amount] = $this_bid;
    //             echo "<tr>
    //                     <td>$course</td>
    //                     <td>$section</td>
    //                     <td>$amount</td>
    //                     <td>Pending</td>
    //                     <td align='center'>
    //                         <form id='drop_form'>
    //                             <input type='hidden' name='drop_bid_courseid' value=$course>
    //                             <input type='hidden' name='drop_bid_sectionid' value=$section>
    //                             <input type='hidden' name='drop_bid_amount' value=$amount>
    //                             <input type='hidden' name='token' value=$token>
    //                             <input type='submit' value='Drop Bid' id='drop_button'>
    //                         </form>
    //                     </td>
    //                 </tr>";  
    //         }     
    //     } elseif($current_round == 1.5) { // round 1 ended, round 2 hasn't started

    //         $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

    //         $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

    //         foreach($round1_successful_bids as [$course, $section, $amount]) {
    //             echo 
    //             "
    //             <tr>
    //                 <td>$course</td>
    //                 <td>$section</td>
    //                 <td>$amount</td>
    //                 <td>Successful (Round 1)</td>
    //                 <td></td>
    //             </tr>
    //             ";
    //         }

    //         foreach($round1_unsuccessful_bids as [$course, $section, $amount]) {
    //             echo 
    //             "
    //             <tr>
    //                 <td>$course</td>
    //                 <td>$section</td>
    //                 <td>$amount</td>
    //                 <td>Unsuccessful (Round 1)</td>
    //                 <td></td>
    //             </tr>
    //             ";
    //         }
    //     } elseif($current_round == 2) { // round 2 ongoing
    //         $round2_pending_bids = $biddao->get_pending_bids_and_amount($_SESSION["userid"], 2);

    //         $round2_pending_courses = array_column($round2_pending_bids, 0);

    //         $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

    //         $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

    //         foreach($round2_pending_bids as [$course, $section, $amount]) {
    //             echo 
    //             "
    //             <tr>
    //                 <td>$course</td>
    //                 <td>$section</td>
    //                 <td>$amount</td>
    //                 <td>Pending</td>
    //                 <td align='center'>
    //                     <form id='drop_form'>
    //                         <input type='hidden' name='drop_bid_courseid' value=$course>
    //                         <input type='hidden' name='drop_bid_sectionid' value=$section>
    //                         <input type='hidden' name='drop_bid_amount' value=$amount>
    //                         <input type='hidden' name='token' value=$token>
    //                         <input type='submit' value='Drop Bid' id='drop_button'>
    //                     </form>
    //                 </td>
    //             </tr>
    //             ";
    //         }

    //         foreach($round1_successful_bids as [$course, $section, $amount]) {
    //             echo 
    //             "
    //             <tr>
    //                 <td>$course</td>
    //                 <td>$section</td>
    //                 <td>$amount</td>
    //                 <td>Successful (Round 1)</td>
    //                 <td align='center'>
    //                     <form id='drop_form'>
    //                         <input type='hidden' name='drop_section_courseid' value=$course>
    //                         <input type='hidden' name='drop_section_sectionid' value=$section>
    //                         <input type='hidden' name='drop_section_amount' value=$amount>
    //                         <input type='hidden' name='token' value=$token>
    //                         <input type='submit' value='Drop Section' id='drop_button'>
    //                     </form>
    //                 </td>

    //             </tr>
    //             ";
    //         }

    //         foreach($round1_unsuccessful_bids as [$course, $section, $amount]) {
    //             if(!in_array($course, $round2_pending_courses)) {
    //                 echo 
    //                 "
    //                 <tr>
    //                     <td>$course</td>
    //                     <td>$section</td>
    //                     <td>$amount</td>
    //                     <td>Unsuccessful (Round 1)</td>
    //                     <td></td>
    //                 </tr>
    //                 ";
    //             }
    //         }     
    //     } elseif($current_round == 2.5) {
    //         $round1_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);

    //         $round1_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 1);

    //         $round2_successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 2);

    //         $round2_unsuccessful_bids = $unsuccessfuldao->get_unsuccessful_bids_and_amount($_SESSION["userid"], 2);

    //         $round2_successful_courses = array_column($round2_successful_bids, 0);
    //         $round2_unsuccessful_courses = array_column($round2_unsuccessful_bids, 0);


    //         foreach($round1_successful_bids as [$course, $section, $amount]) {
    //             echo 
    //             "
    //             <tr>
    //                 <td>$course</td>
    //                 <td>$section</td>
    //                 <td>$amount</td>
    //                 <td>Successful (Round 1)</td>
    //                 <td></td>
    //             </tr>
    //             ";
    //         }

    //         foreach($round2_successful_bids as [$course, $section, $amount]) {
    //             echo 
    //             "
    //             <tr>
    //                 <td>$course</td>
    //                 <td>$section</td>
    //                 <td>$amount</td>
    //                 <td>Successful (Round 2)</td>
    //                 <td></td>
    //             </tr>
    //             ";
    //         }

    //         foreach($round2_unsuccessful_bids as [$course, $section, $amount]) {
    //             if(!in_array($course, $round2_unsuccessful_bids)) {
    //                 echo 
    //                 "
    //                 <tr>
    //                     <td>$course</td>
    //                     <td>$section</td>
    //                     <td>$amount</td>
    //                     <td>Unsuccessful (Round 2)</td>
    //                     <td></td>
    //                 </tr>
    //                 ";
    //             }
    //         }    

    //         foreach($round1_unsuccessful_bids as [$course, $section, $amount]) {
    //             if(!in_array($course, $round2_successful_courses) && !in_array($course, $round2_unsuccessful_courses)) {
    //                 echo 
    //                 "
    //                 <tr>
    //                     <td>$course</td>
    //                     <td>$section</td>
    //                     <td>$amount</td>
    //                     <td>Unsuccessful (Round 1)</td>
    //                     <td></td>
    //                 </tr>
    //                 ";
    //             }
    //         }     
    //     }
    // }
    // echo "</table><br>";

    // if(isset($drop_message)) {
    //     echo "<br>$drop_message";
    // }


    // view timetable segment
    if($current_round == 1 || $current_round == 1.5 || $current_round == 2 || $current_round == 2.5) {
        echo "<br><strong>Your Timetable:</strong><br><br>
        
        <div class='color-box' style='background-color:rgb(243, 182, 52);'></div> - Pending Bid<br>
        <div class='color-box' style='background-color:rgb(0, 223, 30);'></div> - Successful Section
        <br><br>"
        ;

        echo 
        "
        <table id='timetable'>
            <tr>
                <th id='blankdayslot'></th>
                <th id='dayslot'>Mon</th>
                <th id='dayslot'>Tue</th>
                <th id='dayslot'>Wed</th>
                <th id='dayslot'>Thu</th>
                <th id='dayslot'>Fri</th>
            </tr>
        ";

        $days = [1, 2, 3, 4, 5];
        $timeslots = ['08:30', '11:45', '12:00', '15:15', '15:30', '18:45', '19:00', '22:15'];
        $break_timeslots = ['11:45', '15:15', '18:45', '22:15'];

        $sectiondao = new SectionDAO();

        if($current_round == 2.5) {
            $successful_sections = $successfuldao->get_student_successful_bids($_SESSION["userid"],2);
        }

        if($current_round == 1.5 || $current_round == 2) {
            $successful_sections = $successfuldao->get_student_successful_bids($_SESSION["userid"],1);
        }


        foreach($timeslots as $timeslot) {

            if(in_array($timeslot, $break_timeslots)) {
                $height_style = "style='height:5px;'";
            } else {
                $height_style = "";
            }

            echo "<tr><th id='timeslot' $height_style>$timeslot</th>";

            $timeslot = date("H:i:s",strtotime($timeslot));

            for($timetable_day=1; $timetable_day<=5; $timetable_day++) {
                $printed = false;

                if($current_round != 1) {
                    foreach($successful_sections as [$this_course, $this_section, $this_amount]) {
                        [$day, $start, $end] = $sectiondao->get_timetable_details($this_course, $this_section);
                        
                        if($day == $timetable_day && $start == $timeslot) {
                            echo "
                            <td id='confirmed_timeslot' $height_style>
                                $this_course $this_section<br>
                                Bid: $$this_amount
                            ";

                            if($current_round == 2) {
                                echo 
                                "
                                <br>
                                <form id='drop_form'>
                                    <input type='hidden' name='drop_section_courseid' value=$this_course>
                                    <input type='hidden' name='drop_section_sectionid' value=$this_section>
                                    <input type='hidden' name='drop_section_amount' value=$this_amount>
                                    <input type='hidden' name='token' value=$token>
                                    <input type='submit' value='Drop Section' id='drop_button'>
                                </form>
                                ";
                            }

                            echo "</td>";

                            $printed = true;
                            break;
                        }
                    }
                }

                if(!$printed && ($current_round == 1 || $current_round == 2)) {
                    $pending_sections = $biddao->get_pending_bids_and_amount($_SESSION["userid"], $current_round);

                    foreach($pending_sections as [$this_course, $this_section, $this_amount]) {
                        [$day, $start, $end] = $sectiondao->get_timetable_details($this_course, $this_section);
                        
                        if($day == $timetable_day && $start == $timeslot) {
                            echo "
                            <td id='pending_timeslot' $height_style>
                                $this_course $this_section<br>
                                Bid: $$this_amount<br>
                                <form id='drop_form'>
                                    <input type='hidden' name='drop_bid_courseid' value=$this_course>
                                    <input type='hidden' name='drop_bid_sectionid' value=$this_section>
                                    <input type='hidden' name='drop_bid_amount' value=$this_amount>
                                    <input type='hidden' name='token' value=$token>
                                    <input type='submit' value='Drop Bid' id='drop_button'>
                                </form>
                            </td>
                            ";

                            $printed = true;
                            break;
                        }
                    }
                }

                if(!$printed) {
                    echo "<td id='timeslot_data' $height_style></td>";
                }
            }
            echo "</tr>";
        }
        echo "</table><br><br>";
    }
?>
</div>
</html>