<?php
session_start();
require_once __DIR__ . '/config/db.php';
Database::getConnection();


// Roteamento simples via GET
$controller = $_GET['controller'] ?? 'site';   // a página inicial do sistema é o site
$action = $_GET['action'] ?? ($controller === 'auth' ? 'form' : 'home');


// Carregar controller
switch ($controller) {
    case 'auth':
        require_once __DIR__ . '/controllers/AuthController.php';
        $c = new AuthController();
        break;


    // CRUD PRODUTOS E VENDAS
    case 'produto':
        require_once __DIR__ . '/controllers/ProdutoController.php';
        $c = new ProdutoController();
        break;


    case 'entrada':
        require_once __DIR__ . '/controllers/EntradaController.php';
        $c = new EntradaController();
        break;


    case 'venda':
        require_once __DIR__ . '/controllers/VendaController.php';
        $c = new VendaController();
        break;


    case 'relatorio':
        require_once __DIR__ . '/controllers/RelatorioController.php';
        $c = new RelatorioController();
        break;

    case 'usuario':
        require_once __DIR__ . '/controllers/UsuarioController.php';
        $c = new UsuarioController();
        break;
     case 'categoria':
         require_once __DIR__ . '/controllers/CategoriaController.php';
         $c = new CategoriaController();
        break;


    // SITE PÚBLICO (página inicial) E INTERESSES DOS CLIENTES
    case 'site':
        require_once __DIR__ . '/controllers/SiteController.php';
        $c = new SiteController();
        break;

    case 'interesse':
        require_once __DIR__ . '/controllers/InteresseController.php';
        $c = new InteresseController();
        break;


    // Caminho padrão caso o controller não exista
    default:
        die("Controller inválido.");
}


// Executar ação
if (!method_exists($c, $action)) {
    die("Ação inválida.");
}


$c->$action();

