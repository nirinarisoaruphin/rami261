<?php
// includes/functions.php

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// ============================================
// FONCTION DE FORMATAGE ARIARY
// ============================================
function formatCurrency($amount) {
    return number_format($amount, 0, ',', ' ') . ' Ar';
}

function formatCurrencyShort($amount) {
    if ($amount >= 1000000) {
        return number_format($amount / 1000000, 1, ',', ' ') . ' M Ar';
    } elseif ($amount >= 1000) {
        return number_format($amount / 1000, 0, ',', ' ') . ' k Ar';
    }
    return number_format($amount, 0, ',', ' ') . ' Ar';
}

// ============================================
// AUTRES FONCTIONS
// ============================================

function getStatusBadge($status) {
    $badges = [
        'waiting' => '<span class="badge badge-warning">⏳ En attente</span>',
        'playing' => '<span class="badge badge-success">🔄 En cours</span>',
        'finished' => '<span class="badge badge-info">🏆 Terminé</span>',
        'closed' => '<span class="badge badge-danger">🔒 Fermé</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">' . $status . '</span>';
}

function getWinTypeLabel($type) {
    $labels = [
        'normal' => 'Rami normal',
        'tri_joker' => '⭐ Triple Joker',
        'quadri_joker' => '⭐⭐ Quadruple Joker'
    ];
    return $labels[$type] ?? $type;
}

function getWinTypeIcon($type) {
    $icons = [
        'normal' => '🎯',
        'tri_joker' => '⭐',
        'quadri_joker' => '🌟🌟'
    ];
    return $icons[$type] ?? '🏆';
}

function isGameActive($status) {
    return in_array($status, ['waiting', 'playing']);
}

function canJoinGame($status) {
    return $status === 'waiting';
}

function canStartGame($status, $playerCount) {
    return $status === 'waiting' && $playerCount >= MIN_PLAYERS;
}

function getSuitSymbol($suit) {
    $symbols = [
        'hearts' => '♥',
        'diamonds' => '♦',
        'clubs' => '♣',
        'spades' => '♠'
    ];
    return $symbols[$suit] ?? '⭐';
}

function getSuitColor($suit) {
    $colors = [
        'hearts' => 'red',
        'diamonds' => 'red',
        'clubs' => 'black',
        'spades' => 'black'
    ];
    return $colors[$suit] ?? 'purple';
}

function getCardDisplay($card) {
    if ($card['is_joker']) return '⭐ JOKER';
    $symbol = getSuitSymbol($card['suit']);
    return $card['value'] . $symbol;
}

function calculateHandScore($hand) {
    $score = 0;
    foreach ($hand as $card) {
        if (!$card['is_joker']) {
            $score += $card['points'] ?? 0;
        }
    }
    return $score;
}

function getStatusLabel($status) {
    $labels = [
        'waiting' => '⏳ En attente',
        'playing' => '🔄 En cours',
        'finished' => '🏆 Terminé',
        'closed' => '🔒 Fermé'
    ];
    return $labels[$status] ?? $status;
}

function truncateText($text, $length = 20) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function isValidUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) === 1;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generateRoomCode() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

function isUserOnline($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT is_online FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result ? (bool)$result['is_online'] : false;
}

function getPlayerCount($gameId, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM game_players WHERE game_id = ?");
    $stmt->execute([$gameId]);
    $result = $stmt->fetch();
    return $result ? (int)$result['count'] : 0;
}