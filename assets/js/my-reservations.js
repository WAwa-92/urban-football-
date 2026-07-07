document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('reservations-container');
  const errorBanner = document.getElementById('error-banner');
  const sportFilter = document.getElementById('filter-sport');
  const statusFilter = document.getElementById('filter-status');
  const resetButton = document.getElementById('btn-reset-filters');

  if (!container || !errorBanner || !sportFilter || !statusFilter || !resetButton) {
    return;
  }

  const endpoints = {
    list: '../php/get_user_reservations.php',
    cancel: '../php/cancel_reservation.php',
  };

  const dateFormatter = new Intl.DateTimeFormat('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });

  const statusLabels = {
    confirmed: 'Confirmée',
    pending: 'En attente',
    cancelled: 'Annulée',
    rejected: 'Rejetée',
  };

  const ARCHIVED_SPORT_SLUGS = new Set(['tennis']);

  const state = {
    reservations: [],
  };

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const showLoading = () => {
    container.innerHTML = '<div class="loading"></div>';
  };

  const showMessage = (message) => {
    errorBanner.textContent = message;
    errorBanner.classList.add('show');
  };

  const hideMessage = () => {
    errorBanner.textContent = '';
    errorBanner.classList.remove('show');
  };

  const showEmptyState = (title = '😔 Aucune réservation', text = 'Vous n\'avez pas encore de réservation. Commencez dès maintenant !') => {
    container.innerHTML = `
      <div class="empty-state">
        <h2>${title}</h2>
        <p>${text}</p>
        <a href="reservation.html" class="bt">Faire une réservation</a>
      </div>
    `;
  };

  const addOneHour = (time) => {
    if (!/^\d{2}:\d{2}$/.test(String(time || ''))) {
      return '—';
    }

    const [hour, minute] = String(time).split(':').map(Number);
    return `${String((hour + 1) % 24).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
  };

  const formatDate = (dateValue) => {
    if (!dateValue) {
      return '—';
    }

    const parsed = new Date(`${dateValue}T00:00:00`);
    return Number.isNaN(parsed.getTime()) ? String(dateValue) : dateFormatter.format(parsed);
  };

  const canCancelReservation = (reservation) => ['confirmed', 'pending'].includes(reservation.status);

  const isArchivedSport = (reservation) => {
    const slug = String(reservation?.sport_slug || '').toLowerCase();
    const sportName = String(reservation?.sport_name || '').toLowerCase();
    const isInactive = Number(reservation?.sport_is_active) === 0;
    return ARCHIVED_SPORT_SLUGS.has(slug) || sportName === 'tennis' || isInactive;
  };

  const getSportLabel = (reservation) => {
    if (isArchivedSport(reservation)) {
      return 'Activité archivée';
    }
    return String(reservation?.sport_name || '—');
  };

  const populateSportFilter = (reservations) => {
    const previousValue = sportFilter.value;

    const activeSports = new Map();
    reservations.forEach((reservation) => {
      const sportId = Number(reservation?.sport_id || 0);
      if (sportId <= 0 || isArchivedSport(reservation)) {
        return;
      }

      if (!activeSports.has(sportId)) {
        activeSports.set(sportId, getSportLabel(reservation));
      }
    });

    sportFilter.innerHTML = '<option value="">Tous les sports</option>';

    Array.from(activeSports.entries())
      .sort((a, b) => a[1].localeCompare(b[1], 'fr'))
      .forEach(([sportId, sportLabel]) => {
        const option = document.createElement('option');
        option.value = String(sportId);
        option.textContent = sportLabel;
        sportFilter.appendChild(option);
      });

    const hasPreviousOption = Array.from(sportFilter.options).some((option) => option.value === previousValue);
    if (previousValue && hasPreviousOption) {
      sportFilter.value = previousValue;
    }
  };

  const renderComment = (comment) => {
    if (!comment) {
      return '';
    }

    return `<p style="margin-top: 12px; font-size: 0.9rem; color: #6b7280;"><strong>Note :</strong> ${escapeHtml(comment)}</p>`;
  };

  const renderCard = (reservation) => {
    const statusClass = escapeHtml(reservation.status || 'pending');
    const statusLabel = statusLabels[reservation.status] || reservation.status || '—';
    const totalPrice = Number(reservation.price_per_hour || 0);

    return `
      <article class="reservation-card">
        <div class="card-header">
          <div>
            <div class="card-title">${escapeHtml(reservation.terrain_name)}</div>
            <div class="card-id">N° ${escapeHtml(reservation.id)}</div>
          </div>
          <span class="status-badge status-${statusClass}">${escapeHtml(statusLabel)}</span>
        </div>

        <div class="card-grid">
          <div class="card-item">
            <div class="label">Sport</div>
            <div class="value">${escapeHtml(getSportLabel(reservation))}</div>
          </div>
          <div class="card-item">
            <div class="label">Date</div>
            <div class="value">${escapeHtml(formatDate(reservation.reservation_date))}</div>
          </div>
          <div class="card-item">
            <div class="label">Créneau</div>
            <div class="value">${escapeHtml(reservation.reservation_time)} — ${escapeHtml(addOneHour(reservation.reservation_time))}</div>
          </div>
          <div class="card-item">
            <div class="label">Joueurs</div>
            <div class="value">${escapeHtml(reservation.players_count)}</div>
          </div>
          <div class="card-item">
            <div class="label">Tarif/h</div>
            <div class="value">${escapeHtml(totalPrice)} DT</div>
          </div>
          <div class="card-item">
            <div class="label">Total</div>
            <div class="value">${escapeHtml(totalPrice)} DT</div>
          </div>
        </div>

        ${renderComment(reservation.comment)}

        <div class="card-footer">
          ${canCancelReservation(reservation) ? `<button class="btn btn-cancel" data-action="cancel-reservation" data-res-id="${escapeHtml(reservation.id)}">Annuler</button>` : ''}
        </div>
      </article>
    `;
  };

  const renderReservations = (reservations) => {
    if (!reservations.length) {
      showEmptyState();
      return;
    }

    container.innerHTML = `
      <div class="reservations-list">
        ${reservations.map(renderCard).join('')}
      </div>
    `;
  };

  const getFilteredReservations = () => {
    const selectedSport = sportFilter.value;
    const selectedStatus = statusFilter.value;

    return state.reservations.filter((reservation) => {
      const matchesSport = !selectedSport || reservation.sport_id === Number(selectedSport);
      const matchesStatus = !selectedStatus || reservation.status === selectedStatus;
      return matchesSport && matchesStatus;
    });
  };

  const applyFilters = () => {
    renderReservations(getFilteredReservations());
  };

  const fetchJson = async (url, options = {}) => {
    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data.message || data.error || 'Une erreur est survenue.');
    }

    return data;
  };

  const loadReservations = async () => {
    showLoading();
    hideMessage();

    try {
      const data = await fetchJson(endpoints.list);
      state.reservations = Array.isArray(data.reservations) ? data.reservations : [];
      populateSportFilter(state.reservations);
      renderReservations(state.reservations);
    } catch (error) {
      showMessage(error.message || 'Impossible de charger vos réservations.');
      showEmptyState('Erreur', 'Une erreur est survenue lors du chargement.');
    }
  };

  const cancelReservation = async (reservationId) => {
    if (!window.confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
      return;
    }

    try {
      const data = await fetchJson(endpoints.cancel, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ reservation_id: reservationId }),
      });

      showMessage(data.message || 'Réservation annulée.');
      await loadReservations();
    } catch (error) {
      showMessage(error.message || 'Erreur réseau lors de l’annulation.');
    }
  };

  const resetFilters = () => {
    sportFilter.value = '';
    statusFilter.value = '';
    renderReservations(state.reservations);
  };

  const bindEvents = () => {
    sportFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    resetButton.addEventListener('click', resetFilters);

    container.addEventListener('click', (event) => {
      const button = event.target.closest('[data-action="cancel-reservation"]');
      if (!button) {
        return;
      }

      const reservationId = Number(button.dataset.resId || 0);
      if (reservationId > 0) {
        cancelReservation(reservationId);
      }
    });
  };

  bindEvents();
  loadReservations();
});
