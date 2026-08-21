<?php
// create_images.php - Générateur d'images
// Drapeau OFFICIEL Madagascar : Blanc 1/3 | Rouge 2/3 | Vert 3/3

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Générateur d'images - Rami 261</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #0a0a0f; color: #f0f0f5; }
        .container { background: #1a1a2e; padding: 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); }
        h1 { background: linear-gradient(135deg, #7c3aed, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .info { color: #06b6d4; }
        .step { background: rgba(124,58,237,0.1); padding: 10px; border-radius: 5px; margin: 5px 0; border-left: 3px solid #7c3aed; }
        .footer { margin-top: 20px; text-align: center; padding: 15px; background: rgba(34,197,94,0.1); border-radius: 8px; border: 1px solid rgba(34,197,94,0.2); }
        .btn { background: linear-gradient(135deg, #7c3aed, #06b6d4); color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; font-size: 14px; margin: 5px; }
        .btn:hover { transform: scale(1.05); }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .flag-preview { 
            width: 200px; 
            height: 133px; 
            border-radius: 4px; 
            border: 2px solid rgba(255,255,255,0.2); 
            margin: 10px auto; 
            background: linear-gradient(
                to right,
                #ffffff 0%,
                #ffffff 33.33%,
                #fc3e32 33.33%,
                #fc3e32 66.66%,
                #007a3d 66.66%,
                #007a3d 100%
            );
        }
        .flag-label {
            text-align: center;
            font-size: 14px;
            color: #a0a0b8;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🃏 Générateur d'images - Rami 261</h1>
        <p class='info'>🇲🇬 Drapeau OFFICIEL Madagascar</p>
        <div class='flag-preview'></div>
        <div class='flag-label'>Blanc 1/3 | Rouge 2/3 | Vert 3/3</div>
        <hr style='border-color: rgba(255,255,255,0.1);'>";

function logMessage($message, $type = 'info') {
    $colors = ['success' => '#22c55e', 'error' => '#ef4444', 'info' => '#06b6d4'];
    $color = $colors[$type] ?? '#f0f0f5';
    echo "<div class='step' style='border-left-color: {$color};'><span style='color:{$color};'>" . htmlspecialchars($message) . "</span></div>";
    flush();
}

function createDirectory($path) {
    if (!is_dir($path)) {
        if (mkdir($path, 0777, true)) {
            logMessage("📁 Dossier créé : {$path}", 'success');
            return true;
        } else {
            logMessage("❌ Erreur création dossier : {$path}", 'error');
            return false;
        }
    } else {
        logMessage("📁 Dossier existant : {$path}", 'info');
        return true;
    }
}

// ============================================
// 1. CRÉER LES DOSSIERS
// ============================================

echo "<h2>📁 Étape 1: Création des dossiers</h2>";

$folders = [
    'assets/images/background',
    'assets/images/flags',
    'assets/images/cards',
    'assets/images/cards/hearts',
    'assets/images/cards/diamonds',
    'assets/images/cards/clubs',
    'assets/images/cards/spades'
];

foreach ($folders as $folder) {
    createDirectory($folder);
}

// ============================================
// 2. CRÉER LE DRAPEAU MADAGASCAR OFFICIEL
// ============================================

echo "<h2>🇲🇬 Étape 2: Drapeau OFFICIEL Madagascar</h2>";
echo "<p style='color: #f0f0f5;'>Blanc 1/3 | Rouge 2/3 | Vert 3/3</p>";

// Version SVG
$flagSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" width="600" height="400">
    <!-- Fond blanc -->
    <rect width="600" height="400" fill="#ffffff"/>
    <!-- Bande blanche à gauche (1/3) -->
    <rect x="0" y="0" width="200" height="400" fill="#ffffff"/>
    <!-- Bande rouge en haut à droite (2/3 en haut) -->
    <rect x="200" y="0" width="400" height="200" fill="#fc3e32"/>
    <!-- Bande verte en bas à droite (3/3 en bas) -->
    <rect x="200" y="200" width="400" height="200" fill="#007a3d"/>
</svg>';

if (file_put_contents('assets/images/flags/madagascar.svg', $flagSvg)) {
    logMessage("✅ Drapeau OFFICIEL Madagascar (SVG)", 'success');
}

// Version PNG
if (function_exists('imagecreatetruecolor')) {
    $width = 600;
    $height = 400;
    $img = imagecreatetruecolor($width, $height);
    
    $white = imagecolorallocate($img, 255, 255, 255);
    $red = imagecolorallocate($img, 252, 62, 50);
    $green = imagecolorallocate($img, 0, 122, 61);
    
    imagefill($img, 0, 0, $white);
    imagefilledrectangle($img, 0, 0, 200, 400, $white);    // 1/3 Blanc
    imagefilledrectangle($img, 200, 0, 600, 200, $red);    // 2/3 Rouge
    imagefilledrectangle($img, 200, 200, 600, 400, $green); // 3/3 Vert
    
    if (imagepng($img, 'assets/images/flags/madagascar.png')) {
        logMessage("✅ Drapeau OFFICIEL Madagascar (PNG)", 'success');
    }
    imagedestroy($img);
    
    // Version petite
    $width = 150;
    $height = 100;
    $img = imagecreatetruecolor($width, $height);
    
    $white = imagecolorallocate($img, 255, 255, 255);
    $red = imagecolorallocate($img, 252, 62, 50);
    $green = imagecolorallocate($img, 0, 122, 61);
    
    imagefill($img, 0, 0, $white);
    imagefilledrectangle($img, 0, 0, 50, 100, $white);
    imagefilledrectangle($img, 50, 0, 150, 50, $red);
    imagefilledrectangle($img, 50, 50, 150, 100, $green);
    
    if (imagepng($img, 'assets/images/flags/madagascar-small.png')) {
        logMessage("✅ Drapeau OFFICIEL Madagascar petit (PNG)", 'success');
    }
    imagedestroy($img);
}

// ============================================
// 3. CRÉER LE BACKGROUND
// ============================================

echo "<h2>🎨 Étape 3: Image de fond</h2>";

if (function_exists('imagecreatetruecolor')) {
    $width = 800;
    $height = 600;
    $img = imagecreatetruecolor($width, $height);
    
    $dark = imagecolorallocate($img, 10, 10, 20);
    $purple1 = imagecolorallocate($img, 80, 30, 180);
    
    imagefill($img, 0, 0, $dark);
    
    for ($i = 0; $i < $height; $i++) {
        $ratio = $i / $height;
        $r = 10 + ($ratio * 114);
        $g = 10 + ($ratio * 48);
        $b = 20 + ($ratio * 217);
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $i, $width, $i, $color);
    }
    
    $symbols = ['♠', '♥', '♦', '♣'];
    $colors = [
        imagecolorallocate($img, 60, 20, 120),
        imagecolorallocate($img, 40, 100, 200),
        imagecolorallocate($img, 100, 40, 180)
    ];
    
    for ($i = 0; $i < 30; $i++) {
        $x = rand(30, $width - 30);
        $y = rand(30, $height - 30);
        $symbol = $symbols[array_rand($symbols)];
        $color = $colors[array_rand($colors)];
        imagestring($img, 5, $x, $y, $symbol, $color);
    }
    
    $textColor = imagecolorallocate($img, 60, 30, 120);
    imagestring($img, 5, 300, 250, 'Rami 261', $textColor);
    imagerectangle($img, 5, 5, $width - 5, $height - 5, $purple1);
    
    if (imagepng($img, 'assets/images/background/bg-main.png')) {
        logMessage("✅ Background créé (PNG)", 'success');
    }
    imagedestroy($img);
} else {
    logMessage("⚠️ GD non disponible", 'warning');
}

// ============================================
// 4. CRÉER LES CARTES
// ============================================

echo "<h2>🃏 Étape 4: Création des cartes</h2>";

if (function_exists('imagecreatetruecolor')) {
    $suits = ['hearts' => '♥', 'diamonds' => '♦', 'clubs' => '♣', 'spades' => '♠'];
    $suitColors = ['hearts' => 'red', 'diamonds' => 'red', 'clubs' => 'black', 'spades' => 'black'];
    $values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
    
    $cardWidth = 70;
    $cardHeight = 100;
    $cardCount = 0;
    
    foreach ($suits as $suit => $symbol) {
        $colorType = $suitColors[$suit];
        
        foreach ($values as $value) {
            $filename = "assets/images/cards/{$suit}/{$value}.png";
            
            $img = imagecreatetruecolor($cardWidth, $cardHeight);
            
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            $red = imagecolorallocate($img, 220, 40, 40);
            $gray = imagecolorallocate($img, 200, 200, 200);
            $textColor = ($colorType === 'red') ? $red : $black;
            
            imagefill($img, 0, 0, $white);
            imagerectangle($img, 2, 2, $cardWidth - 3, $cardHeight - 3, $gray);
            
            $fontSize = ($value == '10') ? 3 : 4;
            imagestring($img, $fontSize + 1, 5, 3, $value, $textColor);
            imagestring($img, $fontSize + 1, 5, 16, $symbol, $textColor);
            imagestring($img, $fontSize + 1, $cardWidth - 20, $cardHeight - 22, $value, $textColor);
            imagestring($img, $fontSize + 1, $cardWidth - 20, $cardHeight - 9, $symbol, $textColor);
            
            $centerX = $cardWidth / 2 - 5;
            $centerY = $cardHeight / 2 - 5;
            imagestring($img, 5, $centerX - 3, $centerY - 10, $symbol, $textColor);
            
            if (imagepng($img, $filename)) {
                $cardCount++;
            }
            imagedestroy($img);
        }
    }
    
    logMessage("✅ $cardCount cartes créées", 'success');
    
    // Dos de carte
    $img = imagecreatetruecolor($cardWidth, $cardHeight);
    $purple = imagecolorallocate($img, 80, 30, 180);
    $lightPurple = imagecolorallocate($img, 124, 58, 237);
    $darkPurple = imagecolorallocate($img, 50, 20, 120);
    $white = imagecolorallocate($img, 255, 255, 255);
    $gold = imagecolorallocate($img, 212, 175, 55);
    
    imagefill($img, 0, 0, $purple);
    for ($y = 0; $y < $cardHeight; $y += 10) {
        for ($x = 0; $x < $cardWidth; $x += 10) {
            if (($x + $y) % 20 < 10) {
                imagefilledrectangle($img, $x, $y, $x + 8, $y + 8, $darkPurple);
            }
        }
    }
    imagerectangle($img, 3, 3, $cardWidth - 4, $cardHeight - 4, $lightPurple);
    imagerectangle($img, 5, 5, $cardWidth - 6, $cardHeight - 6, $gold);
    imagestring($img, 5, 25, 40, '🃏', $white);
    imagepng($img, 'assets/images/cards/card-back.png');
    imagedestroy($img);
    logMessage("✅ Dos de carte créé", 'success');
    
    // Joker
    $img = imagecreatetruecolor($cardWidth, $cardHeight);
    $purple = imagecolorallocate($img, 124, 58, 237);
    $pink = imagecolorallocate($img, 236, 72, 153);
    $white = imagecolorallocate($img, 255, 255, 255);
    $gold = imagecolorallocate($img, 212, 175, 55);
    
    imagefill($img, 0, 0, $purple);
    imagefilledrectangle($img, 0, 50, $cardWidth, $cardHeight, $pink);
    imagerectangle($img, 3, 3, $cardWidth - 4, $cardHeight - 4, $gold);
    imagestring($img, 5, 28, 20, '⭐', $gold);
    imagestring($img, 4, 15, 55, 'JOKER', $white);
    imagepng($img, 'assets/images/cards/joker.png');
    imagedestroy($img);
    logMessage("✅ Joker créé", 'success');
}

// ============================================
// RÉCAPITULATIF
// ============================================

echo "<h2>📊 Récapitulatif</h2>";

$totalImages = 0;
$folders = [
    'assets/images/background',
    'assets/images/flags',
    'assets/images/cards',
    'assets/images/cards/hearts',
    'assets/images/cards/diamonds',
    'assets/images/cards/clubs',
    'assets/images/cards/spades'
];

foreach ($folders as $folder) {
    if (is_dir($folder)) {
        $files = scandir($folder);
        $files = array_diff($files, ['.', '..']);
        $count = count($files);
        if ($count > 0) {
            logMessage("📂 {$folder} : {$count} fichier(s)", 'info');
            $totalImages += $count;
        }
    }
}

echo "<div class='footer'>";
echo "🎉 <strong>Génération terminée !</strong><br>";
echo "📊 Total : <strong>{$totalImages}</strong> images créées<br>";
echo "🇲🇬 Drapeau : <strong>Blanc 1/3 | Rouge 2/3 | Vert 3/3</strong><br>";
echo "📍 Emplacement : <strong>assets/images/</strong><br><br>";

echo "<form method='POST' action='' style='display:inline;'>";
echo "<button type='submit' name='delete_script' class='btn btn-danger' onclick=\"return confirm('Supprimer ce fichier ?')\">🗑️ Supprimer ce script</button>";
echo "</form>";

echo " <a href='index.php' class='btn'>🏠 Accéder au site</a>";
echo "</div>";

if (isset($_POST['delete_script'])) {
    if (unlink(__FILE__)) {
        echo "<script>alert('✅ Script supprimé !'); window.location.href='index.php';</script>";
    }
}

echo "</div></body></html>";
?>