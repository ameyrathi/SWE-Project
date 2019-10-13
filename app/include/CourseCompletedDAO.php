<?php
require_once("connection_manager.php");

class CourseCompletedDAO {

    /**
     * retrieve course codes of all courses completed by a student
     * @return array of course codes of all courses completed by a student
     */
    function get_completed_courses($userid) {   
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT code FROM course_completed WHERE userid=:userid");

        $stmt->bindParam(":userid", $userid);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            array_push($result, $row["code"]);
        }

        $stmt = null;
        $conn = null;
        
        return $result;
    }

    /**
     * truncates course table (used in bootstrapping stage)
     */
    public function removeAll() {
        $sql = 'TRUNCATE TABLE course_completed';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();

        $stmt = null;
        $conn = null;
    }

    function add_course_completed($userid, $code){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO course_completed VALUES(:userid, :code)");
        
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":code", $code);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function retrieve_all_completed_courses(){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM course_completed");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $completed_courses_list = [];
            foreach($row as $idx => $value) {
                array_push($completed_courses_list, $value);
            }
            array_push($result, $completed_courses_list);
        }
        return $result;
    }

}

?>