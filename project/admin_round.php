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
  <a href="admin_home.php?token=<?php echo $token;?>">Home</a>
  <a class="active" href="admin_round.php?token=<?php echo $token;?>">Round Management</a>
  <a href="admin_bootstrap.php?token=<?php echo $token;?>">Bootstrap</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<div class="content">

</div>

</html>