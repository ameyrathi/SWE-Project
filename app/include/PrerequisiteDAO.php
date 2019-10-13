<?php
require_once("connection_manager.php");

class PrerequisiteDAO {
    function get_prerequisite_courses($courseid) {
        /**
         * retrieve prerequisite courses for specified course
         * @return array of prerequisite course codes
         */
    
            $connection_manager = new connection_manager();
            $conn = $connection_manager->connect();
    
            $stmt = $conn->prepare("SELECT child_course FROM prerequisite WHERE parent_course=:parent_course");
    
            $stmt->bindParam(":parent_course", $courseid);
    
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
    
            $stmt->execute();
    
            $result = [];
    
            while($row = $stmt->fetch()) {
                array_push($result, array_values($row)[0]);
            }
            return $result;
        }
    
    /**
     * truncates prerequisite table (used in bootstrapping stage)
     */
    public function removeAll() {
        $sql = 'TRUNCATE TABLE prerequisite';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    function add_prerequisite($course, $prerequisite){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO prerequisite VALUES(:course, :prerequisite)");
        
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":prerequisite", $prerequisite);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function retrieve_all_prerequisites(){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM prerequisite");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $prerequisites_list = [];
            foreach($row as $idx => $value) {
                array_push($prerequisites_list, $value);
            }
            array_push($result, $prerequisites_list);
        }
        return $result;
    }

}

// $PrerequisiteDAO = new PrerequisiteDAO();
// var_dump($PrerequisiteDAO->get_prerequisite_courses("IS109"));