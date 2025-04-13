<?php
require 'includes/db.php';
require 'includes/header.php';
?>

<div class="container-fluid py-5">
  <!-- Filtres améliorés avec effets glassmorphism -->
  <div class="filters glass-effect p-4 mb-5 rounded-4 animate__animated animate__fadeInDown">
    <div class="row g-3 align-items-center">
      <!-- Filtre par type avec icône animée -->
      <div class="col-md-6 col-lg-4">
        <form method="GET" id="type-form">
          <div class="input-group">
            <span class="input-group-text bg-transparent border-0">
              <i class="bi bi-tags-fill filter-icon"></i>
            </span>
            <select name="type" class="form-select shadow-sm" onchange="this.form.submit()">
              <option value="">🌍 Tous les lieux</option>
              <?php foreach ($pdo->query("SELECT * FROM type_lieu") as $type): ?>
                <option value="<?= $type['id'] ?>" <?= ($_GET['type'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($type['nom']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>

      <!-- Barre de recherche avec suggestions -->
      <div class="col-md-6 col-lg-4">
        <form method="GET" id="search-form" class="search-wrapper">
          <div class="input-group">
            <span class="input-group-text bg-transparent border-0">
              <i class="bi bi-search search-icon"></i>
            </span>
            <input type="text" name="search" class="form-control shadow-sm" 
                   placeholder="Rechercher un lieu..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                   list="suggestions"
                   autocomplete="off">
            <datalist id="suggestions">
              <?php
              $suggestions = $pdo->query("SELECT DISTINCT nom FROM lieu LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
              foreach ($suggestions as $suggestion): ?>
                <option value="<?= htmlspecialchars($suggestion) ?>">
              <?php endforeach; ?>
            </datalist>
            <button type="submit" class="btn btn-search">
              <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </form>
      </div>

      <!-- Boutons avec effets de vague -->
      <div class="col-lg-4 text-lg-end">
        <a href="index.php" class="btn btn-reset shadow-sm ripple-effect me-2">
          <i class="bi bi-arrow-counterclockwise me-2"></i>Réinitialiser
        </a>
        <a href="add_lieu.php" class="btn btn-add shadow-sm ripple-effect">
          <i class="bi bi-plus-circle-fill me-2"></i>Ajouter un lieu
        </a>
      </div>
    </div>
  </div>

  <!-- Grille de lieux avec masonry effect -->
  <div class="row g-4" id="lieux-grid" data-masonry='{"percentPosition": true}'>
    <?php
    $sql = "SELECT lieu.*, type_lieu.nom AS type_nom, lieu.horaires 
            FROM lieu 
            LEFT JOIN type_lieu ON lieu.type_id = type_lieu.id 
            WHERE 1=1";
    $params = [];

    if (!empty($_GET['type']) && is_numeric($_GET['type'])) {
      $sql .= " AND lieu.type_id = ?";
      $params[] = $_GET['type'];
    }

    if (!empty($_GET['search'])) {
      $sql .= " AND lieu.nom LIKE ?";
      $params[] = "%" . $_GET['search'] . "%";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lieux = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($lieux): 
      foreach ($lieux as $index => $lieu):
        $transportStmt = $pdo->prepare("
          SELECT t.nom, t.icone, tl.details 
          FROM transport t 
          INNER JOIN transport_lieu tl ON t.id = tl.transport_id 
          WHERE tl.lieu_id = ?
        ");
        $transportStmt->execute([$lieu['id']]);
        $transports = $transportStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
        <div class="col-md-6 col-lg-4 col-xl-3 lieu-item" data-index="<?= $index ?>">
          <div class="card lieu-card shadow-xl h-100">
            <div class="card-inner">
              <!-- Image avec effet de zoom et overlay -->
              <div class="image-container">
                <img src="<?= htmlspecialchars($lieu['image_url'] ?? 'https://via.placeholder.com/600x400') ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($lieu['nom']) ?>" 
                     loading="lazy">
                <div class="image-overlay"></div>
                <?php if (!empty($lieu['type_nom'])): ?>
                  <span class="badge badge-type pulse-animation">
                    <?= htmlspecialchars($lieu['type_nom']) ?>
                  </span>
                <?php endif; ?>
                <!-- Bouton favori -->
                <button class="btn-favorite">
                  <i class="bi bi-heart"></i>
                </button>
              </div>
              
              <div class="card-body">
                <!-- Titre avec effet de gradient -->
                <h5 class="card-title">
                  <a href="lieu.php?id=<?= $lieu['id'] ?>" class="stretched-link title-link">
                    <?= htmlspecialchars($lieu['nom']) ?>
                  </a>
                </h5>
                
                <!-- Description avec lecture progressive -->
                <div class="card-text description-text" data-full-text="<?= htmlspecialchars($lieu['description'] ?? '') ?>">
                  <?= htmlspecialchars(substr($lieu['description'] ?? '', 0, 100)) . (strlen($lieu['description'] ?? '') > 100 ? '...' : '') ?>
                </div>
                <?php if (strlen($lieu['description'] ?? '') > 100): ?>
                  <button class="btn-read-more">Lire plus</button>
                <?php endif; ?>
                
                <!-- Métadonnées -->
                <div class="metadata">
                  <!-- Site web avec effet de vague -->
                  <div class="meta-item website-link">
                    <?php if (!empty($lieu['site_web'])): ?>
                      <a href="<?= htmlspecialchars($lieu['site_web']) ?>" 
                         target="_blank" 
                         rel="noopener noreferrer" 
                         class="website-url wave-effect">
                        <i class="bi bi-globe"></i>
                        <span><?= parse_url($lieu['site_web'], PHP_URL_HOST) ?></span>
                      </a>
                    <?php else: ?>
                      <span class="text-muted"><i class="bi bi-globe"></i> Site non disponible</span>
                    <?php endif; ?>
                  </div>
                  
                  <!-- Transports avec animations en cascade -->
                  <div class="meta-item transport-list">
                    <?php if ($transports): ?>
                      <div class="transport-label">
                        <i class="bi bi-signpost"></i> Accès:
                      </div>
                      <div class="transport-items">
                        <?php foreach ($transports as $transport): ?>
                          <div class="transport-item" style="--delay: <?= array_search($transport, $transports) * 0.1 ?>s">
                            <i class="bi <?= htmlspecialchars($transport['icone']) ?>"></i>
                            <span><?= htmlspecialchars($transport['nom']) ?></span>
                            <?php if (!empty($transport['details'])): ?>
                              <span class="text-muted">(<?= htmlspecialchars($transport['details']) ?>)</span>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <span class="text-muted"><i class="bi bi-signpost-split"></i> Aucun transport</span>
                    <?php endif; ?>
                  </div>
                  
                  <!-- Actions -->
                  <div class="card-actions">
                    <?php if (!empty($lieu['horaires'])): ?>
                      <button class="btn btn-horaires" 
                              data-bs-toggle="modal" 
                              data-bs-target="#horairesModal" 
                              data-horaires="<?= htmlspecialchars($lieu['horaires']) ?>">
                        <i class="bi bi-clock"></i> Horaires
                      </button>
                    <?php endif; ?>
                    <a href="edit_lieu.php?id=<?= $lieu['id'] ?>" class="btn btn-edit">
                      <i class="bi bi-pencil"></i> Modifier
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; 
    else: ?>
      <div class="col-12 text-center py-5">
        <div class="empty-state animate__animated animate__fadeIn">
          <div class="empty-icon">
            <i class="bi bi-map"></i>
          </div>
          <h3>Aucun lieu trouvé</h3>
          <p class="text-muted">Essayez une autre recherche ou filtre</p>
          <a href="index.php" class="btn btn-primary mt-3">
            <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal pour horaires avec effet glass -->
<div class="modal fade" id="horairesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass-effect">
      <div class="modal-header">
        <h5 class="modal-title">Horaires d'ouverture</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modal-horaires-content" class="horaires-content"></div>
      </div>
    </div>
  </div>
</div>

<!-- Chargement des bibliothèques -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js">

<style>
:root {
  --primary: #4361ee;
  --primary-light: #4cc9f0;
  --secondary: #3a0ca3;
  --accent: #f72585;
  --success: #4cc9f0;
  --warning: #f8961e;
  --danger: #ef233c;
  --dark: #2b2d42;
  --light: #f8f9fa;
  --gray: #adb5bd;
  --glass-bg: rgba(255, 255, 255, 0.15);
  --glass-border: rgba(255, 255, 255, 0.2);
  --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* Base styles */
body {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--dark);
  line-height: 1.6;
  overflow-x: hidden;
}

.container-fluid {
  max-width: 1800px;
  padding: 2rem;
}

/* Glass effect */
.glass-effect {
  background: var(--glass-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--glass-border);
  box-shadow: var(--shadow);
}

/* Filtres améliorés */
.filters {
  position: relative;
  overflow: hidden;
  transition: var(--transition);
}

.filters:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
}

.filter-icon, .search-icon {
  color: var(--primary);
  font-size: 1.2rem;
  transition: var(--transition);
}

.input-group-text {
  background: transparent;
  border-right: none;
}

.form-select, .form-control {
  border-left: none;
  border-radius: 12px !important;
  background: rgba(255, 255, 255, 0.8);
  transition: var(--transition);
  padding: 0.75rem 1rem;
}

.form-select:focus, .form-control:focus {
  background: white;
  box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.2);
  border-color: var(--primary);
}

.btn-search {
  background: var(--primary);
  color: white;
  border-radius: 0 12px 12px 0 !important;
  border: none;
  transition: var(--transition);
}

.btn-search:hover {
  background: var(--secondary);
}

/* Boutons principaux */
.btn-reset, .btn-add, .btn-edit, .btn-horaires {
  border: none;
  border-radius: 12px;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.btn-reset {
  background: linear-gradient(45deg, var(--danger), var(--accent));
  color: white;
}

.btn-add {
  background: linear-gradient(45deg, var(--primary), var(--primary-light));
  color: white;
}

.btn-edit {
  background: linear-gradient(45deg, var(--gray), #6c757d);
  color: white;
}

.btn-horaires {
  background: linear-gradient(45deg, #4cc9f0, #4895ef);
  color: white;
}

/* Effet de vague */
.wave-effect {
  position: relative;
  overflow: hidden;
}

.wave-effect::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 5px;
  height: 5px;
  background: rgba(255, 255, 255, 0.4);
  opacity: 0;
  border-radius: 100%;
  transform: scale(1, 1) translate(-50%, -50%);
  transform-origin: 50% 50%;
}

.wave-effect:focus:not(:active)::after {
  animation: wave 0.6s ease-out;
}

@keyframes wave {
  0% {
    transform: scale(0, 0);
    opacity: 0.5;
  }
  100% {
    transform: scale(20, 20);
    opacity: 0;
  }
}

/* Cartes de lieux */
.lieu-card {
  border: none;
  border-radius: 16px;
  overflow: hidden;
  background: white;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  transition: var(--transition);
  height: 100%;
}

.card-inner {
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.lieu-card:hover {
  transform: translateY(-10px) scale(1.02);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
}

.image-container {
  position: relative;
  height: 220px;
  overflow: hidden;
}

.card-img-top {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.lieu-card:hover .card-img-top {
  transform: scale(1.1);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0) 50%);
}

.badge-type {
  position: absolute;
  top: 20px;
  left: 20px;
  background: linear-gradient(45deg, var(--primary), var(--primary-light));
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.8rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  z-index: 2;
}

.pulse-animation {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.btn-favorite {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--danger);
  font-size: 1.2rem;
  z-index: 2;
  transition: var(--transition);
  opacity: 0;
  transform: translateY(10px);
}

.lieu-card:hover .btn-favorite {
  opacity: 1;
  transform: translateY(0);
}

.btn-favorite:hover {
  background: var(--danger);
  color: white;
}

.card-body {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card-title {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: var(--dark);
}

.title-link {
  color: inherit;
  text-decoration: none;
  background: linear-gradient(to right, var(--primary), var(--primary-light));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  transition: var(--transition);
}

.title-link:hover {
  background: linear-gradient(to right, var(--accent), var(--danger));
  -webkit-background-clip: text;
}

.description-text {
  color: var(--dark);
  opacity: 0.8;
  margin-bottom: 1rem;
  line-height: 1.6;
}

.btn-read-more {
  background: none;
  border: none;
  color: var(--primary);
  padding: 0;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  margin-bottom: 1rem;
  transition: var(--transition);
}

.btn-read-more:hover {
  color: var(--secondary);
}

.metadata {
  margin-top: auto;
}

.meta-item {
  margin-bottom: 1rem;
}

.website-url {
  color: var(--dark);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: var(--transition);
}

.website-url:hover {
  color: var(--primary);
}

.website-url i {
  font-size: 1.1rem;
}

.transport-label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.transport-items {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.transport-item {
  background: rgba(76, 201, 240, 0.1);
  padding: 0.5rem 0.8rem;
  border-radius: 50px;
  font-size: 0.8rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  animation: fadeInUp 0.5s ease-out;
  animation-fill-mode: both;
  animation-delay: var(--delay);
}

.transport-item i {
  color: var(--primary);
}

.card-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
}

.card-actions .btn {
  flex: 1;
  padding: 0.5rem;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

/* État vide */
.empty-state {
  text-align: center;
  padding: 3rem;
}

.empty-icon {
  font-size: 4rem;
  color: var(--primary);
  margin-bottom: 1.5rem;
  display: inline-block;
}

.empty-state h3 {
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: var(--dark);
}

/* Modal */
.modal-content {
  border-radius: 16px;
  overflow: hidden;
}

.modal-header {
  border-bottom: none;
  padding: 1.5rem;
}

.modal-title {
  font-weight: 700;
}

.modal-body {
  padding: 0 1.5rem 1.5rem;
}

.horaires-content {
  white-space: pre-line;
  line-height: 2;
}

/* Responsive */
@media (max-width: 992px) {
  .container-fluid {
    padding: 1.5rem;
  }
  
  .lieu-item {
    margin-bottom: 1.5rem;
  }
  
  .card-title {
    font-size: 1.25rem;
  }
}

@media (max-width: 768px) {
  .filters .row > div {
    margin-bottom: 1rem;
  }
  
  .btn-reset, .btn-add {
    width: 100%;
    margin-bottom: 0.75rem;
  }
}

@media (max-width: 576px) {
  .container-fluid {
    padding: 1rem;
  }
  
  .image-container {
    height: 180px;
  }
  
  .card-body {
    padding: 1.25rem;
  }
  
  .card-title {
    font-size: 1.2rem;
  }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"></script>
<script>
// Initialiser Masonry
document.addEventListener('DOMContentLoaded', function() {
  const grid = document.querySelector('#lieux-grid');
  if (grid) {
    new Masonry(grid, {
      itemSelector: '.lieu-item',
      columnWidth: '.lieu-item',
      percentPosition: true,
      transitionDuration: '0.4s'
    });
  }

  // Gestion des horaires dans le modal
  document.querySelectorAll('.btn-horaires').forEach(btn => {
    btn.addEventListener('click', function() {
      const horaires = this.getAttribute('data-horaires');
      document.getElementById('modal-horaires-content').innerHTML = 
        horaires.replace(/\n/g, '<br>');
    });
  });

  // Lire plus/moins pour la description
  document.querySelectorAll('.btn-read-more').forEach(btn => {
    btn.addEventListener('click', function() {
      const descContainer = this.previousElementSibling;
      const fullText = descContainer.getAttribute('data-full-text');
      
      if (this.textContent === 'Lire plus') {
        descContainer.textContent = fullText;
        this.textContent = 'Lire moins';
      } else {
        descContainer.textContent = fullText.substring(0, 100) + '...';
        this.textContent = 'Lire plus';
      }
    });
  });

  // Animation au survol des cartes
  document.querySelectorAll('.lieu-card').forEach(card => {
    card.addEventListener('mousemove', function(e) {
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      this.style.setProperty('--mouse-x', `${x}px`);
      this.style.setProperty('--mouse-y', `${y}px`);
    });
  });

  // Recherche avec debounce
  const searchForm = document.getElementById('search-form');
  if (searchForm) {
    const searchInput = searchForm.querySelector('input[name="search"]');
    let timeout;
    
    searchInput.addEventListener('input', function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        searchForm.submit();
      }, 500);
    });
  }
});

// Effet de ripple pour les boutons
document.querySelectorAll('.ripple-effect').forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault();
    
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    const ripple = document.createElement('span');
    ripple.classList.add('ripple');
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;
    
    this.appendChild(ripple);
    
    setTimeout(() => {
      ripple.remove();
      if (this.tagName === 'A') {
        window.location.href = this.href;
      }
    }, 600);
  });
});
</script>

<?php require 'includes/footer.php'; ?> ameliore beaucoup le style de la page 