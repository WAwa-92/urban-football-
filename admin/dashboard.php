<?php
require __DIR__ . '/config.php';
requireAdmin();

$pdo = getPDO();
ensureSiteEventsTable($pdo);
ensureNewsTable($pdo);

$totalReservations = (int) $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
$confirmedReservations = (int) $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'")->fetchColumn();
$pendingReservations = (int) $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
$clientsCount = (int) $pdo->query('SELECT COUNT(DISTINCT phone) FROM reservations')->fetchColumn();
$revenue = (float) $pdo->query("SELECT COALESCE(SUM(t.price_per_hour), 0) FROM reservations r INNER JOIN terrains t ON t.id = r.terrain_id WHERE r.status = 'confirmed'")->fetchColumn();
$publishedEvents = (int) $pdo->query('SELECT COUNT(*) FROM site_events WHERE is_published = 1')->fetchColumn();
$publishedNews = (int) $pdo->query('SELECT COUNT(*) FROM news WHERE is_published = 1')->fetchColumn();

$sportStatsStmt = $pdo->query(
    "SELECT s.name AS sport_name,
            COUNT(*) AS total_reservations,
            SUM(CASE WHEN r.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_reservations,
            SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) AS pending_reservations
     FROM reservations r
     INNER JOIN sports s ON s.id = r.sport_id
     GROUP BY s.id, s.name
     ORDER BY s.name"
);
$sportStats = $sportStatsStmt->fetchAll();

$recentStmt = $pdo->query('SELECT r.id, r.first_name, r.last_name, r.phone, r.email, r.status, r.created_at, s.name AS sport_name, t.name AS terrain_name, ts.label AS slot_label, rs.reservation_date FROM reservations r INNER JOIN sports s ON s.id = r.sport_id INNER JOIN terrains t ON t.id = r.terrain_id INNER JOIN reservation_slots rs ON rs.id = r.reservation_slot_id INNER JOIN time_slots ts ON ts.id = rs.time_slot_id ORDER BY r.created_at DESC LIMIT 8');
$recentReservations = $recentStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:30px; flex-wrap:wrap;">
            <div>
                <h1 class="section-title" style="margin-bottom:10px; text-align:left;">Tableau de bord</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_user']['full_name']); ?></p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="bt" href="reservations.php">Gérer les réservations</a>
                <a class="bt" href="events.php">Gérer les événements</a>
                <a class="bt" href="news.php">Gérer les actualités</a>
                <a class="bt" href="export-reservations.php">Exporter CSV</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat-box"><span class="stat-number"><?php echo $totalReservations; ?></span><span class="stat-label">Réservations</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $confirmedReservations; ?></span><span class="stat-label">Confirmées</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $pendingReservations; ?></span><span class="stat-label">En attente</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $clientsCount; ?></span><span class="stat-label">Clients</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $publishedEvents; ?></span><span class="stat-label">Événements publiés</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $publishedNews; ?></span><span class="stat-label">Actualités publiées</span></div>
        </div>

        <div class="stat-box" style="margin: 30px 0; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <span class="stat-number" style="font-size: 2.2em;">DT <?php echo number_format($revenue, 2, ',', ' '); ?></span>
            <span class="stat-label">Chiffre d'affaires estimé</span>
        </div>

        <div class="contact-form" style="max-width: 100%;">
            <h2 class="form-title">Réservations récentes</h2>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#1e3c72; color:#fff;">
                            <th style="padding:12px; text-align:left;">Client</th>
                            <th style="padding:12px; text-align:left;">Sport</th>
                            <th style="padding:12px; text-align:left;">Terrain</th>
                            <th style="padding:12px; text-align:left;">Date</th>
                            <th style="padding:12px; text-align:left;">Créneau</th>
                            <th style="padding:12px; text-align:left;">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReservations as $row): ?>
                            <tr>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['sport_name']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['terrain_name']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['reservation_date']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['slot_label']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="contact-form" style="max-width: 100%; margin-top: 24px;">
            <h2 class="form-title">Statistiques par sport</h2>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#1e3c72; color:#fff;">
                            <th style="padding:12px; text-align:left;">Sport</th>
                            <th style="padding:12px; text-align:left;">Total</th>
                            <th style="padding:12px; text-align:left;">Confirmées</th>
                            <th style="padding:12px; text-align:left;">En attente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sportStats as $sportStat): ?>
                            <tr>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($sportStat['sport_name']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo (int) $sportStat['total_reservations']; ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo (int) $sportStat['confirmed_reservations']; ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo (int) $sportStat['pending_reservations']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p style="margin-top:20px;"><a href="logout.php">Se déconnecter</a></p>
    </div>
</body>
</html>
