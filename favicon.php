<?php
// favicon.php - Génère un favicon avec le drapeau officiel de Madagascar
header('Content-Type: image/x-icon');

// Créer une image 64x64
$img = imagecreatetruecolor(64, 64);

// Couleurs du drapeau Madagascar (officiel)
$white = imagecolorallocate($img, 255, 255, 255);   // #fcfaf9 Blanc
$red = imagecolorallocate($img, 252, 62, 50);       // #fc3e32 Rouge
$green = imagecolorallocate($img, 0, 122, 61);      // #007a3d Vert

// Fond blanc
imagefill($img, 0, 0, $white);

// Bande blanche à gauche (1/3)
imagefilledrectangle($img, 0, 0, 21, 64, $white);

// Bande rouge en haut à droite (2/3 en haut)
imagefilledrectangle($img, 22, 0, 63, 32, $red);

// Bande verte en bas à droite (3/3 en bas)
imagefilledrectangle($img, 22, 32, 63, 64, $green);

// Ajouter un contour fin
$black = imagecolorallocate($img, 0, 0, 0);
imagerectangle($img, 0, 0, 63, 63, $black);

// Ajouter un petit effet de brillance (optionnel)
$light = imagecolorallocate($img, 255, 255, 255, 30);
imagefilledrectangle($img, 2, 2, 20, 10, $light);

// Sauvegarder en PNG (compatible favicon)
header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
?>