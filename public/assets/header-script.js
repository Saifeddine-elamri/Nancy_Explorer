

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
