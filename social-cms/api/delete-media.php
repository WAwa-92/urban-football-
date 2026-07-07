<?php
require_once __DIR__ . '/../../social-cms/config.php';

cmsEnsureManagerAccess();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (!isValidCsrfToken($payload['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['error' => 'Session expirée. Merci de recharger la page.']);
    exit;
}

$id = (int) ($payload['id'] ?? 0);
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Identifiant invalide.']);
    exit;
}

$pdo = cmsPdo();
$stmt = $pdo->prepare('SELECT file_path FROM cms_media_library WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$media = $stmt->fetch();

if (!$media) {
    http_response_code(404);
    echo json_encode(['error' => 'Média introuvable.']);
    exit;
}

// Supprimer le fichier physique si il existe
$filePath = dirname(__DIR__, 2) . '/' . ltrim((string) $media['file_path'], '/');
if (is_file($filePath)) {
    @unlink($filePath);
}

$del = $pdo->prepare('DELETE FROM cms_media_library WHERE id = :id');
$del->execute([':id' => $id]);

echo json_encode(['success' => true]);
