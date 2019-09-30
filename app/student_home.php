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
  <a href="student_drop_bid.php?token=<?php echo $token?>">Drop Bid</a>
  <a href="student_view_results.php?token=<?php echo $token?>">View Results</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<?php
    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $current_round = $biddingrounddao->checkBiddingRound();
    $_SESSION["name"] = $StudentDAO->get_name($_SESSION["userid"]);
    $balance = $StudentDAO->get_balance();
?>


<!-- Page content -->
<div class="content">
    <h1>Welcome to BIOS, <?php echo $_SESSION["name"]; ?>!</h1>
    <h2>Your e$ balance is $<?php echo $balance; ?>.</h2><br><br><br>

<?php
    echo "<h2>";
    if($current_round == 1 || $current_round == 2) { // if a current bidding round is ongoing
        echo "Round $current_round of bidding is currently ongoing.";
    } elseif($current_round == 3) { // if round 2 has ended
        echo "Round 2 has ended.";
    } else { // if round 1 hasn't started
        echo "Round 1 has not started.";
    }
    echo "</h2><br>";
?>
</div>




</html>