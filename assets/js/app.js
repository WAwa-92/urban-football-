document.addEventListener('DOMContentLoaded', () => {
  // ── Hamburger menu ──────────────────────────────────────────
  const navToggle = document.getElementById('nav-toggle');
  const navMenu = document.getElementById('nav-menu');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      const isOpen = navMenu.classList.toggle('nav-open');
      navToggle.classList.toggle('open', isOpen);
      navToggle.setAttribute('aria-expanded', String(isOpen));
    });
    // Close menu when a link is clicked (mobile UX)
    navMenu.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('nav-open');
        navToggle.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }
  // ── Subscription form mobile toggle ─────────────────────────
  const subToggle = document.getElementById('sub-toggle');
  const subWrapper = document.getElementById('sub-wrapper');
  if (subToggle && subWrapper) {
    subToggle.addEventListener('click', () => {
      const isOpen = subWrapper.classList.toggle('is-open');
      subToggle.classList.toggle('is-open', isOpen);
      subToggle.setAttribute('aria-expanded', String(isOpen));
    });
  }

  const sportsData = [
    {
      id: 1,
      name: 'Football',
      terrains: [{ id: 1, name: 'Terrain Football' }],
    },
    {
      id: 2,
      name: 'Tennis',
      terrains: [{ id: 2, name: 'Court Tennis' }],
    },
    {
      id: 3,
      name: 'Padel',
      terrains: [{ id: 3, name: 'Court Padel' }],
    },
    {
      id: 4,
      name: 'Fitness',
      terrains: [{ id: 4, name: 'Salle Fitness' }],
    },
  ];

  const timeSlots = [
    '08:00',
    '09:00',
    '10:00',
    '11:00',
    '12:00',
    '13:00',
    '14:00',
    '15:00',
    '16:00',
    '17:00',
    '18:00',
    '19:00',
  ];

  const reservationSport = document.getElementById('reservation-sport');
  const reservationTerrain = document.getElementById('reservation-terrain');
  const reservationTime = document.getElementById('reservation-time');
  const reservationDate = document.getElementById('reservation-date');
  const reservationSummary = document.getElementById('reservation-summary');
  const navLinks = Array.from(document.querySelectorAll('nav a[href^="#"]'));
  const sections = Array.from(document.querySelectorAll('section[id]'));
  const galleryImages = Array.from(document.querySelectorAll('.gallery-item img'));
  const stats = Array.from(document.querySelectorAll('.stat-number'));
  const monthBadges = Array.from(document.querySelectorAll('.month-badge'));

  if (reservationDate) {
    const today = new Date();
    const tzOffset = today.getTimezoneOffset() * 60000;
    const localDate = new Date(today - tzOffset).toISOString().split('T')[0];
    reservationDate.min = localDate;
    reservationDate.value = localDate;
  }

  function populateSports() {
    if (!reservationSport) return;
    reservationSport.innerHTML = '<option value="">Choisir un sport</option>';
    sportsData.forEach((sport) => {
      const option = document.createElement('option');
      option.value = sport.id;
      option.textContent = sport.name;
      reservationSport.appendChild(option);
    });
  }

  function populateTerrains(selectedSportId) {
    if (!reservationTerrain) return;
    reservationTerrain.innerHTML = '<option value="">Choisir un terrain</option>';
    const sport = sportsData.find((item) => String(item.id) === String(selectedSportId));
    if (!sport) return;
    sport.terrains.forEach((terrain) => {
      const option = document.createElement('option');
      option.value = terrain.id;
      option.textContent = terrain.name;
      reservationTerrain.appendChild(option);
    });
  }

  function populateTimeSlots() {
    if (!reservationTime) return;
    reservationTime.innerHTML = '<option value="">Choisir un créneau</option>';
    timeSlots.forEach((slot) => {
      const option = document.createElement('option');
      option.value = slot;
      option.textContent = slot.replace(':', 'h');
      reservationTime.appendChild(option);
    });
  }

  function updateReservationSummary() {
    if (!reservationSummary) return;

    const sport = sportsData.find((item) => String(item.id) === String(reservationSport?.value));
    const terrain = sport?.terrains.find((item) => String(item.id) === String(reservationTerrain?.value));
    const date = reservationDate?.value;
    const time = reservationTime?.value;

    const parts = [];
    if (sport) parts.push(`<strong>Sport :</strong> ${sport.name}`);
    if (terrain) parts.push(`<strong>Terrain :</strong> ${terrain.name}`);
    if (date) parts.push(`<strong>Date :</strong> ${date}`);
    if (time) parts.push(`<strong>Heure :</strong> ${time}`);

    reservationSummary.innerHTML = parts.length
      ? `<div class="reservation-summary-card">${parts.join('<br>')}</div>`
      : '';
  }

  function syncTerrainAndSummary() {
    const selectedSportId = reservationSport?.value;
    populateTerrains(selectedSportId);
    updateReservationSummary();
  }

  populateSports();
  populateTimeSlots();
  syncTerrainAndSummary();

  reservationSport?.addEventListener('change', syncTerrainAndSummary);
  reservationTerrain?.addEventListener('change', updateReservationSummary);
  reservationDate?.addEventListener('change', updateReservationSummary);
  reservationTime?.addEventListener('change', updateReservationSummary);

  monthBadges.forEach((badge, index) => {
    const now = new Date();
    const monthName = new Intl.DateTimeFormat('fr-FR', { month: 'long' }).format(now);
    badge.textContent = `${monthName} ${now.getFullYear()}`;
    badge.style.opacity = String(0.95 - index * 0.08);
  });

  const animateCounter = (el) => {
    const raw = el.textContent.trim();
    const hasPlus = raw.includes('+');
    const isNumber = /^\d+$/.test(raw.replace('+', ''));

    if (!isNumber) return;

    const target = parseInt(raw, 10);
    const duration = 1300;
    const start = performance.now();

    const step = (timestamp) => {
      const progress = Math.min((timestamp - start) / duration, 1);
      const value = Math.floor(progress * target);
      el.textContent = `${value}${hasPlus ? '+' : ''}`;
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = raw;
    };

    requestAnimationFrame(step);
  };

  const statsObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          statsObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.6 }
  );

  stats.forEach((stat) => statsObserver.observe(stat));

  const revealTargets = document.querySelectorAll(
    '.container, .gallery-section, .horaire, .activity-card, .testimonial-card, .partner-pill, .card, .contact-form, .stat-box, .process-step, .spotlight-card'
  );
  revealTargets.forEach((el) => el.classList.add('reveal'));

  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) entry.target.classList.add('is-visible');
      });
    },
    { threshold: 0.16 }
  );
  revealTargets.forEach((el) => revealObserver.observe(el));

  const sectionObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        navLinks.forEach((link) => link.classList.remove('nav-active'));
        const active = navLinks.find((link) => link.getAttribute('href') === `#${entry.target.id}`);
        if (active) active.classList.add('nav-active');
      });
    },
    { threshold: 0.45 }
  );
  sections.forEach((section) => sectionObserver.observe(section));

  // Lightbox gallery
  const lightbox = document.createElement('div');
  lightbox.className = 'lightbox';
  lightbox.innerHTML = '<img alt="Aperçu" />';
  document.body.appendChild(lightbox);

  const lightboxImage = lightbox.querySelector('img');
  const closeLightbox = () => lightbox.classList.remove('open');

  galleryImages.forEach((img) => {
    img.style.cursor = 'zoom-in';
    img.addEventListener('click', () => {
      lightboxImage.src = img.src;
      lightboxImage.alt = img.alt || 'Aperçu de la galerie';
      lightbox.classList.add('open');
    });
  });
  lightbox.addEventListener('click', closeLightbox);
  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeLightbox();
  });

  // Back to top button
  const topButton = document.createElement('button');
  topButton.className = 'topbar';
  topButton.type = 'button';
  topButton.textContent = '↑';
  topButton.setAttribute('aria-label', 'Retour en haut');
  document.body.appendChild(topButton);

  topButton.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  window.addEventListener('scroll', () => {
    if (window.scrollY > 500) topButton.classList.add('visible');
    else topButton.classList.remove('visible');
  });
});
