<?php

require_once 'Database.php';

class HomeModel {
    private $conn;
    private $table_name = "tabela_exemplo";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getExampleData() {
        $query = "SELECT * FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>