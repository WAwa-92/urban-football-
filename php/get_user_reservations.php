<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

$pdo = getPDO();

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name');
    $stmt->execute([
        ':table_name' => $table,
        ':column_name' => $column,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

try {
    $hasSlotId = columnExists($pdo, 'reservations', 'reservation_slot_id');
    $hasLegacyDate = columnExists($pdo, 'reservations', 'reservation_date');
    $hasLegacyTime = columnExists($pdo, 'reservations', 'reservation_time');

    if ($hasSlotId) {
        $dateExpr = $hasLegacyDate ? 'COALESCE(rs.reservation_date, r.reservation_date, DATE(r.created_at))' : 'COALESCE(rs.reservation_date, DATE(r.created_at))';
        $timeExpr = $hasLegacyTime ? "COALESCE(DATE_FORMAT(ts.start_time, '%H:%i'), TIME_FORMAT(r.reservation_time, '%H:%i'), '08:00')" : "COALESCE(DATE_FORMAT(ts.start_time, '%H:%i'), '08:00')";

        $sql = "
            SELECT
                r.id,
                r.first_name,
                r.last_name,
                r.phone,
                r.email,
                r.sport_id,
                r.terrain_id,
                r.players_count,
                r.comment,
                r.status,
                r.created_at,
                {$dateExpr} AS reservation_date,
                {$timeExpr} AS reservation_time,
                s.name AS sport_name,
                s.slug AS sport_slug,
                s.is_active AS sport_is_active,
                t.name AS terrain_name,
                t.price_per_hour
            FROM reservations r
            JOIN sports s ON s.id = r.sport_id
            JOIN terrains t ON t.id = r.terrain_id
            LEFT JOIN reservation_slots rs ON rs.id = r.reservation_slot_id
            LEFT JOIN time_slots ts ON ts.id = rs.time_slot_id
            ORDER BY r.created_at DESC
        ";
    } else {
        $dateExpr = $hasLegacyDate ? 'r.reservation_date' : 'DATE(r.created_at)';
        $timeExpr = $hasLegacyTime ? "TIME_FORMAT(r.reservation_time, '%H:%i')" : "'08:00'";

        $sql = "
            SELECT
                r.id,
                r.first_name,
                r.last_name,
                r.phone,
                r.email,
                r.sport_id,
                r.terrain_id,
                r.players_count,
                r.comment,
                r.status,
                r.created_at,
                {$dateExpr} AS reservation_date,
                {$timeExpr} AS reservation_time,
                s.name AS sport_name,
                s.slug AS sport_slug,
                s.is_active AS sport_is_active,
                t.name AS terrain_name,
                t.price_per_hour
            FROM reservations r
            JOIN sports s ON s.id = r.sport_id
            JOIN terrains t ON t.id = r.terrain_id
            ORDER BY r.created_at DESC
        ";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    exit(json_encode([
        'success' => true,
        'reservations' => $reservations,
    ]));

} catch (Throwable $e) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]));
}
?>
