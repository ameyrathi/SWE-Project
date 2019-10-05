<?php

require_once("connection_manager.php");

class SectionResultsDAO{

    function add_results($course, $section, $min_bid, $vacancies, $closed_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO section_results VALUES(:course, :section, :min_bid, :vacancies)");
        
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);
        $stmt->bindParam(":min_bid", $min_bid);
        $stmt->bindParam(":vacancies", $vacancies);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function update_results($course, $section, $min_bid , $vacancies) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE section_results SET min_bid = :min_bid, vacancies = :vacancies WHERE course = :course AND section = :section");

        $stmt->bindParam(":min_bid", $min_bid);
        $stmt->bindParam(":vacancies", $vacancies);
        $stmt->bindParam(":course", $course); 
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        return $success;
    }

    function update_min_bid($course, $section, $min_bid) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE section_results SET min_bid = :min_bid WHERE course = :course AND section = :section");

        $stmt->bindParam(":min_bid", $min_bid);
        $stmt->bindParam(":course", $course); 
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        return $success;
    }

    function get_available_seats($course, $section) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT vacancies FROM section_results WHERE course=:course AND section=:section");

        $stmt->bindParam(":course", $course); 
        $stmt->bindParam(":section", $section);

        $stmt->execute();

        if($row = $stmt->fetch()) {
            return $row["vacancies"];
        }
    }

    function add_one_seat($course, $section) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT vacancies FROM section_results WHERE course=:course AND section=:section");

        $stmt->bindParam(":course", $course); 
        $stmt->bindParam(":section", $section);

        $stmt->execute();

        if($row = $stmt->fetch()) {
            $vacancies = $row["vacancies"];
            $vacancies++;

            $stmt = $conn->prepare("UPDATE section_results SET vacancies=$vacancies WHERE course=:course AND section=:section");

            $stmt->bindParam(":course", $course); 
            $stmt->bindParam(":section", $section);

            $success = $stmt->execute();

            return $success;
        }
    }

    function get_min_bid($course, $section) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT min_bid FROM section_results WHERE course=:course AND section=:section");

        $stmt->bindParam(":course", $course); 
        $stmt->bindParam(":section", $section);

        $stmt->execute();

        if($row = $stmt->fetch()) {
            return $row["min_bid"];
        }
    }

    function removeAll(){
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE section_results';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }
}

?>