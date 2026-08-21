<?php
// profile.php - Profil utilisateur avec lien transactions
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

// Récupérer les infos du joueur
$stmt = $pdo->prepare("
    SELECT u.*,
           (SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 1) as wins,
           (SELECT COUNT(*) FROM game_players WHERE user_id = u.id) as games_played
    FROM users u WHERE u.id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Récupérer l'historique des parties (5 dernières)
$stmt = $pdo->prepare("
    SELECT gh.*, u.username as winner_name,
           (SELECT COUNT(*) FROM game_players WHERE game_id = gh.game_id) as player_count
    FROM game_history gh
    JOIN users u ON gh.winner_id = u.id
    WHERE gh.winner_id = ? OR gh.game_id IN (SELECT game_id FROM game_players WHERE user_id = ?)
    ORDER BY gh.finished_at DESC LIMIT 5
");
$stmt->execute([$userId, $userId]);
$history = $stmt->fetchAll();

$theme = $_SESSION['theme'] ?? $_COOKIE['theme'] ?? 'light';
$updateSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $newUsername = trim($_POST['username'] ?? '');
    $newPhone = trim($_POST['phone'] ?? '');
    
    if (!empty($newUsername) && !empty($newPhone)) {
        if (!preg_match('/^(03[23478])\d{7}$/', $newPhone)) {
            $updateSuccess = '❌ Numéro de téléphone invalide. Format: 034, 032, 037, 038 ou 033 + 7 chiffres';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$newUsername, $newPhone, $userId])) {
                $_SESSION['username'] = $newUsername;
                $_SESSION['phone'] = $newPhone;
                $updateSuccess = '✅ Profil mis à jour avec succès !';
                $user['username'] = $newUsername;
                $user['phone'] = $newPhone;
            } else {
                $updateSuccess = '❌ Erreur lors de la mise à jour';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profil - Rami 261</title>
    
    <link rel="icon" href="favicon.php" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.php" type="image/x-icon">
    
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
                <a href="index.php" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="w-8 h-6 rounded overflow-hidden shadow-md flex-shrink-0 bg-white">
                    <img src="assets/images/flags/madagascar.png" alt="Madagascar" 
                         class="w-full h-full object-cover"
                         onerror="this.style.display='none'">
                </div>
                <h1 class="text-lg font-bold text-[var(--text-primary)]">👤 Mon Profil</h1>
            </div>
            <button id="themeToggle" class="theme-toggle">
                <span id="themeIcon"><?php echo $theme === 'dark' ? '🌙' : '☀️'; ?></span>
            </button>
        </header>
        
        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <?php if ($updateSuccess): ?>
                <div class="px-4 py-2 rounded-lg text-sm mb-4 <?php echo strpos($updateSuccess, '✅') !== false ? 'bg-green-500/20 border border-green-500/30 text-green-600' : 'bg-red-500/20 border border-red-500/30 text-red-600'; ?>">
                    <?php echo $updateSuccess; ?>
                </div>
            <?php endif; ?>
            
            <!-- CARTE DE PROFIL -->
            <div class="glass p-6 rounded-2xl text-center mb-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-r from-purple-500 to-cyan-500 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-3 shadow-lg shadow-purple-500/25">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <h2 class="text-xl font-bold text-[var(--text-primary)]"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="text-sm text-[var(--text-secondary)]">📱 <?php echo htmlspecialchars($user['phone']); ?></p>
                <p class="text-sm text-[var(--text-secondary)] mt-1">📅 Membre depuis <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                
                <!-- STATISTIQUES -->
                <div class="grid grid-cols-3 gap-3 mt-4">
                    <div class="stat-card">
                        <span class="stat-icon">💰</span>
                        <p class="stat-value"><?php echo formatCurrency($user['balance'] ?? 0); ?></p>
                        <p class="stat-label">Solde</p>
                    </div>
                    <div class="stat-card" style="border-color: rgba(34,197,94,0.3);">
                        <span class="stat-icon">🏅</span>
                        <p class="stat-value" style="color: #22c55e;"><?php echo $user['wins'] ?? 0; ?></p>
                        <p class="stat-label">Victoires</p>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">📊</span>
                        <p class="stat-value"><?php echo $user['games_played'] ?? 0; ?></p>
                        <p class="stat-label">Parties</p>
                    </div>
                </div>
                
                <!-- BOUTONS D'ACTION -->
                <div class="flex flex-wrap gap-2 justify-center mt-4">
                    <a href="settings.php" class="inline-flex items-center gap-2 px-4 py-2 glass rounded-lg text-[var(--text-primary)] text-sm hover:bg-[var(--bg-glass)] transition-colors">
                        ⚙️ Paramètres
                    </a>
                    
                    <a href="transactions.php" class="inline-flex items-center gap-2 px-4 py-2 glass rounded-lg text-[var(--text-primary)] text-sm hover:bg-[var(--bg-glass)] transition-colors">
                        💰 Transactions
                    </a>
                    <a href="logout.php" class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/20 border border-red-500/30 text-red-500 rounded-lg text-sm hover:bg-red-500/30 transition-colors">
                        🚪 Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- MODIFIER LE PROFIL -->
            <div class="glass p-4 rounded-2xl mb-6">
                <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">✏️ Modifier le profil</h3>
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="action" value="update_profile">
                    <div>
                        <label class="text-xs text-[var(--text-secondary)] block mb-1">Nom d'utilisateur</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-3 py-2 bg-[var(--bg-secondary)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]">
                    </div>
                    <div>
                        <label class="text-xs text-[var(--text-secondary)] block mb-1">📱 Numéro de téléphone</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="w-full px-3 py-2 bg-[var(--bg-secondary)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" placeholder="034 07 223 34" maxlength="10">
                        <p class="text-xs text-[var(--text-secondary)] mt-1">Format: 034, 032, 037, 038 ou 033 + 7 chiffres</p>
                    </div>
                    <button type="submit" class="w-full py-2 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">
                        💾 Mettre à jour
                    </button>
                </form>
            </div>
            
            <!-- HISTORIQUE DES PARTIES -->
            <div>
                <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">📜 Dernières parties</h3>
                <?php if (empty($history)): ?>
                    <div class="glass p-4 text-center text-[var(--text-secondary)] text-sm">
                        Aucune partie jouée
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($history as $h): ?>
                            <div class="glass p-3 rounded-lg flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-bold text-[var(--text-primary)]">
                                        <?php if ($h['winner_id'] == $userId): ?>
                                            🏆 Victoire
                                        <?php else: ?>
                                            ❌ Défaite
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-[var(--text-secondary)]">
                                        <?php echo date('d/m/Y H:i', strtotime($h['finished_at'])); ?> 
                                        • <?php echo $h['player_count']; ?> joueurs
                                    </p>
                                </div>
                                <div class="text-right">
                                    <?php if ($h['winner_id'] == $userId): ?>
                                        <p class="text-sm font-bold text-green-400">
                                            +<?php echo formatCurrency($h['net_win'] ?? 0); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="text-xs text-[var(--text-secondary)]">
                                        <?php 
                                            $types = [
                                                'normal' => 'Rami normal',
                                                'tri_joker' => '⭐ Triple Joker',
                                                'quadri_joker' => '⭐⭐ Quadruple Joker'
                                            ];
                                            echo $types[$h['win_type']] ?? $h['win_type']; 
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                <a href="profile.php" class="flex flex-col items-center text-[var(--accent-primary)] py-1 px-3 rounded-lg transition-colors">
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
            icon.textContent = theme === 'dark' ? '🌙' : '☀️';
        }
        
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            setTheme(current === 'dark' ? 'light' : 'dark');
        });
        
        // Formatage du numéro de téléphone
        document.querySelector('input[name="phone"]')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
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
                const size = 2 + Math.random() * 4;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.animationDuration = (15 + Math.random() * 25) + 's';
                particle.style.animationDelay = Math.random() * 20 + 's';
                const color = colors[Math.floor(Math.random() * colors.length)];
                particle.style.background = color;
                particle.style.boxShadow = '0 0 10px ' + color;
                
                container.appendChild(particle);
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = document.cookie.split('; ').find(row => row.startsWith('theme='));
            const theme = savedTheme ? savedTheme.split('=')[1] : 'light';
            setTheme(theme);
            createBackgroundCards();
            createParticles();
        });
    </script>
</body>
</html>