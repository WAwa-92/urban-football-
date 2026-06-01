<?php
require __DIR__ . '/config.php';
requireAdmin();

$pdo = getPDO();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

$reservations = $pdo->query('SELECT r.id, r.first_name, r.last_name, r.phone, r.email, r.players_count, r.comment, r.status, r.created_at, s.name AS sport_name, t.name AS terrain_name, rs.reservation_date, ts.label AS slot_label FROM reservations r INNER JOIN sports s ON s.id = r.sport_id INNER JOIN terrains t ON t.id = r.terrain_id INNER JOIN reservation_slots rs ON rs.id = r.reservation_slot_id INNER JOIN time_slots ts ON ts.id = rs.time_slot_id ORDER BY r.created_at DESC')->fetchAll();
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
        <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:30px; flex-wrap:wrap;">
            <div>
                <h1 class="section-title" style="margin-bottom:10px; text-align:left;">Gestion des réservations</h1>
                <?php if ($message !== ''): ?>
                    <p><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
            </div>
            <a class="bt" href="dashboard.php">Retour dashboard</a>
        </div>

        <div class="contact-form" style="max-width: 100%;">
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse: collapse;">
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
                        <?php foreach ($reservations as $row): ?>
                            <tr>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['phone'] . ' / ' . $row['email']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['sport_name']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['terrain_name']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['reservation_date']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['slot_label']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['players_count']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($row['status']); ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <form method="POST" style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <input type="hidden" name="reservation_id" value="<?php echo (int) $row['id']; ?>">
                                        <select name="status" required>
                                            <option value="pending">pending</option>
                                            <option value="confirmed">confirmed</option>
                                            <option value="rejected">rejected</option>
                                            <option value="cancelled">cancelled</option>
                                        </select>
                                        <button type="submit" class="submit-btn" style="width:auto; padding:10px 16px;">Mettre à jour</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
