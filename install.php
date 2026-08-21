<?php
// install.php - Script d'installation du projet
// À SUPPRIMER après installation

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Installation - Rami 261</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #0a0a0f; color: #f0f0f5; }
        .box { background: #1a1a2e; padding: 20px; border-radius: 10px; margin: 10px 0; border: 1px solid rgba(255,255,255,0.1); }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .info { color: #06b6d4; }
        .step { background: rgba(124,58,237,0.2); padding: 10px; border-radius: 5px; margin: 5px 0; }
        h1 { background: linear-gradient(135deg, #7c3aed, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        button { background: linear-gradient(135deg, #7c3aed, #06b6d4); color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-size: 16px; }
        button:hover { transform: scale(1.05); }
    </style>
</head>
<body>
    <h1>🃏 Installation de Rami 261</h1>
    <div class='box'>";

// ============================================
// ÉTAPE 1: VÉRIFICATION DE L'ENVIRONNEMENT
// ============================================

echo "<h2>📋 Étape 1: Vérification de l'environnement</h2>";

$errors = 0;
$warnings = 0;

// PHP Version
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '7.4.0', '>=');
echo "<div class='step'>";
echo "✅ PHP Version: <strong>$phpVersion</strong> ";
echo $phpOk ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ PHP 7.4+ requis</span>";
if (!$phpOk) $errors++;
echo "</div>";

// Extensions PDO
$pdoOk = extension_loaded('pdo_mysql');
echo "<div class='step'>";
echo "✅ Extension PDO MySQL: ";
echo $pdoOk ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ Manquante</span>";
if (!$pdoOk) $errors++;
echo "</div>";

// Extension JSON
$jsonOk = extension_loaded('json');
echo "<div class='step'>";
echo "✅ Extension JSON: ";
echo $jsonOk ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ Manquante</span>";
if (!$jsonOk) $errors++;
echo "</div>";

// Extension Session
$sessionOk = extension_loaded('session');
echo "<div class='step'>";
echo "✅ Extension Session: ";
echo $sessionOk ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ Manquante</span>";
if (!$sessionOk) $errors++;
echo "</div>";

// WampServer
$isWamp = strpos(__DIR__, 'wamp64') !== false;
echo "<div class='step'>";
echo "✅ Environnement: ";
echo $isWamp ? "<span class='success'>✓ WampServer détecté</span>" : "<span class='info'>ℹ Serveur standard</span>";
echo "</div>";

// ============================================
// ÉTAPE 2: CONNEXION À LA BASE DE DONNÉES
// ============================================

echo "<h2>📋 Étape 2: Connexion à la base de données</h2>";

try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='step success'>✅ Connexion à MySQL réussie</div>";
    
    // Créer la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS rami261 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<div class='step success'>✅ Base de données 'rami261' créée</div>";
    
    // Sélectionner la base
    $pdo->exec("USE rami261");
    
    // ============================================
    // ÉTAPE 3: CRÉATION DES TABLES
    // ============================================
    
    echo "<h2>📋 Étape 3: Création des tables</h2>";
    
    $schema = file_get_contents('database/schema.sql');
    if ($schema) {
        $queries = explode(';', $schema);
        $tableCount = 0;
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query) && strpos($query, 'CREATE TABLE') !== false) {
                try {
                    $pdo->exec($query);
                    $tableCount++;
                } catch (PDOException $e) {
                    echo "<div class='step error'>❌ Erreur: " . $e->getMessage() . "</div>";
                    $errors++;
                }
            }
        }
        echo "<div class='step success'>✅ $tableCount tables créées</div>";
    } else {
        echo "<div class='step error'>❌ Fichier schema.sql non trouvé</div>";
        $errors++;
    }
    
    // ============================================
    // ÉTAPE 4: DONNÉES INITIALES
    // ============================================
    
    echo "<h2>📋 Étape 4: Données initiales</h2>";
    
    // Vérifier si les données existent déjà
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_config");
    $configCount = $stmt->fetch()['count'] ?? 0;
    
    if ($configCount == 0) {
        try {
            $pdo->exec("
                INSERT INTO system_config (config_key, config_value, description) VALUES
                ('turn_timeout', '30', 'Temps maximum pour un tour (secondes)'),
                ('reconnect_timeout', '60', 'Délai de reconnexion (secondes)'),
                ('commission_rate', '0.05', 'Taux de commission (5%)'),
                ('min_players', '2', 'Nombre minimum de joueurs'),
                ('max_players', '5', 'Nombre maximum de joueurs'),
                ('cards_per_player', '13', 'Nombre de cartes distribuées'),
                ('deck_size', '108', 'Taille du jeu (2x52 + 4 jokers)'),
                ('tri_joker_bonus', '50', 'Bonus pour 3 jokers'),
                ('quadri_joker_bonus', '100', 'Bonus pour 4 jokers')
            ");
            echo "<div class='step success'>✅ Données initiales insérées</div>";
        } catch (PDOException $e) {
            echo "<div class='step error'>❌ Erreur: " . $e->getMessage() . "</div>";
            $errors++;
        }
    } else {
        echo "<div class='step info'>ℹ Les données existent déjà</div>";
    }
    
    // ============================================
    // ÉTAPE 5: VÉRIFICATION FINALE
    // ============================================
    
    echo "<h2>📋 Étape 5: Vérification finale</h2>";
    
    // Vérifier les tables
    $tables = ['users', 'games', 'game_players', 'moves', 'game_history', 'transactions', 'reconnection_tokens', 'system_config'];
    $allExist = true;
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='step success'>✅ Table '$table' existe</div>";
        } else {
            echo "<div class='step error'>❌ Table '$table' manquante</div>";
            $allExist = false;
            $errors++;
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='step error'>❌ Erreur MySQL: " . $e->getMessage() . "</div>";
    $errors++;
}

// ============================================
// RÉSULTAT
// ============================================

echo "<h2>📊 Résultat de l'installation</h2>";

if ($errors === 0) {
    echo "<div class='step success' style='font-size:18px; padding:15px;'>";
    echo "🎉 <strong>Installation réussie !</strong><br>";
    echo "Vous pouvez maintenant accéder à : <a href='index.php' style='color:#7c3aed;'>http://localhost/rami261/</a>";
    echo "</div>";
    echo "<div class='step info'>";
    echo "🔒 <strong>Supprimez ce fichier (install.php) pour des raisons de sécurité</strong>";
    echo "</div>";
} else {
    echo "<div class='step error' style='font-size:16px; padding:15px;'>";
    echo "❌ <strong>$errors erreur(s) détectée(s)</strong><br>";
    echo "Veuillez corriger les problèmes ci-dessus et réessayer.";
    echo "</div>";
}

echo "</div>";

// ============================================
// BOUTON DE SUPPRESSION
// ============================================

if ($errors === 0) {
    echo "<div style='margin-top:20px; text-align:center;'>";
    echo "<form method='POST' action=''>";
    echo "<button type='submit' name='delete_install'>🗑️ Supprimer install.php</button>";
    echo "</form>";
    echo "</div>";
    
    if (isset($_POST['delete_install'])) {
        if (unlink(__FILE__)) {
            echo "<div class='step success'>✅ install.php supprimé avec succès</div>";
        } else {
            echo "<div class='step error'>❌ Impossible de supprimer install.php</div>";
        }
    }
}

echo "</body></html>";
?>

<script>
    // Auto-refresh après 5 secondes
    setTimeout(function() {
        window.location.reload();
    }, 3000);
</script>