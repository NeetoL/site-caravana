<?php

class ConexaoBD {
    private $host = 'localhost'; // servidor onde o banco de dados está hospedado
    private $db_name = 'carava16_sistema'; // nome do banco de dados
    private $username = ''; // usuário do banco de dados
    private $password = ''; // senha do banco de dados
    public $conn;


    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Erro de conexão: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
