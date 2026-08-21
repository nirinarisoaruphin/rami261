<?php
// logout.php - Déconnexion
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    // Mettre à jour le statut en ligne
    $stmt = $pdo->prepare("UPDATE users SET is_online = 0, last_activity = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Détruire la session
$_SESSION = [];
session_destroy();

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Rediriger vers l'accueil
header('Location: index.php');
exit;
?>