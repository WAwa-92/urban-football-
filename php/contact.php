<?php
require __DIR__ . '/db.php';
require __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Urban Center.html#contactez-nous');
    exit;
}

if (!empty($_POST['website'] ?? '')) {
    exit('Spam détecté.');
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    exit('Session expirée. Merci de recharger la page.');
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $phone === '' || $email === '' || $message === '') {
    exit('Veuillez remplir tous les champs obligatoires.');
}

$pdo = getPDO();
$stmt = $pdo->prepare('INSERT INTO contact_messages (name, phone, email, message) VALUES (:name, :phone, :email, :message)');
$stmt->execute([
    ':name' => $name,
    ':phone' => $phone,
    ':email' => $email,
    ':message' => $message,
]);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message envoyé</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages.css">
</head>
<body>
    <section class="success-page">
        <div class="success-card">
            <div class="success-icon">✉️</div>
            <h1 class="success-title">Message envoyé avec succès</h1>
            <p class="success-text">Merci, votre demande a bien été transmise à l'équipe Urban Center.</p>
            <p class="success-highlight">Nous revenons vers vous rapidement par téléphone ou par email.</p>
            <div class="success-actions">
                <a class="bt" href="../Urban Center.html#contactez-nous">Retour au contact</a>
                <a class="bt bt-secondary" href="../Urban Center.html">Retour à l'accueil</a>
            </div>
        </div>
    </section>
    <footer>
        <p>&copy; 2026 Urban Center Hessi Djerbi. Tous droits réservés.</p>
    </footer>
</body>
</html>
