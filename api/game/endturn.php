<?php
// api/game/endturn.php
require_once '../../includes/config.php';
require_once '../../includes/GameManager.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, [], 'Non authentifié');
}

$input = json_decode(file_get_contents('php://input'), true);
$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;
$playerId = $input['player_id'] ?? null;

if (!$gameId || !$playerId) {
    jsonResponse(false, [], 'Paramètres manquants');
}

if ($playerId != $_SESSION['user_id']) {
    jsonResponse(false, [], 'Action non autorisée');
}

$gameManager = new GameManager($gameId);
$success = $gameManager->endTurn($playerId);

if ($success) {
    jsonResponse(true, ['message' => 'Tour terminé']);
} else {
    jsonResponse(false, [], 'Impossible de terminer le tour');
}