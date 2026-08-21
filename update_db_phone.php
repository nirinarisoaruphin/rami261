<?php
// update_db_phone.php - Met à jour la base de données pour le téléphone
require_once 'includes/config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Mise à jour BDD - Rami 261</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; background: #0a0a0f; color: #f0f0f5; }
        .box { background: #1a1a2e; padding: 20px; border-radius: 10px; margin: 10px 0; border: 1px solid rgba(255,255,255,0.1); }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .info { color: #06b6d4; }
    </style>
</head>
<body>
    <h1>🔄 Mise à jour de la base de données</h1>";

try {
    // Vérifier si la colonne email existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='box'><p>📧 Colonne <strong>email</strong> trouvée</p>";
        
        // Sauvegarder les emails existants
        $stmt = $pdo->query("SELECT id, email FROM users");
        $users = $stmt->fetchAll();
        
        // Supprimer la colonne email
        $pdo->exec("ALTER TABLE users DROP COLUMN email");
        echo "<p>✅ Colonne <strong>email</strong> supprimée</p></div>";
    } else {
        echo "<div class='box'><p>ℹ️ Colonne <strong>email</strong> non trouvée</p></div>";
    }
    
    // Ajouter la colonne phone
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) UNIQUE NOT NULL AFTER username");
        echo "<div class='box'><p>✅ Colonne <strong>phone</strong> ajoutée</p></div>";
    } else {
        echo "<div class='box'><p>ℹ️ Colonne <strong>phone</strong> existe déjà</p></div>";
    }
    
    // Mettre à jour le type de la colonne phone
    $pdo->exec("ALTER TABLE users MODIFY COLUMN phone VARCHAR(20) UNIQUE NOT NULL");
    echo "<div class='box'><p>✅ Colonne <strong>phone</strong> mise à jour</p></div>";
    
    echo "<div class='box'><p class='success'>🎉 Base de données mise à jour avec succès !</p>";
    echo "<p><a href='register.php' style='color:#7c3aed;'>📝 Aller à l'inscription</a> | ";
    echo "<a href='login.php' style='color:#7c3aed;'>🔐 Aller à la connexion</a></p></div>";
    
} catch (PDOException $e) {
    echo "<div class='box'><p class='error'>❌ Erreur: " . $e->getMessage() . "</p></div>";
}

echo "</body></html>";
?>