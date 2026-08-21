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
// CONSTANTES DU JEU
// ============================================
define('TURN_TIMEOUT', 30);
define('RECONNECT_TIMEOUT', 60);
define('COMMISSION_RATE', 0.05);
define('MIN_PLAYERS', 2);
define('MAX_PLAYERS', 5);
define('CARDS_PER_PLAYER', 13);
define('DECK_SIZE', 108);
define('TRI_JOKER_BONUS', 50);
define('QUADRI_JOKER_BONUS', 100);

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
// SESSION - NE PAS REDIRIGER ICI
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// FUSEAU HORAIRE
// ============================================
date_default_timezone_set('Europe/Paris');

// ============================================
// FONCTIONS UTILITAIRES - SANS REDIRECTION
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