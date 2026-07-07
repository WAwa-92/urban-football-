<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/csrf.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function redirectAdminToWorkspace(array $adminUser): void
{
    $role = strtolower(trim((string) ($adminUser['role'] ?? 'admin')));
    $role = str_replace([' ', '-'], '_', $role);

    if (in_array($role, ['content_manager'], true)) {
        header('Location: ../social-cms/dashboard.php');
        exit;
    }

    header('Location: login.php?workspace=1');
    exit;
}

if (!empty($_SESSION['admin_user'])) {
    $adminRole = strtolower(trim((string) ($_SESSION['admin_user']['role'] ?? 'admin')));
    $adminRole = str_replace([' ', '-'], '_', $adminRole);

    if ($adminRole === 'content_manager') {
        redirectAdminToWorkspace($_SESSION['admin_user']);
    }
}
if (!empty($_SESSION['site_user'])) {
    if (($_SESSION['site_user']['role'] ?? '') === 'coach') {
        header('Location: coach-dashboard.php');
        exit;
    }
    header('Location: ../Urban Center.html');
    exit;
}

$pdo = getPDO();
require_once __DIR__ . '/../admin/config.php';
ensureDefaultAdmin($pdo);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Session expirée. Merci de réessayer.';
    } else {
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role, status FROM admin_users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && $admin['status'] === 'active' && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_user'] = [
                'id'        => $admin['id'],
                'full_name' => $admin['full_name'],
                'email'     => $admin['email'],
                'role'      => $admin['role'],
            ];
            $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $admin['id']]);

            $adminRole = strtolower(trim((string) ($admin['role'] ?? 'admin')));
            $adminRole = str_replace([' ', '-'], '_', $adminRole);

            if ($adminRole === 'content_manager') {
                redirectAdminToWorkspace($_SESSION['admin_user']);
            }

            header('Location: login.php?workspace=1');
            exit;
        }

        $stmt2 = $pdo->prepare('SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = :email LIMIT 1');
        $stmt2->execute([':email' => $email]);
        $user = $stmt2->fetch();

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['site_user'] = [
                'id'        => $user['id'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
                'role'      => $user['role'],
            ];
            $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);

            if ($user['role'] === 'coach') {
                header('Location: coach-dashboard.php');
                exit;
            }
            header('Location: ../Urban Center.html');
            exit;
        }

        $error = 'Email ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion – Urban Center</title>
    <meta name="description" content="Connectez-vous à votre espace Urban Center : joueur, coach ou administrateur.">
    <link rel="shortcut icon" href="../assets/img/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages.css">
</head>
<body>
    <nav id="main-nav">
        <a href="../Urban Center.html" class="nav-brand">
            <img src="../assets/img/logo.jpg" width="36" height="36" alt="Logo">
            Urban Center
        </a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <ul id="nav-menu">
            <li><a href="../Urban Center.html">Accueil</a></li>
            <li><a href="installations.html">Installations</a></li>
            <li><a href="events.html">Événements</a></li>
            <li><a href="gallery.html">Galerie</a></li>
            <li><a href="../Urban Center.html#contactez-nous">Contact</a></li>
        </ul>
    </nav>

    <div class="page-hero auth-hero">
        <h1>Bon retour 👋</h1>
        <p>Content de vous revoir sur Urban Center.</p>
    </div>

    <section class="container auth-page">
        <div class="contact-form auth-card">

            <?php if (!empty($_SESSION['admin_user']) && (str_replace([' ', '-'], '_', strtolower((string) ($_SESSION['admin_user']['role'] ?? 'admin'))) !== 'content_manager')): ?>
                <div class="auth-workspace-banner">
                    <h2 class="auth-workspace-title">Espace administrateur</h2>
                    <p class="auth-workspace-text">
                        Vous pouvez accéder au dashboard de gestion du site et au Social CMS depuis cet espace.
                    </p>
                    <div class="auth-actions">
                        <a href="../admin/dashboard.php" class="submit-btn auth-action-link">Dashboard du site</a>
                        <a href="../social-cms/dashboard.php" class="submit-btn auth-action-link auth-action-link--dark">Dashboard CMS</a>
                        <a href="logout.php" class="submit-btn auth-action-link auth-action-link--muted">Se déconnecter</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($_SESSION['admin_user'])): ?>

            <?php if ($error !== ''): ?>
                <p class="auth-error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="email">Votre email</label>
                    <input id="email" type="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="exemple@email.com" autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Votre mot de passe</label>
                    <input id="password" type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
                </div>

                <button type="submit" class="submit-btn">Se connecter →</button>
            </form>

            <p class="auth-note auth-space-top">
                Pas encore membre ?
                <a href="register.php" class="auth-link">Créer un compte</a>
            </p>

            <p class="auth-note-compact auth-space-top-sm">
                Les comptes admin / manager peuvent accéder au dashboard du site et au Social CMS depuis l'espace administrateur. Le rôle content manager va directement au Social CMS.
            </p>

            <?php endif; ?>

        </div>
    </section>

    <footer>
        <p>&copy; 2026 Urban Center Hessi Djerbi. Tous droits réservés.</p>
        <p class="auth-footer-note">
            Contact : <a href="mailto:info@urbancenterhjb.com" class="footer-contact">info@urbancenterhjb.com</a>
        </p>
    </footer>

    <script src="../assets/js/app.js" defer></script>
</body>
</html>
