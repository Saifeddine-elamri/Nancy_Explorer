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
      echo '<div class="alert alert-custom animate__animated animate__fadeIn" role="alert">
              <i class="bi bi-exclamation-circle-fill me-2"></i>
              Lieu non trouvé. <a href="index.php" class="alert-link">Retour à l\'accueil</a>
            </div>';
      exit;
    }
  } else {
    echo '<div class="alert alert-custom animate__animated animate__fadeIn" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            ID de lieu invalide. <a href="index.php" class="alert-link">Retour à l\'accueil</a>
          </div>';
    exit;
  }

  // Coordonnées avec valeurs par défaut sécurisées
  $latitude = !empty($lieu['latitude']) && is_numeric($lieu['latitude']) ? $lieu['latitude'] : 48.8566;
  $longitude = !empty($lieu['longitude']) && is_numeric($lieu['longitude']) ? $lieu['longitude'] : 2.3522;

  // Fetch menu items if the lieu is a Café or Restaurant
  $isCafeOrRestaurant = in_array(strtolower($lieu['type_nom'] ?? ''), ['café', 'restaurant']);
  $menuItems = [];
  $menuByCategory = [];
  if ($isCafeOrRestaurant) {
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
    <a href="index.php" class="btn btn-custom btn-floating">
      <i class="bi bi-arrow-left"></i> Retour
    </a>
  </div>

  <div class="row g-5">
    <!-- Image principale et galerie -->
    <div class="col-lg-6">
      <!-- Image principale -->
      <div class="image-wrapper mb-4">
        <img src="<?= htmlspecialchars($lieu['image_url'] ?? 'https://via.placeholder.com/600x400') ?>" 
             class="img-fluid rounded-4 shadow-lg" 
             alt="<?= htmlspecialchars($lieu['nom']) ?>" 
             loading="lazy">
        <div class="image-overlay"></div>
      </div>

      <!-- Galerie photo -->
      <?php if (!empty($galleryImages)) : ?>
        <div class="gallery-wrapper">
          <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <?php foreach ($galleryImages as $index => $image) : ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                  <a href="<?= htmlspecialchars($image['image_url']) ?>" 
                     class="gallery-lightbox" 
                     data-pswp-width="1200" 
                     data-pswp-height="800"
                     data-caption="<?= htmlspecialchars($image['caption'] ?? '') ?>">
                    <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                         class="d-block w-100 rounded-4" 
                         alt="<?= htmlspecialchars($image['caption'] ?? $lieu['nom']) ?>" 
                         loading="lazy">
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Détails du lieu -->
    <div class="col-lg-6">
      <div class="details-card glass-effect p-4 animate__animated animate__fadeInUp">
        <!-- En-tête avec badge flottant -->
        <div class="d-flex justify-content-between align-items-start mb-3">
          <h1 class="mb-0">
            <?= htmlspecialchars($lieu['nom']) ?>
          </h1>
          <span class="badge badge-custom rounded-pill fs-6"><?= htmlspecialchars($lieu['type_nom'] ?? 'Non précisé') ?></span>
        </div>
        
        <!-- Description -->
        <div class="description-wrapper mb-4">
          <p class="lead text-muted mb-0"><?= htmlspecialchars($lieu['description'] ?? 'Aucune description disponible.') ?></p>
        </div>
        
        <!-- Adresse -->
        <div class="info-item bg-soft rounded-3 p-3 mb-3">
          <i class="bi bi-geo-alt-fill me-2 fs-5 text-primary"></i>
          <span class="fw-medium">Adresse :</span> <?= htmlspecialchars($lieu['adresse'] ?? 'Non précisée') ?>
        </div>

        <!-- Horaires avec accordéon -->
        <div class="accordion custom-accordion mb-4" id="horairesAccordion">
          <div class="accordion-item border-0">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed bg-soft text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHoraires" aria-expanded="false" aria-controls="collapseHoraires">
                <i class="bi bi-clock-history me-2"></i> Horaires d'ouverture
              </button>
            </h2>
            <div id="collapseHoraires" class="accordion-collapse collapse" data-bs-parent="#horairesAccordion">
              <div class="accordion-body bg-white rounded-bottom-4">
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
        <?php if ($isCafeOrRestaurant) : ?>
          <?php if (!empty($menuByCategory)) : ?>
            <div class="accordion custom-accordion mb-4" id="menuAccordion">
              <div class="accordion-item border-0">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed bg-soft text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenu" aria-expanded="false" aria-controls="collapseMenu">
                    <i class="bi bi-menu-up me-2"></i> Menu
                  </button>
                </h2>
                <div id="collapseMenu" class="accordion-collapse collapse" data-bs-parent="#menuAccordion">
                  <div class="accordion-body bg-white rounded-bottom-4">
                    <?php foreach ($menuByCategory as $category => $items) : ?>
                      <h5 class="mt-0 mb-3"><?= htmlspecialchars($category) ?></h5>
                      <div class="table-responsive">
                        <table class="table table-borderless mb-4">
                          <?php foreach ($items as $item) : ?>
                          <tr>
                            <td class="py-2">
                              <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                              <?php if (!empty($item['description'])) : ?>
                                <br><small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
                              <?php endif; ?>
                            </td>
                            <td class="py-2 text-end">
                              <?php if (!empty($item['price'])) : ?>
                                <?= htmlspecialchars(number_format($item['price'], 2)) ?> €
                              <?php else : ?>
                                -
                              <?php endif; ?>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                        </table>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php else : ?>
            <div class="info-item bg-soft rounded-3 p-3 mb-3">
              <i class="bi bi-menu-up me-2 fs-5 text-info"></i>
              <span class="fw-medium">Menu :</span> Aucun menu disponible pour le moment.
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <!-- Carte interactive -->
        <div class="map-card rounded-4 overflow-hidden shadow-sm">
          <div id="map" class="rounded-4 animate__animated animate__fadeIn" style="height: 400px; width: 100%;"></div>
          <div class="map-controls">
            <button class="btn btn-recenter btn-sm" onclick="recenterMap()">
              <i class="bi bi-crosshair"></i> Recentrer
            </button>
            <button class="btn btn-directions btn-sm" onclick="openDirections()">
              <i class="bi bi-signpost"></i> Itinéraire
            </button>
          </div>
        </div>

        <!-- Informations supplémentaires -->
        <?php if (!empty($lieu['infos_supplementaires'])) : ?>
        <div class="mt-4">
          <div class="info-item bg-soft rounded-3 p-3">
            <i class="bi bi-info-circle-fill me-2 fs-5 text-info"></i>
            <span class="fw-medium">Informations :</span> <?= htmlspecialchars($lieu['infos_supplementaires']) ?>
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
<!-- Inclure L.Control.Locate pour la géolocalisation -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.76.1/dist/L.Control.Locate.min.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.76.1/dist/L.Control.Locate.min.js" charset="utf-8"></script>
<!-- Inclure PhotoSwipe pour la lightbox -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.2/photoswipe.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.2/photoswipe.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.2/photoswipe-lightbox.min.js"></script>

<style>
:root {
  --primary-color: #4361ee;
  --secondary-color: #3f37c9;
  --accent-color: #4cc9f0;
  --dark-color: #2b2d42;
  --light-color: #f8f9fa;
  --success-color: #4ad66d;
  --warning-color: #f8961e;
  --danger-color: #ef233c;
  --border-radius: 12px;
  --box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

body {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  color: #212529;
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

.btn-custom {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 50px;
  transition: var(--transition);
  box-shadow: var(--box-shadow);
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-custom:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 20px rgba(67, 97, 238, 0.3);
  color: white;
}

/* Image principale */
.image-wrapper {
  position: relative;
  overflow: hidden;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  transition: transform 0.5s ease;
  min-height: 400px;
}

.image-wrapper img {
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
  background: linear-gradient(to top, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0) 50%);
  border-radius: var(--border-radius);
  pointer-events: none;
}

.image-wrapper:hover img {
  transform: scale(1.05);
}

/* Galerie photo */
.gallery-wrapper {
  margin-top: 1.5rem;
}

.carousel-item img {
  height: 200px;
  object-fit: cover;
  border-radius: var(--border-radius);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: var(--transition);
}

.carousel-item img:hover {
  transform: scale(1.03);
}

.carousel-control-prev, .carousel-control-next {
  width: 5%;
  background: rgba(0, 0, 0, 0.3);
  border-radius: var(--border-radius);
}

.carousel-control-prev-icon, .carousel-control-next-icon {
  background-color: var(--primary-color);
  border-radius: 50%;
}

/* Carte des détails */
.details-card {
  border-radius: var(--border-radius);
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: var(--box-shadow);
  transition: var(--transition);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.details-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

h1 {
  font-weight: 700;
  color: var(--dark-color);
  margin-bottom: 1rem;
  font-size: 2.5rem;
  line-height: 1.2;
}

.badge-custom {
  background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
  font-weight: 600;
  padding: 0.5rem 1rem;
  box-shadow: 0 4px 15px rgba(76, 201, 240, 0.3);
  transition: var(--transition);
}

.badge-custom:hover {
  transform: scale(1.05);
}

.description-wrapper {
  border-left: 3px solid var(--accent-color);
  padding-left: 1rem;
  margin: 1.5rem 0;
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
  font-size: 1.05rem;
  transition: var(--transition);
}

.info-item:hover {
  transform: translateX(5px);
}

.bg-soft {
  background-color: rgba(67, 97, 238, 0.08);
}

/* Accordéon personnalisé */
.custom-accordion .accordion-button {
  border-radius: var(--border-radius) !important;
  padding: 1rem 1.5rem;
}

.custom-accordion .accordion-button:not(.collapsed) {
  background-color: rgba(67, 97, 238, 0.1);
  color: var(--primary-color);
  box-shadow: none;
}

.custom-accordion .accordion-body {
  padding: 1rem 1.5rem;
}

/* Menu styling */
.menu-item:hover {
  background-color: rgba(67, 97, 238, 0.05);
  border-radius: 8px;
}

.menu-category {
  border-bottom: 2px solid var(--accent-color);
  padding-bottom: 0.5rem;
  margin-bottom: 1.5rem;
}

/* Carte */
.map-card {
  position: relative;
  margin-top: 1.5rem;
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.map-controls {
  position: absolute;
  bottom: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
  z-index: 1000;
}

.btn-recenter, .btn-directions {
  background: rgba(255, 255, 255, 0.9);
  color: var(--dark-color);
  border: none;
  border-radius: 50px;
  padding: 0.5rem 1rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.btn-recenter:hover, .btn-directions:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.btn-directions {
  background: linear-gradient(135deg, var(--success-color), #3a9b5a);
  color: white;
}

/* Alerte personnalisée */
.alert-custom {
  background: linear-gradient(135deg, var(--danger-color), #d00000);
  color: white;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  text-align: center;
  max-width: 600px;
  margin: 2rem auto;
  box-shadow: var(--box-shadow);
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

/* Responsive */
@media (max-width: 992px) {
  .container-fluid {
    padding: 1.5rem;
  }
  
  h1 {
    font-size: 2rem;
  }
  
  .image-wrapper {
    min-height: 350px;
  }
  
  .carousel-item img {
    height: 150px;
  }
}

@media (max-width: 768px) {
  .container-fluid {
    padding: 1rem;
  }
  
  h1 {
    font-size: 1.75rem;
  }
  
  .details-card {
    padding: 1.5rem;
  }
  
  .map-controls {
    bottom: 15px;
    right: 15px;
  }
  
  .btn-recenter, .btn-directions {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
  }
  
  .carousel-item img {
    height: 120px;
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
  
  .badge-custom {
    font-size: 0.8rem;
    padding: 0.3rem 0.8rem;
  }
  
  .description-wrapper {
    margin: 1rem 0;
  }
  
  .carousel-item img {
    height: 100px;
  }
}
</style>

<script>
// Initialize PhotoSwipe Lightbox
document.addEventListener('DOMContentLoaded', () => {
  const lightbox = new PhotoSwipeLightbox({
    gallery: '.gallery-wrapper',
    children: '.gallery-lightbox',
    pswpModule: PhotoSwipe
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

    L.control.locate({
      position: 'topright',
      drawCircle: true,
      follow: true,
      setView: 'once',
      keepCurrentZoomLevel: true,
      markerStyle: {
        weight: 1,
        opacity: 0.8,
        fillOpacity: 0.8
      },
      circleStyle: {
        weight: 1,
        clickable: false
      },
      icon: 'bi bi-geo',
      metric: true,
      strings: {
        title: "Ma position",
        popup: "Vous êtes à {distance} {unit} de ce point",
        outsideMapBoundsMsg: "Vous semblez être hors de la zone de la carte"
      },
      locateOptions: {
        maxZoom: 15,
        watch: true,
        enableHighAccuracy: true,
        maximumAge: 10000,
        timeout: 10000
      }
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
      color: '#4361ee',
      fillColor: '#4cc9f0',
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
  
  if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
    window.open(`maps://maps.google.com/maps?daddr=${latitude},${longitude}&ll=`);
  } else {
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}`);
  }
}

// Initialiser la carte après le chargement du DOM
document.addEventListener('DOMContentLoaded', initMap);
</script>

<?php require 'includes/footer.php'; ?>