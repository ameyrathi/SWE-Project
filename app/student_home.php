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
  <a href="student_drop_section.php?token=<?php echo $token?>">Drop Section</a>
  <a href="student_view_results.php?token=<?php echo $token?>">View Results</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<?php
    $StudentDAO = new StudentDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $round_message = $biddingrounddao->get_round_message();
    $_SESSION["name"] = $StudentDAO->get_name($_SESSION["userid"]);
    $balance = $StudentDAO->get_balance($_SESSION["userid"]);
?>


<!-- Page content -->
<div class="content">
    <h1>Welcome to BIOS, <?php echo $_SESSION["name"]; ?>!</h1>
    <h2>Your e$ balance is $<?php echo $balance; ?>.</h2><br><br><br>

<?php
    echo "<h1>$round_message</h1>";
?>
</div>




</html>