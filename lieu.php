<?php
require 'includes/db.php';
require 'includes/header.php';
?>

<div class="container-fluid py-5">
  <?php
  // Vérifier si l'ID du lieu est fourni
  if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $sql = "SELECT lieu.*, type_lieu.nom AS type_nom FROM lieu 
            LEFT JOIN type_lieu ON lieu.type_id = type_lieu.id 
            WHERE lieu.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['id']]);
    $lieu = $stmt->fetch(PDO::FETCH_ASSOC);

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

  // Fetch menu items if the lieu is a Café or Restaurant
  $isCafeOrRestaurantOrBar = in_array(strtolower($lieu['type_nom'] ?? ''), ['café', 'restaurant', 'bar']);
  $menuItems = [];
  $menuByCategory = [];
  if ($isCafeOrRestaurantOrBar) {
    try {
      $sqlCheckTable = "SHOW TABLES LIKE 'menu_items'";
      $stmtCheck = $pdo->query($sqlCheckTable);
      if ($stmtCheck->rowCount() > 0) {
        $sqlMenu = "SELECT category, item_name, description, price FROM menu_items WHERE lieu_id = ? ORDER BY category, item_name";
        $stmtMenu = $pdo->prepare($sqlMenu);
        $stmtMenu->execute([$_GET['id']]);
        $menuItems = $stmtMenu->fetchAll(PDO::FETCH_ASSOC);
        foreach ($menuItems as $item) {
          $category = $item['category'] ?: 'General';
          $menuByCategory[$category][] = $item;
        }
      }
    } catch (PDOException $e) {
      // Silently skip menu if table is missing
    }
  }

  // Fetch gallery images
  $galleryImages = [];
  try {
    $sqlCheckTable = "SHOW TABLES LIKE 'lieu_images'";
    $stmtCheck = $pdo->query($sqlCheckTable);
    if ($stmtCheck->rowCount() > 0) {
      $sqlGallery = "SELECT image_url, caption FROM lieu_images WHERE lieu_id = ? ORDER BY id";
      $stmtGallery = $pdo->prepare($sqlGallery);
      $stmtGallery->execute([$_GET['id']]);
      $galleryImages = $stmtGallery->fetchAll(PDO::FETCH_ASSOC);
    }
  } catch (PDOException $e) {
    // Silently skip gallery if table is missing
  }
  ?>

  <!-- Bouton de retour flottant -->
  <div class="floating-back">
    <a href="index.php" class="btn btn-gradient rounded-pill btn-floating">
      <i class="bi bi-chevron-left me-1"></i> Retour
    </a>
  </div>

  <div class="row g-4">
    <!-- Image principale et galerie -->
    <div class="col-lg-6">
      <!-- Image principale -->
      <div class="hero-image-wrapper mb-4 animate__animated animate__fadeInLeft">
        <img src="<?= htmlspecialchars($lieu['image_url'] ?? 'https://via.placeholder.com/800x600?text='.urlencode($lieu['nom'])) ?>" 
             class="img-fluid rounded-4 shadow-lg" 
             alt="<?= htmlspecialchars($lieu['nom']) ?>" 
             loading="lazy">
        <div class="image-overlay"></div>
        <div class="image-badge">
          <span class="badge bg-glass rounded-pill px-3 py-2">
            <i class="bi bi-star-fill text-warning me-1"></i>
            <span class="fw-medium"><?= htmlspecialchars($lieu['type_nom'] ?? 'Lieu') ?></span>
          </span>
        </div>
      </div>

      <!-- Galerie photo -->
      <?php if (!empty($galleryImages)) : ?>
        <div class="gallery-wrapper animate__animated animate__fadeInLeft animate__delay-1s">
          <h5 class="section-title mb-3"><i class="bi bi-images me-2"></i> Galerie</h5>
          <div class="row g-2">
            <?php foreach ($galleryImages as $index => $image) : ?>
              <div class="col-4 col-md-3">
                <a href="<?= htmlspecialchars($image['image_url']) ?>" 
                   class="gallery-item rounded-3 overflow-hidden d-block"
                   data-pswp-width="1200" 
                   data-pswp-height="800"
                   data-caption="<?= htmlspecialchars($image['caption'] ?? '') ?>">
                  <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                       class="img-fluid hover-zoom"
                       alt="<?= htmlspecialchars($image['caption'] ?? $lieu['nom']) ?>" 
                       loading="lazy">
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Détails du lieu -->
    <div class="col-lg-6">
      <div class="details-card glass-card p-4 animate__animated animate__fadeInRight">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h1 class="display-5 fw-bold mb-1"><?= htmlspecialchars($lieu['nom']) ?></h1>
            <div class="d-flex align-items-center text-muted mb-3">
              <i class="bi bi-geo-alt-fill me-1 text-primary"></i>
              <span><?= htmlspecialchars($lieu['adresse'] ?? 'Adresse non précisée') ?></span>
            </div>
          </div>
          <div class="rating-badge">
            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
              <i class="bi bi-check-circle-fill me-1"></i>
              <span>Ouvert</span>
            </span>
          </div>
        </div>
        
        <!-- Description -->
        <div class="description-wrapper mb-4">
          <p class="lead text-muted mb-0"><?= htmlspecialchars($lieu['description'] ?? 'Aucune description disponible.') ?></p>
        </div>
        
        <!-- Informations clés -->
        <div class="key-info-grid mb-4">
          <div class="info-card">
            <div class="info-icon bg-primary bg-opacity-10 text-primary">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div class="info-content">
              <small class="text-muted d-block">Téléphone</small>
              <span class="fw-medium"><?= !empty($lieu['telephone']) ? htmlspecialchars($lieu['telephone']) : 'Non précisé' ?></span>
            </div>
          </div>
          
          <div class="info-card">
            <div class="info-icon bg-info bg-opacity-10 text-info">
              <i class="bi bi-globe"></i>
            </div>
            <div class="info-content">
              <small class="text-muted d-block">Site web</small>
              <span class="fw-medium">
                <?php if (!empty($lieu['site_web'])) : ?>
                  <a href="<?= htmlspecialchars($lieu['site_web']) ?>" target="_blank">Visiter le site</a>
                <?php else : ?>
                  Non précisé
                <?php endif; ?>
              </span>
            </div>
          </div>
          
          <div class="info-card">
            <div class="info-icon bg-warning bg-opacity-10 text-warning">
              <i class="bi bi-credit-card-fill"></i>
            </div>
            <div class="info-content">
              <small class="text-muted d-block">Prix moyen</small>
              <span class="fw-medium">
                <?php if (!empty($lieu['prix_moyen'])) : ?>
                  <?= htmlspecialchars($lieu['prix_moyen']) ?> €
                <?php else : ?>
                  Non précisé
                <?php endif; ?>
              </span>
            </div>
          </div>
          
          <div class="info-card">
            <div class="info-icon bg-purple bg-opacity-10 text-purple">
              <i class="bi bi-people-fill"></i>
            </div>
            <div class="info-content">
              <small class="text-muted d-block">Capacité</small>
              <span class="fw-medium">
                <?php if (!empty($lieu['capacite'])) : ?>
                  <?= htmlspecialchars($lieu['capacite']) ?> pers.
                <?php else : ?>
                  Non précisé
                <?php endif; ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Horaires avec accordéon -->
        <div class="accordion custom-accordion mb-4" id="horairesAccordion">
          <div class="accordion-item border-0 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed bg-light text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHoraires" aria-expanded="false" aria-controls="collapseHoraires">
                <i class="bi bi-clock-history me-2 text-primary"></i> Horaires d'ouverture
              </button>
            </h2>
            <div id="collapseHoraires" class="accordion-collapse collapse" data-bs-parent="#horairesAccordion">
              <div class="accordion-body bg-white">
                <?php if (!empty($lieu['horaires'])) : ?>
                  <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                      <?php
                      $horaires = explode("\n", $lieu['horaires']);
                      foreach ($horaires as $horaire) :
                        if (trim($horaire)) :
                      ?>
                      <tr>
                        <td class="py-2"><?= htmlspecialchars(trim($horaire)) ?></td>
                      </tr>
                      <?php endif; endforeach; ?>
                    </table>
                  </div>
                <?php else : ?>
                  <p class="text-muted mb-0">Horaires non disponibles.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Menu avec accordéon pour cafés et restaurants -->
        <?php if ($isCafeOrRestaurantOrBar) : ?>
          <?php if (!empty($menuByCategory)) : ?>
            <div class="accordion custom-accordion mb-4" id="menuAccordion">
              <div class="accordion-item border-0 rounded-3 overflow-hidden">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed bg-light text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenu" aria-expanded="false" aria-controls="collapseMenu">
                    <i class="bi bi-menu-up me-2 text-primary"></i> Menu & Carte
                  </button>
                </h2>
                <div id="collapseMenu" class="accordion-collapse collapse" data-bs-parent="#menuAccordion">
                  <div class="accordion-body bg-white p-0">
                    <div class="menu-container">
                      <?php foreach ($menuByCategory as $category => $items) : ?>
                        <div class="menu-section">
                          <h5 class="menu-category"><?= htmlspecialchars($category) ?></h5>
                          <div class="menu-items">
                            <?php foreach ($items as $item) : ?>
                            <div class="menu-item">
                              <div class="item-details">
                                <h6 class="item-name"><?= htmlspecialchars($item['item_name']) ?></h6>
                                <?php if (!empty($item['description'])) : ?>
                                  <p class="item-description text-muted"><?= htmlspecialchars($item['description']) ?></p>
                                <?php endif; ?>
                              </div>
                              <div class="item-price">
                                <?php if (!empty($item['price'])) : ?>
                                  <span class="price"><?= htmlspecialchars(number_format($item['price'], 2)) ?> €</span>
                                <?php else : ?>
                                  <span class="price">-</span>
                                <?php endif; ?>
                              </div>
                            </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php else : ?>
            <div class="alert alert-light border mb-4">
              <i class="bi bi-info-circle-fill text-info me-2"></i>
              <span class="fw-medium">Menu :</span> Aucun menu disponible pour le moment.
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <!-- Carte interactive -->
        <div class="map-card rounded-4 overflow-hidden shadow-sm mb-4">
          <div id="map" class="rounded-4" style="height: 300px; width: 100%;"></div>
          <div class="map-controls">
            <button class="btn btn-sm btn-light rounded-pill shadow-sm" onclick="recenterMap()">
              <i class="bi bi-crosshair me-1"></i> Recentrer
            </button>
            <button class="btn btn-sm btn-primary rounded-pill shadow-sm" onclick="openDirections()">
              <i class="bi bi-signpost-2 me-1"></i> Itinéraire
            </button>
          </div>
        </div>

        <!-- Informations supplémentaires -->
        <?php if (!empty($lieu['infos_supplementaires'])) : ?>
        <div class="additional-info mt-4">
          <h5 class="section-title mb-3"><i class="bi bi-info-circle me-2"></i> Informations supplémentaires</h5>
          <div class="info-content bg-light rounded-3 p-3">
            <?= htmlspecialchars($lieu['infos_supplementaires']) ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Inclure Leaflet CSS et JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Inclure Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Inclure Animate.css pour les animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<!-- Inclure PhotoSwipe pour la lightbox -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.2/photoswipe.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.2/photoswipe.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.2/photoswipe-lightbox.min.js"></script>

