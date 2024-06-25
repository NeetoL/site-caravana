<?php

// Incluir todos os controladores
require_once 'App/Controllers/HomeController.php';
require_once 'App/Controllers/SobreController.php';
require_once 'App/Controllers/ContatoController.php';
require_once 'App/Controllers/LoginController.php';
require_once 'App/Controllers/PainelController.php';
require_once 'App/Controllers/DestinosController.php';

// Desabilita a exibição de erros (para ambiente de produção)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Função para sanitizar a URI
function sanitize_uri($uri) {
    $uri = filter_var($uri, FILTER_SANITIZE_URL);
    $uri = strtok($uri, '?');
    return rtrim($uri, '/');
}

$uri = sanitize_uri($_SERVER['REQUEST_URI']);

// Definir as rotas para os controladores
$routes = [
    '' => 'HomeController@index',
    '/' => 'HomeController@index',
    '/sobre' => 'SobreController@index',
    '/contato' => 'ContatoController@index',
    '/login' => 'LoginController@index',
    '/painel'=> 'PainelController@index',
    '/destinos'=> 'DestinosController@index'
];

// Função para despachar a rota
function dispatch($uri, $routes) {
    if (array_key_exists($uri, $routes)) {
        $action = $routes[$uri];
        list($controller, $method) = explode('@', $action);
        if (class_exists($controller) && method_exists($controller, $method)) {
            $controllerInstance = new $controller();
            $controllerInstance->$method();
        } else {
            http_response_code(404);
            echo "404 - Controlador ou método não encontrado!";
        }
    } else {
        http_response_code(404);
        echo "404 - Página não encontrada!";
    }
}

// Despachar a rota
dispatch($uri, $routes);
?>
