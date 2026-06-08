<?php
require_once __DIR__ . '/../../social-cms/config.php';

cmsEnsureManagerAccess();

$wantsJson = (($_GET['format'] ?? '') === 'json')
    || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
    || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
}

function uploadMediaRespond(bool $wantsJson, int $statusCode, array $payload): void
{
    http_response_code($statusCode);

    if ($wantsJson) {
        echo json_encode($payload);
        exit;
    }

    $redirect = trim($_POST['redirect_to'] ?? '/Urban-Center-main/social-cms/pages/media-library.php');
    $separator = str_contains($redirect, '?') ? '&' : '?';

    if (($payload['success'] ?? false) === true) {
        header('Location: ' . $redirect . $separator . 'upload=ok');
    } else {
        $message = rawurlencode((string) ($payload['error'] ?? 'Erreur inconnue'));
        header('Location: ' . $redirect . $separator . 'upload=error&message=' . $message);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    uploadMediaRespond($wantsJson, 405, ['error' => 'Méthode non autorisée']);
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    uploadMediaRespond($wantsJson, 419, ['error' => 'Session expirée']);
}

if (empty($_FILES['media_file']) || !is_uploaded_file($_FILES['media_file']['tmp_name'])) {
    uploadMediaRespond($wantsJson, 422, ['error' => 'Aucun fichier reçu']);
}

$file = $_FILES['media_file'];
$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? 'Général');
$maxSize = 50 * 1024 * 1024;

if ($file['size'] > $maxSize) {
    uploadMediaRespond($wantsJson, 422, ['error' => 'Fichier trop volumineux (50 Mo max)']);
}

$mime = mime_content_type($file['tmp_name']) ?: 'application/octet-stream';
$type = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : (str_contains($mime, 'pdf') ? 'document' : 'other'));

if ($type === 'other') {
    uploadMediaRespond($wantsJson, 422, ['error' => 'Type de fichier non supporté. Utilisez image, vidéo ou PDF.']);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$safeName = uniqid('media_', true) . ($extension ? '.' . strtolower($extension) : '');

$relativeDir = 'social-cms/uploads';
$absoluteDir = __DIR__ . '/../uploads';

if (!is_dir($absoluteDir)) {
    mkdir($absoluteDir, 0775, true);
}

$destination = $absoluteDir . DIRECTORY_SEPARATOR . $safeName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    uploadMediaRespond($wantsJson, 500, ['error' => 'Impossible d’enregistrer le fichier']);
}

$pdo = cmsPdo();
$stmt = $pdo->prepare('INSERT INTO cms_media_library (title, original_name, file_name, file_path, file_type, category, file_size, uploaded_by) VALUES (:title, :original_name, :file_name, :file_path, :file_type, :category, :file_size, :uploaded_by)');
$stmt->execute([
    ':title' => $title !== '' ? $title : pathinfo($file['name'], PATHINFO_FILENAME),
    ':original_name' => $file['name'],
    ':file_name' => $safeName,
    ':file_path' => $relativeDir . '/' . $safeName,
    ':file_type' => $type,
    ':category' => $category !== '' ? $category : 'Général',
    ':file_size' => (int) $file['size'],
    ':uploaded_by' => $_SESSION['admin_user']['email'] ?? null,
]);

uploadMediaRespond($wantsJson, 200, [
    'success' => true,
    'title' => $title,
    'file_path' => $relativeDir . '/' . $safeName,
    'file_type' => $type,
]);
