<?php
require_once '../src/controllers/ClientController.php';
require_once '../src/controllers/ContactController.php';
require_once '../src/controllers/LinkController.php'; // add this

$controllerName = $_GET['controller'] ?? 'client';
$action = $_GET['action'] ?? 'index';

switch ($controllerName) {
    case 'client':
        $controller = new ClientController();
        break;
    case 'contact':
        $controller = new ContactController();
        break;
    case 'link':
        $controller = new LinkController();
        break;
    default:
        http_response_code(404);
        echo "Controller not found";
        exit;
}

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    http_response_code(404);
    echo "Action not found";
}
