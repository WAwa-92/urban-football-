<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Méthode non autorisée.']));
}

$input = json_decode(file_get_contents('php://input'), true);
$reservationId = (int) ($input['reservation_id'] ?? 0);

if ($reservationId <= 0) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'ID de réservation invalide.']));
}

$pdo = getPDO();

try {
    $checkStmt = $pdo->prepare('SELECT id, status FROM reservations WHERE id = :id LIMIT 1');
    $checkStmt->execute([':id' => $reservationId]);
    $reservation = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        http_response_code(404);
        exit(json_encode(['success' => false, 'message' => 'Réservation non trouvée.']));
    }

    if ($reservation['status'] === 'cancelled') {
        http_response_code(400);
        exit(json_encode(['success' => false, 'message' => 'Cette réservation est déjà annulée.']));
    }

    $pdo->beginTransaction();

    $updateStmt = $pdo->prepare('UPDATE reservations SET status = "cancelled" WHERE id = :id');
    $updateStmt->execute([':id' => $reservationId]);

    $releaseStmt = $pdo->prepare('UPDATE reservation_slots SET status = "available" WHERE id IN (SELECT reservation_slot_id FROM reservations WHERE id = :id)');
    $releaseStmt->execute([':id' => $reservationId]);

    $pdo->commit();

    http_response_code(200);
    exit(json_encode([
        'success' => true,
        'message' => 'Réservation annulée avec succès.',
    ]));

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?>
