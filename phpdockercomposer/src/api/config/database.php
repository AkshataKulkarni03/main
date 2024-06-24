<?php

class Database
{
    public $conn;
    private $host = "db";
    private $db_name = "exampledb";
    private $username = "user";
    private $password = "userpassword";

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO ("mysql:host=" . $this->host . "; dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection Error:" . $exception->getMessage();
        }

        return $this->conn;
    }
}