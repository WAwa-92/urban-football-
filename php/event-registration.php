<?php
require __DIR__ . '/db.php';
require __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/events.html#inscription');
    exit;
}

if (!empty($_POST['website'] ?? '')) {
    exit('Spam détecté.');
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    exit('Session expirée. Merci de recharger la page.');
}

$eventId = (int) ($_POST['event_id'] ?? 0);
$eventTitle = trim($_POST['event_title'] ?? '');
$fullName = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$note = trim($_POST['note'] ?? '');

if ($eventTitle === '' || $fullName === '' || $phone === '' || $email === '') {
    exit('Veuillez remplir tous les champs obligatoires.');
}

if (!preg_match('/^[0-9]{8}$/', $phone)) {
    exit('Le numéro de téléphone doit contenir 8 chiffres.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Adresse email invalide.');
}

$pdo = getPDO();

if ($eventId > 0) {
    ensureSiteEventsTable($pdo);
    $check = $pdo->prepare('SELECT title FROM site_events WHERE id = :id LIMIT 1');
    $check->execute([':id' => $eventId]);
    $event = $check->fetch();
    if ($event && !empty($event['title'])) {
        $eventTitle = (string) $event['title'];
    }
}

$stmt = $pdo->prepare('INSERT INTO event_registrations (event_id, event_title, full_name, phone, email, note) VALUES (:event_id, :event_title, :full_name, :phone, :email, :note)');
$stmt->execute([
    ':event_id' => $eventId > 0 ? $eventId : null,
    ':event_title' => $eventTitle,
    ':full_name' => $fullName,
    ':phone' => $phone,
    ':email' => $email,
    ':note' => $note !== '' ? $note : null,
]);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription enregistrée</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages.css">
</head>
<body>
    <section class="success-page">
        <div class="success-card">
            <div class="success-icon">OK</div>
            <h1 class="success-title">Inscription envoyée</h1>
            <p class="success-text">Merci <strong><?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></strong>, votre demande d'inscription est bien enregistrée.</p>
            <p class="success-highlight">Événement : <strong><?php echo htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8'); ?></strong></p>
            <div class="success-actions">
                <a class="bt" href="../pages/events.html">Retour aux événements</a>
                <a class="bt bt-secondary" href="../Urban Center.html">Retour à l'accueil</a>
            </div>
        </div>
    </section>
    <footer>
        <p>&copy; 2026 Urban Center Hessi Djerbi. Tous droits réservés.</p>
    </footer>
</body>
</html>
