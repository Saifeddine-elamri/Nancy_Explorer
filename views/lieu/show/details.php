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

<div class="description-wrapper mb-4">
    <p class="lead text-muted mb-0"><?= htmlspecialchars($lieu['description'] ?? 'Aucune description disponible.') ?></p>
</div>

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
