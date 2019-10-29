<?php
require_once("connection_manager.php");

class SectionDAO {
    function get_class_day_start_end($courseid, $section) {
    /**
     * retrieve class day, class start time, class end time of a section of a course
     * @param string $courseid course id
     * @param string $section section of course
     * @return array of class day, class start time, class end time
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT day, start, end FROM section WHERE course=:courseid AND section=:section");

        $stmt->bindParam(":courseid", $courseid);
        $stmt->bindParam(":section", $section);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        if($row = $stmt->fetch()) {
            return array_values($row);
        }
        return false;
    }

    function is_valid_section($courseid, $section) {
    /**
     * checks whether a section of a course exists
     * @param string $courseid course id
     * @param string $section section of course
     * @return boolean true if section of a course exists, false otherwise
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM section WHERE course=:courseid AND section=:section");

        $stmt->bindParam(":courseid", $courseid);
        $stmt->bindParam(":section", $section);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        if($stmt->fetch()) {
            return true;
        }
        return false;
    }

    /**
     * truncates section table (used in bootstrapping stage)
     */
    public function removeAll() {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE section';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    function add_section($course, $section, $day, $start, $end, $instructor, $venue, $size){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO section VALUES(:course, :section, :day, :start, :end, :instructor, :venue, :size)");
        
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);
        $stmt->bindParam(":day", $day);
        $stmt->bindParam(":start", $start);
        $stmt->bindParam(":end", $end);
        $stmt->bindParam(":instructor", $instructor);
        $stmt->bindParam(":venue", $venue);
        $stmt->bindParam(":size", $size);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function get_size($courseid, $section){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT size FROM section WHERE course=:courseid AND section=:section");

        $stmt->bindParam(":courseid", $courseid);
        $stmt->bindParam(":section", $section);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        if($row = $stmt->fetch()) {
            return $row["size"];
        }

    }

    function retrieve_all_course_section(){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT course, section, size FROM section");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $this_course_section_list = [];
            foreach($row as $idx => $value) {
                array_push($this_course_section_list, $value);
            }
            array_push($result, $this_course_section_list);
        }
        return $result;
    }

    public function retrieve_all_sections() {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM section");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $section_list = [];
            foreach($row as $idx => $value) {
                array_push($section_list, $value);
            }
            array_push($result, $section_list);
        }
        return $result;
    }

    function get_timetable_details($course, $section) {
        // returns [day, start, end]

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT day, start, end FROM section WHERE course=:course AND section=:section");

        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":section", $section);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        if($row = $stmt->fetch()) {
            return array_values($row);
        }
    }

    function get_course_sections_times($course) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT section, day, start, end, instructor FROM section WHERE course=:course");

        $stmt->bindParam(":course", $course);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $result[] = array_values($row);
        }

        if($result != []) {
            return $result;
        }

        return false;
    }
}


// $SectionDAO = new SectionDAO();
// var_dump($SectionDAO->retrieve_all_course_section());

?>