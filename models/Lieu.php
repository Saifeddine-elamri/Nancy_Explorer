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
}