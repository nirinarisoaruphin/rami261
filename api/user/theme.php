<?php
// api/user/theme.php - Sauvegarde du thème via AJAX
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, [], 'Non authentifié');
}

$input = json_decode(file_get_contents('php://input'), true);
$theme = $input['theme'] ?? 'light';

// Valider le thème
if (!in_array($theme, ['light', 'dark'])) {
    jsonResponse(false, [], 'Thème invalide');
}

try {
    // Vérifier si la colonne theme existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'theme'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN theme VARCHAR(20) DEFAULT 'light'");
    }
    
    // Sauvegarder dans la BDD
    $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
    $stmt->execute([$theme, $_SESSION['user_id']]);
    
    // Sauvegarder dans le cookie
    setcookie('theme', $theme, time() + 31536000, '/');
    
    jsonResponse(true, ['theme' => $theme]);
    
} catch (PDOException $e) {
    jsonResponse(false, [], 'Erreur de sauvegarde');
}
?>