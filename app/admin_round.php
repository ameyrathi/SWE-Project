<html>

<link rel="stylesheet" href="stylesheet.css"/>

<?php
    require_once 'include/common.php';
    require_once 'round1_closing.php';
    require_once 'round2_closing.php';
    require_once 'include/protect_token.php';

    if(isset($_GET["token"])) {
        $token = $_GET["token"];
    } else {
        $token = "";
    }

    token_gateway($token);


    $biddingrounddao = new BiddingRoundDAO();

    if(isset($_GET['stop_round1'])) {
        $biddingrounddao->stop_round(1);
        header("Location: admin_round.php?token=$token&run_stop_round1=true");
    } elseif(isset($_GET['start_round2'])) {
        $biddingrounddao->start_round(2);
        header("Location: admin_round.php?token=$token&run_start_round2=true");
    } elseif(isset($_GET['stop_round2'])) {
        $biddingrounddao->stop_round(2);
        header("Location: admin_round.php?token=$token&run_stop_round2=true");
    }
?>

<!-- The sidebar -->
<div class="sidebar">
  <a href="admin_home.php?token=<?php echo $token;?>">Home</a>
  <a class="active" href="admin_round.php?token=<?php echo $token;?>">Round Management</a>
  <a href="admin_bootstrap.php?token=<?php echo $token;?>">Bootstrap</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<div class="content">
<?php
    $round_message = $biddingrounddao->get_round_message();
    $round_status = $biddingrounddao->get_current_round(); // 0.5, 1, 1.5, 2 or 2.5

    if($round_status == 0.5) {
        echo "<h1>Please conduct bootstrapping to open Round 1 bidding.</h1>";
    } elseif($round_status == 1) {
        echo "<h1>$round_message</h1>";

        echo
        "
        <form>
            <input type='hidden' name='token' value=$token>
            <input type='submit' name='stop_round1' value='Stop Round 1'>
        </form>        
        ";
    } elseif($round_status == 1.5) {
        echo "<h1>$round_message</h1>";

        echo 
        "
        <form>
            <input type='hidden' name='token' value=$token>
            <input type='submit' name='start_round2' value='Start Round 2'>
        </form>        
        ";

        if(isset($_GET['run_stop_round1'])) {
            require_once 'round1_closing.php';

            close_bidding_round1();
        }
    } elseif($round_status == 2) {
        echo "<h1>$round_message</h1>";

        echo
            "
            <form>
                <input type='hidden' name='token' value=$token>
                <input type='submit' name='stop_round2' value='Stop Round 2'>
            </form>
            ";
    } elseif($round_status == 2.5) {
        echo "<h1>$round_message</h1>";

        if(isset($_GET['run_stop_round2'])) {
            require_once 'round2_closing.php';

            close_bidding_round2();
        }
    }





    // echo "<p>";
    // $BiddingRoundDAO = new BiddingRoundDAO();

    // if(isset($_GET["close_round"])) {
    //     $round_to_close = $_GET["close_round"];
        
    //     if($round_to_close == "Close Round 1") { // if admin wants to close round 1
    //         $round1_close_message = close_bidding_round1();
    //     } elseif($round_to_close == "Close Round 2") { // if admin wants to close round 2
    //         $round2_close_message = close_bidding_round2(); // TO DO ROUND 2 CLOSING
    //     }
    // }

    // $current_round = $BiddingRoundDAO->checkBiddingRound();

    // if($current_round == 3) {
    //     echo "<h1>Round 2 has ended.</h1><br>";
    // } else {
    //     if($current_round != null) {
    //         echo "<h1>Bidding Round $current_round is ongoing.</h1><br>
    //         <form>
    //         <input type='hidden' name='token' value=$token>
            
    //         <input type='submit' value='Close Round $current_round' name='close_round'>
    //         </form><br>";
    //     } else { // before round 1, ie. bootstrapping not done yet
    //         echo "<h1>Please conduct bootstrapping to open Round 1 bidding.</h1>";
    //     }
    // }

    // // closing message is placed here bc it must be after "Bidding Round X is ongoing" message
    // if(isset($round1_close_message)) {
    //     echo $round1_close_message;
    // }

    // if(isset($round2_close_message)) {
    //     echo $round2_close_message;
    // }
        
?>



</div>