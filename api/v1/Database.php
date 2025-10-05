<?php
// GANTI NILAI INI SESUAI DENGAN KREDENSIAL DATABASE SIMRS ANDA
class Database {
    private $host = "localhost"; 
    private $db_name = "sik_pkudev"; 
    private $username = "root"; 
    private $password = ""; 
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            // Nonaktifkan error mode ketat PDO untuk menghindari error lingkungan hosting
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT); 
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            return null;
        }
        return $this->conn;
    }
}