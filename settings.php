<?php
// settings.php - Paramètres avec afficher/masquer mot de passe
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$currentTheme = $_COOKIE['theme'] ?? 'light';

// Changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Veuillez remplir tous les champs';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Le nouveau mot de passe doit faire au moins 6 caractères';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Les mots de passe ne correspondent pas';
        } else {
            // Vérifier l'ancien mot de passe
            if (password_verify($oldPassword, $user['password'])) {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed, $userId])) {
                    $message = '✅ Mot de passe modifié avec succès !';
                } else {
                    $error = '❌ Erreur lors de la modification';
                }
            } else {
                $error = '❌ Ancien mot de passe incorrect';
            }
        }
    }
}

$theme = $user['theme'] ?? $_COOKIE['theme'] ?? 'light';
$notifications = $user['notifications'] ?? $_COOKIE['notifications'] ?? 1;
$sound = $user['sound'] ?? $_COOKIE['sound'] ?? 1;
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Paramètres - Rami 261</title>
    
    <link rel="icon" href="favicon.php" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.php" type="image/x-icon">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cards.css">
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col">
        
        <div class="bg-layer-1"></div>
        <div class="bg-layer-flag"></div>
        <div class="bg-layer-cards" id="cardsBackground"></div>
        <div class="bg-layer-particles" id="particlesContainer"></div>
        
        <header class="glass p-4 flex justify-between items-center z-10">
            <div class="flex items-center gap-3">
                <a href="profile.php" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="flag-header"></div>
                <h1 class="text-lg font-bold text-[var(--text-primary)]">⚙️ Paramètres</h1>
            </div>
            <button id="themeToggle" class="theme-toggle">
                <span id="themeIcon"><?php echo $theme === 'dark' ? '🌙' : '☀️'; ?></span>
            </button>
        </header>
        
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <?php if ($message): ?>
                <div class="bg-green-500/20 border border-green-500/30 text-green-600 px-4 py-3 rounded-lg text-sm mb-4"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-600 px-4 py-3 rounded-lg text-sm mb-4"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="glass p-6 rounded-2xl space-y-6">
                
                <!-- Thème -->
                <div>
                    <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">🎨 Apparence</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 text-[var(--text-secondary)] text-sm cursor-pointer">
                            <input type="radio" name="theme" value="light" <?php echo $theme === 'light' ? 'checked' : ''; ?> class="accent-[var(--accent-primary)] w-4 h-4">
                            ☀️ Thème clair
                        </label>
                        <label class="flex items-center gap-3 text-[var(--text-secondary)] text-sm cursor-pointer">
                            <input type="radio" name="theme" value="dark" <?php echo $theme === 'dark' ? 'checked' : ''; ?> class="accent-[var(--accent-primary)] w-4 h-4">
                            🌙 Thème sombre
                        </label>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div>
                    <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">🔔 Notifications</h3>
                    <label class="flex items-center gap-3 text-[var(--text-secondary)] text-sm cursor-pointer">
                        <input type="checkbox" name="notifications" value="1" <?php echo $notifications ? 'checked' : ''; ?> class="accent-[var(--accent-primary)] w-4 h-4">
                        Activer les notifications
                    </label>
                </div>
                
                <!-- Sons -->
                <div>
                    <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">🔊 Sons</h3>
                    <label class="flex items-center gap-3 text-[var(--text-secondary)] text-sm cursor-pointer">
                        <input type="checkbox" name="sound" value="1" <?php echo $sound ? 'checked' : ''; ?> class="accent-[var(--accent-primary)] w-4 h-4">
                        Activer les effets sonores
                    </label>
                </div>
                
                <!-- Changement de mot de passe -->
                <div class="border-t border-[var(--border-glass)] pt-4">
                    <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">🔑 Changer le mot de passe</h3>
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="action" value="change_password">
                        <div>
                            <label class="text-xs text-[var(--text-secondary)] block mb-1">Ancien mot de passe</label>
                            <div class="relative">
                                <input type="password" name="old_password" id="oldPassword" required class="w-full px-3 py-2 bg-[var(--bg-secondary)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)] pr-10">
                                <button type="button" onclick="togglePassword('oldPassword', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-[var(--text-secondary)] block mb-1">Nouveau mot de passe</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="newPassword" required class="w-full px-3 py-2 bg-[var(--bg-secondary)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)] pr-10" minlength="6">
                                <button type="button" onclick="togglePassword('newPassword', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-[var(--text-secondary)] mt-1">Minimum 6 caractères</p>
                        </div>
                        <div>
                            <label class="text-xs text-[var(--text-secondary)] block mb-1">Confirmer le nouveau mot de passe</label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="confirmNewPassword" required class="w-full px-3 py-2 bg-[var(--bg-secondary)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)] pr-10" minlength="6">
                                <button type="button" onclick="togglePassword('confirmNewPassword', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">
                            🔐 Changer le mot de passe
                        </button>
                    </form>
                </div>
                
                <button type="submit" id="saveSettings" class="w-full py-2.5 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">
                    💾 Sauvegarder les paramètres
                </button>
            </div>
            
            <div class="glass p-4 rounded-2xl mt-4">
                <h3 class="text-sm font-bold text-[var(--text-primary)] mb-2">ℹ️ Informations</h3>
                <ul class="text-xs text-[var(--text-secondary)] space-y-1">
                    <li>👤 Connecté en tant que : <strong class="text-[var(--text-primary)]"><?php echo htmlspecialchars($user['username']); ?></strong></li>
                    <li>📱 Téléphone : <strong class="text-[var(--text-primary)]"><?php echo htmlspecialchars($user['phone']); ?></strong></li>
                    <li>🎨 Thème actuel : <strong class="text-[var(--text-primary)]"><?php echo $theme === 'dark' ? 'Sombre 🌙' : 'Clair ☀️'; ?></strong></li>
                </ul>
            </div>
            
        </main>
        
        <nav class="fixed bottom-0 left-0 right-0 glass border-t border-[var(--border-glass)] z-20">
            <div class="flex justify-around max-w-md mx-auto p-2">
                <a href="index.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-xs">Accueil</span>
                </a>
                <a href="game.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.5 6.5L5 3l3 2.5M5 3l-2 5 3-2.5z"/></svg>
                    <span class="text-xs">Partie</span>
                </a>
                <a href="leaderboard.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="text-xs">Classement</span>
                </a>
                <a href="profile.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-xs">Profil</span>
                </a>
            </div>
        </nav>
        
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
        // SAUVEGARDE DES PARAMÈTRES
        // ============================================
        document.getElementById('saveSettings')?.addEventListener('click', function(e) {
            e.preventDefault();
            
            const theme = document.querySelector('input[name="theme"]:checked')?.value || 'light';
            const notifications = document.querySelector('input[name="notifications"]')?.checked ? 1 : 0;
            const sound = document.querySelector('input[name="sound"]')?.checked ? 1 : 0;
            
            // Sauvegarder via AJAX
            fetch('api/user/theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ theme, notifications, sound })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour le thème
                    document.documentElement.setAttribute('data-theme', theme);
                    document.cookie = `theme=${theme}; path=/; max-age=31536000`;
                    
                    const icon = document.getElementById('themeIcon');
                    icon.textContent = theme === 'dark' ? '🌙' : '☀️';
                    
                    // Afficher un message
                    const msg = document.createElement('div');
                    msg.className = 'bg-green-500/20 border border-green-500/30 text-green-600 px-4 py-3 rounded-lg text-sm mb-4';
                    msg.textContent = '✅ Paramètres sauvegardés avec succès !';
                    const container = document.querySelector('.glass');
                    container.insertBefore(msg, container.firstChild);
                    
                    setTimeout(() => msg.remove(), 3000);
                }
            })
            .catch(err => console.error(err));
        });
        
        // ============================================
        // CARTES EN ARRIÈRE-PLAN
        // ============================================
        
        function createBackgroundCards() {
            const container = document.getElementById('cardsBackground');
            if (!container) return;
            
            const suits = ['♠', '♥', '♦', '♣'];
            const colors = ['black', 'red', 'red', 'black'];
            const values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
            const animations = ['floatCard1', 'floatCard2', 'floatCard3'];
            
            for (let i = 0; i < 15; i++) {
                const suit = suits[Math.floor(Math.random() * suits.length)];
                const color = colors[suits.indexOf(suit)];
                const value = values[Math.floor(Math.random() * values.length)];
                const animIndex = Math.floor(Math.random() * animations.length);
                
                const card = document.createElement('div');
                card.className = `bg-card-item ${color}`;
                
                card.style.left = (5 + Math.random() * 85) + '%';
                card.style.top = (5 + Math.random() * 85) + '%';
                card.style.setProperty('--rotate', (-25 + Math.random() * 50) + 'deg');
                card.style.setProperty('--duration', (18 + Math.random() * 25) + 's');
                card.style.setProperty('--delay', Math.random() * 15 + 's');
                card.style.setProperty('--anim', animations[animIndex]);
                
                card.innerHTML = `
                    <span class="value-top">${value}</span>
                    <span class="suit">${suit}</span>
                    <span class="value-bottom">${value}</span>
                `;
                
                container.appendChild(card);
            }
        }
        
        function createParticles() {
            const container = document.getElementById('particlesContainer');
            if (!container) return;
            
            const colors = ['#7c3aed', '#06b6d4', '#fc3e32', '#007a3d', '#6b21a8'];
            
            for (let i = 0; i < 30; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                particle.style.left = Math.random() * 100 + '%';
                particle.style.width = (2 + Math.random() * 4) + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDuration = (15 + Math.random() * 25) + 's';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.background = colors[Math.floor(Math.random() * colors.length)];
                particle.style.boxShadow = '0 0 10px ' + particle.style.background;
                
                container.appendChild(particle);
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = document.cookie.split('; ').find(row => row.startsWith('theme='));
            const theme = savedTheme ? savedTheme.split('=')[1] : 'light';
            document.documentElement.setAttribute('data-theme', theme);
            
            createBackgroundCards();
            createParticles();
        });
    </script>
</body>
</html>