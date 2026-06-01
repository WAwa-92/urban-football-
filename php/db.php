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

function ensureSiteEventsTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS site_events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            sport_type ENUM('football', 'padel', 'fitness', 'tennis', 'multi', 'other') NOT NULL DEFAULT 'multi',
            date_label VARCHAR(120) NOT NULL,
            event_date DATE NULL,
            event_time VARCHAR(50) NULL,
            location VARCHAR(180) NULL,
            participants_info VARCHAR(180) NULL,
            description TEXT NOT NULL,
            detail_1 VARCHAR(180) NULL,
            detail_2 VARCHAR(180) NULL,
            detail_3 VARCHAR(180) NULL,
            cta_label VARCHAR(80) NOT NULL DEFAULT 'S\'inscrire',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            display_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_site_events_published (is_published),
            INDEX idx_site_events_order (display_order),
            INDEX idx_site_events_sport (sport_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $count = (int) $pdo->query('SELECT COUNT(*) FROM site_events')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO site_events (title, sport_type, date_label, event_date, event_time, location, participants_info, description, detail_1, detail_2, detail_3, cta_label, is_published, display_order)
         VALUES (:title, :sport_type, :date_label, :event_date, :event_time, :location, :participants_info, :description, :detail_1, :detail_2, :detail_3, :cta_label, :is_published, :display_order)'
    );

    $defaults = [
        [
            'title' => 'Tournoi Hebdomadaire 5v5',
            'sport_type' => 'football',
            'date_label' => 'Chaque vendredi',
            'event_date' => null,
            'event_time' => '19h00',
            'location' => 'Terrain de football Urban Center',
            'participants_info' => '4 équipes · 5 joueurs/équipe',
            'description' => 'Compétition de football en salle pour équipes locales avec trophée mensuel pour les finalistes.',
            'detail_1' => '📅 Tous les vendredis à 19h00',
            'detail_2' => '👥 4 équipes · 5 joueurs/équipe',
            'detail_3' => '📍 Terrain de football Urban Center',
            'cta_label' => 'S\'inscrire',
            'is_published' => 1,
            'display_order' => 1,
        ],
        [
            'title' => 'Soirée Padel Open',
            'sport_type' => 'padel',
            'date_label' => 'Samedis',
            'event_date' => null,
            'event_time' => '18h00',
            'location' => 'Court de Padel Urban Center',
            'participants_info' => 'Duos · 2v2',
            'description' => 'Matchs et inscriptions pour joueurs de padel de tous niveaux en format double élimination.',
            'detail_1' => '📅 Chaque samedi à 18h00',
            'detail_2' => '👥 Duos · 2v2',
            'detail_3' => '📍 Court de Padel Urban Center',
            'cta_label' => 'S\'inscrire',
            'is_published' => 1,
            'display_order' => 2,
        ],
        [
            'title' => 'Fitness Challenge Mensuel',
            'sport_type' => 'fitness',
            'date_label' => '1er dimanche / mois',
            'event_date' => null,
            'event_time' => '10h00',
            'location' => 'Salle de Fitness Urban Center',
            'participants_info' => 'Ouvert à tous les abonnés',
            'description' => 'Session d\'entraînement collectif et défi sportif animé par le coach avec classement mensuel.',
            'detail_1' => '📅 1er dimanche du mois à 10h00',
            'detail_2' => '👥 Ouvert à tous les abonnés',
            'detail_3' => '📍 Salle de Fitness Urban Center',
            'cta_label' => 'S\'inscrire',
            'is_published' => 1,
            'display_order' => 3,
        ],
        [
            'title' => 'Tournoi Tennis Amateur',
            'sport_type' => 'tennis',
            'date_label' => 'Mensuel',
            'event_date' => null,
            'event_time' => null,
            'location' => 'Court de Tennis Urban Center',
            'participants_info' => 'Individuel · Tous niveaux',
            'description' => 'Compétition amicale en simple pour les membres et visiteurs du complexe avec tirage au sort.',
            'detail_1' => '📅 Dernier dimanche du mois',
            'detail_2' => '👥 Individuel · Tous niveaux',
            'detail_3' => '📍 Court de Tennis Urban Center',
            'cta_label' => 'S\'inscrire',
            'is_published' => 1,
            'display_order' => 4,
        ],
    ];

    foreach ($defaults as $event) {
        $stmt->execute($event);
    }
}
