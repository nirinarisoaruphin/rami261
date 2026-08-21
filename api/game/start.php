<?php
// api/game/start.php
require_once '../../includes/config.php';
require_once '../../includes/GameManager.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, [], 'Non authentifié');
}

$input = json_decode(file_get_contents('php://input'), true);
$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;

if (!$gameId) {
    jsonResponse(false, [], 'ID de partie manquant');
}

$gameManager = new GameManager($gameId);

// Vérifier que l'utilisateur est le host
$gameData = $gameManager->getGameData();
if ($gameData['host_id'] != $_SESSION['user_id']) {
    jsonResponse(false, [], 'Seul le propriétaire peut démarrer la partie');
}

$success = $gameManager->startGame();
if ($success) {
    jsonResponse(true, ['message' => 'Partie démarrée']);
} else {
    jsonResponse(false, [], 'Impossible de démarrer la partie');
}