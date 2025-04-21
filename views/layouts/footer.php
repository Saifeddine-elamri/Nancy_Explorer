<footer class="footer glass-effect py-5 mt-5 animate__animated animate__fadeInUp">
  <div class="container">
    <div class="row g-4">
      <!-- Navigation -->
      <div class="col-md-4 col-lg-3">
        <h5 class="footer-title">Navigation</h5>
        <ul class="list-unstyled footer-links">
          <li><a href="/" class="footer-link">Accueil</a></li>
          <li><a href="/ajouter" class="footer-link">Ajouter un lieu</a></li>
          <li><a href="#" class="footer-link">À propos</a></li>
          <li><a href="#" class="footer-link">Contact</a></li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div class="col-md-4 col-lg-5">
        <h5 class="footer-title">Restez informé</h5>
        <form id="newsletter-form" class="newsletter-form">
          <div class="input-group">
            <span class="input-group-text bg-transparent border-0">
              <i class="bi bi-envelope-fill text-neon"></i>
            </span>
            <input type="email" class="form-control shadow-sm" 
                   placeholder="Votre email..." 
                   required 
                   aria-label="Adresse email pour la newsletter">
            <button type="submit" class="btn btn-submit ripple-effect">
              <i class="bi bi-arrow-right-circle-fill"></i>
            </button>
          </div>
        </form>
        <p class="text-muted small mt-2">Recevez les dernières mises à jour de Ville Explorer.</p>
      </div>

      <!-- Liens sociaux -->
      <div class="col-md-4 col-lg-4 text-md-end">
        <h5 class="footer-title">Suivez-nous</h5>
        <div class="social-links">
          <a href="https://x.com" class="social-icon" aria-label="Suivez-nous sur X">
            <i class="bi bi-twitter-x"></i>
          </a>
          <a href="https://github.com" class="social-icon" aria-label="Suivez-nous sur GitHub">
            <i class="bi bi-github"></i>
          </a>
          <a href="https://linkedin.com" class="social-icon" aria-label="Suivez-nous sur LinkedIn">
            <i class="bi bi-linkedin"></i>
          </a>
          <a href="https://instagram.com" class="social-icon" aria-label="Suivez-nous sur Instagram">
            <i class="bi bi-instagram"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Crédits -->
    <div class="text-center mt-4">
      <p class="footer-credits">
        © <?= date("Y") ?> Nancy Explorer  
        <span class="heart-pulse">❤️</span>
      </p>
    </div>
  </div>

  <!-- Bouton Retour en haut -->
  <button class="btn btn-scroll-top ripple-effect" id="scroll-top" aria-label="Retour en haut de la page">
    <i class="bi bi-arrow-up-circle-fill"></i>
  </button>
</footer>

<!-- Scripts (éviter les doublons avec header.php) -->
<script defer src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script defer src="/assets/script.js"></script>
<script src="/assets/footer-script.js"></script>
<script src="/assets/show-script.js"></script>

</body>
</html>