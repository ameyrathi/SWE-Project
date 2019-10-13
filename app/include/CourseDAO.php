<?php
require_once("connection_manager.php");

class CourseDAO {

    /**
     * retrieve course title of a course
     * @param string $courseid course id
     * @return string course title
     */
    function get_course_title($courseid) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT title FROM course WHERE course=:courseid");

        $stmt->bindParam(":courseid", $courseid);
        
        $stmt->execute();

        return $stmt->fetch()[0];
    }

    /**
     * retrieve course description of a course
     * @param string $courseid course id
     * @return string course description
     */
    function get_course_description($courseid) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT description FROM course WHERE course=:courseid");

        $stmt->bindParam(":courseid", $courseid);

        $stmt->execute();

        return $stmt->fetch()[0];
    }

    /**
     * retrieve school that a course belongs to
     * @param string $courseid course id
     * @return string school of course
     */
    function get_school($courseid) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT school FROM course WHERE course=:courseid");

        $stmt->bindParam(":courseid", $courseid);

        $stmt->execute();

        return $stmt->fetch()[0];
    }

    /**
     * retrieve exam date, exam start time, exam end time of a course
     * @param string $courseid course id
     * @return array of exam date, exam start time, exam end time
     */
    function get_exam_date_start_end($courseid) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT examdate, examstart, examend FROM course WHERE course=:courseid");

        $stmt->bindParam(":courseid", $courseid);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();


        if($row = $stmt->fetch()) {
            return array_values($row);
        }
        return false;
    }

    /**
     * truncates course table (used in bootstrapping stage)
     */
    public function removeAll() {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE course';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    /**
     * adds a course to the database
     */
    function add_course($course, $school, $title, $description, $exam_date, $exam_start, $exam_end){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO course VALUES(:course, :school, :title, :description, :exam_date, :exam_start, :exam_end)");
        
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":school", $school);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":exam_date", $exam_date);
        $stmt->bindParam(":exam_start", $exam_start);
        $stmt->bindParam(":exam_end", $exam_end);

        $success = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $success;
    }

    function get_course($courseid){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT course FROM course WHERE course=:courseid");

        $stmt->bindParam(":courseid", $courseid);

        $stmt->execute();

        return $stmt->fetch()[0];
    }

    function retrieve_all_courses(){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM course");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $course_list = [];
            foreach($row as $idx => $value) {
                array_push($course_list, $value);
            }
            array_push($result, $course_list);
        }
        return $result;
    }
}

// $CourseDAO = new CourseDAO();
// var_dump($CourseDAO->get_exam_date_start_end("IS100"));

?>