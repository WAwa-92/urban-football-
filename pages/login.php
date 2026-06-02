<?php
require __DIR__ . '/../php/db.php';
require __DIR__ . '/../php/csrf.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pdo = getPDO();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Session expirée. Merci de réessayer.';
    } else {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['site_user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        $update = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $update->execute([':id' => $user['id']]);

        if ($user['role'] === 'coach') {
            header('Location: coach-dashboard.php');
            exit;
        }

        header('Location: ../Urban Center.html');
        exit;
    }

    $error = 'Identifiants utilisateur invalides.';
    }
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
            <p style="margin-bottom: 20px; color: #666;">Utilisateur → accueil · Coach → espace coach.</p>

            <?php if ($error !== ''): ?>
                <p style="color: #c0392b; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
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
                <a href="logout.php" style="color:#1e3c72; font-weight:700;">Déconnexion utilisateur</a>
                &nbsp;·&nbsp;
                <a href="../admin/login.php" style="color:#1e3c72; font-weight:700;">Connexion administrateur</a>
            </p>
        </div>
    </div>
</body>
</html>
