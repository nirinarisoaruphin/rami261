<?php
// api/game/state.php
require_once '../../includes/config.php';
require_once '../../includes/GameManager.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, [], 'Non authentifié');
}

$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;
if (!$gameId) {
    jsonResponse(false, [], 'ID de partie manquant');
}

$gameManager = new GameManager($gameId);
$gameData = $gameManager->getGameData();

if (!$gameData) {
    jsonResponse(false, [], 'Partie non trouvée');
}

$state = $gameManager->getGameState($_SESSION['user_id']);
jsonResponse(true, $state);