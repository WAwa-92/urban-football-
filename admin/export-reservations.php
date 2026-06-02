<?php
require __DIR__ . '/config.php';
requireAdmin();

$pdo = getPDO();

$allowedStatuses = ['pending', 'confirmed', 'rejected', 'cancelled'];
$filterStatus  = isset($_GET['status']) && in_array($_GET['status'], $allowedStatuses, true) ? $_GET['status'] : '';
$filterSport   = isset($_GET['sport']) ? trim($_GET['sport']) : '';
$filterSearch  = isset($_GET['search']) ? trim($_GET['search']) : '';

$where  = [];
$params = [];

if ($filterStatus !== '') {
    $where[] = 'r.status = :status';
    $params[':status'] = $filterStatus;
}
if ($filterSport !== '') {
    $where[] = 's.name = :sport';
    $params[':sport'] = $filterSport;
}
if ($filterSearch !== '') {
    $where[] = '(r.first_name LIKE :search OR r.last_name LIKE :search OR r.email LIKE :search OR r.phone LIKE :search)';
    $params[':search'] = '%' . $filterSearch . '%';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare(
    "SELECT r.first_name, r.last_name, r.phone, r.email, s.name AS sport_name, t.name AS terrain_name,
            rs.reservation_date, ts.label AS slot_label, r.players_count, r.status, r.created_at
     FROM reservations r
     INNER JOIN sports s ON s.id = r.sport_id
     INNER JOIN terrains t ON t.id = r.terrain_id
     INNER JOIN reservation_slots rs ON rs.id = r.reservation_slot_id
     INNER JOIN time_slots ts ON ts.id = rs.time_slot_id
     $whereClause
     ORDER BY r.created_at DESC"
);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reservations-' . date('Y-m-d_H-i') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Nom', 'Prénom', 'Téléphone', 'Email', 'Sport', 'Terrain', 'Date', 'Créneau', 'Joueurs', 'Statut', 'Créé le'], ';');

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['last_name'],
        $row['first_name'],
        $row['phone'],
        $row['email'],
        $row['sport_name'],
        $row['terrain_name'],
        $row['reservation_date'],
        $row['slot_label'],
        $row['players_count'],
        $row['status'],
        $row['created_at'],
    ], ';');
}

fclose($output);
exit;
