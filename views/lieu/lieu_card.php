<?php


// $transports = isset($lieu['id']) ? getTransportsForLieu($pdo, $lieu['id']) : [];
$lieuId = (int)($lieu['id'] ?? 0);
$lieuNom = $lieu['nom'] ?? 'Lieu sans nom';
$lieuDescription = $lieu['description'] ?? '';
$lieuImage = $lieu['image_url'] ?? 'assets/images/placeholder-lieu.jpg';
$lieuTypeNom = $lieu['type_nom'] ?? '';
$lieuSiteWeb = $lieu['site_web'] ?? '';
$lieuHoraires = $lieu['horaires'] ?? '';

// Préparer l'URL du site web et son nom à afficher
$siteWebHost = !empty($lieuSiteWeb) ? parse_url($lieuSiteWeb, PHP_URL_HOST) : '';
$siteWebLabel = $siteWebHost ?: 'Site web';

// Déterminer si la description doit être tronquée
$isLongDescription = mb_strlen($lieuDescription, 'UTF-8') > 100;
$truncatedDescription = $isLongDescription ? 
    mb_strimwidth($lieuDescription, 0, 100, '...', 'UTF-8') : 
    $lieuDescription;
?>

<div class="col-md-6 col-lg-4 col-xl-3 lieu-item" data-lieu-id="<?= $lieuId ?>" aria-labelledby="lieu-title-<?= $lieuId ?>">
  <article class="card lieu-card shadow-xl h-100">
    <div class="card-inner">
      <!-- Image et badges -->
      <div class="image-container position-relative">
        <img src="<?= htmlspecialchars($lieuImage, ENT_QUOTES, 'UTF-8') ?>" 
             class="card-img-top" 
             alt="Photo de <?= htmlspecialchars($lieuNom, ENT_QUOTES, 'UTF-8') ?>" 
             loading="lazy"
             width="600" 
             height="400">
        <div class="image-overlay"></div>
        
        <?php if (!empty($lieuTypeNom)): ?>
          <span class="badge badge-type pulse-animation" aria-label="Type: <?= htmlspecialchars($lieuTypeNom, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($lieuTypeNom, ENT_QUOTES, 'UTF-8') ?>
          </span>
        <?php endif; ?>
        
        <button class="btn-favorite" 
                aria-label="Ajouter <?= htmlspecialchars($lieuNom, ENT_QUOTES, 'UTF-8') ?> aux favoris" 
                type="button"
                data-lieu-id="<?= $lieuId ?>">
          <i class="bi bi-heart" aria-hidden="true"></i>
        </button>
      </div>
      
      <!-- Corps de la carte -->
      <div class="card-body">
        <!-- Titre avec lien -->
        <h5 class="card-title" id="lieu-title-<?= $lieuId ?>">
          <a href="<?= htmlspecialchars('/' . str_replace(' ', '-', $lieuNom) . '/' . $lieuId, ENT_QUOTES, 'UTF-8') ?>" 
            class="stretched-link title-link text-decoration-none">
            <?= htmlspecialchars($lieuNom, ENT_QUOTES, 'UTF-8') ?>
          </a>
        </h5>


        </h5>
        
        <!-- Description avec option "lire plus" -->
        <div class="card-text description-text" id="description-<?= $lieuId ?>">
          <p><?= htmlspecialchars($truncatedDescription, ENT_QUOTES, 'UTF-8') ?></p>
          
          <?php if ($isLongDescription): ?>
            <div class="description-full visually-hidden">
              <?= htmlspecialchars($lieuDescription, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <button class="btn-read-more" 
                    type="button" 
                    aria-expanded="false" 
                    aria-controls="description-<?= $lieuId ?>"
                    data-action="expand-description">
              <span class="read-more-text">Lire plus</span>
              <span class="read-less-text d-none">Réduire</span>
            </button>
          <?php endif; ?>
        </div>
        
        <!-- Métadonnées -->
        <div class="metadata mt-3">
          <!-- Site web -->
          <div class="meta-item website-link mb-2">
            <?php if (!empty($lieuSiteWeb)): ?>
              <a href="<?= htmlspecialchars($lieuSiteWeb, ENT_QUOTES, 'UTF-8') ?>" 
                 target="_blank" 
                 rel="noopener noreferrer" 
                 class="website-url wave-effect"
                 aria-label="Visiter le site web de <?= htmlspecialchars($lieuNom, ENT_QUOTES, 'UTF-8') ?>">
                <i class="bi bi-globe" aria-hidden="true"></i>
                <span><?= htmlspecialchars($siteWebLabel, ENT_QUOTES, 'UTF-8') ?></span>
              </a>
            <?php else: ?>
              <span class="text-muted" aria-label="Site web indisponible">
                <i class="bi bi-globe" aria-hidden="true"></i> Site non disponible
              </span>
            <?php endif; ?>
          </div>
          
          <!-- Transports -->
          <div class="meta-item transport-list mb-2">
            <div class="transport-label" aria-label="Moyens de transport">
              <i class="bi bi-signpost" aria-hidden="true"></i> Accès :
            </div>
            
            <?php if (!empty($transports)): ?>
              <ul class="transport-items list-unstyled ps-3 mt-1">
                <?php foreach ($transports as $index => $transport): 
                  $transportNom = $transport['nom'] ?? 'Inconnu';
                  $transportIcone = $transport['icone'] ?? 'bi-circle';
                  $transportDetails = $transport['details'] ?? '';
                ?>
                  <li class="transport-item" style="--delay: <?= $index * 0.1 ?>s">
                    <i class="bi <?= htmlspecialchars($transportIcone, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($transportNom, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (!empty($transportDetails)): ?>
                      <span class="text-muted">
                        (<?= htmlspecialchars($transportDetails, ENT_QUOTES, 'UTF-8') ?>)
                      </span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="text-muted mb-0 ps-3" aria-label="Aucun transport disponible">
                Aucune information de transport disponible
              </p>
            <?php endif; ?>
          </div>
          
          <!-- Boutons d'action -->
          <div class="card-actions d-flex flex-wrap gap-2 mt-3">
            <?php if (!empty($lieuHoraires)): ?>
              <button class="btn btn-sm btn-horaires" 
                      type="button"
                      data-bs-toggle="modal" 
                      data-bs-target="#horairesModal" 
                      data-horaires="<?= htmlspecialchars($lieuHoraires, ENT_QUOTES, 'UTF-8') ?>"
                      data-lieu-nom="<?= htmlspecialchars($lieuNom, ENT_QUOTES, 'UTF-8') ?>"
                      aria-label="Voir les horaires de <?= htmlspecialchars($lieuNom, ENT_QUOTES, 'UTF-8') ?>">
                <i class="bi bi-clock" aria-hidden="true"></i> Horaires
              </button>
            <?php endif; ?>
            
            <a href="edit_lieu.php?id=<?= $lieuId ?>" 
               class="btn btn-sm btn-edit"
               aria-label="Modifier les informations de <?= htmlspecialchars($lieuNom, ENT_QUOTES, 'UTF-8') ?>">
              <i class="bi bi-pencil" aria-hidden="true"></i> Modifier
            </a>
          </div>
        </div>
      </div>
    </div>
  </article>
</div>