<style>
:root {
  --primary-color: #4a6bff;
  --secondary-color: #3a56e8;
  --accent-color: #00c9ff;
  --dark-color: #1e293b;
  --light-color: #f8fafc;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --purple: #8b5cf6;
  --border-radius: 12px;
  --border-radius-sm: 8px;
  --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --box-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  color: #334155;
  line-height: 1.6;
}

/* Conteneur principal */
.container-fluid {
  max-width: 1400px;
  padding: 2rem;
}

/* Bouton de retour flottant */
.floating-back {
  position: sticky;
  top: 20px;
  z-index: 1000;
  margin-bottom: 20px;
}

.btn-gradient {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
  border: none;
  padding: 0.75rem 1.25rem;
  border-radius: 50px;
  transition: var(--transition);
  box-shadow: var(--box-shadow);
  font-weight: 500;
  display: inline-flex;
  align-items: center;
}

.btn-gradient:hover {
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(74, 107, 255, 0.3);
  color: white;
}

/* Image principale */
.hero-image-wrapper {
  position: relative;
  overflow: hidden;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow-lg);
  transition: transform 0.5s ease;
  min-height: 400px;
}

.hero-image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
  will-change: transform;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0) 50%);
  border-radius: var(--border-radius);
  pointer-events: none;
}

