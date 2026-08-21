<?php
// login.php
require_once 'includes/config.php';

// Si déjà connecté, rediriger vers l'accueil UNIQUEMENT si on est sur login.php
if (isLoggedIn() && basename($_SERVER['PHP_SELF']) === 'login.php') {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                $stmt = $pdo->prepare("UPDATE users SET is_online = 1, last_activity = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email ou mot de passe incorrect';
            }
        } catch (PDOException $e) {
            $error = 'Erreur de base de données';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connexion - Rami 261</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-[var(--bg-primary)] p-4">
        <div class="w-full max-w-sm">
            
            <!-- BACKGROUND -->
            <div class="bg-layer-1" style="position:fixed;top:0;left:0;right:0;bottom:0;background:radial-gradient(ellipse at center,#e8e5f0,#d5d0e0);z-index:0;pointer-events:none;"></div>
            <div class="bg-layer-flag" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-15deg) scale(2);width:400px;height:267px;opacity:0.08;z-index:0;pointer-events:none;border-radius:12px;background:linear-gradient(to right,#ffffff 0%,#ffffff 33.33%,#fc3e32 33.33%,#fc3e32 66.66%,#007a3d 66.66%,#007a3d 100%);"></div>
            
            <div class="relative z-10">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 to-cyan-400 bg-clip-text text-transparent">🃏 Rami 261</h1>
                    <p class="text-[var(--text-secondary)] text-sm mt-2">Connectez-vous pour jouer</p>
                </div>
                <div class="glass p-6 rounded-2xl">
                    <?php if ($error): ?>
                        <div class="bg-red-500/20 border border-red-500/30 text-red-600 px-4 py-2 rounded-lg text-sm mb-4"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">Email</label>
                            <input type="email" name="email" required class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" placeholder="votre@email.com">
                        </div>
                        <div>
                            <label class="text-sm text-[var(--text-secondary)] block mb-1">Mot de passe</label>
                            <input type="password" name="password" required class="w-full px-4 py-2 bg-white rounded-lg text-[var(--text-primary)] border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" placeholder="Votre mot de passe">
                        </div>
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">Se connecter</button>
                    </form>
                    <div class="text-center mt-4"><a href="register.php" class="text-sm text-[var(--accent-primary)] hover:underline">Pas encore de compte ? S'inscrire</a></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>