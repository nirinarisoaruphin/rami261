<?php
// generate_cards.php - Génère des images de cartes
// Exécutez ce fichier une fois pour créer les images

$suits = ['hearts', 'diamonds', 'clubs', 'spades'];
$values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
$suitSymbols = ['♥', '♦', '♣', '♠'];
$suitColors = ['red', 'red', 'black', 'black'];

// Créer les dossiers
foreach ($suits as $suit) {
    $dir = "assets/images/cards/{$suit}/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "📁 Dossier créé : {$dir}\n";
    }
}

// Générer chaque carte
for ($i = 0; $i < count($suits); $i++) {
    $suit = $suits[$i];
    $symbol = $suitSymbols[$i];
    $color = $suitColors[$i];
    
    foreach ($values as $value) {
        $filename = "assets/images/cards/{$suit}/{$value}.png";
        
        // Créer l'image
        $img = imagecreatetruecolor(70, 100);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        $red = imagecolorallocate($img, 255, 0, 0);
        $textColor = ($color === 'red') ? $red : $black;
        
        // Fond blanc
        imagefill($img, 0, 0, $white);
        
        // Bordure
        imagerectangle($img, 2, 2, 67, 97, $black);
        
        // Valeur en haut à gauche
        imagestring($img, 5, 5, 5, $value, $textColor);
        imagestring($img, 5, 5, 18, $symbol, $textColor);
        
        // Valeur en bas à droite (inversée)
        imagestring($img, 5, 50, 75, $value, $textColor);
        imagestring($img, 5, 50, 88, $symbol, $textColor);
        
        // Symbole au centre
        imagestring($img, 5, 30, 45, $symbol, $textColor);
        
        // Sauvegarder
        imagepng($img, $filename);
        imagedestroy($img);
        
        echo "✅ Carte générée : {$value}{$symbol}\n";
    }
}

// Générer le dos de carte
$img = imagecreatetruecolor(70, 100);
$purple = imagecolorallocate($img, 124, 58, 237);
$darkPurple = imagecolorallocate($img, 80, 30, 180);
$white = imagecolorallocate($img, 255, 255, 255);

// Fond violet
imagefill($img, 0, 0, $purple);

// Motif
for ($y = 0; $y < 100; $y += 10) {
    for ($x = 0; $x < 70; $x += 10) {
        if (($x + $y) % 20 < 10) {
            imagesetpixel($img, $x, $y, $darkPurple);
        }
    }
}

// Symbole au centre
imagestring($img, 5, 25, 40, '🃏', $white);
imagepng($img, 'assets/images/cards/card-back.png');
imagedestroy($img);
echo "✅ Dos de carte généré\n";

// Générer le joker
$img = imagecreatetruecolor(70, 100);
$purple = imagecolorallocate($img, 124, 58, 237);
$pink = imagecolorallocate($img, 236, 72, 153);
$white = imagecolorallocate($img, 255, 255, 255);

// Dégradé
imagefill($img, 0, 0, $purple);
imagefilledrectangle($img, 0, 50, 70, 100, $pink);

// Texte
imagestring($img, 5, 20, 40, '⭐', $white);
imagestring($img, 3, 15, 55, 'JOKER', $white);
imagepng($img, 'assets/images/cards/joker.png');
imagedestroy($img);
echo "✅ Joker généré\n";

echo "\n🎉 Toutes les cartes ont été générées !\n";
echo "📍 Emplacement : assets/images/cards/\n";
?>