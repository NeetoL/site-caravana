<?php
session_start();
require_once 'App/Models/Database.php'; // Importa a classe de conexão

// Verifica se os dados foram recebidos via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Obtém os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['password'];

    // Verifica se o email está presente e é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro_login = 'Email inválido';
    } else {
        try {
            // Cria uma nova instância da classe de conexão
            $conexaoBD = new ConexaoBD();
            $conexao = $conexaoBD->getConnection();

            // Consulta o banco de dados para verificar se o email existe
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
                    // Senha correta, inicia a sessão
                    $_SESSION['usuario'] = $result['ID'];
                    $_SESSION['nome'] = $result['NOME'];
                } else {
                    $erro_login = 'Senha incorreta';
                }
            } else {
                $erro_login = 'Usuário não encontrado';
            }

            // Fecha a conexão com o banco de dados
            $conexao = null;

        } catch (PDOException $e) {
            $erro_login = 'Erro no servidor: ' . $e->getMessage();
        }
    }
}

// Verifica se o usuário está logado
$logado = isset($_SESSION['usuario']);

// Processa o logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: /painel");
    exit;
}

$usuarios = [];
$viagens = [];

if ($logado) {
    try {
        // Cria uma nova instância da classe de conexão
        $conexaoBD = new ConexaoBD();
        $conexao = $conexaoBD->getConnection();

        // Consulta o banco de dados para obter todos os usuários (destinos)
        $queryUsuarios = "SELECT * FROM usuario";
        $stmtUsuarios = $conexao->prepare($queryUsuarios);
        $stmtUsuarios->execute();
        $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

        // Consulta o banco de dados para obter todas as viagens
        $queryViagens = "SELECT * FROM destinos";
        $stmtViagens = $conexao->prepare($queryViagens);
        $stmtViagens->execute();
        $viagens = $stmtViagens->fetchAll(PDO::FETCH_ASSOC);

        // Fecha a conexão com o banco de dados
        $conexao = null;
    } catch (PDOException $e) {
        $erro_painel = 'Erro ao obter dados: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Caravana's</title>
    <?php if (!$logado) { ?>
    <link rel="stylesheet" href="App/Views/css/login.css">
    <?php } else { ?>
    <link rel="stylesheet" href="App/Views/css/painel.css">
    <?php } ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet">
     <!-- Toastr CSS -->
     <link rel="stylesheet" href="App/Views/css/toastr.min.css">    
    <!-- Toastr JS -->
    <script src="App/Views/js/toastr.min.js"></script>  
    <script>  

$(function(){
    console.log("<?php echo $hashedPassword; ?>");
});

    function submitForm(param) {
        if (param === "dashboard") {
            $("#usuario").show();
            $("#viagem").show();
            $("#addviagem").hide();
            $("#addusuario").hide();
        } else if (param === "adicionarviagem") {
            $("#usuario").hide();
            $("#viagem").hide();
            $("#addviagem").show();
            $("#addusuario").hide();
        } else if (param === "adicionarusuario") {
            $("#viagem").hide();
            $("#usuario").hide();
            $("#addviagem").hide();
            $("#addusuario").show();
        }
    }

    function addUsuario(){
      var nome = $("#usuario_nome");
      var email = $("#usuario_email");
      var senha = $("#usuario_password");

      $.ajax({
          url: "App/Models/painel.php",
          type: 'POST',
          data: {
              nome: nome.val(),
              email: email.val(),
              senha: senha.val()
          },
          success: function (data){
            data = JSON.parse(data);
            console.log(data);
            if(data.codigo == 0){
              toastr.success(data.message);
              nome.val("");
              email.val("");
              senha.val("");              
              setTimeout(function() {
                window.location.reload();
              }, 1000);
            }else{
              toastr.error(data.message);
            }
          },
          error: function (xhr, status, error) {
              console.error(error);
          }
      });
    }

    function addViagem() {
        var destino = $("#viagem_destino");
        var preco = $("#viagem_preco");
        var qtd = $("#viagem_qtd");
        var imagem = $("#viagem_imagem")[0].files[0];

        var formData = new FormData();
        formData.append('destino', destino.val());
        formData.append('preco', preco.val());
        formData.append('qtd', qtd.val());
        formData.append('imagem', imagem);

        $.ajax({
            url: "App/Models/AddViagem.php",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
              debugger;
                data = JSON.parse(data);
                console.log(data);
                if (data.codigo == 0) {
                    toastr.success(data.message);
                    destino.val("");
                    preco.val("");
                    qtd.val("");
                    $("#viagem_imagem").val("");
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error(data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    
    function excluir(id){
      if(confirm("Deseja realmente excluir o usuário?","Sim","Não")){
        
        var idLogado = <?php echo empty($usuarios[0]['ID']) ? 'null' : $usuarios[0]['ID']; ?>;

        if(idLogado === id){
          toastr.error("Você não pode excluir o usuário logado.");
        }else{

          $.ajax({
              url: "App/Models/ExcluirUsuario.php",
              type: 'POST',
              data: {
                  id: id
              },
              success: function (data){
                debugger;
                data = JSON.parse(data);
                console.log(data);
                if(data.codigo == 0){
                  toastr.success(data.message);
                  setTimeout(function() {
                    window.location.reload();
                  }, 1000);
                }else{
                  toastr.error(data.message);
                }
              },
              error: function (xhr, status, error) {
                  console.error(error);
              }
          });
        }
      }    
    }

    function excluirViagem(id){
      if(confirm("Deseja realmente excluir o usuário?","Sim","Não")){
        $.ajax({
            url: "App/Models/excluirViagem.php",
            type: 'POST',
            data: {
                id: id
            },
            success: function (data){
              debugger;
              data = JSON.parse(data);
              console.log(data);
              if(data.codigo == 0){
                toastr.success(data.message);
                setTimeout(function() {
                  window.location.reload();
                }, 1000);
              }else{
                toastr.error(data.message);
              }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
      }    
    }
  
</script>
</head>

<body>
    <?php if (!$logado) { ?>
        <div class="container login-container">
            <div class="row justify-content-center align-items-center">
                <div class="col-md-5 login-form-2">
                    <h3>Área de Login</h3>
                    
                    <form method="post">
                    <?php
                    // Exibe mensagem de erro, se houver
                    if (isset($erro_login)) {
                        echo '<div class="alert alert-danger">' . htmlspecialchars($erro_login) . '</div>';
                    }
                    ?>
                        <div data-mdb-input-init class="form-outline">
                          <input name="email" type="email" class="form-control" placeholder="Your Email *" required />
                          <label class="form-label" for="form2Example1">Email address</label>
                        </div>
                        <div data-mdb-input-init class="form-outline">
                          <input name="password" type="password" class="form-control" placeholder="Your Password *" required />
                          <label class="form-label" for="form2Example2">Password</label>
                        </div>

                        <div class="form-group text-center">
                            <input type="submit" name="login" class="btn btn-primary btn-block" value="Login" />
                        </div>
                        <div class="form-group text-center">
                            <a href="/" class="ForgetPwd">Voltar para o site</a>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    <?php } else { ?>

  <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
    <header class="topbar" data-navbarbg="skin6">
      <nav class="navbar top-navbar navbar-expand-md navbar-dark text-center">
        <div class="navbar-header" data-logobg="skin6">
          <a class="navbar-brand" href="/">
              <img 
              src="App/Views/img/logo-transparente.png" 
              alt="homepage" 
              class="dark-logo" 
              style="
                    width: 100px; 
                    margin-left:50px;"
              />
          </a>
          <a class="
                nav-toggler
                waves-effect waves-light
                text-dark
                d-block d-md-none
              " href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
        </div>
        <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
          <ul class="navbar-nav me-auto mt-md-0">
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle waves-effect waves-dark text-dark" href="#" id="navbarDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo "Seja bem-vindo(a), ".htmlspecialchars($_SESSION['nome']); ?>
              </a>
            </li>
          </ul>
        </div>
      </nav>
    </header>
    <aside class="left-sidebar" data-sidebarbg="skin6">
      <div class="scroll-sidebar">
        <nav class="sidebar-nav">
          <ul id="sidebarnav">
            <li class="sidebar-item">
              <a style="text-decoration: none;" class="sidebar-link waves-effect waves-dark sidebar-link" onclick="submitForm('dashboard')" aria-expanded="false"><i
                  class="me-3 mdi mdi-view-dashboard fs-3" aria-hidden="true"></i><span
                  class="hide-menu">Dashboard</span></a>
            </li>
            <li class="sidebar-item">
              <a style="text-decoration: none;" class="sidebar-link waves-effect waves-dark sidebar-link" onclick="submitForm('adicionarviagem')"
                aria-expanded="false"><i class="me-3 mdi mdi-airplane-plus fs-3" aria-hidden="true"></i><span
                  class="hide-menu">Adicionar Viagens</span></a>
            </li>
            <li class="sidebar-item">
              <a style="text-decoration: none;" class="sidebar-link waves-effect waves-dark sidebar-link" onclick="submitForm('adicionarusuario')"
                aria-expanded="false"><i class="me-3 mdi mdi-account-plus fs-3" aria-hidden="true"></i><span
                  class="hide-menu">Adicionar Usuario</span></a>
            </li>
            <li class="sidebar-item text-center">              
                  <form method="post">
                    <input type="submit" name="logout" class="btn btn-danger" value="Logout">
                </form>
            </li>
          </ul>
        </nav>
      </div>
    </aside>
    <div class="page-wrapper">
      <div class="page-breadcrumb">
        <div class="row align-items-center">
          <div class="col-md-6 col-8 align-self-center">
            <h3 class="page-title mb-0 p-0" id="titulo">Dashboard</h3>
          </div>
        </div>
      </div>

      <div class="container-fluid">
        <div class="row" id="usuario">
          <div class="col-sm-12">
            <div class="card">
              <div class="card-body">
                <div class="d-md-flex">
                  <h4 class="
                        card-title
                        col-md-10
                        mb-md-0 mb-3
                        align-self-center
                      ">
                    Usuários
                  </h4>
                </div>
                <div class="table-responsive mt-5">
                  <table class="table stylish-table no-wrap">
                    <thead>
                      <tr>
                        <th class="border-top-0" colspan="2">E-mail</th>
                        <th class="border-top-0 text-center">Nome</th>
                        <th class="border-top-0 text-center"></th>
                        <th class="border-top-0 text-center"></th>
                        <th class="border-top-0 text-center"></th>
                      </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($usuarios as $usuario) { ?>
                        <?php $initial = strtoupper($usuario['NOME'][0]); ?>
                        <tr>
                            <td style="width: 50px">
                              <span class="round"><?php echo $initial; ?></span>
                            </td>
                            <td class="align-middle">
                              <h6><?php echo htmlspecialchars($usuario['EMAIL']); ?></h6>
                              <small class="text-muted">Usuario</small>
                            </td>
                            <td class="align-middle text-center"><?php echo htmlspecialchars($usuario['NOME']); ?></td>
                            <td class="align-middle text-center"></td>
                            <td class="align-middle text-center"></td>
                            <td class="align-middle text-center"><button class="btn btn-danger" onclick="excluir(<?php echo htmlspecialchars($usuario['ID']); ?>)">Excluir</button></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row" id="viagem">
          <div class="col-sm-12">
            <div class="card">
              <div class="card-body">
                <div class="d-md-flex">
                  <h4 class="
                        card-title
                        col-md-10
                        mb-md-0 mb-3
                        align-self-center
                      ">
                    Viagens
                  </h4>                  
                </div>
                <div class="table-responsive mt-5">
                  <table class="table stylish-table no-wrap">
                    <thead>
                      <tr>
                        <th class="border-top-0" colspan="2">Destino</th>
                        <th class="border-top-0 text-center">Preço</th>
                        <th class="border-top-0 text-center">Quantidade Dias</th>
                        <th class="border-top-0 text-center">Data de Criação</th>
                        <th class="border-top-0 text-center"></th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($viagens as $viagem) { ?>
                        <?php $initial = strtoupper($viagem['destino'][0]); ?>
                        <tr>
                            <td style="width: 50px">
                            <span class="round"><?php echo $initial; ?></span>
                            </td>
                            <td class="align-middle">
                            <h6><?php echo htmlspecialchars($viagem['destino']); ?></h6>
                            <small class="text-muted">Cidade, País</small>
                            </td>
                            <td class="align-middle text-center"><?php echo 'R$ ' . number_format($viagem['preco'], 2, ',', '.'); ?></td>
                            <td class="align-middle text-center"><?php echo htmlspecialchars($viagem['DiasDeViagem']) . " dias"; ?></td>
                            <td class="align-middle text-center"><?php echo htmlspecialchars(date('d/m/Y', strtotime($viagem['data_insercao']))); ?></td>
                            <td class="align-middle text-center"><button class="btn btn-danger" onclick="excluirViagem(<?php echo $viagem['id'] ?>)">Excluir</button></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row" style="display:none" id="addusuario">
        <div class="col-sm-12">
            <div class="card">
              <div class="card-body">
                <div class="d-md-flex">
                  <h4 class="
                        card-title
                        col-md-10
                        mb-md-0 mb-3
                        align-self-center
                      ">
                    Adicionar Usuario
                  </h4>                  
                </div>
                <div class="table-responsive mt-5 text-center">
                  <table class="table stylish-table no-wrap">
                    <thead>
                      <tr>
                        <th class="text-center">Nome</th>
                        <th class="text-center">E-mail</th>
                        <th class="text-center">Senha</th>
                      </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><input class="text-center form-control" required type="text" id="usuario_nome"></td>
                        <td><input class="text-center form-control" required type="email" id="usuario_email"></td>
                        <td><input class="text-center form-control" required type="password" id="usuario_password"></td>
                    </tr>
                    </tbody>
                  </table>
                  <input type="button" class="btn btn-success" value="Adicionar" onclick="addUsuario()">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row" style="display:none" id="addviagem">
          <div class="col-sm-12">
            <div class="card">
              <div class="card-body">
                <div class="d-md-flex">
                  <h4 class="
                        card-title
                        col-md-10
                        mb-md-0 mb-3
                        align-self-center
                      ">
                    Adicionar Viagem
                  </h4>                  
                </div>
                <div class="table-responsive mt-5 text-center">
                  <table class="table stylish-table no-wrap">
                    <thead>
                      <tr>
                        <th class="text-center">Destino</th>
                        <th class="text-center">Preço</th>
                        <th class="text-center">Quantidade Dias</th>
                        <th class="text-center">imagem</th>
                      </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><input class="text-center form-control" type="text" id="viagem_destino"></td>
                        <td><input class="text-center form-control" type="text" id="viagem_preco"></td>
                        <td><input class="text-center form-control" type="text" id="viagem_qtd"></td>
                        <td><input class="text-center form-control" type="file" id="viagem_imagem"></td>
                    </tr>
                    </tbody>
                  </table>
                  <input type="button" class="btn btn-success" value="Adicionar" id="add_viagem" onclick="addViagem()">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer text-center">
      <p>Copyright © 2024 <a href="https://www.linkedin.com/in/luizrodriguescastroneto/" style="text-decoration: none; color: inherit;">Luiz Rodrigues</a> - Todos os direitos reservados</p>
      </footer>
    </div>
  </div>        
    <?php } ?>
</body>
</html>
