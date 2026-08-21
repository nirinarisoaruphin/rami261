<?php
// login.php - Page de connexion avec champs visibles
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($phone) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } elseif (!preg_match('/^(03[23478])\d{7}$/', $phone)) {
        $error = 'Numéro de téléphone invalide';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
            $stmt->execute([$phone]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['phone'] = $user['phone'];
                
                $stmt = $pdo->prepare("
                    SELECT balance,
                           (SELECT COUNT(*) FROM game_players WHERE user_id = ? AND is_winner = 1) as wins,
                           (SELECT COUNT(*) FROM game_players WHERE user_id = ?) as games_played
                    FROM users WHERE id = ?
                ");
                $stmt->execute([$user['id'], $user['id'], $user['id']]);
                $_SESSION['user_stats'] = $stmt->fetch();
                
                $stmt = $pdo->prepare("UPDATE users SET is_online = 1, last_activity = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                redirect('index.php');
            } else {
                $error = 'Numéro ou mot de passe incorrect';
            }
        } catch (PDOException $e) {
            $error = 'Erreur de base de données';
        }
    }
}

$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connexion - Rami 261</title>
    
    <link rel="icon" href="favicon.php" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.php" type="image/x-icon">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .input-visible {
            background: #ffffff !important;
            color: #1a1a2e !important;
            border: 2px solid #7c3aed !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 16px !important;
            font-weight: 500 !important;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1) !important;
            transition: all 0.3s ease !important;
        }
        
        .input-visible:focus {
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.2) !important;
            border-color: #5b21b6 !important;
            transform: scale(1.01);
        }
        
        .input-visible::placeholder {
            color: #9ca3af !important;
            font-weight: 400 !important;
            opacity: 0.8 !important;
        }
        
        [data-theme="dark"] .input-visible {
            background: #1a1a2e !important;
            color: #f0f0f5 !important;
            border-color: #7c3aed !important;
        }
        
        [data-theme="dark"] .input-visible::placeholder {
            color: #6a6a8a !important;
        }
        
        .label-visible {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #1a1a2e !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin-bottom: 6px !important;
        }
        
        [data-theme="dark"] .label-visible {
            color: #f0f0f5 !important;
        }
        
        .badge-format {
            background: rgba(124, 58, 237, 0.08) !important;
            border: 1px solid rgba(124, 58, 237, 0.15) !important;
            padding: 3px 10px !important;
            border-radius: 20px !important;
            font-size: 10px !important;
            color: #4a4a5e !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }
        
        .badge-format:hover {
            background: rgba(124, 58, 237, 0.15) !important;
            transform: scale(1.02);
        }
        
        [data-theme="dark"] .badge-format {
            color: #a0a0b8 !important;
            background: rgba(124, 58, 237, 0.12) !important;
            border-color: rgba(124, 58, 237, 0.2) !important;
        }
        
        [data-theme="dark"] .badge-format:hover {
            background: rgba(124, 58, 237, 0.2) !important;
        }
        
        .eye-button {
            background: transparent !important;
            border: none !important;
            padding: 8px !important;
            cursor: pointer !important;
            color: #6a6a8a !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .eye-button:hover {
            color: #7c3aed !important;
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-[var(--bg-primary)] p-4">
        <div class="w-full max-w-sm">
            
            <!-- BACKGROUND -->
            <div class="bg-layer-1" style="position:fixed;top:0;left:0;right:0;bottom:0;background:var(--bg-layer-1);z-index:0;pointer-events:none;"></div>
            <div class="bg-layer-flag" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-15deg) scale(2);width:400px;height:267px;opacity:0.08;z-index:0;pointer-events:none;border-radius:12px;background:linear-gradient(to right,#ffffff 0%,#ffffff 33.33%,#fc3e32 33.33%,#fc3e32 66.66%,#007a3d 66.66%,#007a3d 100%);"></div>
            
            <div class="relative z-10">
                <div class="text-center mb-8">
                    <!-- TITRE AVEC DRAPEAU -->
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
                    <p class="text-[var(--text-secondary)] text-sm mt-2">🇲🇬 Connectez-vous avec votre numéro</p>
                </div>
                
                <div class="glass p-6 rounded-2xl">
                    <?php if ($error): ?>
                        <div class="bg-red-500/20 border border-red-500/30 text-red-600 px-4 py-3 rounded-lg text-sm mb-4 flex items-center gap-2">
                            <span>❌</span>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-5">
                        <!-- NUMÉRO DE TÉLÉPHONE -->
                        <div>
                            <label class="label-visible">
                                📱 Numéro de téléphone
                                <span class="text-red-500 text-sm">*</span>
                            </label>
                            <input type="tel" name="phone" required value="<?php echo htmlspecialchars($phone); ?>" 
                                   class="input-visible w-full" 
                                   placeholder="034 07 223 34" maxlength="10">
                            <div class="flex flex-wrap gap-1 mt-2">
                                <span class="badge-format">034 XX XXX XX</span>
                                <span class="badge-format">032 XX XXX XX</span>
                                <span class="badge-format">037 XX XXX XX</span>
                                <span class="badge-format">038 XX XXX XX</span>
                                <span class="badge-format">033 XX XXX XX</span>
                            </div>
                        </div>
                        
                        <!-- MOT DE PASSE -->
                        <div>
                            <label class="label-visible">
                                🔒 Mot de passe
                                <span class="text-red-500 text-sm">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="password" id="loginPassword" required 
                                       class="input-visible w-full pr-12" 
                                       placeholder="Votre mot de passe" 
                                       value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>">
                                <button type="button" onclick="togglePassword('loginPassword', this)" class="eye-button absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="loginPasswordIcon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-[var(--text-secondary)] mt-1 opacity-70"></p>
                        </div>
                        
                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-xl text-white font-bold text-sm hover:scale-105 transition-all duration-300 shadow-lg shadow-purple-500/25">
                            🔐 Se connecter
                        </button>
                    </form>
                    
                    <div class="text-center mt-5 pt-4 border-t border-[var(--border-glass)]">
                        <a href="register.php" class="text-sm text-[var(--accent-primary)] hover:underline font-medium">
                            Pas encore de compte ? S'inscrire
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // ============================================
        // AFFICHER/MASQUER LE MOT DE PASSE
        // ============================================
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
        
        // ============================================
        // FORMATAGE AUTO DU NUMÉRO
        // ============================================
        document.querySelector('input[name="phone"]')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
        
        // ============================================
        // THÈME
        // ============================================
        const savedTheme = document.cookie.split('; ').find(row => row.startsWith('theme='));
        const theme = savedTheme ? savedTheme.split('=')[1] : 'light';
        document.documentElement.setAttribute('data-theme', theme);
    </script>
</body>
</html>