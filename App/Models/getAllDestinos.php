<?php
// Incluir a classe de conexão
require_once 'Database.php';

try {
    // Criar um objeto de conexão
    $conexao = new ConexaoBD();
    $pdo = $conexao->getConnection();

    // Preparar e executar a consulta SQL
    $sql = "SELECT * FROM `destinos` ORDER BY `destinos`.`data_insercao`";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Capturar os resultados da consulta
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retornar os resultados como JSON
    header('Content-Type: application/json');
    echo json_encode($resultados);
    
} catch (PDOException $e) {
    echo json_encode(array('error' => 'Erro na consulta: ' . $e->getMessage()));
}
?>
