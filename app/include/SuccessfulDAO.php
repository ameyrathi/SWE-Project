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
}

?>