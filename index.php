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