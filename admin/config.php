<?php
require __DIR__ . '/../php/db.php';

session_start();

function ensureDefaultAdmin(PDO $pdo): void
{
    ensureSiteEventsTable($pdo);

    $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();

    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO admin_users (full_name, email, password_hash, role, status) VALUES (:full_name, :email, :password_hash, :role, :status)');
        $stmt->execute([
            ':full_name' => 'Administrateur',
            ':email' => 'admin@urbancenter.com',
            ':password_hash' => password_hash('Admin123!', PASSWORD_DEFAULT),
            ':role' => 'super_admin',
            ':status' => 'active',
        ]);
    }
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_user'])) {
        header('Location: login.php');
        exit;
    }
}
