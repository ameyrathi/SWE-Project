<?php
require_once("connection_manager.php");

class StudentDAO {
    function get_name($userid) {
    /**
     * retrieve name of a student
     * @param string $userid user id
     * @return string name of student
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT name FROM student WHERE userid=:userid");

        $stmt->bindParam(":userid", $userid);

        $stmt->execute();

        return $stmt->fetch()["name"];
    }

    function get_school($userid) {
    /**
     * retrieve school of student
     * @param string $userid user id
     * @return string school of student
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT school FROM student WHERE userid=:userid");

        $stmt->bindParam(":userid", $userid);

        $stmt->execute();

        if($school = $stmt->fetch()[0]) {
            return $school;
        } else {
            return false;
        }
    }

    function get_balance($userid) {
    /**
     * retrieve e-$ of student
     * @param string $userid user id
     * @return double e-$ balance of student, or false if not found
     */

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT edollar FROM student WHERE userid=:userid");

        $stmt->bindParam(":userid", $userid);

        $stmt->execute();

        if($balance = $stmt->fetch()[0]) {
            return $balance;
        }
    }

    function deduct_balance($userid, $amount) {
    /**
     * deduct e-$ of student by amount
     * @param string $amount amount to be deducted
     * @return boolean success of balance deduction
     */
        $original_balance = $this->get_balance($userid);
        $balance_after_deduction = $original_balance - $amount;

        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("UPDATE student SET edollar = :balance WHERE userid = :userid");

        $stmt->bindParam(":balance", $balance_after_deduction);
        $stmt->bindParam(":userid", $userid);

        $success = $stmt->execute();

        return $success;

    }

    function add_balance($userid, $amount) {
        /**
         * add e-$ of student by amount
         * @param string $amount amount to be added
         * @return boolean success of balance addition
         */
            $original_balance = $this->get_balance($userid);
            $balance_after_addition = $original_balance + $amount;
    
            $connection_manager = new connection_manager();
            $conn = $connection_manager->connect();
    
            $stmt = $conn->prepare("UPDATE student SET edollar = :balance WHERE userid = :userid");
    
            $stmt->bindParam(":balance", $balance_after_addition);
            $stmt->bindParam(":userid", $userid);
    
            $success = $stmt->execute();
    
            return $success;
        }

    public function getPassword($userid){
        /**
         * gets the user's password from database 
         * @param string $userid is the username
         * @return string password is the password
         */
    
        $connMgr = new connection_manager();
        $conn = $connMgr->connect();
        
        // Update SQL statement 
        $sql = "SELECT password FROM student WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $success = $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);


        if($success==1){
            while($row=$stmt->fetch()){
                $password = $row["password"];
            }
        }
        else{
            $_SESSION["errors"] = "Username Invalid";
            header('Location: login.php');
            die;
        }

        $stmt = null;
        $conn = null;        
        return $password;
    }

    public function validUser($userid){
        /**
         * checks if the username is Valid
         * @param string $userid is the username
         * @return boolean if username exists in the database
         */
        $connMgr = new connection_manager();
        $conn = $connMgr->connect();

        $sql = 'SELECT * FROM student WHERE userid = :userid';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $success = $stmt->fetch();
        
        if($success == false) {
            return false;
        }
        return true;
    }

    function adminLogin($password){
        if($password == "P@ssword2"){
            return true;
        }
        return false;
    }

    /**
     * truncates student table (used in bootstrapping stage)
     */
    public function removeAll() {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE student';
        
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    } 

    public function add_student($userid, $password, $name, $school, $edollar) {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("INSERT INTO student VALUES(:userid, :password, :name, :school, :edollar)");
        
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":school", $school);
        $stmt->bindParam(":edollar", $edollar);

        $success = $stmt->execute();

        return $success;
    }

    public function retrieve_all_students() {
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM student");

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch()) {
            $student_list = [];
            foreach($row as $idx => $value) {
                array_push($student_list, $value);
            }
            array_push($result, $student_list);
        }
        return $result;
    }

    public function delete_student($userid){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("DELETE FROM student WHERE userid=:userid");

        $stmt->bindParam(":userid", $userid);

        $success = $stmt->execute();

        return $success;
    }

    public function retrieve_student($userid){
        $connection_manager = new connection_manager();
        $conn = $connection_manager->connect();

        $stmt = $conn->prepare("SELECT * FROM student WHERE userid=:userid");

        $stmt->bindParam(":userid", $userid);

        $stmt->execute();

        return $stmt->fetch();
    }
}

?>