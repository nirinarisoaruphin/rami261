<?php
// api/user/stats.php
require_once '../../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, [], 'Non authentifié');
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        u.balance,
        u.total_wins,
        u.total_games,
        (SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 1) as wins,
        (SELECT COUNT(*) FROM game_players WHERE user_id = u.id) as games_played,
        (SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 0) as losses,
        ROUND((SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 1) / 
              NULLIF((SELECT COUNT(*) FROM game_players WHERE user_id = u.id), 0) * 100, 1) as win_rate
    FROM users u
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

if ($stats) {
    jsonResponse(true, $stats);
} else {
    jsonResponse(false, [], 'Statistiques non trouvées');
}