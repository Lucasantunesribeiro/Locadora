<?php

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Definir constantes
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Handler de erros personalizado
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    error_log("PHP Error: $message in $file on line $line");
    
    if (!headers_sent()) {
        http_response_code(500);
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor']);
        } else {
            echo '<h1>500 - Erro interno do servidor</h1>';
        }
    }
    exit;
});

// Handler de exceções não capturadas
set_exception_handler(function($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    if (!headers_sent()) {
        http_response_code(500);
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor']);
        } else {
            echo '<h1>500 - Erro interno do servidor</h1>';
        }
    }
    exit;
});

try {
    // Autoloader
    require_once ROOT_PATH . '/src/autoload.php';
} catch (Exception $e) {
    error_log("Autoloader Error: " . $e->getMessage());
    http_response_code(500);
    echo '<h1>500 - Erro de configuração</h1>';
    exit;
}

try {
    // Controle de erros baseado no ambiente
    if (config('app.debug')) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 0);
        error_reporting(0);
    }

    // Inicializar sessão
    startSession();

    // Criar instância do router
    $router = new Router();

// Rotas da aplicação
$router->get('/', function() {
    if (isAuthenticated()) {
        startSession();
        // Admin vai para dashboard admin, usuário comum vai para dashboard do usuário
        if ($_SESSION['usuario_role'] === 'admin') {
            require ROOT_PATH . '/src/views/home.php';
        } else {
            require ROOT_PATH . '/src/views/user_dashboard.php';
        }
    } else {
        require ROOT_PATH . '/public/index.html';
    }
});

$router->get('/login', function() {
    require ROOT_PATH . '/src/views/login.html';
});

$router->get('/cadastro', function() {
    require ROOT_PATH . '/src/views/cadastro.html';
});

$router->get('/health', function() {
    require ROOT_PATH . '/public/health.php';
});



// Rotas da API
$router->post('/api/login', 'AuthController@login');
$router->post('/api/cadastro', 'AuthController@register');
$router->post('/api/logout', 'AuthController@logout');



// Rota de teste
$router->post('/api/test', function() {
    error_reporting(0);
    ini_set('display_errors', 0);
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Teste funcionando', 'post' => $_POST]);
    exit;
});

// Rotas de carros (públicas para visualização)
$router->get('/api/carros', 'CarroController@listar');
$router->get('/api/listar_carro', 'CarroController@listar');
$router->get('/api/carros/*', 'CarroController@detalhar');
// Rotas de carros (autenticadas para modificação)
$router->post('/api/carros', 'CarroController@criar', ['AuthMiddleware']);
$router->post('/api/criacao_carro', 'CarroController@criar', ['AuthMiddleware']);
$router->put('/api/carros/*', 'CarroController@atualizar', ['AuthMiddleware']);
$router->post('/api/atualizar_carro', 'CarroController@atualizar', ['AuthMiddleware']);
$router->delete('/api/carros/*', 'CarroController@deletar', ['AuthMiddleware']);
$router->post('/api/deletar_carro', 'CarroController@deletar', ['AuthMiddleware']);

// Rotas de usuários (apenas admin)
$router->get('/api/usuarios', 'UsuarioController@listar', ['AuthMiddleware']);
$router->get('/api/listar_usuarios', 'UsuarioController@listar', ['AuthMiddleware']);
$router->post('/api/usuarios', 'UsuarioController@criar', ['AuthMiddleware']);
$router->get('/api/usuarios/*', 'UsuarioController@obter', ['AuthMiddleware']);
$router->put('/api/usuarios/*', 'UsuarioController@atualizar', ['AuthMiddleware']);
$router->post('/api/atualizar_usuario', 'UsuarioController@atualizar', ['AuthMiddleware']);
$router->delete('/api/usuarios/*', 'UsuarioController@deletar', ['AuthMiddleware']);
$router->post('/api/deletar_usuario', 'UsuarioController@deletar', ['AuthMiddleware']);

// Rotas de perfil (qualquer usuário autenticado)
$router->get('/api/perfil', 'UsuarioController@obterPerfil', ['AuthMiddleware']);
$router->post('/api/perfil', 'UsuarioController@atualizarPerfil', ['AuthMiddleware']);

// Rotas de aluguéis (autenticadas)
$router->get('/api/alugueis', 'AluguelController@listarTodos', ['AuthMiddleware']);
$router->get('/api/meus-alugueis', 'AluguelController@listar', ['AuthMiddleware']);
$router->get('/api/alugueis/*', 'AluguelController@obter', ['AuthMiddleware']);
$router->post('/api/alugueis', 'AluguelController@criar', ['AuthMiddleware']);
$router->post('/api/alugar_carro', 'AluguelController@criar', ['AuthMiddleware']);
$router->put('/api/alugueis/*', 'AluguelController@atualizar', ['AuthMiddleware']);
$router->post('/api/atualizar_aluguel', 'AluguelController@atualizar', ['AuthMiddleware']);
$router->delete('/api/alugueis/*', 'AluguelController@deletar', ['AuthMiddleware']);
$router->post('/api/deletar_aluguel', 'AluguelController@deletar', ['AuthMiddleware']);

// Rotas protegidas
$router->get('/admin/*', function() {
    if (!isAuthenticated()) {
        redirectTo('/login');
    }
    // Servir páginas administrativas
}, ['AuthMiddleware']);

    // Resolver rota
    $router->resolve();

} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    if (!headers_sent()) {
        http_response_code(500);
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor']);
        } else {
            echo '<h1>500 - Erro interno do servidor</h1>';
        }
    }
}