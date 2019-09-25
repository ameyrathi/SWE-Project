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
    
    $biddingrounddao = new BiddingRoundDAO();
    $current_round = $biddingrounddao->checkBiddingRound();
?>

<!-- The sidebar -->
<div class="sidebar">
  <a href="student_home.php?token=<?php echo $token?>">Home</a>
  <a href="student_add_bid.php?token=<?php echo $token?>">Bid</a>
  <a class="active" href="student_drop_bid.php?token=<?php echo $token?>">Drop Bid</a>
  <a href="student_view_bid.php?token=<?php echo $token?>">View Bids</a>
  <a href="student_view_results.php?token=<?php echo $token?>">View Results</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<div class="content">

<h1>Current bidding round: <?php echo $bidding_round ?><br><br></h1>
<form>
    Course: <input type="text" name="drop_courseid" value=<?php echo $drop_courseid?>><br><br>
    Section: <input type="text" name="drop_section" value=<?php echo $drop_section?>><br><br>
<input type="submit"/>
</form><br>

</div>
</html>