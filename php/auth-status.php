<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$scriptDir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
$basePath = preg_replace('#/php$#', '', $scriptDir);
$basePath = is_string($basePath) && $basePath !== '' ? $basePath : '';

$siteUser = $_SESSION['site_user'] ?? null;
$adminUser = $_SESSION['admin_user'] ?? null;

if (is_array($adminUser) && !empty($adminUser['id'])) {
    $role = strtolower(trim((string) ($adminUser['role'] ?? 'admin')));
    $role = str_replace([' ', '-'], '_', $role);
    $isContentManager = $role === 'content_manager';

    echo json_encode([
        'logged_in' => true,
        'user_type' => 'admin',
        'full_name' => (string) ($adminUser['full_name'] ?? ''),
        'role' => (string) ($adminUser['role'] ?? 'admin'),
        'dashboard_url' => $isContentManager
            ? $basePath . '/social-cms/dashboard.php'
            : $basePath . '/admin/dashboard.php',
        'site_dashboard_url' => $basePath . '/admin/dashboard.php',
        'cms_dashboard_url' => $basePath . '/social-cms/dashboard.php',
        'logout_url' => $isContentManager
            ? $basePath . '/social-cms/admin/logout.php'
            : $basePath . '/admin/logout.php',
    ]);
    exit;
}

if (!is_array($siteUser) || empty($siteUser['id'])) {
    echo json_encode([
        'logged_in' => false,
    ]);
    exit;
}

echo json_encode([
    'logged_in' => true,
    'user_type' => 'site',
    'full_name' => (string) ($siteUser['full_name'] ?? ''),
    'role' => (string) ($siteUser['role'] ?? 'user'),
    'dashboard_url' => ($siteUser['role'] ?? '') === 'coach'
        ? $basePath . '/pages/coach-dashboard.php'
        : $basePath . '/pages/my-reservations.html',
    'logout_url' => $basePath . '/pages/logout.php',
]);
