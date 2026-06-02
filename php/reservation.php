<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';
require __DIR__ . '/send_email.php';
require __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Méthode non autorisée.']));
}

if (!empty($_POST['website'] ?? '')) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Spam détecté.']));
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    exit(json_encode(['success' => false, 'message' => 'Session expirée. Merci de recharger la page.']));
}

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$sportId = (int) ($_POST['sport_id'] ?? 0);
$terrainId = (int) ($_POST['terrain_id'] ?? 0);
$reservationDate = $_POST['reservation_date'] ?? '';
$reservationTime = $_POST['reservation_time'] ?? '';
$playersCount = (int) ($_POST['players_count'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($firstName === '' || $lastName === '' || $phone === '' || $email === '' || $sportId <= 0 || $terrainId <= 0 || $reservationDate === '' || $reservationTime === '' || $playersCount <= 0) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires.']));
}

$pdo = getPDO();
$reservationId = null;

try {
    $pdo->beginTransaction();

    $timeStmt = $pdo->prepare('SELECT id FROM time_slots WHERE start_time = :start_time AND is_active = 1 LIMIT 1');
    $timeStmt->execute([':start_time' => $reservationTime . ':00']);
    $timeSlotId = (int) ($timeStmt->fetchColumn() ?: 0);

    if ($timeSlotId <= 0) {
        throw new RuntimeException('Créneau invalide.');
    }

    $slotStmt = $pdo->prepare('SELECT id, status FROM reservation_slots WHERE terrain_id = :terrain_id AND reservation_date = :reservation_date AND time_slot_id = :time_slot_id LIMIT 1 FOR UPDATE');
    $slotStmt->execute([
        ':terrain_id' => $terrainId,
        ':reservation_date' => $reservationDate,
        ':time_slot_id' => $timeSlotId,
    ]);
    $slot = $slotStmt->fetch();

    if (!$slot) {
        $createSlot = $pdo->prepare('INSERT INTO reservation_slots (terrain_id, reservation_date, time_slot_id, status) VALUES (:terrain_id, :reservation_date, :time_slot_id, "available")');
        $createSlot->execute([
            ':terrain_id' => $terrainId,
            ':reservation_date' => $reservationDate,
            ':time_slot_id' => $timeSlotId,
        ]);
        $slotId = (int) $pdo->lastInsertId();
        $slotStatus = 'available';
    } else {
        $slotId = (int) $slot['id'];
        $slotStatus = $slot['status'];
    }

    if ($slotStatus !== 'available') {
        throw new RuntimeException('Ce créneau est déjà réservé.');
    }

    $reservationStmt = $pdo->prepare('INSERT INTO reservations (first_name, last_name, phone, email, sport_id, terrain_id, reservation_slot_id, players_count, comment, status) VALUES (:first_name, :last_name, :phone, :email, :sport_id, :terrain_id, :reservation_slot_id, :players_count, :comment, "confirmed")');
    $reservationStmt->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':phone' => $phone,
        ':email' => $email,
        ':sport_id' => $sportId,
        ':terrain_id' => $terrainId,
        ':reservation_slot_id' => $slotId,
        ':players_count' => $playersCount,
        ':comment' => $comment !== '' ? $comment : null,
    ]);

    $reservationId = (int) $pdo->lastInsertId();

    $updateSlot = $pdo->prepare('UPDATE reservation_slots SET status = "reserved" WHERE id = :id');
    $updateSlot->execute([':id' => $slotId]);

    $terrainStmt = $pdo->prepare('SELECT t.name, t.price_per_hour, s.name as sport_name FROM terrains t JOIN sports s ON s.id = t.sport_id WHERE t.id = :id');
    $terrainStmt->execute([':id' => $terrainId]);
    $terrainData = $terrainStmt->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();

    if ($terrainData) {
        sendConfirmationEmail(
            $email,
            $firstName,
            $lastName,
            $terrainData['sport_name'],
            $terrainData['name'],
            $reservationDate,
            $reservationTime,
            $terrainData['price_per_hour'],
            $playersCount,
            $reservationId
        );
    }

    http_response_code(201);
    exit(json_encode([
        'success' => true,
        'message' => 'Réservation confirmée avec succès.',
        'reservation_id' => $reservationId,
    ]));

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
