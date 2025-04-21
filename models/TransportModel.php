<?php
// models/TransportModel.php

function getTransportsForLieu(PDO $pdo, int $lieuId): array {
    try {
        $stmt = $pdo->prepare("SELECT t.icone, t.nom, t.details 
                               FROM transports t 
                               JOIN lieu_transports lt ON t.id = lt.transport_id 
                               WHERE lt.lieu_id = :lieu_id");
        $stmt->execute(['lieu_id' => $lieuId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTransportsForLieu: " . $e->getMessage(), 3, BASE_PATH . '/logs/error.log');
        return [];
    }
}