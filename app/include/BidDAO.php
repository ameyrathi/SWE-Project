<?php
require_once("connection_manager.php");

class BidDAO {

    function add_bid($userid, $amount, $courseid, $section, $current_round) {
    /**
     * adds bid to system
     * @param double $amount e-$ student wants to bid with
     * @param string $courseid course code student wants to bid for
     * @param string $section section student wants to bid for
     * @return bool success of the bid creation for the course and section
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($current_round == 1) {
            $table = "round1_bid";
        } elseif($current_round == 2) {
            $table = "round2_bid";
        }

        $stmt = $conn->prepare("INSERT INTO $table VALUES(:userid, :amount, :code, :section)");
        
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":code", $courseid);
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        return $success;
    }

    function drop_bid($userid, $courseid, $current_round) {
    /**
     * drops bid for a course
     * @param string $courseid course id
     * @return boolean success of bid dropping for the course
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($current_round == 1) {
            $table = "round1_bid";
        } elseif($current_round == 2) {
            $table = "round2_bid";
        }
        $stmt = $conn->prepare("DELETE FROM $table WHERE code=:code AND userid=:userid");

        $stmt->bindParam(":code", $courseid);
        $stmt->bindParam(":userid", $userid);

        $success = $stmt->execute();

        return $success;
    }

    /**
     * truncates bid table (used in bootstrapping stage)
     */
    public function removeAll() {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE round1_bid';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    function update_bid_for_bootstrap($userid, $amount, $courseid, $section) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE round1_bid SET amount=:amount WHERE userid=:userid AND code=:courseid AND section=:section");
        
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":courseid", $courseid);
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        return $success;
    }

    function retrieve_and_sort_bids($current_round){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($current_round == 1) {
            $table = "round1_bid";
        } elseif($current_round == 2) {
            $table = "round2_bid";
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

    function get_pending_bids_and_amount($userid, $current_round) {
        /**
         * retrieve course codes, sections, amounts of all successful bids placed by a student in stated round
         * @return array of bidded courses, sections and amounts, eg. [["IS100", "S1", 20], ["ECON001", "S2", 15]]
         */
    
            $connection_manager = new connection_manager();
            $conn = $connection_manager->connect();
    
            if($current_round == 1) {
                $table = "round1_bid";
            } elseif($current_round == 2) {
                $table = "round2_bid";
            }
    
            $stmt = $conn->prepare("SELECT code, section, amount FROM $table WHERE userid=:userid");
    
            $stmt->bindParam(":userid", $userid);
    
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
    
            $stmt->execute();
    
            $result = [];
    
            while($row = $stmt->fetch()) {
                array_push($result, array_values($row));
            }
            return $result;
        }

    function bid_already_exists($userid, $courseid, $section, $current_round) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        if($current_round == 1) {
            $table = "round1_bid";
        } elseif($current_round == 2) {
            $table = "round2_bid";
        }

        $stmt = $conn->prepare("SELECT * FROM $table WHERE userid=:userid AND code=:courseid AND section=:section");

        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":courseid", $courseid);
        $stmt->bindParam(":section", $section);

        $stmt->execute();

        return $stmt->fetch();
    }
}

// $BidDAO = new BidDAO();
// var_dump($BidDAO->retrieve_and_sort_bids(1));

?>