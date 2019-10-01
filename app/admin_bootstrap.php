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

    require_once "bootstrap.php";
    $biddingrounddao = new BiddingRoundDAO();
    $current_round = $biddingrounddao->checkBiddingRound();
?>

<!-- The sidebar -->
<div class="sidebar">
  <a href="admin_home.php?token=<?php echo $token;?>">Home</a>
  <a href="admin_round.php?token=<?php echo $token;?>">Round Management</a>
  <a class="active" href="admin_bootstrap.php?token=<?php echo $token;?>">Bootstrap</a>
  <a href="sign_out.php">Sign Out</a>
</div>

<div class="content">

<?php
if($current_round == null) {
    if(isset($_FILES["bootstrap-file"])) {
        $bootstrap_success = doBootstrap();

        if($bootstrap_success != "failed") {

            [$num_record_loaded, $errors] = $bootstrap_success;

            echo "<br><h1>Processed:</h1>";

            foreach($num_record_loaded as $this_record_loaded) {
                echo $this_record_loaded . "<br>";
            }

            echo "<br><h1>Errors:</h1>";

            if(empty($errors)) {
                echo "No errors found.";
            } else {
                foreach($errors as $error) {
                    echo $error . "<br>";
                }
            }
        }
    }
    
    if(!isset($_FILES["bootstrap-file"]) || (isset($bootstrap_success) && $bootstrap_success == "failed")) {
        
        echo "
        <h1>Upload a file to bootstrap and start Round 1 bidding:</h1><br>
        <form method='post' enctype='multipart/form-data'>
        File:
        <input type='file' name='bootstrap-file' /><br><br>
        <!-- substitute the above value with a valid token -->
        <input type='submit' value='Bootstrap'/>
        </form>
        ";

        if(isset($bootstrap_success) && $bootstrap_success == "failed") {
            echo "<p id='error'>";
            echo "Bootstrap failed. Please upload the correct zip file.";
            echo "</p>";
        }
    }
    
} else {
    echo "<h1>You have already completed bootstrapping.</h1>";
}

?>

</div>
</html>