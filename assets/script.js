// Dark mode toggle
document.getElementById("toggle-dark").addEventListener("click", () => {
  document.body.classList.toggle("dark-mode");
  localStorage.setItem("darkMode", document.body.classList.contains("dark-mode"));
});

// Restore preference
if (localStorage.getItem("darkMode") === "true") {
  document.body.classList.add("dark-mode");
}

// Initialiser Masonry
document.addEventListener('DOMContentLoaded', function() {
  const grid = document.querySelector('#lieux-grid');
  if (grid) {
    new Masonry(grid, {
      itemSelector: '.lieu-item',
      columnWidth: '.lieu-item',
      percentPosition: true,
      transitionDuration: '0.4s'
    });
  }

  // Gestion des horaires dans le modal
  document.querySelectorAll('.btn-horaires').forEach(btn => {
    btn.addEventListener('click', function() {
      const horaires = this.getAttribute('data-horaires');
      document.getElementById('modal-horaires-content').innerHTML = 
        horaires.replace(/\n/g, '<br>');
    });
  });

  // Lire plus/moins pour la description
  document.querySelectorAll('.btn-read-more').forEach(btn => {
    btn.addEventListener('click', function() {
      const descContainer = this.previousElementSibling;
      const fullText = descContainer.getAttribute('data-full-text');
      
      if (this.textContent === 'Lire plus') {
        descContainer.textContent = fullText;
        this.textContent = 'Lire moins';
      } else {
        descContainer.textContent = fullText.substring(0, 100) + '...';
        this.textContent = 'Lire plus';
      }
    });
  });

  // Animation au survol des cartes
  document.querySelectorAll('.lieu-card').forEach(card => {
    card.addEventListener('mousemove', function(e) {
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      this.style.setProperty('--mouse-x', `${x}px`);
      this.style.setProperty('--mouse-y', `${y}px`);
    });
  });

  // Recherche avec debounce
  const searchForm = document.getElementById('search-form');
  if (searchForm) {
    const searchInput = searchForm.querySelector('input[name="search"]');
    let timeout;
    
    searchInput.addEventListener('input', function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        searchForm.submit();
      }, 500);
    });
  }
});

// Effet de ripple pour les boutons
document.querySelectorAll('.ripple-effect').forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault();
    
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    const ripple = document.createElement('span');
    ripple.classList.add('ripple');
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;
    
    this.appendChild(ripple);
    
    setTimeout(() => {
      ripple.remove();
      if (this.tagName === 'A') {
        window.location.href = this.href;
      }
    }, 600);
  });
});