.image-badge {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 2;
}

.bg-glass {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.hero-image-wrapper:hover img {
  transform: scale(1.03);
}

/* Galerie photo */
.gallery-wrapper {
  margin-top: 1.5rem;
}

.section-title {
  font-weight: 600;
  color: var(--dark-color);
  display: flex;
  align-items: center;
}

.gallery-item {
  position: relative;
  aspect-ratio: 1/1;
  transition: var(--transition);
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: var(--transition);
}

.hover-zoom:hover {
  transform: scale(1.05);
}

/* Carte des détails */
.glass-card {
  border-radius: var(--border-radius);
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: var(--box-shadow-lg);
  transition: var(--transition);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.glass-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
}

h1 {
  font-weight: 700;
  color: var(--dark-color);
  line-height: 1.2;
}

.description-wrapper {
  border-left: 3px solid var(--accent-color);
  padding-left: 1rem;
  margin: 1.5rem 0;
}

/* Grille d'informations clés */
.key-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 1.5rem;
}

.info-card {
  background: white;
  border-radius: var(--border-radius-sm);
  padding: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: var(--box-shadow);
  transition: var(--transition);
}

.info-card:hover {
  transform: translateY(-3px);
}

.info-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.info-content small {
  font-size: 0.75rem;
}

/* Accordéon personnalisé */
.custom-accordion .accordion-button {
  border-radius: var(--border-radius-sm) !important;
  padding: 1rem;
  font-size: 0.95rem;
}

