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
    $current_round = $biddingrounddao->checkBiddingRound();

    if($current_round == null) {
        echo "<h2>Round 1 has not started.</h2>";
    } else {

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
            <input type='submit'/>
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
                        Your currently balance is $balance.";
                } else { // return errors
                    echo "<strong><span id='error'>Errors:</span></strong><br>";
                    $error_counter = 1;
                    foreach($bid_check_success as $error) {
                        echo "<span id='error'>$error_counter. $error</span><br>";
                    }
                }
            } elseif($current_round == 2) {
                $bid_check_success = round2_bid_check($bid_amount, $bid_course, $bid_section);
                if($bid_check_success == "success") {
                    $balance = $StudentDAO->get_balance($_SESSION["userid"]);
                    echo "You have successfully bidded $$bid_amount for $bid_course $bid_section.<br>
                        Your currently balance is $balance.<br>";
                    
                    // real time information
                    $sectionresultsdao = new SectionResultsDAO();
                    $total_available_seats = $sectionresultsdao->get_available_seats($bid_course, $bid_section);

                    echo 
                        "<h2>Real-time Bid Information:<br>
                        Course: $bid_course<br>
                        Section: $bid_section<br>
                        Total Available Seats: $total_available_seats<br>
                        Minimum Bid: 
                        ";
                        /////////////////////// TO CONTINUEEEEEEEEEE /////////////////////////
                        
                } else { // return errors
                    echo "<strong><span id='error'>Errors:</span></strong><br>";
                    $error_counter = 1;
                    foreach($bid_check_success as $error) {
                        echo "<span id='error'>$error_counter. $error</span><br>";
                    }
                }
            }
        }
    }

?>

</div>
</html>