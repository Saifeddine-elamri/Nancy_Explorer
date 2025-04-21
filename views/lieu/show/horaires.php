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