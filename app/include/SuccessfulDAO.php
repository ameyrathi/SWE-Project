<?php

require_once("connection_manager.php");

class SuccessfulDAO{

    function add_success($userid, $amount, $course, $section, $current_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($current_round == 1) {
            $table = "round1_successful";
        } elseif($current_round == 2) {
            $table = "round2_successful";
        }

        $stmt = $conn->prepare("INSERT INTO $table VALUES(:userid, :amount, :course, :section)");
        
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function check_success($userid, $course, $section, $closed_round) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_successful";
        } elseif($closed_round == 2) {
            $table = "round2_successful";
        }

        $stmt = $conn->prepare("SELECT * FROM $table WHERE userid=:userid AND course=:course AND section=:section");
        
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);

        $stmt->execute();

        return $stmt->fetch();
    }

    function get_successful_bids_and_amount($userid, $closed_round) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $stmt = $conn->prepare("SELECT * FROM round1_successful WHERE userid=:userid");
        } elseif($closed_round == 2) {
            $stmt = $conn->prepare("SELECT * FROM round1_successful WHERE userid=:userid UNION SELECT * FROM $round2_successful WHERE userid=:userid");
        }
        
        $stmt->bindParam(":userid", $userid);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            [$userid, $amount, $course, $section] = array_values($row);
            $result[] = [$course, $section, $amount];
        }

        return $result;
    }

    function drop_section($userid, $course, $section) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        // only need to check for round1_successful, not round2_successful
        // wiki: "success bids from round 2 are final and cannot be dropped"
        if($this->check_success($userid, $course, $section, 1) != false) { // in round1_successful
            $stmt = $conn->prepare("DELETE FROM round1_successful WHERE course=:course AND userid=:userid");
        }     

        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":userid", $userid);

        $success = $stmt->execute();

        return $success;
    }
}

?>