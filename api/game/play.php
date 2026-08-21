<?php
// api/game/play.php
require_once '../../includes/config.php';
require_once '../../includes/GameManager.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, [], 'Non authentifié');
}

$input = json_decode(file_get_contents('php://input'), true);
$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;
$playerId = $input['player_id'] ?? null;
$cardIndices = $input['card_indices'] ?? [];

if (!$gameId || !$playerId) {
    jsonResponse(false, [], 'Paramètres manquants');
}

if ($playerId != $_SESSION['user_id']) {
    jsonResponse(false, [], 'Action non autorisée');
}

if (empty($cardIndices) || count($cardIndices) < 3) {
    jsonResponse(false, [], 'Sélectionnez au moins 3 cartes');
}

$gameManager = new GameManager($gameId);
$success = $gameManager->playMeld($playerId, $cardIndices);

if ($success) {
    jsonResponse(true, ['message' => 'Combinaison validée']);
} else {
    jsonResponse(false, [], 'Combinaison invalide');
}