<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🌆 Nancy Explorer</title>
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
      <a class="navbar-brand neon-glow" href="/index.php">
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
            <a class="nav-link nav-link-animated" href="/index.php">Accueil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-animated" href="/add_lieu.php">Ajouter un lieu</a>
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

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;800&display=swap');

    /* Style global */
    body {
      font-family: 'Manrope', sans-serif;
      background: radial-gradient(circle at 30% 20%, #e9ecef 0%, #dfe6e9 50%, #b2bec3 100%);
      animation: gradientPulse 30s ease infinite;
      color: #1a1a1a;
      transition: background 0.5s ease, color 0.5s ease;
    }

    body.dark-mode {
      background: radial-gradient(circle at 30% 20%, #2d3436 0%, #1e272e 50%, #0a0e17 100%);
      color: #f1f3f5;
    }

    @keyframes gradientPulse {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Navbar */
    .navbar {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
      position: sticky;
      top: 0;
      z-index: 1030;
      padding: 1rem 0;
    }

    .dark-mode .navbar {
      background: rgba(0, 0, 0, 0.3);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Logo */
    .navbar-brand {
      font-weight: 800;
      font-size: 1.8rem;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all 0.4s ease;
    }

    .neon-glow {
      background: linear-gradient(45deg, #00b894, #0984e3);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 0 10px rgba(0, 184, 148, 0.5);
    }

    .navbar-brand:hover .neon-glow {
      text-shadow: 0 0 20px rgba(0, 184, 148, 0.8);
      transform: scale(1.05);
    }

    .brand-icon {
      font-size: 1.5rem;
      animation: pulseIcon 2s infinite;
    }

    @keyframes pulseIcon {
      0% { transform: scale(1); }
      50% { transform: scale(1.2); }
      100% { transform: scale(1); }
    }

    /* Bouton hamburger */
    .custom-toggler {
      border: none;
      padding: 10px;
      position: relative;
      width: 40px;
      height: 40px;
      background: transparent;
    }

    .toggler-icon, .toggler-icon::before, .toggler-icon::after {
      width: 24px;
      height: 3px;
      background: #00b894;
      position: absolute;
      left: 8px;
      transition: all 0.3s ease;
    }

    .toggler-icon {
      top: 18px;
    }

    .toggler-icon::before {
      content: '';
      top: -8px;
    }

    .toggler-icon::after {
      content: '';
      top: 8px;
    }

    .custom-toggler[aria-expanded="true"] .toggler-icon {
      background: transparent;
    }

    .custom-toggler[aria-expanded="true"] .toggler-icon::before {
      transform: rotate(45deg);
      top: 0;
    }

    .custom-toggler[aria-expanded="true"] .toggler-icon::after {
      transform: rotate(-45deg);
      top: 0;
    }

    /* Liens */
    .nav-link {
      font-weight: 600;
      font-size: 1.1rem;
      color: #2d3436;
      padding: 10px 15px;
      position: relative;
      transition: color 0.3s ease;
    }

    .dark-mode .nav-link {
      color: #f1f3f5;
    }

    .nav-link-animated::after {
      content: '';
      position: absolute;
      bottom: 5px;
      left: 15px;
      width: 0;
      height: 2px;
      background: linear-gradient(45deg, #00b894, #0984e3);
      transition: width 0.3s ease;
    }

    .nav-link-animated:hover::after, .nav-link-animated.active::after {
      width: calc(100% - 30px);
    }

    .nav-link:hover {
      color: #00b894;
    }

    .dropdown-menu {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .dark-mode .dropdown-menu {
      background: rgba(0, 0, 0, 0.3);
    }

    .dropdown-item {
      color: #2d3436;
      transition: background 0.3s ease, color 0.3s ease;
    }

    .dark-mode .dropdown-item {
      color: #f1f3f5;
    }

    .dropdown-item:hover {
      background: linear-gradient(45deg, #00b894, #0984e3);
      color: white;
    }

    /* Recherche */
    .search-group {
      position: relative;
      transition: all 0.3s ease;
    }

    .search-input {
      border-radius: 25px;
      background: rgba(255, 255, 255, 0.7);
      border: none;
      padding: 10px 40px 10px 20px;
      font-size: 0.95rem;
      transition: all 0.4s ease;
    }

    .dark-mode .search-input {
      background: rgba(255, 255, 255, 0.2);
      color: #f1f3f5;
    }

    .search-input:focus {
      background: white;
      box-shadow: 0 0 15px rgba(0, 184, 148, 0.5);
      transform: scale(1.02);
    }

    .dark-mode .search-input:focus {
      background: rgba(255, 255, 255, 0.9);
    }

    .search-group .input-group-text {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
    }

    /* Bouton mode sombre/clair */
    .btn-mode {
      background: linear-gradient(45deg, #00b894, #0984e3);
      color: white;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      position: relative;
      overflow: hidden;
      transition: all 0.4s ease;
    }

    .dark-mode .btn-mode {
      background: linear-gradient(45deg, #ff3f74, #ff9f43);
    }

    .btn-mode:hover {
      transform: rotate(360deg);
      box-shadow: 0 5px 15px rgba(0, 184, 148, 0.5);
    }

    .mode-icon {
      font-size: 1.2rem;
      transition: transform 0.5s ease;
    }

    .dark-mode .mode-icon::before {
      content: '\f4f9'; /* icône de lune */
    }

    .ripple-effect::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255, 255, 255, 0.4);
      border-radius: 50%;
      transform: translate(-50%, -50%);
      animation: ripple 0.6s ease-out;
    }

    @keyframes ripple {
      to { width: 100px; height: 100px; opacity: 0; }
    }

    /* Responsive */
    @media (max-width: 992px) {
      .navbar-brand {
        font-size: 1.5rem;
      }

      .nav-link {
        font-size: 1rem;
        padding: 8px 10px;
      }

      .search-input {
        font-size: 0.9rem;
      }
    }

    @media (max-width: 576px) {
      .navbar-collapse {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 12px;
        margin-top: 10px;
        padding: 15px;
      }

      .dark-mode .navbar-collapse {
        background: rgba(0, 0, 0, 0.3);
      }

      .search-group {
        margin-bottom: 10px;
      }

      .btn-mode {
        width: 36px;
        height: 36px;
      }
    }
  </style>

  <script>
    // Initialiser AOS
    document.addEventListener('DOMContentLoaded', () => {
      AOS.init({ duration: 800, once: true });

      // Mode sombre/clair
      const toggleDark = document.getElementById('toggle-dark');
      const body = document.body;

      // Charger le mode préféré
      if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
      }

      toggleDark.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        const isDark = body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
      });
    });
  </script>
</body>