<?php
require __DIR__ . '/config.php';
require __DIR__ . '/../php/csrf.php';
requireAdmin();

$pdo = getPDO();
$message = '';

// --- Mise à jour du statut ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'Session expirée. Merci de réessayer.';
    } else {
    $reservationId = (int) ($_POST['reservation_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $allowedStatuses = ['pending', 'confirmed', 'rejected', 'cancelled'];

    if ($reservationId > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT reservation_slot_id FROM reservations WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $reservationId]);
        $slotId = (int) ($stmt->fetchColumn() ?: 0);

        if ($slotId > 0) {
            $updateReservation = $pdo->prepare('UPDATE reservations SET status = :status WHERE id = :id');
            $updateReservation->execute([':status' => $newStatus, ':id' => $reservationId]);

            $slotStatus = ($newStatus === 'confirmed') ? 'reserved' : 'available';
            $updateSlot = $pdo->prepare('UPDATE reservation_slots SET status = :slot_status WHERE id = :id');
            $updateSlot->execute([':slot_status' => $slotStatus, ':id' => $slotId]);

            $pdo->commit();
            $message = 'Réservation mise à jour.';
        } else {
            $pdo->rollBack();
            $message = 'Réservation introuvable.';
        }
    }
    }
}

// --- Filtres ---
$allowedStatuses = ['pending', 'confirmed', 'rejected', 'cancelled'];
$filterStatus  = isset($_GET['status']) && in_array($_GET['status'], $allowedStatuses, true) ? $_GET['status'] : '';
$filterSport   = isset($_GET['sport'])  ? trim($_GET['sport'])  : '';
$filterSearch  = isset($_GET['search']) ? trim($_GET['search']) : '';

$where  = [];
$params = [];

