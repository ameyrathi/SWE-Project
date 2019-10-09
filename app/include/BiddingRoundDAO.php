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

    function get_current_round() {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM bidding_round");

        $stmt->execute();

        if($row = $stmt->fetch()) {
            $round = $row["round"];
            $status = $row["status"];

            if($status == "Ongoing") {
                return $round;
            }

            if($status == "Not Started" && $round == 1) {
                return 0.5;
            }

            if($status == "Ended") {
                if($round == 1) {
                    return 1.5;
                }

                if($round == 2) {
                    return 2.5;
                }
            }
        }

        return null;
    }

    function get_round() {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT round FROM bidding_round");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        if($round = $stmt->fetch()) {
            return $round["round"];
        }
    }

    function get_status() {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT status FROM bidding_round");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        if($round = $stmt->fetch()) {
            return $round["status"];
        }
    }

    function start_round($round) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE bidding_round SET round=:round, status='Ongoing'");
        
        $stmt->bindParam(":round", $round);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function stop_round($round) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE bidding_round SET round=:round, status='Ended'");
        
        $stmt->bindParam(":round", $round);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function get_round_message() {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM bidding_round");

        $stmt->execute();

        if($row = $stmt->fetch()) {
            $round = $row["round"];
            $status = $row["status"];
        }

        if($status == "Not Started") {
            return "Round $round has not started.";
        } elseif($status == "Ongoing") {
            return "Round $round is ongoing.";
        } elseif($status == "Ended") {
            return "Round $round has ended.";
        } else {
            return null;
        }
    }

    function end_round($round) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE bidding_round SET round=:round AND status='Ended'");
        
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

// $biddingrounddao = new BiddingRoundDAO();
// var_dump($biddingrounddao->get_round_message());

?>