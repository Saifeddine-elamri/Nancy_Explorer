<?php
// models/Lieu.php
class Lieu {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getLieux($filters = []) {
        $query = "SELECT * FROM lieu";
        $conditions = [];
        $params = [];

        if (!empty($filters['type'])) {
            $conditions[] = "type_id = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "nom LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if ($conditions) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch all types for the filter dropdown
    public function getTypes() {
        $stmt = $this->pdo->prepare("SELECT * FROM type_lieu");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch suggestions for the search datalist
    public function getSearchSuggestions() {
        $stmt = $this->pdo->prepare("SELECT DISTINCT nom FROM lieu LIMIT 10");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Récupérer tous les transports disponibles
    public function getTransports() {
        $stmt = $this->pdo->prepare("SELECT * FROM transport");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un lieu spécifique par son ID
// Récupérer un lieu spécifique par son ID (avec type_nom + menu si applicable)
public function getLieuById($id) {
    // Récupère le lieu avec le nom du type (nécessaire pour savoir si c'est un restaurant, café, ou bar)
    $stmt = $this->pdo->prepare("
        SELECT lieu.*, type_lieu.nom AS type_nom 
        FROM lieu 
        LEFT JOIN type_lieu ON lieu.type_id = type_lieu.id 
        WHERE lieu.id = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $lieu = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifie si c'est un café / restaurant / bar
    $isCafeOrRestaurantOrBar = in_array(strtolower($lieu['type_nom'] ?? ''), ['café', 'restaurant', 'bar']);

    // Initialise menu
    $lieu['menu'] = [];

    if ($isCafeOrRestaurantOrBar) {
        try {
            $stmtCheck = $this->pdo->query("SHOW TABLES LIKE 'menu_items'");
            if ($stmtCheck->rowCount() > 0) {
                $sqlMenu = "SELECT category, item_name, description, price FROM menu_items WHERE lieu_id = ? ORDER BY category, item_name";
                $stmtMenu = $this->pdo->prepare($sqlMenu);
                $stmtMenu->execute([$id]);
                $menuItems = $stmtMenu->fetchAll(PDO::FETCH_ASSOC);

                // Regroupe les items par catégorie
                $menuByCategory = [];
                foreach ($menuItems as $item) {
                    $category = $item['category'] ?: 'Général';
                    $menuByCategory[$category][] = $item;
                }
                $lieu['menu'] = $menuByCategory;
            }
        } catch (PDOException $e) {
            // Tu peux logger l'erreur ici si tu veux, sinon on ignore
        }
    }

    return $lieu;
}

}
