<?php
require __DIR__ . '/../php/db.php';

session_start();

$pdo = getPDO();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, status FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
        $_SESSION['site_user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
        ];

        $update = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $update->execute([':id' => $user['id']]);

        header('Location: ../Urban Center.html');
        exit;
    }

    $error = 'Identifiants utilisateur invalides.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Utilisateur</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 560px; margin-top: 80px;">
        <div class="contact-form" style="max-width: 100%;">
            <h1 class="section-title" style="font-size: 2rem; margin-bottom: 20px;">Connexion utilisateur</h1>
            <p style="margin-bottom: 20px; color: #666;">Connectez-vous puis vous serez redirigé vers la page d'accueil.</p>

            <?php if ($error !== ''): ?>
                <p style="color: #c0392b; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="submit-btn">Se connecter</button>
            </form>

            <p style="margin-top: 16px; color: #666; font-size: 0.95rem;">
                Pas encore de compte ?
                <a href="register.php" style="color:#1e3c72; font-weight:700;">S'inscrire</a>
            </p>
            <p style="margin-top: 8px;">
                <a href="../admin/login.php" style="color:#1e3c72; font-weight:700;">Connexion administrateur</a>
            </p>
        </div>
    </div>
</body>
</html>
