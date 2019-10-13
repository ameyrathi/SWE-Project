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

    function check_success($userid, $course, $section, $closed_round) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_successful";
        } elseif($closed_round == 2) {
            $table = "round2_successful";
        }

        $stmt = $conn->prepare("SELECT * FROM $table WHERE userid=:userid AND code=:course AND section=:section");
        
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
            $table = "round1_successful";
        } elseif($closed_round == 2) {
            $table = "round2_successful";
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

    function drop_section($userid, $course, $section) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        // only need to check for round1_successful, not round2_successful
        // wiki: "success bids from round 2 are final and cannot be dropped"
        if($this->check_success($userid, $course, $section, 1) != false) { // in round1_successful
            $stmt = $conn->prepare("DELETE FROM round1_successful WHERE code=:course AND userid=:userid");
        }     

        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":userid", $userid);

        $success = $stmt->execute();

        return $success;
    }

    function retrieve_sort_bids($closed_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_successful";
        } elseif($closed_round == 2) {
            // this scenario won't happen though
            // viewing results of round 2 is done through check_success() function
            $table = "round2_successful";
        }

        $stmt = $conn->prepare("SELECT * FROM $table");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $raw_bids = [];

        while($row = $stmt->fetch()) {
            $this_bid_list = [];
            foreach($row as $idx => $value) {
                array_push($this_bid_list, $value);
            }
            array_push($raw_bids, $this_bid_list);
        }

        // $result is now in this format:
            // [ ['ben.ng.2009', '11', 'IS100', 'S1'], ['calvin.ng.2009', '12', 'IS100', 'S1'], ... ]

        // but we want it to be in this format:
            // [ "IS100, S1" => [ ['ben.ng.2009','11'], ['calvin.ng.2009','12'] ], ... ]

        $result = [];

        foreach($raw_bids as $this_bid) {
            [$userid, $amount, $course, $section] = $this_bid;
            $course_section_concat = $course . ", " . $section;

            if(!array_key_exists($course_section_concat, $result)) { // if course_section not a key in $result yet
                $result[$course_section_concat] = [[$userid, $amount]];
            } else { // if course_section already exists as a key in $result
                $result[$course_section_concat][] = [$userid, $amount];
            }
        }

        foreach($result as $course_section_concat => &$this_bid_list) { // pass by reference so usort will modify it
            usort(
                $this_bid_list, 
                function($a, $b) {
                    $sorting = 0;
                    if ($a[1] < $b[1]) {
                        $sorting = 1;
                    } else if ($a[1] > $b[1]) {
                        $sorting = -1;
                    }
                    return $sorting; 
                }
            );
        }
        return $result;
    }

    function retrieve_sort_this_section_bids($course, $section, $closed_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_successful";
        } elseif($closed_round == 2) {
            $table = "round2_successful";
        }

        $stmt = $conn->prepare("SELECT userid, amount FROM $table WHERE code=:course AND section=:section");

        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $this_bid_list = [];
            foreach($row as $idx => $value) {
                array_push($this_bid_list, $value);
            }
            array_push($result, $this_bid_list);
        }

        // $result is now in this format:
            // [ ['ben.ng.2009', '11'], ['calvin.ng.2009', '12'], ... ]
        
        usort(
            $result, 
            function($a, $b) {
                $sorting = 0;
                if ($a[1] < $b[1]) {
                    $sorting = 1;
                } else if ($a[1] > $b[1]) {
                    $sorting = -1;
                }
                return $sorting; 
            }
        );

        return $result;
    }

    function retrieve_successful_bids($course, $section, $closed_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_successful";
        } elseif($closed_round == 2) {
            $table = "round2_successful";
        }

        $stmt = $conn->prepare("SELECT * FROM $table WHERE code=:course AND section=:section");

        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $this_bid_list = [];
            foreach($row as $idx => $value) {
                array_push($this_bid_list, $value);
            }
            array_push($result, $this_bid_list);
        }
        return $result;
    }

    function get_specific_bid($userid, $course, $section, $closed_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($closed_round == 1) {
            $table = "round1_successful";
        } elseif($closed_round == 2) {
            $table = "round2_successful";
        }

        $stmt = $conn->prepare("SELECT * FROM $table WHERE userid=:userid AND code=:course AND section=:section");

        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            foreach($row as $idx => $value) {
                array_push($result, $value);
            }
        }
        return $result;
    }

    function retrieve_all_bids($current_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($current_round == 1) {
            $table = "round1_successful";
        } elseif($current_round == 2) {
            $table = "round2_successful";
        }

        $stmt = $conn->prepare("SELECT * FROM $table");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $student_list = [];
            foreach($row as $idx => $value) {
                array_push($bids_list, $value);
            }
            array_push($result, $bids_list);
        }
        return $result;
    }
}

// $successfuldao = new SuccessfulDAO();
// var_dump($successfuldao->retrieve_sort_this_section_bids("IS100", "S1", 1));

?>