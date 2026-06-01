<?php
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Urban Center.html#abonnements');
    exit;
}

if (!empty($_POST['website'] ?? '')) {
    exit('Spam détecté.');
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
</head>
<body>
    <div class="container" style="padding: 60px 20px; text-align: center;">
        <h1>Demande d’abonnement enregistrée</h1>
        <p>Merci, votre demande a bien été envoyée.</p>
        <p><a href="../Urban Center.html#abonnements">Retour au site</a></p>
    </div>
</body>
</html>
