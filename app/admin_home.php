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
    $round_message = $biddingrounddao->get_round_message();
?>

<html>

<link rel="stylesheet" href="stylesheet.css"/>

<!-- The sidebar -->
<div class="sidebar">
  <a class="active" href="admin_home.php?token=<?php echo $token;?>">Home</a>
  <a href="admin_round.php?token=<?php echo $token;?>">Round Management</a>
  <a href="admin_bootstrap.php?token=<?php echo $token;?>">Bootstrap</a>
  <a href="sign_out.php">Sign Out</a>
</div>


<div class="content">
<body>
<p>
<h1>Welcome, Admin!</h1>
<?php
    echo "<br><h1>$round_message</h1>";
?>
</p>
</body>
</div>
</html>
