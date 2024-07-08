<?php
require_once '../../App/Models/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destino = isset($_POST['destino']) ? $_POST['destino'] : null;
    $preco = isset($_POST['preco']) ? $_POST['preco'] : null;
    $DiasDeViagem = isset($_POST['qtd']) ? $_POST['qtd'] : null;
    $imagem = isset($_FILES['imagem']) ? $_FILES['imagem'] : null;

    if ($destino && $preco && $DiasDeViagem && $imagem) {
        // Verifique se houve erro no upload
        if ($imagem['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['message' => 'Erro ao fazer upload da imagem', 'codigo' => 1]);
            exit;
        }

        // Defina o diretório de destino
        $pastaDestino = '../../App/Views/img/'; // Certifique-se de que a pasta 'uploads' existe e tem permissões de escrita

        // Crie um nome único para a imagem
        $nomeImagem = uniqid() . '-' . basename($imagem['name']);

        // Caminho completo para salvar a imagem
        $caminhoCompleto = $pastaDestino . $nomeImagem;

        // Mova a imagem para a pasta de destino
        if (move_uploaded_file($imagem['tmp_name'], $caminhoCompleto)) {
            $database = new ConexaoBD();
            $conn = $database->getConnection();

            // Prepara a consulta SQL para inserir os dados da viagem
            $query = 'INSERT INTO destinos (destino, preco, DiasDeViagem, imagem) VALUES (:destino, :preco, :DiasDeViagem, :imagem)';
            $stmt = $conn->prepare($query);

            $stmt->bindParam(':destino', $destino);
            $stmt->bindParam(':preco', $preco);
            $stmt->bindParam(':DiasDeViagem', $DiasDeViagem);
            $stmt->bindParam(':imagem', $nomeImagem); // Armazena apenas o nome da imagem no banco de dados

            if ($stmt->execute()) {
                echo json_encode(['message' => 'Viagem inserida com sucesso', 'codigo' => 0]);
            } else {
                echo json_encode(['message' => 'Erro ao inserir viagem', 'codigo' => 1]);
            }
        } else {
            echo json_encode(['message' => 'Erro ao mover a imagem para a pasta de destino', 'codigo' => 1]);
        }
    } else {
        echo json_encode(['message' => 'Por favor, preencha todos os campos', 'codigo' => 1]);
    }
} else {
    echo json_encode(['message' => 'Requisição inválida', 'codigo' => 1]);
}
?>
