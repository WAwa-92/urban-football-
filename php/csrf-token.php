<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/csrf.php';

echo json_encode([
    'success' => true,
    'token' => generateCsrfToken(),
]);
