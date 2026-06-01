<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/db.php';

$terrainId = (int) ($_GET['terrain_id'] ?? 0);
$date      = trim($_GET['date'] ?? '');

if ($terrainId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode([]);
    exit;
}

$today = (new DateTimeImmutable())->setTime(0, 0)->format('Y-m-d');
if ($date < $today) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getPDO();

    $stmt = $pdo->prepare('
        SELECT ts.start_time
        FROM reservation_slots rs
        JOIN time_slots ts ON ts.id = rs.time_slot_id
        WHERE rs.terrain_id       = :terrain_id
          AND rs.reservation_date = :date
          AND rs.status           IN ("reserved", "blocked")
    ');
    $stmt->execute([
        ':terrain_id' => $terrainId,
        ':date'       => $date,
    ]);

    $reserved = array_map(
        fn($row) => substr($row['start_time'], 0, 5),
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

    echo json_encode(array_values($reserved));

} catch (Throwable $e) {
    echo json_encode([]);
}
