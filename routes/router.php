<?php 
class Router {
    private $routes = [];
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addRoute(string $method, string $path, callable $handler): void {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(): void {
        $uri = $this->getCleanUri();
        $method = $this->getMethod();

        try {
            $this->processRoute($method, $uri);
        } catch (Exception $e) {
            $this->sendError($e->getCode(), $e->getMessage());
        }
    }

    private function getCleanUri(): string {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        // Suppression du query string si présent
        return preg_replace('/\?.*$/', '', $uri);
    }

    private function getMethod(): string {
        $method = $_SERVER['REQUEST_METHOD'];
        // Gestion des méthodes override (pour PUT, DELETE, etc.)
        if ($method === 'POST' && isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return $method;
    }

    private function processRoute(string $method, string $uri): void {
        if (!isset($this->routes[$method])) {
            throw new Exception('Méthode non autorisée', 405);
        }

        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->convertRouteToPattern($route);
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func_array($handler, $params);
                return;
            }
        }

        throw new Exception('Page introuvable', 404);
    }

    private function convertRouteToPattern(string $route): string {
        // Convertit les paramètres de route {param} en patterns regex
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[a-zA-Z0-9\-]+)', $route);
        return '#^' . $pattern . '$#';
    }

    private function sendError(int $code, string $message): void {
        http_response_code($code);
        
        // Vous pourriez utiliser un système de templates ici
        if ($code === 404) {
            include __DIR__ . '/../views/errors/404.php';
        } elseif ($code === 405) {
            header('Allow: ' . implode(', ', array_keys($this->routes)));
            include __DIR__ . '/../views/errors/405.php';
        } else {
            echo $message;
        }
        exit;
    }
}
