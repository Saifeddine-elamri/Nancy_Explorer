<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🌆 Nancy Explorer</title>
  <link href="/assets/show-style.css" rel="stylesheet">
  <link href="/assets/show-horaires-style.css" rel="stylesheet">
  <link href="/assets/header-style.css" rel="stylesheet">
  <link href="/assets/footer-style.css" rel="stylesheet">
  <link href="/assets/style.css" rel="stylesheet">
  <!-- Bootstrap 5 (une seule version pour éviter les conflits) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <!-- AOS (conservé pour compatibilité avec tes pages) -->
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
  <script defer src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <!-- Style personnalisé -->
</head>
<body>
  <nav class="navbar navbar-expand-lg glass-effect shadow-lg animate__animated animate__fadeInDown" id="main-nav">
    <div class="container">
      <!-- Logo -->
      <a class="navbar-brand neon-glow" href="/">
        <span class="brand-icon">🌆</span> Nancy Explorer
      </a>

      <!-- Bouton hamburger -->
      <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" 
              data-bs-target="#navbarContent" aria-controls="navbarContent" 
              aria-expanded="false" aria-label="Ouvrir le menu">
        <span class="toggler-icon"></span>
      </button>

      <!-- Contenu de la navbar -->
      <div class="collapse navbar-collapse" id="navbarContent">
        <!-- Liens à gauche -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link nav-link-animated" href="/">Accueil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-animated" href="/ajouter">Ajouter un lieu</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link nav-link-animated dropdown-toggle" href="#" id="moreDropdown" 
               role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Plus
            </a>
            <ul class="dropdown-menu glass-effect rounded-3" aria-labelledby="moreDropdown">
              <li><a class="dropdown-item" href="#">À propos</a></li>
              <li><a class="dropdown-item" href="#">Contact</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">FAQ</a></li>
            </ul>
          </li>
        </ul>

        <!-- Recherche -->
        <form class="d-flex align-items-center me-3" method="GET" action="/index.php">
          <div class="input-group search-group">
            <span class="input-group-text bg-transparent border-0">
              <i class="bi bi-search text-neon"></i>
            </span>
            <input type="text" name="search" class="form-control search-input" 
                   placeholder="Rechercher un lieu..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          </div>
        </form>

        <!-- Bouton mode sombre/clair -->
        <button class="btn btn-mode shadow-sm ripple-effect" id="toggle-dark" aria-label="Basculer entre mode sombre et clair">
          <i class="bi bi-sun-fill mode-icon"></i>
        </button>
      </div>
    </div>
  </nav>
<script src="/assets/header-script.js"></script>
</body>