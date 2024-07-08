<?php
require_once '../../App/Models/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = isset($_POST['nome']) ? $_POST['nome'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $senha = isset($_POST['senha']) ? $_POST['senha'] : null;

    if ($usuario && $email && $senha) {
        $database = new ConexaoBD();
        $conn = $database->getConnection();

        // Verifica se o email já existe no banco de dados
        $query = 'SELECT COUNT(*) FROM usuario WHERE email = :email';
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $emailExists = $stmt->fetchColumn();

        if ($emailExists) {
            echo json_encode(['message' => 'Email já cadastrado', 'codigo' => 1]);
            exit;
        }

        // Criptografa a senha
        $senha_criptografada = password_hash($senha, PASSWORD_BCRYPT);

        // Prepara a consulta SQL para inserir os dados do usuário
        $query = 'INSERT INTO usuario (nome, email, senha) VALUES (:nome, :email, :senha)';
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':nome', $usuario);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha_criptografada);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Usuário inserido com sucesso', 'codigo' => 0]);
        } else {
            echo json_encode(['message' => 'Erro ao inserir usuário', 'codigo' => 1]);
        }
    } else {
        echo json_encode(['message' => 'Por favor, preencha todos os campos', 'codigo' => 1]);
    }
} else {
    echo json_encode(['message' => 'Requisição inválida', 'codigo' => 1]);
}
?>
