<?php
// settings.php - Paramètres utilisateur avec sauvegarde BDD
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Récupérer les infos de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Récupérer le thème actuel
$currentTheme = $_COOKIE['theme'] ?? 'light';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'light';
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    $sound = isset($_POST['sound']) ? 1 : 0;
    
    try {
        // Sauvegarder dans la base de données
        $stmt = $pdo->prepare("UPDATE users SET theme = ?, notifications = ?, sound = ? WHERE id = ?");
        $stmt->execute([$theme, $notifications, $sound, $userId]);
        
        // Sauvegarder dans le cookie
        setcookie('theme', $theme, time() + 31536000, '/');
        setcookie('notifications', $notifications, time() + 31536000, '/');
        setcookie('sound', $sound, time() + 31536000, '/');
        
        $_SESSION['theme'] = $theme;
        $_SESSION['notifications'] = $notifications;
        $_SESSION['sound'] = $sound;
        
        $message = '✅ Paramètres mis à jour avec succès !';
        $currentTheme = $theme;
        
        // Rediriger pour appliquer le thème immédiatement
        header('Location: settings.php?success=1');
        exit;
        
    } catch (PDOException $e) {
        $error = '❌ Erreur lors de la sauvegarde : ' . $e->getMessage();
    }
}

// Vérifier si la colonne theme existe dans la table users
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'theme'");
    if ($stmt->rowCount() == 0) {
        // Ajouter la colonne theme si elle n'existe pas
        $pdo->exec("ALTER TABLE users ADD COLUMN theme VARCHAR(20) DEFAULT 'light'");
        $pdo->exec("ALTER TABLE users ADD COLUMN notifications TINYINT(1) DEFAULT 1");
        $pdo->exec("ALTER TABLE users ADD COLUMN sound TINYINT(1) DEFAULT 1");
    }
} catch (PDOException $e) {
    // Ignorer les erreurs
}

// Récupérer les préférences depuis la BDD
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cards.css">
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col">
        
        <!-- BACKGROUND -->
        <div class="bg-layer-1"></div>
        <div class="bg-layer-flag"></div>
        <div class="bg-layer-cards" id="cardsBackground"></div>
        <div class="bg-layer-particles" id="particlesContainer"></div>
        
        <!-- HEADER -->
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
            <button id="themeToggle" class="theme-toggle" title="Changer de thème">
                <span id="themeIcon"><?php echo $theme === 'dark' ? '🌙' : '☀️'; ?></span>
            </button>
        </header>
        
        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-500/20 border border-green-500/30 text-green-600 px-4 py-3 rounded-lg text-sm mb-4">
                    ✅ Paramètres mis à jour avec succès !
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-600 px-4 py-3 rounded-lg text-sm mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="glass p-6 rounded-2xl">
                <form method="POST" class="space-y-6">
                    
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
                        <p class="text-xs text-[var(--text-secondary)] mt-2 opacity-60">
                            Le thème est sauvegardé dans votre compte
                        </p>
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
                    
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">
                        💾 Sauvegarder les paramètres
                    </button>
                </form>
            </div>
            
            <!-- Informations -->
            <div class="glass p-4 rounded-2xl mt-4">
                <h3 class="text-sm font-bold text-[var(--text-primary)] mb-2">ℹ️ Informations</h3>
                <ul class="text-xs text-[var(--text-secondary)] space-y-1">
                    <li>👤 Connecté en tant que : <strong class="text-[var(--text-primary)]"><?php echo htmlspecialchars($user['username']); ?></strong></li>
                    <li>📧 Email : <strong class="text-[var(--text-primary)]"><?php echo htmlspecialchars($user['email']); ?></strong></li>
                    <li>🎨 Thème actuel : <strong class="text-[var(--text-primary)]"><?php echo $theme === 'dark' ? 'Sombre 🌙' : 'Clair ☀️'; ?></strong></li>
                    <li>💾 Les préférences sont sauvegardées dans votre compte</li>
                </ul>
            </div>
            
        </main>
        
        <!-- BOTTOM NAVIGATION -->
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
        // GESTION DU THÈME
        // ============================================
        
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            document.cookie = `theme=${theme}; path=/; max-age=31536000`;
            
            const icon = document.getElementById('themeIcon');
            if (theme === 'dark') {
                icon.textContent = '🌙';
            } else {
                icon.textContent = '☀️';
            }
            
            // Sauvegarder via AJAX
            fetch('api/user/theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ theme: theme })
            }).catch(err => console.log('Theme saved locally'));
        }
        
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
            
            // Mettre à jour le formulaire
            document.querySelector(`input[name="theme"][value="${newTheme}"]`).checked = true;
        }
        
        // ============================================
        // GÉNÉRER LES CARTES EN ARRIÈRE-PLAN
        // ============================================
        
        function createBackgroundCards() {
            const container = document.getElementById('cardsBackground');
            if (!container) return;
            
            const suits = ['♠', '♥', '♦', '♣'];
            const colors = ['black', 'red', 'red', 'black'];
            const values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
            const animations = ['floatCard1', 'floatCard2', 'floatCard3'];
            
            const numCards = 15;
            
            for (let i = 0; i < numCards; i++) {
                const suit = suits[Math.floor(Math.random() * suits.length)];
                const color = colors[suits.indexOf(suit)];
                const value = values[Math.floor(Math.random() * values.length)];
                const animIndex = Math.floor(Math.random() * animations.length);
                
                const card = document.createElement('div');
                card.className = `bg-card-item ${color}`;
                
                const x = 5 + Math.random() * 85;
                const y = 5 + Math.random() * 85;
                const rotate = -25 + Math.random() * 50;
                const duration = 18 + Math.random() * 25;
                const delay = Math.random() * 15;
                
                card.style.left = x + '%';
                card.style.top = y + '%';
                card.style.setProperty('--rotate', rotate + 'deg');
                card.style.setProperty('--duration', duration + 's');
                card.style.setProperty('--delay', delay + 's');
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
            
            const numParticles = 30;
            const colors = ['#7c3aed', '#06b6d4', '#fc3e32', '#007a3d', '#6b21a8'];
            
            for (let i = 0; i < numParticles; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const x = Math.random() * 100;
                const size = 2 + Math.random() * 4;
                const duration = 15 + Math.random() * 25;
                const delay = Math.random() * 20;
                const color = colors[Math.floor(Math.random() * colors.length)];
                
                particle.style.left = x + '%';
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.animationDuration = duration + 's';
                particle.style.animationDelay = delay + 's';
                particle.style.background = color;
                particle.style.boxShadow = '0 0 10px ' + color;
                
                container.appendChild(particle);
            }
        }
        
        // ============================================
        // INITIALISATION
        // ============================================
        
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = document.cookie.split('; ').find(row => row.startsWith('theme='));
            const theme = savedTheme ? savedTheme.split('=')[1] : 'light';
            document.documentElement.setAttribute('data-theme', theme);
            
            createBackgroundCards();
            createParticles();
            
            document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
        });
    </script>
</body>
</html>