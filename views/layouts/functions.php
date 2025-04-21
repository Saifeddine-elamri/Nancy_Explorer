<?php
function getLieux($pdo, $filters = []) {
    $sql = "SELECT lieu.*, type_lieu.nom AS type_nom, lieu.horaires 
            FROM lieu 
            LEFT JOIN type_lieu ON lieu.type_id = type_lieu.id 
            WHERE 1=1";
    $params = [];

    if (!empty($filters['type']) && is_numeric($filters['type'])) {
        $sql .= " AND lieu.type_id = ?";
        $params[] = $filters['type'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND lieu.nom LIKE ?";
        $params[] = "%" . $filters['search'] . "%";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTransportsForLieu($pdo, $lieuId) {
    $stmt = $pdo->prepare("
        SELECT t.nom, t.icone, tl.details 
        FROM transport t 
        INNER JOIN transport_lieu tl ON t.id = tl.transport_id 
        WHERE tl.lieu_id = ?
    ");
    $stmt->execute([$lieuId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}