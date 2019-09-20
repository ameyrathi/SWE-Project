<?php
require_once("connection_manager.php");

class StudentDAO {
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
}