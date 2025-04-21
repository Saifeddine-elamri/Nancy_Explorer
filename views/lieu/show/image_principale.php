<div class="hero-image-wrapper mb-4 animate__animated animate__fadeInLeft">
    <img src="/<?= htmlspecialchars($lieu['image_url'] ?? 'https://via.placeholder.com/800x600?text='.urlencode($lieu['nom'])) ?>" 
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
