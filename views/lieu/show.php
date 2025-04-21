<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid py-5">
  <?php require 'show/validation_id.php'; ?>

  <div class="floating-back">
    <a href="/" class="btn btn-gradient rounded-pill btn-floating">
      <i class="bi bi-chevron-left me-1"></i> Retour
    </a>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <?php 
        require 'show/image_principale.php'; 
        require 'show/galerie.php'; 
      ?>
    </div>
    <div class="col-lg-6">
      <div class="details-card glass-card p-4 animate__animated animate__fadeInRight">
        <?php 
          require 'show/details.php';
          require 'show/horaires.php';
          require 'show/menu.php';
          require 'show/map.php';
          require 'show/infos-supplementaires.php';
        ?>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
