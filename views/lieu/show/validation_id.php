<?php
// Vérifier si l'ID du lieu est fourni
if (isset($id) && is_numeric($id)) {
    if (!$lieu) {
        echo '<div class="alert alert-elegant animate__animated animate__fadeIn" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                Lieu non trouvé. <a href="index.php" class="alert-link">Retour à l\'accueil</a>
              </div>';
        exit;
    }
} else {
    echo '<div class="alert alert-elegant animate__animated animate__fadeIn" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            ID de lieu invalide. <a href="index.php" class="alert-link">Retour à l\'accueil</a>
          </div>';
    exit;
}

// Coordonnées avec valeurs par défaut sécurisées
$latitude = !empty($lieu['latitude']) && is_numeric($lieu['latitude']) ? $lieu['latitude'] : 48.8566;
$longitude = !empty($lieu['longitude']) && is_numeric($lieu['longitude']) ? $lieu['longitude'] : 2.3522;
?>
