<?php
require_once("connection_manager.php");

class BidDAO {

    function add_bid($amount, $courseid, $section) {
    /**
     * adds bid to system
     * @param double $amount e-$ student wants to bid with
     * @param string $courseid course code student wants to bid for
     * @param string $section section student wants to bid for
     * @return bool success of the bid creation for the course and section
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO bid VALUES(:userid, :amount, :code, :section)");
        
        $stmt->bindParam(":userid", $_SESSION["userid"]);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":code", $courseid);
        $stmt->bindParam(":section", $section);

        $success = $stmt->execute();

        return $success;
    }

    function get_bids() {
    /**
     * retrieve course code, section and amount for all bids placed by student
     * @return array of course code, section, amount for all bids placed by student
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT code, section, amount FROM bid WHERE userid=:userid");

        $stmt->bindParam(":userid", $_SESSION["userid"]);

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

    function drop_bid($courseid) {
    /**
     * drops bid for a course
     * @param string $courseid course id
     * @return boolean success of bid dropping for the course
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("DELETE FROM bid WHERE code=:code AND userid=:userid");

        $stmt->bindParam(":code", $courseid);
        $stmt->bindParam(":userid", $_SESSION["userid"]);

        $success = $stmt->execute();

        return $success;
    }

    function get_bidded_courses() {
    /**
     * retrieve course codes and sections of all successful bids placed by a student
     * @return array of bidded courses & sections, eg. [["IS100", "S1"], ["ECON001", "S2"]]
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT code, section FROM bid WHERE userid=:userid");

        $stmt->bindParam(":userid", $_SESSION["userid"]);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            array_push($result, array_values($row));
        }
        return $result;
    }

    /**
     * truncates bid table (used in bootstrapping stage)
     */
    public function removeAll() {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE bid';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    function add_bid_for_bootstrap($userid, $amount, $courseid, $section) {
        /**
         * adds bid to system
         * @param double $amount e-$ student wants to bid with
         * @param string $courseid course code student wants to bid for
         * @param string $section section student wants to bid for
         * @return bool success of the bid creation for the course and section
         */
    
            $connection_manager = new connection_manager();
            $conn = $connection_manager->connect();
    
            $stmt = $conn->prepare("INSERT INTO bid VALUES(:userid, :amount, :code, :section)");
            
            $stmt->bindParam(":userid", $userid);
            $stmt->bindParam(":amount", $amount);
            $stmt->bindParam(":code", $courseid);
            $stmt->bindParam(":section", $section);
    
            $success = $stmt->execute();
    
            return $success;
    }

    function retrieve_all_bids(){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM bid");

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

}

// $BidDAO = new BidDAO();
// var_dump($BidDAO->get_bidded_courses("ian.ng.2009"));

?>