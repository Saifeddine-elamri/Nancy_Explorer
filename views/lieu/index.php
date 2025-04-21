<?php
// views/lieu/index.php
require BASE_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid py-5">
    <?php include BASE_PATH . '/views/lieu/filters.php'; ?>
    
    <div class="row g-4" id="lieux-grid" data-masonry='{"percentPosition": true}'>
        <?php
        if (!empty($lieux)): 
            foreach ($lieux as $index => $lieu):
                include BASE_PATH . '/views/lieu/lieu_card.php';
            endforeach; 
        else: 
            include BASE_PATH . '/views/lieu/empty_state.php';
        endif; 
        ?>
    </div>
</div>

<?php 
include BASE_PATH . '/views/lieu/horaires_modal.php';
require BASE_PATH . '/views/layouts/footer.php'; 
?>