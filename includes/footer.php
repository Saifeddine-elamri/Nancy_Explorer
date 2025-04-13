<footer class="footer glass-effect py-5 mt-5 animate__animated animate__fadeInUp">
  <div class="container">
    <div class="row g-4">
      <!-- Navigation -->
      <div class="col-md-4 col-lg-3">
        <h5 class="footer-title">Navigation</h5>
        <ul class="list-unstyled footer-links">
          <li><a href="/index.php" class="footer-link">Accueil</a></li>
          <li><a href="/add_lieu.php" class="footer-link">Ajouter un lieu</a></li>
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
        © <?= date("Y") ?> Ville Explorer | Propulsé avec 
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
<script>
  AOS.init({ duration: 800, once: true });

  // Gestion du formulaire newsletter (exemple)
  document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    if (email) {
      alert('Merci pour votre inscription ! (Ceci est une démo)');
      this.reset();
    }
  });

  // Bouton Retour en haut
  const scrollTopBtn = document.getElementById('scroll-top');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      scrollTopBtn.classList.add('visible');
    } else {
      scrollTopBtn.classList.remove('visible');
    }
  });

  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;800&display=swap');

/* Footer */
.footer {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255, 255, 255, 0.3);
  color: #2d3436;
  position: relative;
  overflow: hidden;
}

.dark-mode .footer {
  background: rgba(0, 0, 0, 0.3);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: #f1f3f5;
}

.footer::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
  animation: glow 15s ease infinite;
}

@keyframes glow {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Titres */
.footer-title {
  font-weight: 800;
  font-size: 1.4rem;
  background: linear-gradient(45deg, #2d3436, #00b894);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 1.5rem;
}

.dark-mode .footer-title {
  background: linear-gradient(45deg, #f1f3f5, #ff9f43);
}

/* Liens */
.footer-links li {
  margin-bottom: 0.75rem;
}

.footer-link {
  color: #2d3436;
  text-decoration: none;
  font-size: 1rem;
  font-weight: 500;
  position: relative;
  transition: color 0.3s ease;
}

.dark-mode .footer-link {
  color: #f1f3f5;
}

.footer-link::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(45deg, #00b894, #0984e3);
  transition: width 0.3s ease;
}

.footer-link:hover::after {
  width: 100%;
}

.footer-link:hover {
  color: #00b894;
}

.dark-mode .footer-link:hover {
  color: #ff9f43;
}

/* Newsletter */
.newsletter-form .input-group {
  position: relative;
}

.newsletter-form .form-control {
  border-radius: 25px;
  background: rgba(255, 255, 255, 0.7);
  border: none;
  padding: 12px 50px 12px 20px;
  font-size: 0.95rem;
  transition: all 0.4s ease;
}

.dark-mode .newsletter-form .form-control {
  background: rgba(255, 255, 255, 0.2);
  color: #f1f3f5;
}

.newsletter-form .form-control:focus {
  background: white;
  box-shadow: 0 0 15px rgba(0, 184, 148, 0.5);
  transform: scale(1.02);
}

.dark-mode .newsletter-form .form-control:focus {
  background: rgba(255, 255, 255, 0.9);
}

.newsletter-form .input-group-text {
  position: absolute;
  right: 60px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
}

.text-neon {
  color: #00b894;
  filter: drop-shadow(0 0 5px rgba(0, 184, 148, 0.5));
}

.newsletter-form .btn-submit {
  background: linear-gradient(45deg, #00b894, #0984e3);
  color: white;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  transition: all 0.4s ease;
}

.newsletter-form .btn-submit:hover {
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 5px 15px rgba(0, 184, 148, 0.5);
}

/* Liens sociaux */
.social-links {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
}

.social-icon {
  font-size: 1.5rem;
  color: #2d3436;
  transition: all 0.3s ease;
}

.dark-mode .social-icon {
  color: #f1f3f5;
}

.social-icon:hover {
  color: #00b894;
  transform: translateY(-5px) scale(1.2);
  filter: drop-shadow(0 0 10px rgba(0, 184, 148, 0.7));
}

.dark-mode .social-icon:hover {
  color: #ff9f43;
  filter: drop-shadow(0 0 10px rgba(255, 159, 67, 0.7));
}

/* Crédits */
.footer-credits {
  font-size: 0.9rem;
  font-weight: 500;
  color: #2d3436;
}

.dark-mode .footer-credits {
  color: #f1f3f5;
}

.heart-pulse {
  display: inline-block;
  animation: heartBeat 2s infinite;
  color: #ff6b6b;
}

.dark-mode .heart-pulse {
  color: #ff9f43;
}

@keyframes heartBeat {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

/* Bouton Retour en haut */
.btn-scroll-top {
  position: fixed;
  bottom: 30px;
  right: 30px;
  background: linear-gradient(45deg, #00b894, #0984e3);
  color: white;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  opacity: 0;
  visibility: hidden;
  transition: all 0.4s ease;
  z-index: 1000;
}

.btn-scroll-top.visible {
  opacity: 1;
  visibility: visible;
  transform: translateY(-10px);
}

.btn-scroll-top:hover {
  transform: translateY(-15px);
  box-shadow: 0 8px 25px rgba(0, 184, 148, 0.5);
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
  to { width: 150px; height: 150px; opacity: 0; }
}

/* Responsive */
@media (max-width: 992px) {
  .footer-title {
    font-size: 1.2rem;
  }

  .social-links {
    justify-content: flex-start;
  }

  .btn-scroll-top {
    width: 45px;
    height: 45px;
    font-size: 1.3rem;
  }
}

@media (max-width: 576px) {
  .footer {
    padding: 2rem 0;
  }

  .newsletter-form .form-control {
    font-size: 0.9rem;
    padding: 10px 45px 10px 15px;
  }

  .newsletter-form .btn-submit {
    width: 36px;
    height: 36px;
  }

  .social-links {
    gap: 10px;
  }

  .social-icon {
    font-size: 1.3rem;
  }

  .footer-credits {
    font-size: 0.85rem;
  }
}
</style>

</body>
</html>