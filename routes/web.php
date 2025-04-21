<?php // routes/web.php
require_once __DIR__ . '/../controllers/LieuController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new LieuController($pdo);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', $uri);  // Diviser l'URL en segments

// Vérifier que uriSegments est un tableau et qu'il a le bon nombre de segments
if (is_array($uriSegments) && count($uriSegments) > 1) {

    switch ($uri) {
        case '/':
        case '/index.php':
            $controller->index();
            break;

        case '/ajouter':
            $controller->ajouter();
            break;

        default:
            // Vérifie si l'URL contient un nom de lieu et un ID
            if (count($uriSegments) === 3 && !empty($uriSegments[1]) && is_numeric($uriSegments[2])) {
                $lieuNom = urldecode($uriSegments[1]);  // Décoder le nom du lieu (pour gérer les caractères spéciaux)
                $lieuId = $uriSegments[2];  // Récupérer l'ID du lieu

                // Appeler une méthode du contrôleur pour afficher le lieu avec l'ID
                $controller->show($lieuId);
            } else {
                http_response_code(404);
                echo "Page not found";
            }
    }
} else {
    http_response_code(400);  // Code de réponse incorrecte si l'URL est mal formatée
    echo "Invalid URL format";
}
