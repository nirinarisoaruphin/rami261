<?php
// includes/config.php

// ============================================
// CONSTANTES DE BASE
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'rami261');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_URL', 'http://localhost/rami261/');
define('SITE_NAME', 'Rami 261');
define('SITE_VERSION', '1.0.0');

// ============================================
// CONSTANTES MONNAIE - ARIARY
// ============================================
define('CURRENCY_SYMBOL', 'Ar');
define('CURRENCY_CODE', 'MGA');
define('CURRENCY_DECIMALS', 0);
define('CURRENCY_DECIMAL_POINT', ',');
define('CURRENCY_THOUSANDS_SEPARATOR', ' ');

// ============================================
// CONSTANTES DU JEU
// ============================================
define('TURN_TIMEOUT', 30);
define('RECONNECT_TIMEOUT', 60);
define('COMMISSION_RATE', 0.05);
define('MIN_PLAYERS', 2);
define('MAX_PLAYERS', 5);
define('CARDS_PER_PLAYER', 13);
define('DECK_SIZE', 108);
define('TRI_JOKER_BONUS', 50000);
define('QUADRI_JOKER_BONUS', 100000);

// ============================================
// CONNEXION BDD
// ============================================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ============================================
// SESSION
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// FUSEAU HORAIRE
// ============================================
date_default_timezone_set('Indian/Antananarivo');

// ============================================
// FONCTIONS UTILITAIRES
// ============================================
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUsername(): ?string {
    return $_SESSION['username'] ?? null;
}

function getCurrentPhone(): ?string {
    return $_SESSION['phone'] ?? null;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function jsonResponse(bool $success, array $data = [], string $error = ''): void {
    header('Content-Type: application/json');
    $response = ['success' => $success];
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    if (!empty($error)) {
        $response['error'] = $error;
    }
    echo json_encode($response);
    exit;
}

// ============================================
// CHARGER LE THÈME DEPUIS LA BDD
// ============================================
if (isLoggedIn()) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'theme'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT theme FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $userTheme = $stmt->fetch();
            
            if ($userTheme && !empty($userTheme['theme'])) {
                $_SESSION['theme'] = $userTheme['theme'];
                setcookie('theme', $userTheme['theme'], time() + 31536000, '/');
            }
        }
    } catch (PDOException $e) {
        // Ignorer les erreurs
    }
}

if (!isset($_SESSION['theme']) && isset($_COOKIE['theme'])) {
    $_SESSION['theme'] = $_COOKIE['theme'];
}

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}

// ============================================
// CHARGER LES STATS DE L'UTILISATEUR (GLOBAL)
// ============================================
$userStats = null;
if (isLoggedIn()) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.balance,
                (SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 1) as wins,
                (SELECT COUNT(*) FROM game_players WHERE user_id = u.id) as games_played
            FROM users u
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $userStats = $stmt->fetch();
        
        // Stocker en session pour éviter trop de requêtes
        $_SESSION['user_stats'] = $userStats;
    } catch (PDOException $e) {
        $userStats = null;
    }
} else {
    $userStats = null;
}