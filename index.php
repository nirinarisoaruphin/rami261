<?php
// index.php - Page d'accueil
require_once 'includes/config.php';
require_once 'includes/functions.php';

$isLoggedIn = isLoggedIn();
$username = getCurrentUsername();
$userId = getCurrentUserId();

$activeGames = [];
$playerStats = null;

if ($isLoggedIn) {
    try {
        $stmt = $pdo->prepare("
            SELECT g.*, COUNT(gp.id) as player_count, u.username as host_name
            FROM games g
            JOIN users u ON g.host_id = u.id
            LEFT JOIN game_players gp ON g.id = gp.game_id
            WHERE g.status IN ('waiting', 'playing')
            GROUP BY g.id
            ORDER BY g.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $activeGames = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("
            SELECT balance,
                   (SELECT COUNT(*) FROM game_players WHERE user_id = ? AND is_winner = 1) as wins,
                   (SELECT COUNT(*) FROM game_players WHERE user_id = ?) as games_played
            FROM users WHERE id = ?
        ");
        $stmt->execute([$userId, $userId, $userId]);
        $playerStats = $stmt->fetch();
    } catch (PDOException $e) {
        $activeGames = [];
        $playerStats = null;
    }
}

$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Rami 261 - Accueil</title>
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
                <div class="flag-header"></div>
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-purple-400 to-cyan-400 bg-clip-text text-transparent">🃏 Rami 261</h1>
                    <span class="text-xs text-[var(--text-secondary)]">🇲🇬 Jeu de cartes en ligne</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button id="themeToggle" class="theme-toggle" title="Changer de thème">
                    <span id="themeIcon">☀️</span>
                </button>
                <?php if ($isLoggedIn): ?>
                    <a href="profile.php" class="flex items-center gap-2 glass px-3 py-1 rounded-full hover:bg-[var(--bg-glass)] transition-colors">
                        <span class="text-xs font-medium text-[var(--text-primary)]"><?php echo htmlspecialchars($username); ?></span>
                        <span class="w-6 h-6 rounded-full bg-gradient-to-r from-purple-500 to-cyan-500 flex items-center justify-center text-white text-xs font-bold">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        </header>
        
        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <div class="glass p-4 rounded-2xl mb-6 flex items-center gap-4 bg-gradient-to-r from-purple-500/10 to-cyan-500/10 animate-slide-up">
                <div class="flag-banner"></div>
                <div class="flex-1">
                    <h2 class="text-sm font-bold text-[var(--text-primary)]">🇲🇬 Rami 261 Madagascar</h2>
                    <p class="text-xs text-[var(--text-secondary)]">Le jeu de cartes préféré des Malgaches</p>
                </div>
            </div>
            
            <?php if (!$isLoggedIn): ?>
                <div class="glass p-6 rounded-2xl text-center mb-6 bg-gradient-to-r from-purple-500/20 to-cyan-500/20">
                    <h2 class="text-2xl font-bold text-[var(--text-primary)] mb-2">🎯 Bienvenue au Rami 261</h2>
                    <p class="text-[var(--text-secondary)] text-sm mb-4">Rejoignez des parties en ligne avec vos amis</p>
                    <div class="flex gap-3 justify-center flex-wrap">
                        <a href="login.php" class="px-6 py-2 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">Se connecter</a>
                        <a href="register.php" class="px-6 py-2 glass rounded-lg text-[var(--text-primary)] font-bold text-sm hover:bg-[var(--bg-glass)] transition-colors">S'inscrire</a>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="glass p-3 text-center"><span class="text-2xl">🃏</span><p class="text-xs text-[var(--text-secondary)] mt-1">108 cartes</p></div>
                    <div class="glass p-3 text-center"><span class="text-2xl">👥</span><p class="text-xs text-[var(--text-secondary)] mt-1">2-5 joueurs</p></div>
                    <div class="glass p-3 text-center"><span class="text-2xl">🏆</span><p class="text-xs text-[var(--text-secondary)] mt-1">Classement</p></div>
                </div>
                
            <?php else: ?>
                
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="stat-card"><span class="stat-icon">💰</span><p class="stat-value"><?php echo number_format($playerStats['balance'] ?? 0, 2); ?>€</p><p class="stat-label">Solde</p></div>
                    <div class="stat-card" style="border-color: rgba(34,197,94,0.3);"><span class="stat-icon">🏅</span><p class="stat-value" style="color: #22c55e;"><?php echo $playerStats['wins'] ?? 0; ?></p><p class="stat-label">Victoires</p></div>
                    <div class="stat-card"><span class="stat-icon">📊</span><p class="stat-value"><?php echo $playerStats['games_played'] ?? 0; ?></p><p class="stat-label">Parties</p></div>
                </div>
                
                <div class="glass p-4 rounded-2xl mb-6">
                    <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">🚀 Créer une partie</h3>
                    <form id="createGameForm" class="flex gap-3">
                        <input type="number" id="betAmount" placeholder="Mise (€)" class="flex-1 px-3 py-2 bg-[var(--bg-secondary)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]" min="0.5" step="0.5" value="1.00">
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">Créer</button>
                    </form>
                </div>
                
                <div class="glass p-4 rounded-2xl mb-6">
                    <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">🔑 Rejoindre une partie</h3>
                    <form id="joinGameForm" class="flex gap-3">
                        <input type="text" id="roomCode" placeholder="Code (ex: ABC123)" class="flex-1 px-3 py-2 bg-[var(--bg-secondary)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)] uppercase" maxlength="6">
                        <button type="submit" class="px-4 py-2 glass rounded-lg text-[var(--text-primary)] font-bold text-sm hover:bg-[var(--bg-glass)] transition-colors">Rejoindre</button>
                    </form>
                </div>
                
                <div>
                    <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">📋 Parties actives</h3>
                    <?php if (empty($activeGames)): ?>
                        <div class="glass p-4 text-center text-[var(--text-secondary)] text-sm">Aucune partie active</div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($activeGames as $game): ?>
                                <a href="game.php?id=<?php echo $game['id']; ?>" class="glass p-3 rounded-lg flex justify-between items-center hover:bg-[var(--bg-glass)] transition-colors">
                                    <div>
                                        <p class="font-bold text-[var(--text-primary)] text-sm">#<?php echo htmlspecialchars($game['room_code']); ?></p>
                                        <p class="text-xs text-[var(--text-secondary)]">👤 <?php echo htmlspecialchars($game['host_name']); ?> • 💰 <?php echo number_format($game['bet_amount'], 2); ?>€ • 👥 <?php echo $game['player_count']; ?>/<?php echo $game['max_players']; ?></p>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs <?php echo $game['status'] === 'waiting' ? 'badge-warning' : 'badge-success'; ?>">
                                        <?php echo $game['status'] === 'waiting' ? '⏳ En attente' : '🔄 En cours'; ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
            <?php endif; ?>
            
        </main>
        
        <!-- BOTTOM NAVIGATION -->
        <nav class="fixed bottom-0 left-0 right-0 glass border-t border-[var(--border-glass)] z-20">
            <div class="flex justify-around max-w-md mx-auto p-2">
                <a href="index.php" class="flex flex-col items-center text-[var(--accent-primary)] py-1 px-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-xs">Accueil</span>
                </a>
                <a href="game.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.5 6.5L5 3l3 2.5M5 3l-2 5 3-2.5z"/></svg>
                    <span class="text-xs">Partie</span>
                </a>
                <a href="leaderboard.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="text-xs">Classement</span>
                </a>
                <a href="profile.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)]">
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
        }
        
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
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
        
        // ============================================
        // GÉNÉRER LES PARTICULES
        // ============================================
        
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
            setTheme(theme);
            
            createBackgroundCards();
            createParticles();
            
            document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
        });
        
        // ============================================
        // CRÉER UNE PARTIE
        // ============================================
        
        document.getElementById('createGameForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const bet = document.getElementById('betAmount').value;
            try {
                const response = await fetch('api/game/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ bet_amount: parseFloat(bet) })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = `game.php?id=${data.game_id}`;
                } else {
                    alert('❌ ' + (data.error || 'Erreur lors de la création'));
                }
            } catch (error) {
                alert('❌ Erreur de connexion au serveur');
            }
        });
        
        // ============================================
        // REJOINDRE UNE PARTIE
        // ============================================
        
        document.getElementById('joinGameForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const code = document.getElementById('roomCode').value.toUpperCase().trim();
            if (code.length !== 6) {
                alert('❌ Le code doit faire 6 caractères');
                return;
            }
            try {
                const response = await fetch('api/game/join.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room_code: code })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = `game.php?id=${data.game_id}`;
                } else {
                    alert('❌ ' + (data.error || 'Code invalide ou partie pleine'));
                }
            } catch (error) {
                alert('❌ Erreur de connexion au serveur');
            }
        });
        
        document.getElementById('roomCode')?.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>