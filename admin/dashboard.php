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
$totalSubscriptions = (int) $pdo->query('SELECT COUNT(*) FROM gym_subscriptions')->fetchColumn();
$pendingSubscriptions = (int) $pdo->query("SELECT COUNT(*) FROM gym_subscriptions WHERE status = 'en_attente'")->fetchColumn();
$acceptedSubscriptions = (int) $pdo->query("SELECT COUNT(*) FROM gym_subscriptions WHERE status = 'acceptee'")->fetchColumn();
$totalEventRegistrations = (int) $pdo->query('SELECT COUNT(*) FROM event_registrations')->fetchColumn();
$newEventRegistrations = (int) $pdo->query("SELECT COUNT(*) FROM event_registrations WHERE status = 'nouveau'")->fetchColumn();

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

$recentSubscriptionsStmt = $pdo->query('SELECT id, full_name, phone, subscription_type, status, created_at FROM gym_subscriptions ORDER BY created_at DESC LIMIT 8');
$recentSubscriptions = $recentSubscriptionsStmt->fetchAll();

$recentEventRegistrationsStmt = $pdo->query('SELECT id, event_title, full_name, phone, email, status, created_at FROM event_registrations ORDER BY created_at DESC LIMIT 8');
$recentEventRegistrations = $recentEventRegistrationsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1 class="section-title">Tableau de bord</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_user']['full_name']); ?></p>
            </div>
            <div class="dashboard-actions">
                <a class="bt" href="reservations.php">Gérer les réservations</a>
                <a class="bt" href="events.php">Gérer les événements</a>
                <a class="bt" href="news.php">Gérer les actualités</a>
                <a class="bt" href="../social-cms/dashboard.php">Accéder au Social CMS</a>
                <a class="bt" href="export-reservations.php">Exporter CSV</a>
                <a class="bt" href="../Urban Center.html">Voir le site</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat-box"><span class="stat-number"><?php echo $totalReservations; ?></span><span class="stat-label">Réservations</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $confirmedReservations; ?></span><span class="stat-label">Confirmées</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $pendingReservations; ?></span><span class="stat-label">En attente</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $clientsCount; ?></span><span class="stat-label">Clients</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $publishedEvents; ?></span><span class="stat-label">Événements publiés</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $publishedNews; ?></span><span class="stat-label">Actualités publiées</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $totalSubscriptions; ?></span><span class="stat-label">Demandes abonnement</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $pendingSubscriptions; ?></span><span class="stat-label">Abonnements en attente</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $acceptedSubscriptions; ?></span><span class="stat-label">Abonnements acceptés</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $totalEventRegistrations; ?></span><span class="stat-label">Inscriptions événements</span></div>
            <div class="stat-box"><span class="stat-number"><?php echo $newEventRegistrations; ?></span><span class="stat-label">Inscriptions nouvelles</span></div>
        </div>

        <div class="stat-box dashboard-revenue">
            <span class="stat-number">DT <?php echo number_format($revenue, 2, ',', ' '); ?></span>
            <span class="stat-label">Chiffre d'affaires estimé</span>
        </div>

        <div class="contact-form dashboard-panel">
            <h2 class="form-title">Réservations récentes</h2>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Sport</th>
                            <th>Terrain</th>
                            <th>Date</th>
                            <th>Créneau</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReservations as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['sport_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['terrain_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['reservation_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['slot_label']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="contact-form dashboard-panel">
            <h2 class="form-title">Statistiques par sport</h2>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Sport</th>
                            <th>Total</th>
                            <th>Confirmées</th>
                            <th>En attente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sportStats as $sportStat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sportStat['sport_name']); ?></td>
                                <td><?php echo (int) $sportStat['total_reservations']; ?></td>
                                <td><?php echo (int) $sportStat['confirmed_reservations']; ?></td>
                                <td><?php echo (int) $sportStat['pending_reservations']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="contact-form dashboard-panel">
            <h2 class="form-title">Demandes d'abonnement salle de sport (récentes)</h2>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSubscriptions)): ?>
                            <tr>
                                <td class="dashboard-empty" colspan="5">Aucune demande d'abonnement pour le moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentSubscriptions as $subscription): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subscription['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($subscription['phone']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst((string) $subscription['subscription_type'])); ?></td>
                                    <td><?php echo htmlspecialchars((string) $subscription['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $subscription['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="contact-form dashboard-panel">
            <h2 class="form-title">Inscriptions événements (récentes)</h2>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Événement</th>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentEventRegistrations)): ?>
                            <tr>
                                <td class="dashboard-empty" colspan="6">Aucune inscription événement pour le moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentEventRegistrations as $registration): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string) $registration['event_title']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $registration['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $registration['phone']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $registration['email']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $registration['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $registration['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="dashboard-footer">
            <a href="../Urban Center.html">Retour au site public</a>
            <a href="logout.php">Se déconnecter</a>
        </p>
    </div>
</body>
</html>
