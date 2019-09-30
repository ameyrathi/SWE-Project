<?php

require_once("connection_manager.php");

class Round1DAO{

    function add_results($course, $section, $min_bid , $vacancies){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO round1_results VALUES(:course, :section, :min_bid, :vacancies)");
        
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);
        $stmt->bindParam(":min_bid", $min_bid);
        $stmt->bindParam(":vacancies", $vacancies);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function update_results($course, $section, $min_bid , $vacancies){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE round1_results SET min_bid = :min_bid, vacancies = :vacancies WHERE course = :course AND section = :section");

        $stmt->bindParam(":min_bid", $min_bid);
        $stmt->bindParam(":vacancies", $vacancies);
        $stmt->bindParam(":course", $course); 
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        return $success;
    }

    function removeAll(){
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE round1_results';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }
}

?>