CREATE DATABASE IF NOT EXISTS urban_center
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_unicode_ci;

USE urban_center;

DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS reservation_slots;
DROP TABLE IF EXISTS terrains;
DROP TABLE IF EXISTS sports;
DROP TABLE IF EXISTS time_slots;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS coaches;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS site_events;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS gym_subscriptions;
DROP TABLE IF EXISTS stadium_reservations;

CREATE TABLE sports (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	slug VARCHAR(120) NOT NULL UNIQUE,
	description TEXT NULL,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE terrains (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	sport_id INT UNSIGNED NOT NULL,
	name VARCHAR(100) NOT NULL,
	description TEXT NULL,
	price_per_hour DECIMAL(10,2) NOT NULL DEFAULT 0.00,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_terrains_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	INDEX idx_terrains_sport (sport_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE time_slots (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	start_time TIME NOT NULL,
	end_time TIME NOT NULL,
	label VARCHAR(50) NOT NULL,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY uniq_slot (start_time, end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservation_slots (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	terrain_id INT UNSIGNED NOT NULL,
	reservation_date DATE NOT NULL,
	time_slot_id INT UNSIGNED NOT NULL,
	status ENUM('available', 'reserved', 'blocked') NOT NULL DEFAULT 'available',
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_reservation_slots_terrain FOREIGN KEY (terrain_id) REFERENCES terrains(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_reservation_slots_time_slot FOREIGN KEY (time_slot_id) REFERENCES time_slots(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	UNIQUE KEY uniq_slot_per_day (terrain_id, reservation_date, time_slot_id),
	INDEX idx_slot_date (reservation_date),
	INDEX idx_slot_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservations (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	first_name VARCHAR(100) NOT NULL,
	last_name VARCHAR(100) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	email VARCHAR(150) NOT NULL,
	sport_id INT UNSIGNED NOT NULL,
	terrain_id INT UNSIGNED NOT NULL,
	reservation_slot_id INT UNSIGNED NOT NULL,
	players_count TINYINT UNSIGNED NOT NULL,
	comment TEXT NULL,
	status ENUM('pending', 'confirmed', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_reservations_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_reservations_terrain FOREIGN KEY (terrain_id) REFERENCES terrains(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_reservations_slot FOREIGN KEY (reservation_slot_id) REFERENCES reservation_slots(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	UNIQUE KEY uniq_reservation_slot (reservation_slot_id),
	INDEX idx_reservation_status (status),
	INDEX idx_reservation_phone (phone),
	INDEX idx_reservation_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_users (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	full_name VARCHAR(120) NOT NULL,
	email VARCHAR(150) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	role ENUM('admin', 'manager', 'super_admin') NOT NULL DEFAULT 'admin',
	status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
	last_login_at TIMESTAMP NULL DEFAULT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	full_name VARCHAR(120) NOT NULL,
	email VARCHAR(150) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	role ENUM('user', 'coach') NOT NULL DEFAULT 'user',
	status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
	last_login_at TIMESTAMP NULL DEFAULT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_users_role (role),
	INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE coaches (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED NOT NULL,
	specialty VARCHAR(120) NULL,
	bio TEXT NULL,
	years_experience TINYINT UNSIGNED NULL,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_coaches_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE KEY uniq_coach_user (user_id),
	INDEX idx_coaches_specialty (specialty),
	INDEX idx_coaches_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE site_events (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(180) NOT NULL,
	sport_type ENUM('football', 'padel', 'fitness', 'women', 'tennis', 'multi', 'other') NOT NULL DEFAULT 'multi',
	date_label VARCHAR(120) NOT NULL,
	event_date DATE NULL,
	event_time VARCHAR(50) NULL,
	location VARCHAR(180) NULL,
	participants_info VARCHAR(180) NULL,
	description TEXT NOT NULL,
	detail_1 VARCHAR(180) NULL,
	detail_2 VARCHAR(180) NULL,
	detail_3 VARCHAR(180) NULL,
	cta_label VARCHAR(80) NOT NULL DEFAULT 'S''inscrire',
	is_published TINYINT(1) NOT NULL DEFAULT 1,
	display_order INT NOT NULL DEFAULT 0,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_site_events_published (is_published),
	INDEX idx_site_events_order (display_order),
	INDEX idx_site_events_sport (sport_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE news (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(180) NOT NULL,
	content TEXT NOT NULL,
	image_url VARCHAR(255) NULL,
	published_at DATETIME NULL,
	is_published TINYINT(1) NOT NULL DEFAULT 1,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_news_published (is_published),
	INDEX idx_news_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sports (id, name, slug, description, is_active) VALUES
(1, 'Football', 'football', 'Réservation de terrains de football.', 1),
(3, 'Padel', 'padel', 'Réservation de terrains de padel.', 1),
(4, 'Fitness', 'fitness', 'Abonnements et accès à la salle de fitness.', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), is_active = VALUES(is_active);

UPDATE sports SET is_active = 0 WHERE slug = 'tennis';

INSERT INTO terrains (sport_id, name, description, price_per_hour) VALUES
(1, 'Terrain Football', 'Terrain 5v5 principal', 60.00),
(3, 'Court Padel', 'Terrain de padel moderne', 50.00),
(4, 'Salle Fitness', 'Accès salle fitness', 30.00)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), price_per_hour = VALUES(price_per_hour);

INSERT INTO time_slots (start_time, end_time, label) VALUES
('08:00:00', '09:00:00', '08h00 - 09h00'),
('09:00:00', '10:00:00', '09h00 - 10h00'),
('10:00:00', '11:00:00', '10h00 - 11h00'),
('11:00:00', '12:00:00', '11h00 - 12h00'),
('12:00:00', '13:00:00', '12h00 - 13h00'),
('13:00:00', '14:00:00', '13h00 - 14h00'),
('14:00:00', '15:00:00', '14h00 - 15h00'),
('15:00:00', '16:00:00', '15h00 - 16h00'),
('16:00:00', '17:00:00', '16h00 - 17h00'),
('17:00:00', '18:00:00', '17h00 - 18h00'),
('18:00:00', '19:00:00', '18h00 - 19h00'),
('19:00:00', '20:00:00', '19h00 - 20h00')
ON DUPLICATE KEY UPDATE label = VALUES(label);

INSERT INTO site_events (title, sport_type, date_label, event_date, event_time, location, participants_info, description, detail_1, detail_2, detail_3, cta_label, is_published, display_order) VALUES
('Tournoi Hebdomadaire 5v5', 'football', 'Chaque vendredi', NULL, '19h00', 'Terrain de football Urban Center', '4 équipes · 5 joueurs/équipe', 'Compétition de football en salle pour équipes locales avec trophée mensuel pour les finalistes.', '📅 Tous les vendredis à 19h00', '👥 4 équipes · 5 joueurs/équipe', '📍 Terrain de football Urban Center', 'S''inscrire', 1, 1),
('Soirée Padel Open', 'padel', 'Samedis', NULL, '18h00', 'Court de Padel Urban Center', 'Duos · 2v2', 'Matchs et inscriptions pour joueurs de padel de tous niveaux en format double élimination.', '📅 Chaque samedi à 18h00', '👥 Duos · 2v2', '📍 Court de Padel Urban Center', 'S''inscrire', 1, 2),
('Fitness Challenge Mensuel', 'fitness', '1er dimanche / mois', NULL, '10h00', 'Salle de Fitness Urban Center', 'Ouvert à tous les abonnés', 'Session d''entraînement collectif et défi sportif animé par le coach avec classement mensuel.', '📅 1er dimanche du mois à 10h00', '👥 Ouvert à tous les abonnés', '📍 Salle de Fitness Urban Center', 'S''inscrire', 1, 3),
('Urban Beach Fitness Women', 'women', 'Chaque dimanche', NULL, '08h00', 'Zone plage Urban Beach Fitness', 'Exclusivement pour femmes', 'Événement fitness en bord de plage exclusivement réservé aux femmes, avec séance encadrée et ambiance conviviale.', '📅 Dimanche à 08h00', '👩 Réservé aux femmes', '📍 Zone plage Urban Beach Fitness', 'S''inscrire', 1, 0);

UPDATE site_events SET is_published = 0 WHERE sport_type = 'tennis';

CREATE TABLE stadium_reservations (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	team_name VARCHAR(100) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	reservation_date DATE NOT NULL,
	reservation_time TIME NOT NULL,
	message TEXT NULL,
	status ENUM('en_attente', 'confirmee', 'annulee') NOT NULL DEFAULT 'en_attente',
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_reservation_date (reservation_date),
	INDEX idx_reservation_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gym_subscriptions (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	full_name VARCHAR(100) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	subscription_type ENUM('mensuel', 'trimestriel', 'annuel') NOT NULL,
	message TEXT NULL,
	status ENUM('en_attente', 'acceptee', 'refusee') NOT NULL DEFAULT 'en_attente',
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_subscription_phone (phone),
	INDEX idx_subscription_type (subscription_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	email VARCHAR(150) NOT NULL,
	message TEXT NOT NULL,
	status ENUM('nouveau', 'lu', 'traite') NOT NULL DEFAULT 'nouveau',
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_contact_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
