<?php
// routes/web.php
require_once __DIR__ . '/../controllers/LieuController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new LieuController($pdo);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);



switch ($uri) {
    case '/':
    case '/index.php':
        $controller->index();
        break;

    case '/ajouter':
        $controller->ajouter();
        break;

    default:
        http_response_code(404);
        echo "Page not found";
}
