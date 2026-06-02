<?php
require __DIR__ . '/config.php';
require __DIR__ . '/../php/csrf.php';

$pdo = getPDO();
ensureDefaultAdmin($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Session expirée. Merci de réessayer.';
    } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role, status FROM admin_users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if ($admin && $admin['status'] === 'active' && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_user'] = [
            'id' => $admin['id'],
            'full_name' => $admin['full_name'],
            'email' => $admin['email'],
            'role' => $admin['role'],
        ];

        $update = $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id');
        $update->execute([':id' => $admin['id']]);

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Identifiants invalides.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 520px; margin-top: 80px;">
        <div class="contact-form" style="max-width: 100%;">
            <h1 class="section-title" style="font-size: 2rem; margin-bottom: 20px;">Back Office</h1>
            <p style="margin-bottom: 20px; color: #666;">Connectez-vous pour gérer les réservations et les événements.</p>
            <?php if ($error !== ''): ?>
                <p style="color: #c0392b; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
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
            <p style="margin-top: 18px; color: #666; font-size: 0.95rem;">
                Compte initial : admin@urbancenter.com / Admin123!
            </p>
        </div>
    </div>
</body>
</html>
