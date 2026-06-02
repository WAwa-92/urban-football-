<?php
require __DIR__ . '/../php/db.php';
require __DIR__ . '/../php/csrf.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pdo = getPDO();
$error = '';

function redirectAfterSignup(string $accountType): never
{
    if ($accountType === 'coach') {
        header('Location: coach-dashboard.php');
        exit;
    }

    header('Location: ../Urban Center.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Session expirée. Merci de réessayer.';
    } else {
    $fullName = preg_replace('/\s+/', ' ', trim($_POST['full_name'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $accountType = $_POST['account_type'] ?? 'user';
    $specialty = trim($_POST['specialty'] ?? '');
    $experience = (int) ($_POST['years_experience'] ?? 0);
    $bio = trim($_POST['bio'] ?? '');
    $allowedTypes = ['user', 'coach'];

    if ($fullName === '' || $email === '' || $password === '' || $passwordConfirm === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!in_array($accountType, $allowedTypes, true)) {
        $error = 'Type de compte invalide.';
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
            try {
                $pdo->beginTransaction();

                $insert = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, status) VALUES (:full_name, :email, :password_hash, :role, :status)');
                $insert->execute([
                    ':full_name' => $fullName,
                    ':email' => $email,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':role' => $accountType,
                    ':status' => 'active',
                ]);

                $userId = (int) $pdo->lastInsertId();

                if ($accountType === 'coach') {
                    $coachInsert = $pdo->prepare('INSERT INTO coaches (user_id, specialty, bio, years_experience, is_active) VALUES (:user_id, :specialty, :bio, :years_experience, :is_active)');
                    $coachInsert->execute([
                        ':user_id' => $userId,
                        ':specialty' => $specialty !== '' ? $specialty : null,
                        ':bio' => $bio !== '' ? $bio : null,
                        ':years_experience' => $experience > 0 ? $experience : null,
                        ':is_active' => 1,
                    ]);
                }

                $pdo->commit();

                session_regenerate_id(true);
                $_SESSION['site_user'] = [
                    'id' => $userId,
                    'full_name' => $fullName,
                    'email' => $email,
                    'role' => $accountType,
                ];

                redirectAfterSignup($accountType);
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'Impossible de finaliser l\'inscription pour le moment.';
            }
        }
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
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label>Type de compte</label>
                    <select name="account_type" id="account_type" required>
                        <option value="user" <?php echo (($_POST['account_type'] ?? 'user') === 'user') ? 'selected' : ''; ?>>Utilisateur</option>
                        <option value="coach" <?php echo (($_POST['account_type'] ?? '') === 'coach') ? 'selected' : ''; ?>>Coach</option>
                    </select>
                </div>
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

                <div id="coach-extra-fields" style="display:<?php echo (($_POST['account_type'] ?? '') === 'coach') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label>Spécialité (coach)</label>
                        <input type="text" name="specialty" value="<?php echo htmlspecialchars($_POST['specialty'] ?? ''); ?>" placeholder="Ex: Préparation physique">
                    </div>
                    <div class="form-group">
                        <label>Années d'expérience</label>
                        <input type="number" min="0" max="60" name="years_experience" value="<?php echo htmlspecialchars($_POST['years_experience'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Bio (optionnel)</label>
                        <textarea name="bio" rows="3" placeholder="Présentation rapide"><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="submit-btn">S'inscrire</button>
            </form>

            <p style="margin-top: 16px; color: #666; font-size: 0.95rem;">
                Déjà un compte ?
                <a href="login.php" style="color:#1e3c72; font-weight:700;">Se connecter</a>
            </p>
            <p style="margin-top: 8px;">
                <a href="../admin/login.php" style="color:#1e3c72; font-weight:700;">Connexion administrateur</a>
            </p>
        </div>
    </div>
    <script>
        (function () {
            const typeSelect = document.getElementById('account_type');
            const coachFields = document.getElementById('coach-extra-fields');

            if (!typeSelect || !coachFields) {
                return;
            }

            function toggleCoachFields() {
                coachFields.style.display = typeSelect.value === 'coach' ? 'block' : 'none';
            }

            typeSelect.addEventListener('change', toggleCoachFields);
            toggleCoachFields();
        })();
    </script>
</body>
</html>
