<?php
// clear_cache.php
// Nettoyer le cache du navigateur et les sessions

session_start();

// Détruire la session
$_SESSION = [];
session_destroy();

// Supprimer le cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Rediriger avec un message
header('Location: index.php?cache=cleared');
exit;