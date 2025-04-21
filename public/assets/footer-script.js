
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
