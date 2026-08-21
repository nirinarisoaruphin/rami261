<?php
// ajax/game_status.php
require_once '../includes/config.php';
require_once '../includes/GameManager.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;

if (!$gameId) {
    echo json_encode(['success' => false, 'error' => 'ID de partie manquant']);
    exit;
}

$gameManager = new GameManager($gameId);
$gameData = $gameManager->getGameData();

if (!$gameData) {
    echo json_encode(['success' => false, 'error' => 'Partie non trouvée']);
    exit;
}

$state = $gameManager->getGameState($_SESSION['user_id']);

echo json_encode([
    'success' => true,
    'status' => $gameData['status'],
    'current_turn' => $gameData['current_turn'] ?? 0,
    'players' => $state['players'],
    'is_my_turn' => $state['is_my_turn'],
    'game' => $gameData
]);
exit;