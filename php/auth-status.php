<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$siteUser = $_SESSION['site_user'] ?? null;

if (!is_array($siteUser) || empty($siteUser['id'])) {
    echo json_encode([
        'logged_in' => false,
    ]);
    exit;
}

echo json_encode([
    'logged_in' => true,
    'full_name' => (string) ($siteUser['full_name'] ?? ''),
    'role' => (string) ($siteUser['role'] ?? 'user'),
]);