.custom-accordion .accordion-button:not(.collapsed) {
  background-color: rgba(74, 107, 255, 0.05);
  color: var(--primary-color);
  box-shadow: none;
}

.custom-accordion .accordion-body {
  padding: 1rem;
}

/* Menu styling */
.menu-container {
  padding: 1rem;
}

.menu-section {
  margin-bottom: 1.5rem;
}

.menu-category {
  color: var(--dark-color);
  font-weight: 600;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--accent-color);
}

.menu-items {
  display: grid;
  gap: 12px;
}

.menu-item {
  display: flex;
  justify-content: space-between;
  padding: 12px;
  border-radius: var(--border-radius-sm);
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: var(--transition);
}

.menu-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.item-name {
  font-weight: 600;
  margin-bottom: 4px;
  color: var(--dark-color);
}

.item-description {
  font-size: 0.85rem;
  margin-bottom: 0;
}

.item-price {
  font-weight: 600;
  color: var(--primary-color);
  white-space: nowrap;
  margin-left: 12px;
}

/* Carte */
.map-card {
  position: relative;
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.map-controls {
  position: absolute;
  bottom: 15px;
  right: 15px;
  display: flex;
  gap: 8px;
  z-index: 1000;
}

.map-controls .btn {
  font-size: 0.8rem;
  padding: 0.35rem 0.75rem;
  display: inline-flex;
  align-items: center;
}

/* Alerte élégante */
.alert-elegant {
  background: linear-gradient(135deg, var(--danger-color), #dc2626);
  color: white;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  text-align: center;
  max-width: 600px;
  margin: 2rem auto;
  box-shadow: var(--box-shadow-lg);
  border: none;
}

.alert-link {
  color: white;
  text-decoration: underline;
  font-weight: 600;
}

/* Animation */
.animate-delay-1 {
  animation-delay: 0.1s;
}

.animate-delay-2 {
  animation-delay: 0.2s;
}

/* Custom marker for map */
.custom-marker {
  width: 40px;
  height: 40px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.custom-marker i {
  color: var(--primary-color);
  font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 992px) {
  .container-fluid {
    padding: 1.5rem;
  }
  
  h1 {
    font-size: 2rem;
  }
  
  .hero-image-wrapper {
    min-height: 350px;
  }
  
  .key-info-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .container-fluid {
    padding: 1rem;
  }
  
  h1 {
    font-size: 1.75rem;
  }
  
  .glass-card {
    padding: 1.5rem;
  }
  
  .map-controls {
    bottom: 10px;
    right: 10px;
  }
  
  .map-controls .btn {
    padding: 0.3rem 0.6rem;
    font-size: 0.75rem;
  }
}

@media (max-width: 576px) {
  h1 {
    font-size: 1.5rem;
  }
  
  .floating-back {
    position: static;
    margin-bottom: 1rem;
  }
  
  .description-wrapper {
    margin: 1rem 0;
  }
  
  .hero-image-wrapper {
    min-height: 300px;
  }
}
</style>

<script>
// Initialize PhotoSwipe Lightbox
document.addEventListener('DOMContentLoaded', () => {
  const lightbox = new PhotoSwipeLightbox({
    gallery: '.gallery-wrapper',
    children: 'a.gallery-item',
    pswpModule: PhotoSwipe,
    bgOpacity: 0.9,
    spacing: 0,
    loop: false,
    pinchToClose: false
  });
  
  lightbox.on('uiRegister', function() {
    lightbox.pswp.ui.registerElement({
      name: 'download-button',
      ariaLabel: 'Download image',
      order: 8,
      isButton: true,
      html: '<i class="bi bi-download"></i>',
      onClick: (event, el) => {
        const pswp = lightbox.pswp;
        const target = pswp.currSlide.data.element;
        if (target) {
          const link = document.createElement('a');
          link.href = target.href;
          link.download = '';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        }
      }
    });
  });
  
  lightbox.init();
});

// Variables globales pour la carte
let mapInstance = null;
let marker = null;

function initMap() {
  const latitude = <?= json_encode($latitude) ?>;
  const longitude = <?= json_encode($longitude) ?>;
  const validCoords = !isNaN(latitude) && !isNaN(longitude);

  if (validCoords) {
    mapInstance = L.map('map', {
      scrollWheelZoom: false,
      zoomControl: false,
      minZoom: 10,
      maxZoom: 18,
      fadeAnimation: true,
      zoomAnimation: true
    }).setView([latitude, longitude], 15);

    L.control.zoom({
      position: 'topright'
    }).addTo(mapInstance);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> © <a href="https://carto.com/attributions">CARTO</a>',
      maxZoom: 19,
      subdomains: 'abcd',
      detectRetina: true
    }).addTo(mapInstance);

    const customIcon = L.divIcon({
      html: '<div class="custom-marker"><i class="bi bi-geo-alt-fill"></i></div>',
      iconSize: [40, 40],
      className: ''
    });

    marker = L.marker([latitude, longitude], {
      icon: customIcon,
      riseOnHover: true
    }).addTo(mapInstance);

    L.circle([latitude, longitude], {
      color: var(--primary-color),
      fillColor: var(--accent-color),
      fillOpacity: 0.2,
      radius: 100
    }).addTo(mapInstance);

    setTimeout(() => {
      mapInstance.invalidateSize();
    }, 100);
  } else {
    document.getElementById('map').innerHTML = 
      '<div class="d-flex justify-content-center align-items-center h-100 bg-light rounded-4"><p class="text-muted p-4 m-0">Coordonnées non valides</p></div>';
  }
}

function recenterMap() {
  if (mapInstance) {
    mapInstance.flyTo([<?= json_encode($latitude) ?>, <?= json_encode($longitude) ?>], 15, {
      duration: 1,
      easeLinearity: 0.25
    });
  }
}

function openDirections() {
  const latitude = <?= json_encode($latitude) ?>;
  const longitude = <?= json_encode($longitude) ?>;
  const name = encodeURIComponent("<?= addslashes($lieu['nom']) ?>");
  
  if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
    window.open(`maps://maps.google.com/maps?daddr=${latitude},${longitude}&q=${name}`);
  } else {
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}&destination_place_id=${name}`);
  }
}

// Initialiser la carte après le chargement du DOM
document.addEventListener('DOMContentLoaded', initMap);
</script>

<?php require 'includes/footer.php'; ?>