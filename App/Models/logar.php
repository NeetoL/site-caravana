<?php
require_once 'Database.php'; // Importa a classe de conexão

// Verifica se os dados foram recebidos via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['password'];


    // Verifica se o email está presente e é válido (melhor prática)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode(array('error' => 'Email inválido'));
        exit;
    }

    try {
        // Cria uma nova instância da classe de conexão
        $conexaoBD = new ConexaoBD();
        $conexao = $conexaoBD->getConnection();

        // Consultar o banco de dados para verificar se o email existe
        $query = "SELECT * FROM usuario WHERE EMAIL = :email";
        $stmt = $conexao->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Usuário encontrado, verificar a senha
            $senha_hash = $result['SENHA'];

            // Verifica se a senha fornecida corresponde à senha hash armazenada
            if (password_verify($senha, $senha_hash)) {
                // Senha correta
                $response = array(
                    'success' => true,
                    'message' => 'Login bem-sucedido'
                );
            } else {
                // Senha incorreta
                $response = array(
                    'success' => false,
                    'error' => 'Senha incorreta'
                );
            }
        } else {
            // Usuário não encontrado
            $response = array(
                'success' => false,
                'error' => 'Usuário não encontrado'
            );
        }

        // Fecha a conexão com o banco de dados
        $conexao = null;

    } catch (PDOException $e) {
        // Em caso de erro de conexão ou consulta
        http_response_code(500); // Internal Server Error
        echo json_encode(array('error' => 'Erro no servidor: ' . $e->getMessage()));
        exit;
    }

    // Retorna a resposta em formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Retorna um erro se não for recebido via POST
    http_response_code(405); // Método não permitido
    echo json_encode(array('error' => 'Método não permitido'));
}
?>
