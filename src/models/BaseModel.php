<?php
require_once '../config/database.php';

class BaseModel {
    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getPdo();
    }
}
?>