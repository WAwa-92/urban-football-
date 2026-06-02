<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

$pdo = getPDO();

try {
    $stmt = $pdo->prepare('
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
            r.reservation_date,
            r.reservation_time,
            s.name as sport_name,
            t.name as terrain_name,
            t.price_per_hour
        FROM reservations r
        JOIN sports s ON s.id = r.sport_id
        JOIN terrains t ON t.id = r.terrain_id
        ORDER BY r.created_at DESC
    ');
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
