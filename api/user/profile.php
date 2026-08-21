<?php
// api/user/profile.php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, [], 'Non authentifié');
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT u.*,
           (SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 1) as wins,
           (SELECT COUNT(*) FROM game_players WHERE user_id = u.id) as games_played
    FROM users u
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($user) {
    // Supprimer le mot de passe
    unset($user['password']);
    jsonResponse(true, ['user' => $user]);
} else {
    jsonResponse(false, [], 'Utilisateur non trouvé');
}