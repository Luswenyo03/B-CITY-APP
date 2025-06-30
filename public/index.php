<?php
// Include the BaseController first (HomeController depends on it)
require_once '../src/controllers/BaseController.php';

// Then include HomeController
require_once '../src/controllers/HomeController.php';

// Now instantiate the controller
$controller = new HomeController();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/BinaryCity/public/' || $uri === '/BinaryCity/public/index.php') {
    require_once '../src/controllers/HomeController.php';
    $controller = new HomeController();
    $controller->index();

} elseif ($uri === '/BinaryCity/public/contact') {
    require_once '../src/controllers/ContactController.php';
    $controller = new ContactController();
    $controller->index();

} else {
    echo "404 - Page not found";
}