<?php
// register.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $acceptTerms = isset($_POST['accept_terms']);
    
    $formData = ['username' => $username, 'email' => $email];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } elseif (strlen($username) < 3) {
        $error = 'Le nom d\'utilisateur doit faire au moins 3 caractères';
    } elseif (strlen($username) > 30) {
        $error = 'Le nom d\'utilisateur ne doit pas dépasser 30 caractères';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit faire au moins 6 caractères';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (!$acceptTerms) {
        $error = 'Vous devez accepter les conditions d\'utilisation';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        
        if ($stmt->fetch()) {
            $error = 'Cet email ou nom d\'utilisateur est déjà utilisé';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, balance, created_at) VALUES (?, ?, ?, 10.00, NOW())");
            if ($stmt->execute([$username, $email, $hashed])) {
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
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 to-cyan-400 bg-clip-text text-transparent">🃏 Rami 261</h1>
                    <p class="text-[var(--text-secondary)] text-sm mt-2">Créez votre compte</p>
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
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">📧 Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" placeholder="votre@email.com">
                        </div>
                        <div>
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">🔒 Mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" placeholder="Minimum 6 caractères" minlength="6">
                        </div>
                        <div>
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">✅ Confirmer <span class="text-red-500">*</span></label>
                            <input type="password" name="confirm_password" required class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" placeholder="Confirmez" minlength="6">
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
</body>
</html>