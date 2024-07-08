<?php
require_once '../../App/Models/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id) {
        $database = new ConexaoBD();
        $conn = $database->getConnection();

        // Prepara a consulta SQL para deletar o usuário
        $query = 'DELETE FROM usuario WHERE id = :id';
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Usuário excluído com sucesso', 'codigo' => 0]);
        } else {
            echo json_encode(['message' => 'Erro ao excluir usuário', 'codigo' => 1]);
        }
    } else {
        echo json_encode(['message' => 'ID do usuário não fornecido', 'codigo' => 1]);
    }
} else {
    echo json_encode(['message' => 'Requisição inválida', 'codigo' => 1]);
}
?>