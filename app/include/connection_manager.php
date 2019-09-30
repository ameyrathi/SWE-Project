<?php

class connection_manager {
    public $server_name = "localhost";
    public $database_name = "spm";
    public $username = "root";
    public $password = "";

    public function connect() {
        $conn = new PDO("mysql:host={$this->server_name};dbname={$this->database_name}", $this->username, $this->password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // if fail, exception will be thrown

        return $conn;
    }
}

?>