<?php
require __DIR__ . '/db.php';
require __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Urban Center.html#abonnements');
    exit;
}

if (!empty($_POST['website'] ?? '')) {
    exit('Spam détecté.');
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    exit('Session expirée. Merci de recharger la page.');
}

$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subscriptionType = $_POST['abonnement'] ?? '';
$message = trim($_POST['message'] ?? '');

$allowedTypes = ['mensuel', 'trimestriel', 'annuel'];

if ($fullName === '' || $phone === '' || !in_array($subscriptionType, $allowedTypes, true)) {
    exit('Veuillez remplir tous les champs obligatoires.');
}

$pdo = getPDO();
$stmt = $pdo->prepare('INSERT INTO gym_subscriptions (full_name, phone, subscription_type, message) VALUES (:full_name, :phone, :subscription_type, :message)');
$stmt->execute([
    ':full_name' => $fullName,
    ':phone' => $phone,
    ':subscription_type' => $subscriptionType,
    ':message' => $message !== '' ? $message : null,
]);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande enregistrée</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages.css">
</head>
<body>
    <section class="success-page">
        <div class="success-card">
            <div class="success-icon">🏋️</div>
            <h1 class="success-title">Demande d’abonnement enregistrée</h1>
            <p class="success-text">Merci, votre demande a bien été envoyée à l'équipe Urban Center.</p>
            <p class="success-highlight">Nous vous recontactons rapidement pour finaliser l'abonnement.</p>
            <div class="success-actions">
                <a class="bt" href="../Urban Center.html#abonnements">Retour aux abonnements</a>
                <a class="bt bt-secondary" href="../Urban Center.html">Retour à l'accueil</a>
            </div>
        </div>
    </section>
    <footer>
        <p>&copy; 2026 Urban Center Hessi Djerbi. Tous droits réservés.</p>
    </footer>
</body>
</html>
