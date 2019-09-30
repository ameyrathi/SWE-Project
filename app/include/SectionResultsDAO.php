<?php

require_once("connection_manager.php");

class SectionResultsDAO{

    function add_results($course, $section, $min_bid, $vacancies, $closed_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_results";
        } elseif($closed_round == 2) {
            $table = "round2_results";
        }

        $stmt = $conn->prepare("INSERT INTO $table VALUES(:course, :section, :min_bid, :vacancies)");
        
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);
        $stmt->bindParam(":min_bid", $min_bid);
        $stmt->bindParam(":vacancies", $vacancies);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function update_results($course, $section, $min_bid , $vacancies, $closed_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_results";
        } elseif($closed_round == 2) {
            $table = "round2_results";
        }

        $stmt = $conn->prepare("UPDATE $table SET min_bid = :min_bid, vacancies = :vacancies WHERE course = :course AND section = :section");

        $stmt->bindParam(":min_bid", $min_bid);
        $stmt->bindParam(":vacancies", $vacancies);
        $stmt->bindParam(":course", $course); 
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        return $success;
    }

    function removeAll($closed_round){
        if($closed_round == 1) {
            $table = "round1_results";
        } elseif($closed_round == 2) {
            $table = "round2_results";
        }

        $sql = 'SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE $table';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }
}

?>