<?php

require_once("connection_manager.php");

class BiddingRoundDAO{
    
    function addBiddingRound($round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO bidding_round VALUES(:round)");
        
        $stmt->bindParam(":round", $round);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function updateBiddingRound($round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE bidding_round SET round=:round");
        
        $stmt->bindParam(":round", $round);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function checkBiddingRound(){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT round FROM bidding_round");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        if($round = $stmt->fetch()) {
            return $round["round"];
        }
    }
}

?>