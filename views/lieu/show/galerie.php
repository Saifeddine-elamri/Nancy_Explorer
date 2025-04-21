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
