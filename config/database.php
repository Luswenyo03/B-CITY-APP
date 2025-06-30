<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = 'localhost';
        $db_name = 'contact';
        $db_user = 'Binary';
        $db_pass = 'binary@2025';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db_name", $db_user, $db_pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getPdo() {
        return $this->pdo;
    }
}
?>