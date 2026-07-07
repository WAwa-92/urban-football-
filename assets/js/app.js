document.addEventListener('DOMContentLoaded', () => {
  const App = (() => {
    const BREAKPOINT_DESKTOP = 900;
    const ADMIN_ROLES = new Set(['admin', 'manager', 'content_manager', 'super_admin']);
    const SPORTS_DATA = [
      { id: 1, name: 'Football', terrains: [{ id: 1, name: 'Terrain Football' }] },
      { id: 3, name: 'Padel', terrains: [{ id: 3, name: 'Court Padel' }] },
      { id: 4, name: 'Fitness', terrains: [{ id: 4, name: 'Salle Fitness' }] },
    ];
    const TIME_SLOTS = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];

    const qs = (selector, scope = document) => scope.querySelector(selector);
    const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
    const isPagesRoute = window.location.pathname.includes('/pages/');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const REVEAL_SELECTORS = [
      '.activity-card',
      '.testimonial-card',
      '.partner-pill',
      '.card',
      '.stat-box',
      '.process-step',
      '.spotlight-card',
      '.install-card',
      '.value-card',
      '.team-card',
      '.news-card',
      '.event-card',
      '.gallery-item',
    ].join(', ');

    const elements = {
      // Base
      body: document.body,
//qs= querySelector, qsa=querySelectorAll
      // Navigation
      mainNav: qs('#main-nav'),
      navToggle: qs('#nav-toggle'),
      navMenu: qs('#nav-menu'),
      subToggle: qs('#sub-toggle'),
      subWrapper: qs('#sub-wrapper'),

      // Forms / reservation
      csrfInputs: qsa('input[data-csrf-token]'),
      reservationSport: qs('#reservation-sport'),
      reservationTerrain: qs('#reservation-terrain'),
      reservationTime: qs('#reservation-time'),
      reservationDate: qs('#reservation-date'),
      reservationSummary: qs('#reservation-summary'),

      // Sections & gallery
      navLinks: qsa('nav a[href^="#"]'),
      sections: qsa('section[id]'),
      galleryImages: qsa('.gallery-item img'),
      galleryFilterButtons: qsa('.filter-btn[data-filter]'),
      galleryFilterItems: qsa('.gallery-item[data-category]'),

      // UI helpers
      stats: qsa('.stat-number'),
      monthBadges: qsa('.month-badge'),
      revealTargets: qsa(REVEAL_SELECTORS),
    };

    const getJson = async (url) => {
      const response = await fetch(url, { headers: { Accept: 'application/json' } });
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      return response.json();
    };

    const createNavItem = (href, label, className) => {
      const item = document.createElement('li');
      item.dataset.authItem = 'dynamic';
      item.innerHTML = `<a href="${href}" class="${className}">${label}</a>`;
      return item;
    };

    const initMobileNav = () => {
      const { navToggle, navMenu, mainNav, body } = elements;
      if (!navToggle || !navMenu) {
        return;
      }

      const closeNav = () => {
        navMenu.classList.remove('nav-open');
        navToggle.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
        body.style.overflow = '';
      };

      const openNav = () => {
        navMenu.classList.add('nav-open');
        navToggle.classList.add('open');
        navToggle.setAttribute('aria-expanded', 'true');
        if (window.innerWidth < BREAKPOINT_DESKTOP) {
          body.style.overflow = 'hidden';
        }
      };

      navToggle.addEventListener('click', () => {
        navMenu.classList.contains('nav-open') ? closeNav() : openNav();
      });

      qsa('a', navMenu).forEach((link) => {
        link.addEventListener('click', closeNav);
      });

      document.addEventListener('click', (event) => {
        if (!navMenu.classList.contains('nav-open') || !mainNav || mainNav.contains(event.target)) {
          return;
        }
        closeNav();
      });

      window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          closeNav();
        }
      });

      window.addEventListener('resize', () => {
        if (window.innerWidth >= BREAKPOINT_DESKTOP) {
          closeNav();
        }
      });
    };

    const initSubMenu = () => {
      const { subToggle, subWrapper } = elements;
      if (!subToggle || !subWrapper) {
        return;
      }

      subToggle.addEventListener('click', () => {
        const isOpen = subWrapper.classList.toggle('is-open');
        subToggle.classList.toggle('is-open', isOpen);
        subToggle.setAttribute('aria-expanded', String(isOpen));
      });
    };

    const initCsrfTokens = async () => {
      if (elements.csrfInputs.length === 0) {
        return;
      }

      const endpoint = isPagesRoute ? '../php/csrf-token.php' : 'php/csrf-token.php';

      try {
        const data = await getJson(endpoint);
        if (!data?.token) {
          return;
        }

        elements.csrfInputs.forEach((input) => {
          input.value = data.token;
        });
      } catch {
        // Si la requête échoue, la validation serveur continuera de protéger les formulaires.
      }
    };

    const initAuthNavigation = async () => {
      const { navMenu, body } = elements;
      if (!navMenu) {
        return;
      }

      const endpoint = isPagesRoute ? '../php/auth-status.php' : 'php/auth-status.php';

      try {
        const auth = await getJson(endpoint);

        if (!auth || auth.logged_in !== true) {
          body.classList.remove('is-authenticated');
          return;
        }

        body.classList.add('is-authenticated');

        qsa('a', navMenu).forEach((link) => {
          const href = (link.getAttribute('href') || '').trim();
          if (/(^|\/)register\.php$/i.test(href) || /(^|\/)login\.php$/i.test(href)) {
            link.closest('li')?.remove();
          }
        });

        qsa('li[data-auth-item="dynamic"]', navMenu).forEach((item) => item.remove());

        const isAdminUser = auth.user_type === 'admin' || ADMIN_ROLES.has(String(auth.role || ''));
        const siteDashboardHref = String(auth.site_dashboard_url || '').trim() || (isPagesRoute ? '../admin/dashboard.php' : 'admin/dashboard.php');
        const cmsDashboardHref = String(auth.cms_dashboard_url || '').trim() || (isPagesRoute ? '../social-cms/dashboard.php' : 'social-cms/dashboard.php');
        const accountHref = String(auth.dashboard_url || '').trim() || (isAdminUser
          ? siteDashboardHref
          : auth.role === 'coach'
            ? (isPagesRoute ? 'coach-dashboard.php' : 'pages/coach-dashboard.php')
            : (isPagesRoute ? 'my-reservations.html' : 'pages/my-reservations.html'));
        const accountLabel = isAdminUser ? 'Dashboard' : (auth.role === 'coach' ? 'Espace Coach' : 'Mes réservations');
        const logoutHref = String(auth.logout_url || '').trim() || (isAdminUser
          ? (isPagesRoute ? '../social-cms/admin/logout.php' : 'social-cms/admin/logout.php')
          : (isPagesRoute ? 'logout.php' : 'pages/logout.php'));

        if (auth.user_type === 'admin') {
          navMenu.append(
            createNavItem(siteDashboardHref, 'Dashboard site', 'nav-action'),
            createNavItem(cmsDashboardHref, 'Dashboard CMS', 'nav-action'),
            createNavItem(logoutHref, 'Déconnexion', 'nav-action-secondary')
          );
        } else {
          navMenu.append(
            createNavItem(accountHref, accountLabel, 'nav-action'),
            createNavItem(logoutHref, 'Déconnexion', 'nav-action-secondary')
          );
        }
      } catch {
        body.classList.remove('is-authenticated');
      }
    };

    const initNavScrollState = () => {
      const { mainNav } = elements;
      if (!mainNav) {
        return;
      }

      const updateState = () => {
        mainNav.classList.toggle('is-scrolled', window.scrollY > 8);
      };

      updateState();
      window.addEventListener('scroll', updateState, { passive: true });
    };

    const setTodayAsMinDate = () => {
      const { reservationDate } = elements;
      if (!reservationDate) {
        return;
      }

      const now = new Date();
      const localNow = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
      const today = localNow.toISOString().split('T')[0];

      reservationDate.min = today;
      if (!reservationDate.value) {
        reservationDate.value = today;
      }
    };

    const populateReservationSports = () => {
      const { reservationSport } = elements;
      if (!reservationSport) {
        return;
      }

      reservationSport.innerHTML = '<option value="">Choisir un sport</option>';
      SPORTS_DATA.forEach((sport) => {
        const option = document.createElement('option');
        option.value = String(sport.id);
        option.textContent = sport.name;
        reservationSport.appendChild(option);
      });
    };

    const populateReservationTerrains = (sportId) => {
      const { reservationTerrain } = elements;
      if (!reservationTerrain) {
        return;
      }

      reservationTerrain.innerHTML = '<option value="">Choisir un terrain</option>';
      const selectedSport = SPORTS_DATA.find((sport) => String(sport.id) === String(sportId));
      if (!selectedSport) {
        return;
      }

      selectedSport.terrains.forEach((terrain) => {
        const option = document.createElement('option');
        option.value = String(terrain.id);
        option.textContent = terrain.name;
        reservationTerrain.appendChild(option);
      });
    };

    const populateReservationTimes = () => {
      const { reservationTime } = elements;
      if (!reservationTime) {
        return;
      }

      reservationTime.innerHTML = '<option value="">Choisir un créneau</option>';
      TIME_SLOTS.forEach((slot) => {
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = slot.replace(':', 'h');
        reservationTime.appendChild(option);
      });
    };

    const updateReservationSummary = () => {
      const { reservationSport, reservationTerrain, reservationDate, reservationTime, reservationSummary } = elements;
      if (!reservationSummary) {
        return;
      }

      const selectedSport = SPORTS_DATA.find((sport) => String(sport.id) === String(reservationSport?.value));
      const selectedTerrain = selectedSport?.terrains.find((terrain) => String(terrain.id) === String(reservationTerrain?.value));

      const summaryRows = [
        selectedSport ? `<strong>Sport :</strong> ${selectedSport.name}` : '',
        selectedTerrain ? `<strong>Terrain :</strong> ${selectedTerrain.name}` : '',
        reservationDate?.value ? `<strong>Date :</strong> ${reservationDate.value}` : '',
        reservationTime?.value ? `<strong>Heure :</strong> ${reservationTime.value}` : '',
      ].filter(Boolean);

      reservationSummary.innerHTML = summaryRows.length
        ? `<div class="reservation-summary-card">${summaryRows.join('<br>')}</div>`
        : '';
    };

    const initReservationHelpers = () => {
      const { reservationSport, reservationTerrain, reservationDate, reservationTime } = elements;
      if (!reservationSport && !reservationTerrain && !reservationDate && !reservationTime) {
        return;
      }

      const syncReservationFields = () => {
        populateReservationTerrains(reservationSport?.value || '');
        updateReservationSummary();
      };

      setTodayAsMinDate();
      populateReservationSports();
      populateReservationTimes();
      syncReservationFields();

      reservationSport?.addEventListener('change', syncReservationFields);
      reservationTerrain?.addEventListener('change', updateReservationSummary);
      reservationDate?.addEventListener('change', updateReservationSummary);
      reservationTime?.addEventListener('change', updateReservationSummary);
    };

    const initGalleryFilters = () => {
      const { galleryFilterButtons, galleryFilterItems } = elements;
      if (!galleryFilterButtons.length || !galleryFilterItems.length) {
        return;
      }

      galleryFilterButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const selectedFilter = button.dataset.filter || 'all';

          galleryFilterButtons.forEach((filterButton) => {
            filterButton.classList.toggle('active', filterButton === button);
          });

          galleryFilterItems.forEach((item) => {
            const matches = selectedFilter === 'all' || item.dataset.category === selectedFilter;
            item.style.display = matches ? '' : 'none';
          });
        });
      });
    };

    const initMonthBadges = () => {
      if (!elements.monthBadges.length) {
        return;
      }

      const now = new Date();
      const monthLabel = new Intl.DateTimeFormat('fr-FR', { month: 'long' }).format(now);

      elements.monthBadges.forEach((badge, index) => {
        badge.textContent = `${monthLabel} ${now.getFullYear()}`;
        badge.style.opacity = String(0.95 - index * 0.08);
      });
    };

    const animateCounter = (element) => {
      const rawValue = element.textContent.trim();
      const hasPlus = rawValue.includes('+');
      const numericValue = rawValue.replace('+', '');

      if (!/^\d+$/.test(numericValue)) {
        return;
      }

      if (prefersReducedMotion) {
        element.textContent = rawValue;
        return;
      }

      const target = Number.parseInt(numericValue, 10);
      const duration = 1300;
      const start = performance.now();

      const tick = (timestamp) => {
        const progress = Math.min((timestamp - start) / duration, 1);
        const currentValue = Math.floor(progress * target);
        element.textContent = `${currentValue}${hasPlus ? '+' : ''}`;

        if (progress < 1) {
          requestAnimationFrame(tick);
          return;
        }

        element.textContent = rawValue;
      };

      requestAnimationFrame(tick);
    };

    const initCounters = () => {
      if (!elements.stats.length) {
        return;
      }

      if (!('IntersectionObserver' in window)) {
        elements.stats.forEach(animateCounter);
        return;
      }

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          animateCounter(entry.target);
          observer.unobserve(entry.target);
        });
      }, { threshold: 0.6 });

      elements.stats.forEach((stat) => observer.observe(stat));
    };

    const initRevealAnimations = () => {
      if (!elements.revealTargets.length) {
        return;
      }

      elements.revealTargets.forEach((item) => item.classList.add('reveal'));

      if (!('IntersectionObserver' in window) || prefersReducedMotion) {
        elements.revealTargets.forEach((item) => item.classList.add('is-visible'));
        return;
      }

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
          }
        });
      }, { threshold: 0.16 });

      elements.revealTargets.forEach((item) => observer.observe(item));
    };

    const initSectionSpy = () => {
      const { navLinks, sections } = elements;
      if (!navLinks.length || !sections.length || !('IntersectionObserver' in window)) {
        return;
      }

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          navLinks.forEach((link) => link.classList.remove('nav-active'));
          const activeLink = navLinks.find((link) => link.getAttribute('href') === `#${entry.target.id}`);
          activeLink?.classList.add('nav-active');
        });
      }, { threshold: 0.45 });

      sections.forEach((section) => observer.observe(section));
    };

    const initLightbox = () => {
      if (!elements.galleryImages.length) {
        return;
      }

      const lightbox = document.createElement('div');
      lightbox.className = 'lightbox';
      lightbox.innerHTML = '<img alt="Aperçu" />';
      document.body.appendChild(lightbox);

      const lightboxImage = qs('img', lightbox);
      const closeLightbox = () => lightbox.classList.remove('open');

      elements.galleryImages.forEach((image) => {
        image.style.cursor = 'zoom-in';
        image.addEventListener('click', () => {
          lightboxImage.src = image.src;
          lightboxImage.alt = image.alt || 'Aperçu de la galerie';
          lightbox.classList.add('open');
        });
      });

      lightbox.addEventListener('click', closeLightbox);
      window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          closeLightbox();
        }
      });
    };

    const initBackToTopButton = () => {
      const button = document.createElement('button');
      button.className = 'topbar';
      button.type = 'button';
      button.textContent = '↑';
      button.setAttribute('aria-label', 'Retour en haut');
      document.body.appendChild(button);

      button.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
      });

      const toggleVisibility = () => {
        button.classList.toggle('visible', window.scrollY > 500);
      };

      toggleVisibility();
      window.addEventListener('scroll', toggleVisibility, { passive: true });
    };

    const init = () => {
      initMobileNav();
      initSubMenu();
      initCsrfTokens();
      initAuthNavigation();
      initNavScrollState();
      initReservationHelpers();
      initGalleryFilters();
      initMonthBadges();
      initCounters();
      // Effets optionnels désactivés pour garder une interface plus simple.
    };

    return { init };
  })();

  App.init();
});
