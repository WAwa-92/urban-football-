<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

try {
    $pdo = getPDO();
    ensureSiteEventsTable($pdo);

    $rows = $pdo->query('SELECT id, title, sport_type, date_label, event_date, event_time, location, participants_info, description, detail_1, detail_2, detail_3, cta_label FROM site_events WHERE is_published = 1 ORDER BY display_order ASC, created_at DESC')->fetchAll();

    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([]);
}
