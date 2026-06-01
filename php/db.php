<?php
function getPDO(): PDO
{
    $dbName = 'urban_center';

    $connections = [
        ['host' => '127.0.0.1', 'port' => '8889', 'user' => 'root', 'password' => 'root'],
        ['host' => 'localhost', 'port' => '8889', 'user' => 'root', 'password' => 'root'],
        ['host' => '127.0.0.1', 'port' => '3306', 'user' => 'root', 'password' => ''],
        ['host' => 'localhost', 'port' => '3306', 'user' => 'root', 'password' => ''],
    ];

    $lastError = null;

    foreach ($connections as $config) {
        try {
            $serverDsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
            $serverPdo = new PDO($serverDsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $serverPdo->exec("CREATE DATABASE IF NOT EXISTS {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $dbDsn = "mysql:host={$config['host']};port={$config['port']};dbname={$dbName};charset=utf8mb4";
            return new PDO($dbDsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            $lastError = $e->getMessage();
        }
    }

    http_response_code(500);
    exit('Connexion à la base impossible. Vérifiez MAMP/MySQL et les identifiants dans php/db.php. Détail: ' . $lastError);
}
