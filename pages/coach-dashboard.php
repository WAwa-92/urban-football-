<?php
require __DIR__ . '/../php/db.php';

session_start();

if (empty($_SESSION['site_user']) || (($_SESSION['site_user']['role'] ?? '') !== 'coach')) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$userId = (int) $_SESSION['site_user']['id'];

$stmt = $pdo->prepare('SELECT c.specialty, c.bio, c.years_experience, c.is_active FROM coaches c WHERE c.user_id = :user_id LIMIT 1');
$stmt->execute([':user_id' => $userId]);
$coach = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Coach</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 900px; margin-top: 60px;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:22px;">
            <h1 class="section-title" style="text-align:left; margin-bottom:0;">Espace Coach</h1>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="bt" href="../Urban Center.html">Retour au site</a>
                <a class="bt" href="logout.php">Déconnexion</a>
            </div>
        </div>

        <div class="contact-form" style="max-width:100%;">
            <h2 class="form-title">Bienvenue <?php echo htmlspecialchars($_SESSION['site_user']['full_name']); ?></h2>
            <p style="margin-bottom: 14px; color:#64748b;">Votre compte coach est actif. Cet espace est prêt pour les prochaines fonctionnalités (planning, séances, suivi membres).</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px; margin-top:10px;">
                <div class="reservation-summary-card">
                    <strong>Spécialité</strong>
                    <div><?php echo htmlspecialchars($coach['specialty'] ?? '—'); ?></div>
                </div>
                <div class="reservation-summary-card">
                    <strong>Expérience</strong>
                    <div><?php echo isset($coach['years_experience']) && $coach['years_experience'] !== null ? (int) $coach['years_experience'] . ' ans' : '—'; ?></div>
                </div>
                <div class="reservation-summary-card">
                    <strong>Statut</strong>
                    <div><?php echo (isset($coach['is_active']) && (int) $coach['is_active'] === 1) ? 'Actif' : 'Inactif'; ?></div>
                </div>
            </div>

            <?php if (!empty($coach['bio'])): ?>
                <div style="margin-top:16px;">
                    <strong>Bio</strong>
                    <p style="margin-top:8px;"><?php echo nl2br(htmlspecialchars($coach['bio'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
