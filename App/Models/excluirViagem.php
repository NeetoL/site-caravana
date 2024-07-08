<?php
require_once '../../App/Models/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id) {
        $database = new ConexaoBD();
        $conn = $database->getConnection();

        // Prepara a consulta SQL para deletar a viagem
        $query = 'DELETE FROM destinos WHERE id = :id';
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Viagem excluída com sucesso', 'codigo' => 0]);
            } else {
                $errorInfo = $stmt->errorInfo();
                echo json_encode(['message' => 'Erro ao excluir viagem', 'codigo' => 1, 'error' => $errorInfo[2]]);
            }
        } catch (PDOException $e) {
            echo json_encode(['message' => 'Erro ao excluir viagem', 'codigo' => 1, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => 'ID da viagem não fornecido', 'codigo' => 1]);
    }
} else {
    echo json_encode(['message' => 'Requisição inválida', 'codigo' => 1]);
}
?>
