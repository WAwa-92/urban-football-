<?php
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Urban Center.html#contactez-nous');
    exit;
}

if (!empty($_POST['website'] ?? '')) {
    exit('Spam détecté.');
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
</head>
<body>
    <div class="container" style="padding: 60px 20px; text-align: center;">
        <h1>Message envoyé</h1>
        <p>Merci, votre message a bien été reçu.</p>
        <p><a href="../Urban Center.html#contactez-nous">Retour au site</a></p>
    </div>
</body>
</html>
