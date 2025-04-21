// Initialize PhotoSwipe Lightbox
document.addEventListener('DOMContentLoaded', () => {
  const lightbox = new PhotoSwipeLightbox({
    gallery: '.gallery-wrapper',
    children: 'a.gallery-item',
    pswpModule: PhotoSwipe,
    bgOpacity: 0.9,
    spacing: 0,
    loop: false,
    pinchToClose: false
  });
  
  lightbox.on('uiRegister', function() {
    lightbox.pswp.ui.registerElement({
      name: 'download-button',
      ariaLabel: 'Download image',
      order: 8,
      isButton: true,
      html: '<i class="bi bi-download"></i>',
      onClick: (event, el) => {
        const pswp = lightbox.pswp;
        const target = pswp.currSlide.data.element;
        if (target?.href) {
          const link = document.createElement('a');
          link.href = target.href;
          link.download = target.href.split('/').pop() || 'image';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        }
      }
    });
  });
  
  lightbox.init();
});

// Variables globales pour la carte
let mapInstance = null;
let marker = null;

function initMap() {
  const mapElement = document.getElementById('map');
  if (!mapElement) return;

  const latitude = Number(mapElement.dataset.latitude);
  const longitude = Number(mapElement.dataset.longitude);
  const validCoords = isFinite(latitude) && isFinite(longitude) && latitude !== 0 && longitude !== 0;

  if (validCoords) {
    mapInstance = L.map('map', {
      scrollWheelZoom: false,
      zoomControl: false,
      minZoom: 10,
      maxZoom: 18,
      fadeAnimation: true,
      zoomAnimation: true
    }).setView([latitude, longitude], 15);

    L.control.zoom({
      position: 'topright'
    }).addTo(mapInstance);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> © <a href="https://carto.com/attributions">CARTO</a>',
      maxZoom: 19,
      subdomains: 'abcd',
      detectRetina: true
    }).addTo(mapInstance);

    const customIcon = L.divIcon({
      html: '<div class="custom-marker"><i class="bi bi-geo-alt-fill"></i></div>',
      iconSize: [40, 40],
      className: ''
    });

    marker = L.marker([latitude, longitude], {
      icon: customIcon,
      riseOnHover: true
    }).addTo(mapInstance);

    L.circle([latitude, longitude], {
      color: getComputedStyle(document.documentElement).getPropertyValue('--primary-color'),
      fillColor: getComputedStyle(document.documentElement).getPropertyValue('--accent-color'),
      fillOpacity: 0.2,
      radius: 100
    }).addTo(mapInstance);

    requestAnimationFrame(() => {
      mapInstance.invalidateSize();
    });
  } else {
    mapElement.innerHTML = 
      '<div class="d-flex justify-content-center align-items-center h-100 bg-light rounded-4"><p class="text-muted p-4 m-0">Coordonnées non valides</p></div>';
  }
}
// recenterMap
function recenterMap() {
  // Vérifier si mapInstance existe
  if (!mapInstance || !marker) return;

  // Récupérer les coordonnées depuis l'élément DOM ou une variable globale
  const latitude = Number(document.getElementById('map').dataset.latitude);
  const longitude = Number(document.getElementById('map').dataset.longitude);

  // Vérifier si les coordonnées sont valides
  if (isFinite(latitude) && isFinite(longitude)) {
    mapInstance.flyTo([latitude, longitude], 15, {
      duration: 1,
      easeLinearity: 0.25
    });
  }
}

// openDirections
function openDirections() {
  // Récupérer les coordonnées et le nom depuis l'élément DOM
  const latitude = Number(document.getElementById('map').dataset.latitude);
  const longitude = Number(document.getElementById('map').dataset.longitude);
  const name = encodeURIComponent(document.getElementById('map').dataset.name || '');

  // Vérifier si les coordonnées sont valides
  if (!isFinite(latitude) || !isFinite(longitude)) return;

  const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
  const url = isIOS 
    ? `maps://maps.apple.com/?daddr=${latitude},${longitude}&q=${name}`
    : `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}&query=${name}`;
  
  window.open(url, '_blank');
}

// Initialiser la carte après le chargement du DOM
document.addEventListener('DOMContentLoaded', initMap);