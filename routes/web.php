<?php
// routes/web.php

require_once __DIR__ . '/../controllers/LieuController.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/router.php';

// Initialisation du routeur
$router = new Router($pdo);
$controller = new LieuController($pdo);

// Définition des routes
$router->addRoute('GET', '', fn() => $controller->index());
$router->addRoute('GET', 'ajouter', fn() => $controller->ajouter());
$router->addRoute('GET', '{slug}/{id}', fn($slug, $id) => $controller->show($id));

// Routes POST exemple :
// $router->addRoute('POST', 'lieu', fn() => $controller->store());

// Gestion des requêtes
$router->dispatch();