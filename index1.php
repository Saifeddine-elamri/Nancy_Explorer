<?php
require 'includes/db.php';
require 'includes/header.php';
require 'includes/functions.php';

?>

<div class="container-fluid py-5">
  <?php include 'includes/filters.php'; ?>
  
  <div class="row g-4" id="lieux-grid" data-masonry='{"percentPosition": true}'>
    <?php
    $lieux = getLieux($pdo, $_GET);
    
    if ($lieux): 
      foreach ($lieux as $index => $lieu):
        include 'includes/lieu_card.php';
      endforeach; 
    else: 
      include 'includes/empty_state.php';
    endif; 
    ?>
  </div>
</div>

<?php 
include 'includes/horaires_modal.php';
include 'includes/footer.php'; 
?>