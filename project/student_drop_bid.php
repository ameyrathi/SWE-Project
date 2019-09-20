<html>

<link rel="stylesheet" href="stylesheet.css"/>

<!-- The sidebar -->
<div class="sidebar">
  <a href="student_home.php">Home</a>
  <a href="student_add_bid.php">Bid</a>
  <a class="active" href="student_drop_bid.php">Drop Bid</a>
  <a href="student_view_bid.php">View Bids</a>
  <a href="student_view_results.php">View Results</a>
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