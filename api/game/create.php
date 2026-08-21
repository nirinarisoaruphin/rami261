<?php
// api/game/create.php
require_once '../../includes/config.php';
require_once '../../includes/GameManager.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$bet = $input['bet_amount'] ?? 1.00;

$gameManager = new GameManager();
$roomCode = $gameManager->createGame($_SESSION['user_id'], $bet);

if ($roomCode) {
    $game = $pdo->prepare("SELECT id FROM games WHERE room_code = ?");
    $game->execute([$roomCode]);
    $gameId = $game->fetch()['id'];
    
    echo json_encode([
        'success' => true,
        'game_id' => $gameId,
        'room_code' => $roomCode
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la création']);
}