if ($filterStatus !== '') {
    $where[]  = 'r.status = :status';
    $params[':status'] = $filterStatus;
}
if ($filterSport !== '') {
    $where[]  = 's.name = :sport';
    $params[':sport'] = $filterSport;
}
if ($filterSearch !== '') {
    $where[]  = '(r.first_name LIKE :search OR r.last_name LIKE :search OR r.email LIKE :search OR r.phone LIKE :search)';
    $params[':search'] = '%' . $filterSearch . '%';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// --- Pagination ---
$perPage     = 15;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$offset      = ($currentPage - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM reservations r INNER JOIN sports s ON s.id = r.sport_id $whereClause");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset      = ($currentPage - 1) * $perPage;

$sql = "SELECT r.id, r.first_name, r.last_name, r.phone, r.email, r.players_count, r.comment,
               r.status, r.created_at,
               s.name AS sport_name, t.name AS terrain_name,
               rs.reservation_date, ts.label AS slot_label
        FROM reservations r
        INNER JOIN sports s ON s.id = r.sport_id
        INNER JOIN terrains t ON t.id = r.terrain_id
        INNER JOIN reservation_slots rs ON rs.id = r.reservation_slot_id
        INNER JOIN time_slots ts ON ts.id = rs.time_slot_id
        $whereClause
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$reservations = $stmt->fetchAll();

// Liste des sports pour le filtre
$sports = $pdo->query('SELECT name FROM sports ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);

// Couleurs des badges de statut
$badgeStyle = [
    'pending'   => 'background:#f59e0b;color:#fff;',
    'confirmed' => 'background:#10b981;color:#fff;',
    'rejected'  => 'background:#ef4444;color:#fff;',
    'cancelled' => 'background:#6b7280;color:#fff;',
];
$badgeLabel = [
    'pending'   => 'En attente',
    'confirmed' => 'Confirmée',
    'rejected'  => 'Refusée',
    'cancelled' => 'Annulée',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des réservations</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:24px; flex-wrap:wrap;">
            <div>
                <h1 class="section-title" style="margin-bottom:6px; text-align:left;">Gestion des réservations</h1>
                <p style="color:#aaa; font-size:0.9rem;"><?= $totalRows ?> résultat<?= $totalRows > 1 ? 's' : '' ?> trouvé<?= $totalRows > 1 ? 's' : '' ?></p>
                <?php if ($message !== ''): ?>
                    <p style="color:#10b981; margin-top:6px;"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="bt" href="dashboard.php">Retour dashboard</a>
                <a class="bt" href="events.php">Gérer les événements</a>
                <a class="bt" href="news.php">Gérer les actualités</a>
                <a class="bt" href="export-reservations.php?<?= http_build_query(array_filter([
                    'status' => $filterStatus,
                    'sport' => $filterSport,
                    'search' => $filterSearch,
                ])) ?>">Exporter CSV</a>
            </div>
        </div>

        <!-- Filtres -->
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:24px; align-items:flex-end;">
            <div>
                <label style="display:block; font-size:0.85rem; color:#aaa; margin-bottom:4px;">Recherche</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filterSearch) ?>"
                       placeholder="Nom, email, téléphone…"
                       style="padding:10px 14px; border-radius:8px; border:1px solid #333; background:#1a1a2e; color:#fff; width:220px;">
            </div>
            <div>
                <label style="display:block; font-size:0.85rem; color:#aaa; margin-bottom:4px;">Sport</label>
                <select name="sport" style="padding:10px 14px; border-radius:8px; border:1px solid #333; background:#1a1a2e; color:#fff;">
                    <option value="">Tous les sports</option>
                    <?php foreach ($sports as $sp): ?>
                        <option value="<?= htmlspecialchars($sp) ?>" <?= $filterSport === $sp ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sp) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.85rem; color:#aaa; margin-bottom:4px;">Statut</label>
                <select name="status" style="padding:10px 14px; border-radius:8px; border:1px solid #333; background:#1a1a2e; color:#fff;">
                    <option value="">Tous les statuts</option>
                    <option value="pending"   <?= $filterStatus === 'pending'   ? 'selected' : '' ?>>En attente</option>
                    <option value="confirmed" <?= $filterStatus === 'confirmed' ? 'selected' : '' ?>>Confirmée</option>
                    <option value="rejected"  <?= $filterStatus === 'rejected'  ? 'selected' : '' ?>>Refusée</option>
                    <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                </select>
            </div>
            <button type="submit" class="submit-btn" style="width:auto; padding:10px 22px;">Filtrer</button>
            <?php if ($filterStatus || $filterSport || $filterSearch): ?>
                <a href="reservations.php" style="padding:10px 18px; border-radius:8px; background:#333; color:#fff; text-decoration:none; font-size:0.9rem;">✕ Réinitialiser</a>
            <?php endif; ?>
        </form>

        <!-- Tableau -->
        <div class="contact-form" style="max-width:100%;">
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#1e3c72; color:#fff;">
                            <th style="padding:12px; text-align:left;">Client</th>
                            <th style="padding:12px; text-align:left;">Contact</th>
                            <th style="padding:12px; text-align:left;">Sport</th>
                            <th style="padding:12px; text-align:left;">Terrain</th>
                            <th style="padding:12px; text-align:left;">Date</th>
                            <th style="padding:12px; text-align:left;">Créneau</th>
                            <th style="padding:12px; text-align:left;">Joueurs</th>
                            <th style="padding:12px; text-align:left;">Statut</th>
                            <th style="padding:12px; text-align:left;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservations)): ?>
                            <tr>
                                <td colspan="9" style="padding:24px; text-align:center; color:#aaa;">
                                    Aucune réservation ne correspond aux filtres.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($reservations as $row): ?>
                            <tr style="transition:background 0.15s;" onmouseover="this.style.background='rgba(255,122,24,0.05)'" onmouseout="this.style.background=''">
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee; font-size:0.85rem;">
                                    <?= htmlspecialchars($row['phone']) ?><br>
                                    <span style="color:#aaa;"><?= htmlspecialchars($row['email']) ?></span>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['sport_name']) ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['terrain_name']) ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['reservation_date']) ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['slot_label']) ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee; text-align:center;"><?= (int) $row['players_count'] ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <?php $st = $row['status']; ?>
                                    <span style="<?= $badgeStyle[$st] ?? '' ?> padding:4px 10px; border-radius:20px; font-size:0.8rem; font-weight:600; white-space:nowrap;">
                                        <?= $badgeLabel[$st] ?? htmlspecialchars($st) ?>
                                    </span>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <form method="POST" style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="reservation_id" value="<?= (int) $row['id'] ?>">
                                        <select name="status" style="padding:6px 10px; border-radius:6px; border:1px solid #333; background:#1a1a2e; color:#fff; font-size:0.85rem;">
                                            <option value="pending"   <?= $row['status'] === 'pending'   ? 'selected' : '' ?>>En attente</option>
                                            <option value="confirmed" <?= $row['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmée</option>
                                            <option value="rejected"  <?= $row['status'] === 'rejected'  ? 'selected' : '' ?>>Refusée</option>
                                            <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                                        </select>
                                        <button type="submit" class="submit-btn" style="width:auto; padding:8px 14px; font-size:0.85rem;">Valider</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <?php
                    $queryBase = array_filter([
                        'status' => $filterStatus,
                        'sport'  => $filterSport,
                        'search' => $filterSearch,
                    ]);
                ?>
                <div style="display:flex; justify-content:center; gap:8px; margin-top:24px; flex-wrap:wrap;">
                    <?php if ($currentPage > 1): ?>
                        <a href="?<?= http_build_query(array_merge($queryBase, ['page' => $currentPage - 1])) ?>"
                           style="padding:8px 16px; border-radius:8px; background:#1e3c72; color:#fff; text-decoration:none;">&laquo; Précédent</a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                        <a href="?<?= http_build_query(array_merge($queryBase, ['page' => $p])) ?>"
                           style="padding:8px 14px; border-radius:8px; text-decoration:none;
                                  <?= $p === $currentPage ? 'background:#ff7a18; color:#fff; font-weight:700;' : 'background:#333; color:#fff;' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($queryBase, ['page' => $currentPage + 1])) ?>"
                           style="padding:8px 16px; border-radius:8px; background:#1e3c72; color:#fff; text-decoration:none;">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
                <p style="text-align:center; color:#aaa; font-size:0.85rem; margin-top:10px;">
                    Page <?= $currentPage ?> sur <?= $totalPages ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
