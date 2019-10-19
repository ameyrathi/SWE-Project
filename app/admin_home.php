<?php
    require_once 'include/common.php';
    require_once 'include/protect_token.php';

    if(isset($_GET["token"])) {
        $token = $_GET["token"];
    } else {
        $token = "";
    }

    token_gateway($token);

    require_once 'round1_closing.php';
    require_once 'round2_closing.php';

    $biddingrounddao = new BiddingRoundDAO();
    $round_message = $biddingrounddao->get_round_message();
?>

<html>

<link rel="stylesheet" href="stylesheet.css"/>

<!-- The sidebar -->
<div class="sidebar">
  <a class="active" href="admin_home.php?token=<?php echo $token;?>">Home</a>
  <a href="admin_bootstrap.php?token=<?php echo $token;?>">Bootstrap</a>
  <a href="sign_out.php">Sign Out</a>
</div>


<div class="content">
<body>
<p>
<h1>Welcome, Admin!</h1>
<?php
   
    if(isset($_GET["token"])) {
        $token = $_GET["token"];
    } else {
        $token = "";
    }

    token_gateway($token);


    $biddingrounddao = new BiddingRoundDAO();

    if(isset($_GET['stop_round1'])) {
        $biddingrounddao->stop_round(1);
        header("Location: admin_home.php?token=$token&run_stop_round1=true");
    } elseif(isset($_GET['start_round2'])) {
        $biddingrounddao->start_round(2);
        header("Location: admin_home.php?token=$token&run_start_round2=true");
    } elseif(isset($_GET['stop_round2'])) {
        $biddingrounddao->stop_round(2);
        header("Location: admin_home.php?token=$token&run_stop_round2=true");
    }
?>

<!-- The sidebar -->


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
?>
</p>
</body>
</div>
</html>
