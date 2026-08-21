<?php
// api/game/join.php
require_once '../../includes/config.php';
require_once '../../includes/GameManager.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$roomCode = strtoupper($input['room_code'] ?? '');

if (strlen($roomCode) !== 6) {
    echo json_encode(['success' => false, 'error' => 'Code invalide']);
    exit;
}

$gameManager = new GameManager();
$success = $gameManager->joinGame($roomCode, $_SESSION['user_id']);

if ($success) {
    $game = $pdo->prepare("SELECT id FROM games WHERE room_code = ?");
    $game->execute([$roomCode]);
    $gameId = $game->fetch()['id'];
    
    echo json_encode([
        'success' => true,
        'game_id' => $gameId,
        'room_code' => $roomCode
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Impossible de rejoindre la partie']);
}