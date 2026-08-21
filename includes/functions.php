<?php
// includes/functions.php

function sanitizeInput(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

function formatDate(string $date): string {
    return date('d/m/Y H:i', strtotime($date));
}

function formatMoney(float $amount): string {
    return number_format($amount, 2, ',', ' ') . ' €';
}

function getStatusBadge(string $status): string {
    $badges = [
        'waiting' => '<span class="badge badge-warning">⏳ En attente</span>',
        'playing' => '<span class="badge badge-success">🔄 En cours</span>',
        'finished' => '<span class="badge badge-info">🏆 Terminé</span>',
        'closed' => '<span class="badge badge-danger">🔒 Fermé</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">' . $status . '</span>';
}

function getWinTypeLabel(string $type): string {
    $labels = [
        'normal' => 'Rami normal',
        'tri_joker' => '⭐ Triple Joker',
        'quadri_joker' => '⭐⭐ Quadruple Joker'
    ];
    return $labels[$type] ?? $type;
}

function getWinTypeIcon(string $type): string {
    $icons = [
        'normal' => '🎯',
        'tri_joker' => '⭐',
        'quadri_joker' => '🌟🌟'
    ];
    return $icons[$type] ?? '🏆';
}

function getAvatarUrl(string $avatar): string {
    return SITE_URL . 'assets/images/avatars/' . $avatar;
}

function getCardImageUrl(array $card): string {
    if ($card['is_joker']) {
        return SITE_URL . 'assets/images/cards/joker.png';
    }
    return SITE_URL . 'assets/images/cards/' . $card['suit'] . '/' . $card['value'] . '.png';
}

function isGameActive(string $status): bool {
    return in_array($status, ['waiting', 'playing']);
}

function canJoinGame(string $status): bool {
    return $status === 'waiting';
}

function canStartGame(string $status, int $playerCount): bool {
    return $status === 'waiting' && $playerCount >= MIN_PLAYERS;
}

// ============================================
// NOUVELLES FONCTIONS
// ============================================

function getSuitSymbol(string $suit): string {
    $symbols = [
        'hearts' => '♥',
        'diamonds' => '♦',
        'clubs' => '♣',
        'spades' => '♠'
    ];
    return $symbols[$suit] ?? '⭐';
}

function getSuitColor(string $suit): string {
    $colors = [
        'hearts' => 'red',
        'diamonds' => 'red',
        'clubs' => 'black',
        'spades' => 'black'
    ];
    return $colors[$suit] ?? 'purple';
}

function getCardDisplay(array $card): string {
    if ($card['is_joker']) {
        return '⭐ JOKER';
    }
    $symbol = getSuitSymbol($card['suit']);
    return $card['value'] . $symbol;
}

function calculateHandScore(array $hand): int {
    $score = 0;
    foreach ($hand as $card) {
        if (!$card['is_joker']) {
            $score += $card['points'] ?? 0;
        }
    }
    return $score;
}

function calculateMeldScore(array $melds): int {
    $score = 0;
    foreach ($melds as $meld) {
        foreach ($meld as $card) {
            if (!$card['is_joker']) {
                $score += $card['points'] ?? 0;
            }
        }
    }
    return $score;
}

function getWinRate(int $wins, int $games): float {
    if ($games === 0) return 0;
    return round(($wins / $games) * 100, 1);
}

function getStatusLabel(string $status): string {
    $labels = [
        'waiting' => '⏳ En attente',
        'playing' => '🔄 En cours',
        'finished' => '🏆 Terminé',
        'closed' => '🔒 Fermé'
    ];
    return $labels[$status] ?? $status;
}

function truncateText(string $text, int $length = 20): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function isValidUsername(string $username): bool {
    return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) === 1;
}

function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generateRoomCode(): string {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

function isUserOnline(int $userId, PDO $pdo): bool {
    $stmt = $pdo->prepare("SELECT is_online FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result ? (bool)$result['is_online'] : false;
}

function getPlayerCount(int $gameId, PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM game_players WHERE game_id = ?");
    $stmt->execute([$gameId]);
    $result = $stmt->fetch();
    return $result ? (int)$result['count'] : 0;
}