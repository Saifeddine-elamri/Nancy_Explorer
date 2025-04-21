<?php if (!empty($lieu['infos_supplementaires'])) : ?>
<div class="additional-info mt-4">
    <h5 class="section-title mb-3"><i class="bi bi-info-circle me-2"></i> Informations supplémentaires</h5>
    <div class="info-content bg-light rounded-3 p-3">
        <?= htmlspecialchars($lieu['infos_supplementaires']) ?>
    </div>
</div>
<?php endif; ?>
