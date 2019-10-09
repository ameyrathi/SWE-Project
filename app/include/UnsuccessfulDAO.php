<?php

require_once("connection_manager.php");

class UnsuccessfulDAO{

    function add_unsuccessful($userid, $amount, $course, $section, $current_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($current_round == 1) {
            $table = "round1_unsuccessful";
        } elseif($current_round == 2) {
            $table = "round2_unsuccessful";
        }

        $stmt = $conn->prepare("INSERT INTO $table VALUES(:userid, :amount, :code, :section)");
        
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":code", $course);
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function get_unsuccessful_bids_and_amount($userid, $closed_round) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_unsuccessful";
        } elseif($closed_round == 2) {
            $table = "round2_unsuccessful";
        }

        $stmt = $conn->prepare("SELECT * from $table WHERE userid=:userid");

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
}

// $unsuccessfuldao = new UnsuccessfulDAO();
// $success = $unsuccessfuldao->add_unsuccessful("valarie.ng.2009", 30, "IS204", "S1", 1);
// var_dump($success);