<?php
require_once __DIR__ . '/../../social-cms/config.php';

cmsEnsureManagerAccess();

header('Content-Type: application/json; charset=utf-8');
$pdo = cmsPdo();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $pdo->query('SELECT * FROM cms_editorial_calendar ORDER BY scheduled_date ASC, id DESC')->fetchAll();
    echo json_encode($rows);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (in_array($method, ['POST', 'DELETE'], true) && !isValidCsrfToken($payload['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['error' => 'Session expirée']);
    exit;
}

if ($method === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO cms_editorial_calendar (title, content, platform, activity, audience, scheduled_date, scheduled_time, status, created_by) VALUES (:title, :content, :platform, :activity, :audience, :scheduled_date, :scheduled_time, :status, :created_by)');
    $stmt->execute([
        ':title' => trim($payload['title'] ?? ''),
        ':content' => trim($payload['content'] ?? ''),
        ':platform' => trim($payload['platform'] ?? 'Instagram'),
        ':activity' => trim($payload['activity'] ?? 'Activité'),
        ':audience' => trim($payload['audience'] ?? 'Public'),
        ':scheduled_date' => $payload['scheduled_date'] ?? date('Y-m-d'),
        ':scheduled_time' => $payload['scheduled_time'] ?: null,
        ':status' => 'scheduled',
        ':created_by' => $_SESSION['admin_user']['email'] ?? null,
    ]);

    echo json_encode(['success' => true, 'id' => (int) $pdo->lastInsertId()]);
    exit;
}

if (in_array($method, ['PUT', 'PATCH'], true)) {
    $id = (int) ($payload['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'Identifiant invalide']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE cms_editorial_calendar
        SET title = :title,
            content = :content,
            platform = :platform,
            activity = :activity,
            audience = :audience,
            scheduled_date = :scheduled_date,
            scheduled_time = :scheduled_time,
            status = :status
        WHERE id = :id');

    $stmt->execute([
        ':id' => $id,
        ':title' => trim($payload['title'] ?? ''),
        ':content' => trim($payload['content'] ?? ''),
        ':platform' => trim($payload['platform'] ?? 'Instagram'),
        ':activity' => trim($payload['activity'] ?? 'Activité'),
        ':audience' => trim($payload['audience'] ?? 'Public'),
        ':scheduled_date' => $payload['scheduled_date'] ?? date('Y-m-d'),
        ':scheduled_time' => $payload['scheduled_time'] ?: null,
        ':status' => trim($payload['status'] ?? 'draft'),
    ]);

    echo json_encode(['success' => true, 'updated' => $stmt->rowCount() > 0]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int) ($payload['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM cms_editorial_calendar WHERE id = :id');
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Méthode non autorisée']);
