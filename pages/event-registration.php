<?php
require_once __DIR__ . '/../php/csrf.php';

$eventId = (int) ($_GET['event_id'] ?? 0);
$eventTitle = trim($_GET['event'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription événement – Urban Center</title>
    <meta name="description" content="Inscrivez-vous facilement à un tournoi ou événement Urban Center.">
    <link rel="shortcut icon" href="../assets/img/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages.css">
</head>
<body>
    <nav id="main-nav">
        <a href="../Urban Center.html" class="nav-brand"><img src="../assets/img/logo.jpg" width="36" height="36" alt="Logo" class="nav-brand-logo">Urban Center</a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <ul id="nav-menu">
            <li><a href="../Urban Center.html">Accueil</a></li>
            <li><a href="installations.html">Installations</a></li>
            <li><a href="events.html" class="nav-active">Événements</a></li>
            <li><a href="gallery.html">Galerie</a></li>
            <li><a href="../Urban Center.html#contactez-nous">Contact</a></li>
        </ul>
    </nav>

    <div class="page-hero page-hero-short">
        <h1>Inscription à un événement</h1>
        <p>Remplissez le formulaire, notre équipe vous confirme votre participation rapidement.</p>
    </div>

    <section class="container page-narrow">
        <div class="contact-form form-max">
            <form class="event-form" action="../php/event-registration.php" method="POST">
                <input type="text" name="website" class="honeypot" autocomplete="off" tabindex="-1" aria-hidden="true">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="event_id" value="<?php echo $eventId > 0 ? $eventId : ''; ?>">

                <div class="form-group">
                    <label for="ev-event">Événement souhaité *</label>
                    <input type="text" id="ev-event" name="event_title" required value="<?php echo htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: Tournoi Hebdomadaire 5v5">
                    <small class="form-help-text">Si vous venez depuis le bouton “S'inscrire”, ce champ est pré-rempli.</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ev-name">Nom complet *</label>
                        <input type="text" id="ev-name" name="name" placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <label for="ev-phone">Téléphone *</label>
                        <input type="tel" id="ev-phone" name="phone" placeholder="8 chiffres" pattern="[0-9]{8}" maxlength="8" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ev-email">Email *</label>
                    <input type="email" id="ev-email" name="email" placeholder="votre@email.com" required>
                </div>

                <div class="form-group">
                    <label for="ev-note">Note (optionnel)</label>
                    <textarea id="ev-note" name="note" rows="4" placeholder="Précision niveau, équipe, disponibilité..."></textarea>
                </div>

                <button type="submit" class="submit-btn">Envoyer ma demande d'inscription</button>
                <p class="back-link-row"><a href="events.html">← Retour à la page Événements</a></p>
            </form>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 Urban Center Hessi Djerbi. Tous droits réservés.</p>
    </footer>
    <script src="../assets/js/app.js" defer></script>
</body>
</html>
