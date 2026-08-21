<?php
// check.php - Diagnostic du système

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic - Rami 261</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #0a0a0f; color: #f0f0f5; }
        .box { background: #1a1a2e; padding: 20px; border-radius: 10px; margin: 10px 0; border: 1px solid rgba(255,255,255,0.1); }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .info { color: #06b6d4; }
        .warning { color: #eab308; }
    </style>
</head>
<body>
    <h1>🩺 Diagnostic - Rami 261</h1>";

// ============================================
// PHP INFO
// ============================================

echo "<div class='box'>";
echo "<h2>🔧 Environnement PHP</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Serveur:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Système:</strong> " . PHP_OS . "</p>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "s</p>";
echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "</div>";

// ============================================
// EXTENSIONS PHP
// ============================================

echo "<div class='box'>";
echo "<h2>📦 Extensions PHP</h2>";

$extensions = ['pdo_mysql', 'json', 'session', 'openssl', 'mbstring', 'curl', 'gd'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>" . ($loaded ? "✅" : "❌") . " <strong>$ext</strong> - " . ($loaded ? "<span class='success'>Chargée</span>" : "<span class='error'>Non chargée</span>") . "</p>";
}
echo "</div>";

// ============================================
// CONNEXION BDD
// ============================================

echo "<div class='box'>";
echo "<h2>🗄️ Base de données</h2>";

try {
    require_once 'includes/config.php';
    echo "<p>✅ <span class='success'>Connexion réussie</span></p>";
    
    // Vérifier les tables
    $tables = ['users', 'games', 'game_players', 'moves', 'game_history', 'transactions', 'reconnection_tokens', 'system_config'];
    echo "<p><strong>Tables :</strong></p>";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo "<p style='margin-left:20px;'>" . ($exists ? "✅" : "❌") . " $table</p>";
    }
    
    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'] ?? 0;
    echo "<p><strong>👥 Utilisateurs:</strong> $userCount</p>";
    
    // Compter les parties
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM games");
    $gameCount = $stmt->fetch()['count'] ?? 0;
    echo "<p><strong>🎮 Parties:</strong> $gameCount</p>";
    
} catch (Exception $e) {
    echo "<p>❌ <span class='error'>Erreur: " . $e->getMessage() . "</span></p>";
}
echo "</div>";

// ============================================
// FICHIERS
// ============================================

echo "<div class='box'>";
echo "<h2>📁 Fichiers du projet</h2>";

$files = [
    'index.php', 'game.php', 'login.php', 'register.php', 'profile.php',
    'leaderboard.php', 'logout.php', 'rules.php', 'settings.php', '.htaccess',
    'includes/config.php', 'includes/Database.php', 'includes/GameManager.php',
    'includes/CardManager.php', 'includes/CombinationValidator.php',
    'assets/css/style.css', 'assets/js/game.js', 'assets/js/ui.js',
    'database/schema.sql'
];

foreach ($files as $file) {
    $exists = file_exists($file);
    echo "<p>" . ($exists ? "✅" : "❌") . " $file</p>";
}
echo "</div>";

// ============================================
// RECOMMANDATIONS
// ============================================

echo "<div class='box'>";
echo "<h2>💡 Recommandations</h2>";

// PHP memory limit
if (intval(ini_get('memory_limit')) < 128) {
    echo "<p>⚠️ <span class='warning'>Memory limit devrait être au moins 128M</span></p>";
}

// Max execution time
if (intval(ini_get('max_execution_time')) < 30) {
    echo "<p>⚠️ <span class='warning'>max_execution_time devrait être au moins 30s</span></p>";
}

// Display errors en production
if (ini_get('display_errors') == 1) {
    echo "<p>⚠️ <span class='warning'>display_errors est activé - Désactivez en production</span></p>";
}

echo "</div>";

// ============================================
// LIENS UTILES
// ============================================

echo "<div class='box' style='text-align:center;'>";
echo "<h2>🔗 Liens utiles</h2>";
echo "<p><a href='index.php' style='color:#7c3aed;'>🏠 Accueil</a> | ";
echo "<a href='login.php' style='color:#7c3aed;'>🔐 Connexion</a> | ";
echo "<a href='register.php' style='color:#7c3aed;'>📝 Inscription</a> | ";
echo "<a href='leaderboard.php' style='color:#7c3aed;'>🏆 Classement</a></p>";
echo "</div>";

echo "</body></html>";