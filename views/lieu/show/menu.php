<?php
$isCafeOrRestaurantOrBar = in_array(strtolower($lieu['type_nom'] ?? ''), ['café', 'restaurant', 'bar']);
$menuByCategory = $lieu['menu'] ?? [];
?>

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
                                            <div class="menu-item d-flex justify-content-between align-items-start border-bottom py-2">
                                                <div class="item-details me-3">
                                                    <h6 class="item-name mb-1"><?= htmlspecialchars($item['item_name']) ?></h6>
                                                    <?php if (!empty($item['description'])) : ?>
                                                        <p class="item-description text-muted mb-0"><?= htmlspecialchars($item['description']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="item-price text-nowrap">
                                                    <?php if (!empty($item['price'])) : ?>
                                                        <span class="price fw-semibold"><?= htmlspecialchars(number_format($item['price'], 2)) ?> €</span>
                                                    <?php else : ?>
                                                        <span class="price text-muted">-</span>
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
