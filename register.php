<?php
// register.php - Page d'inscription avec drapeau avant le titre
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $acceptTerms = isset($_POST['accept_terms']);
    
    $formData = ['username' => $username, 'phone' => $phone];
    
    if (empty($username) || empty($phone) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } elseif (strlen($username) < 3) {
        $error = 'Le nom d\'utilisateur doit faire au moins 3 caractères';
    } elseif (strlen($username) > 30) {
        $error = 'Le nom d\'utilisateur ne doit pas dépasser 30 caractères';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores';
    } elseif (!preg_match('/^(03[23478])\d{7}$/', $phone)) {
        $error = 'Numéro de téléphone invalide. Format: 034, 032, 037, 038 ou 033 + 7 chiffres (ex: 0348072234)';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit faire au moins 6 caractères';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (!$acceptTerms) {
        $error = 'Vous devez accepter les conditions d\'utilisation';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? OR username = ?");
        $stmt->execute([$phone, $username]);
        
        if ($stmt->fetch()) {
            $error = 'Ce numéro de téléphone ou nom d\'utilisateur est déjà utilisé';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, phone, password, balance, created_at) VALUES (?, ?, ?, 10.00, NOW())");
            if ($stmt->execute([$username, $phone, $hashed])) {
                $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                $formData = [];
            } else {
                $error = 'Erreur lors de la création du compte';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inscription - Rami 261</title>
    
    <link rel="icon" href="favicon.php" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.php" type="image/x-icon">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-[var(--bg-primary)] p-4">
        <div class="w-full max-w-sm">
            
            <div class="bg-layer-1" style="position:fixed;top:0;left:0;right:0;bottom:0;background:radial-gradient(ellipse at center,#e8e5f0,#d5d0e0);z-index:0;pointer-events:none;"></div>
            <div class="bg-layer-flag" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-15deg) scale(2);width:400px;height:267px;opacity:0.08;z-index:0;pointer-events:none;border-radius:12px;background:linear-gradient(to right,#ffffff 0%,#ffffff 33.33%,#fc3e32 33.33%,#fc3e32 66.66%,#007a3d 66.66%,#007a3d 100%);"></div>
            
            <div class="relative z-10">
                <div class="text-center mb-8">
                    <!-- TITRE AVEC DRAPEAU AVANT -->
                    <div class="flex items-center justify-center gap-3 mb-2">
                        <div class="w-10 h-7 rounded overflow-hidden shadow-md flex-shrink-0 bg-white border border-gray-200">
                            <img src="assets/images/flags/madagascar.png" alt="Drapeau Madagascar"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'">
                        </div>
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 to-cyan-400 bg-clip-text text-transparent">
                            RAMI 261
                        </h1>
                    </div>
                    <p class="text-[var(--text-secondary)] text-sm mt-2">🇲🇬 Créez votre compte</p>
                </div>
                <div class="glass p-6 rounded-2xl">
                    <?php if ($error): ?>
                        <div class="bg-red-500/20 border border-red-500/30 text-red-600 px-4 py-2 rounded-lg text-sm mb-4"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="bg-green-500/20 border border-green-500/30 text-green-600 px-4 py-2 rounded-lg text-sm mb-4">
                            <?php echo htmlspecialchars($success); ?>
                            <a href="login.php" class="text-[var(--accent-primary)] hover:underline">Se connecter</a>
                        </div>
                    <?php endif; ?>
                    <?php if (!$success): ?>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">👤 Nom d'utilisateur <span class="text-red-500">*</span></label>
                            <input type="text" name="username" required value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>" class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" placeholder="Votre pseudo" minlength="3" maxlength="30" pattern="[a-zA-Z0-9_]+">
                        </div>
                        <div>
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">📱 Numéro de téléphone <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" required value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>" class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" placeholder="034 07 223 34" maxlength="10">
                            <p class="text-xs text-[var(--text-secondary)] mt-1">Format: 034, 032, 037, 038 ou 033 + 7 chiffres</p>
                        </div>
                        
                        <div>
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">🔒 Mot de passe <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" id="registerPassword" required class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)] pr-12" placeholder="Minimum 6 caractères" minlength="6">
                                <button type="button" onclick="togglePassword('registerPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="registerPasswordIcon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-[var(--text-secondary)] mt-1">Minimum 6 caractères</p>
                        </div>
                        
                        <div>
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">✅ Confirmer le mot de passe <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="registerConfirmPassword" required class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)] pr-12" placeholder="Confirmez votre mot de passe" minlength="6">
                                <button type="button" onclick="togglePassword('registerConfirmPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="registerConfirmPasswordIcon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-2">
                            <input type="checkbox" name="accept_terms" id="accept_terms" required class="mt-1 accent-[var(--accent-primary)] w-4 h-4">
                            <label for="accept_terms" class="text-xs text-[var(--text-secondary)]">J'accepte les conditions d'utilisation</label>
                        </div>
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">🚀 Créer mon compte</button>
                    </form>
                    <?php endif; ?>
                    <div class="text-center mt-4"><a href="login.php" class="text-sm text-[var(--accent-primary)] hover:underline">Déjà un compte ? Se connecter</a></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // AFFICHER/MASQUER LE MOT DE PASSE
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('svg');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"/>
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }
        
        // Formatage du numéro de téléphone
        document.querySelector('input[name="phone"]')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    </script>
</body>
</html>