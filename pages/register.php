<?php
require __DIR__ . '/../php/db.php';

session_start();

$pdo = getPDO();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($fullName === '' || $email === '' || $password === '' || $passwordConfirm === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute([':email' => $email]);

        if ($check->fetchColumn()) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $insert = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, status) VALUES (:full_name, :email, :password_hash, :status)');
            $insert->execute([
                ':full_name' => $fullName,
                ':email' => $email,
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ':status' => 'active',
            ]);

            $userId = (int) $pdo->lastInsertId();
            $_SESSION['site_user'] = [
                'id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
            ];

            $success = 'Inscription réussie. Redirection...';
            header('Refresh: 1; url=../Urban Center.html');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Utilisateur</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 560px; margin-top: 80px;">
        <div class="contact-form" style="max-width: 100%;">
            <h1 class="section-title" style="font-size: 2rem; margin-bottom: 20px;">Créer un compte</h1>
            <p style="margin-bottom: 20px; color: #666;">Inscrivez-vous puis vous serez connecté automatiquement.</p>

            <?php if ($error !== ''): ?>
                <p style="color: #c0392b; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <p style="color: #166534; margin-bottom: 15px;"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirmer mot de passe</label>
                    <input type="password" name="password_confirm" required>
                </div>
                <button type="submit" class="submit-btn">S'inscrire</button>
            </form>

            <p style="margin-top: 16px; color: #666; font-size: 0.95rem;">
                Déjà un compte ?
                <a href="login.php" style="color:#1e3c72; font-weight:700;">Se connecter</a>
            </p>
        </div>
    </div>
</body>
</html>
