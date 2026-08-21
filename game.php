<?php
// game.php - Interface de jeu avec style amélioré
require_once 'includes/config.php';
require_once 'includes/GameManager.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$gameId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$gameId) {
    redirect('index.php');
}

$gameManager = new GameManager($gameId);
$gameData = $gameManager->getGameData();

if (!$gameData) {
    redirect('index.php');
}

$gameState = $gameManager->getGameState($_SESSION['user_id']);
$isHost = $gameData['host_id'] == $_SESSION['user_id'];
$roomCode = $gameData['room_code'];

$theme = $_SESSION['theme'] ?? $_COOKIE['theme'] ?? 'light';
$pageTitle = 'Rami 261 - Partie #' . htmlspecialchars($roomCode);
$isLoggedIn = isLoggedIn();

// Calculer le pot
$potAmount = 0;
if (isset($gameState['players'])) {
    $potAmount = $gameData['bet_amount'] * count($gameState['players']);
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Rami 261 - Partie #<?php echo htmlspecialchars($roomCode); ?></title>
    
    <link rel="icon" href="favicon.php" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.php" type="image/x-icon">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cards.css">
    
    <style>
        /* Styles supplémentaires pour plus de clarté */
        .player-card {
            background: var(--bg-glass);
            border: 2px solid var(--border-glass);
            border-radius: 12px;
            padding: 10px 14px;
            text-align: center;
            min-width: 70px;
            flex-shrink: 0;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .player-card.current-turn {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
            background: var(--bg-card);
        }
        
        .player-card.winner {
            border-color: #eab308;
            box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.2);
        }
        
        .player-card .p-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            margin: 0 auto 6px;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
        }
        
        .player-card .p-name {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 60px;
        }
        
        .player-card .p-cards {
            font-size: 0.65rem;
            color: var(--text-secondary);
        }
        
        .player-card .p-badge {
            font-size: 0.55rem;
            color: #eab308;
            font-weight: 700;
        }
        
        .game-status-bar {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .game-status-bar .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .game-status-bar .status-dot.waiting {
            background: #eab308;
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        .game-status-bar .status-dot.playing {
            background: #22c55e;
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        .game-status-bar .status-dot.finished {
            background: #3b82f6;
        }
        
        .game-status-bar .status-dot.closed {
            background: #ef4444;
        }
        
        .game-status-bar .status-text {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .game-status-bar .player-count-text {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .game-status-bar .player-count-text strong {
            color: var(--text-primary);
        }
        
        .pile-card {
            width: 65px;
            height: 90px;
            background: var(--bg-card);
            border-radius: 10px;
            border: 2px solid var(--border-glass);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: var(--shadow-card);
            transition: all 0.3s ease;
        }
        
        .pile-card.clickable {
            cursor: pointer;
        }
        
        .pile-card.clickable:hover {
            border-color: var(--accent-primary);
            transform: scale(1.05);
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.2);
        }
        
        .pile-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        
        @media (max-width: 480px) {
            .action-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .pile-card {
                width: 50px;
                height: 70px;
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col">
        
        <!-- BACKGROUND -->
        <div class="bg-layer-1"></div>
        <div class="bg-layer-flag"></div>
        <div class="bg-layer-cards" id="cardsBackground"></div>
        <div class="bg-layer-particles" id="particlesContainer"></div>
        
        <!-- HEADER -->
        <header class="glass px-4 py-3 flex justify-between items-center z-10">
            <div class="flex items-center gap-2.5">
                <a href="index.php" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="flag-header"></div>
                <div>
                    <h1 class="text-sm font-extrabold bg-gradient-to-r from-purple-400 to-cyan-400 bg-clip-text text-transparent">🃏 Rami 261</h1>
                    <span class="text-[10px] font-medium text-[var(--text-secondary)]">#<?php echo htmlspecialchars($roomCode); ?></span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[var(--text-primary)] bg-[var(--bg-glass)] px-3 py-1 rounded-full border border-[var(--border-glass)]">
                    💰 <?php echo formatCurrency($potAmount); ?>
                </span>
                <button id="themeToggle" class="theme-toggle">
                    <span id="themeIcon"><?php echo $theme === 'dark' ? '🌙' : '☀️'; ?></span>
                </button>
            </div>
        </header>
        
        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 pb-28">
            
            <!-- STATUT DU JEU -->
            <div class="game-status-bar mb-4">
                <div class="status-indicator flex items-center gap-2">
                    <span class="status-dot <?php echo $gameData['status']; ?>"></span>
                    <span class="status-text">
                        <?php 
                            $statusMessages = [
                                'waiting' => '⏳ En attente...', 
                                'playing' => '🎯 En jeu', 
                                'finished' => '🏆 Terminé', 
                                'closed' => '🔒 Fermé'
                            ];
                            echo $statusMessages[$gameData['status']] ?? $gameData['status'];
                        ?>
                    </span>
                </div>
                <div class="player-count-text">
                    Joueurs <strong><span id="playerCount"><?php echo count($gameState['players'] ?? []); ?></span></strong>/<?php echo MAX_PLAYERS; ?>
                </div>
            </div>
            
            <!-- LISTE DES JOUEURS -->
            <div id="playersList" class="flex gap-2 mb-4 overflow-x-auto pb-2 scrollbar-hide">
                <?php if (isset($gameState['players'])): ?>
                    <?php foreach ($gameState['players'] as $player): ?>
                        <div class="player-card <?php echo ($gameState['current_turn'] ?? 0) == $player['position'] ? 'current-turn' : ''; ?> <?php echo ($player['is_winner'] ?? false) ? 'winner' : ''; ?>">
                            <div class="p-avatar"><?php echo isset($player['username']) ? strtoupper(substr($player['username'], 0, 1)) : '?'; ?></div>
                            <p class="p-name">
                                <?php echo htmlspecialchars($player['username'] ?? '?'); ?>
                                <?php if (($player['user_id'] ?? 0) == $_SESSION['user_id']): ?>
                                    <span class="text-[var(--accent-primary)]">★</span>
                                <?php endif; ?>
                            </p>
                            <p class="p-cards">
                                <?php 
                                    $hand = isset($player['hand']) ? (is_array($player['hand']) ? $player['hand'] : json_decode($player['hand'], true)) : [];
                                    echo is_array($hand) ? count($hand) : 0; 
                                ?> 🃏
                            </p>
                            <?php if ($player['is_winner'] ?? false): ?>
                                <p class="p-badge">👑 Gagnant</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- TALON & DÉFAUSSE -->
            <div class="flex justify-between items-center mb-6 px-4">
                <div class="text-center">
                    <span class="text-xs text-[var(--text-secondary)] font-medium">Pioche</span>
                    <div id="drawPile" class="pile-card mx-auto mt-1 <?php echo ($gameData['status'] === 'playing' && ($gameState['is_my_turn'] ?? false)) ? 'clickable' : 'disabled'; ?>">
                        <span>🃏</span>
                    </div>
                    <span class="text-xs text-[var(--text-secondary)] mt-1 block" id="cardsLeft">108</span>
                </div>
                <div class="text-center">
                    <span class="text-xs text-[var(--text-secondary)] font-medium">Défausse</span>
                    <div id="discardPile" class="pile-card mx-auto mt-1">
                        <span>🎴</span>
                    </div>
                </div>
            </div>
            
            <!-- MELDS -->
            <div id="meldsContainer" class="mb-4">
                <p class="text-xs text-[var(--text-secondary)] text-center">Aucune combinaison posée</p>
            </div>
            
            <!-- MAIN DU JOUEUR -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-bold text-[var(--text-primary)]">Votre main</span>
                    <span class="text-xs text-[var(--text-secondary)] bg-[var(--bg-glass)] px-3 py-1 rounded-full border border-[var(--border-glass)]">
                        Score: <span id="handScore" class="font-bold text-[var(--text-primary)]">0</span>
                    </span>
                </div>
                <div id="playerHand" class="flex flex-wrap gap-2 justify-center min-h-[100px] p-3 glass rounded-xl">
                    <p class="text-[var(--text-secondary)] text-sm">Chargement...</p>
                </div>
            </div>
            
            <!-- BOUTONS D'ACTION -->
            <div class="action-grid mb-4">
                <button id="btnDraw" class="btn-primary w-full text-center disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                    🎯 Piocher
                </button>
                <button id="btnMeld" class="btn-primary w-full text-center disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                    📦 Poser
                </button>
                <button id="btnDiscard" class="btn-secondary w-full text-center disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                    🗑️ Défausser
                </button>
                <button id="btnEndTurn" class="btn-secondary w-full text-center disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                    ⏭️ Fin tour
                </button>
            </div>
            
            <!-- MESSAGE -->
            <div id="gameMessage" class="glass p-3 text-center text-sm text-[var(--text-secondary)] rounded-xl font-medium">
                <?php if ($gameData['status'] === 'waiting'): ?>
                    ⏳ En attente de joueurs... (<?php echo MIN_PLAYERS; ?> minimum)
                    <?php if ($isHost && count($gameState['players'] ?? []) >= MIN_PLAYERS): ?>
                        <button id="btnStart" class="ml-2 px-4 py-1.5 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white text-xs font-bold hover:scale-105 transition-transform">
                            🚀 Démarrer
                        </button>
                    <?php endif; ?>
                <?php elseif ($gameData['status'] === 'playing'): ?>
                    <?php if ($gameState['is_my_turn'] ?? false): ?>
                        🎯 C'est votre tour ! Choisissez une action.
                    <?php else: ?>
                        ⏳ Tour du joueur suivant...
                    <?php endif; ?>
                <?php elseif ($gameData['status'] === 'finished'): ?>
                    🏆 Partie terminée !
                <?php endif; ?>
            </div>
            
        </main>
        
        <!-- BOTTOM NAVIGATION -->
        <?php if ($isLoggedIn): ?>
        <nav class="fixed bottom-0 left-0 right-0 glass border-t border-[var(--border-glass)] z-20 safe-bottom">
            <div class="flex justify-around max-w-md mx-auto py-1.5 px-2">
                <a href="index.php" class="flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-[var(--accent-primary)]' : 'text-[var(--text-secondary)]'; ?> py-1 px-3 rounded-lg transition-all hover:text-[var(--text-primary)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-[10px] font-medium">Accueil</span>
                </a>
                <a href="game.php<?php echo $gameId ? '?id='.$gameId : ''; ?>" class="flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'game.php' ? 'text-[var(--accent-primary)]' : 'text-[var(--text-secondary)]'; ?> py-1 px-3 rounded-lg transition-all hover:text-[var(--text-primary)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.5 6.5L5 3l3 2.5M5 3l-2 5 3-2.5z"/>
                    </svg>
                    <span class="text-[10px] font-medium">Partie</span>
                </a>
                <a href="leaderboard.php" class="flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'text-[var(--accent-primary)]' : 'text-[var(--text-secondary)]'; ?> py-1 px-3 rounded-lg transition-all hover:text-[var(--text-primary)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-[10px] font-medium">Classement</span>
                </a>
                <a href="profile.php" class="flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'text-[var(--accent-primary)]' : 'text-[var(--text-secondary)]'; ?> py-1 px-3 rounded-lg transition-all hover:text-[var(--text-primary)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-[10px] font-medium">Profil</span>
                </a>
            </div>
        </nav>
        <?php endif; ?>
        
    </div>
    
    <script>
        // ============================================
        // GESTION DU THÈME
        // ============================================
        
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            document.cookie = `theme=${theme}; path=/; max-age=31536000`;
            const icon = document.getElementById('themeIcon');
            if (icon) icon.textContent = theme === 'dark' ? '🌙' : '☀️';
        }
        
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            setTheme(current === 'dark' ? 'light' : 'dark');
            fetch('api/user/theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ theme: current === 'dark' ? 'light' : 'dark' })
            }).catch(() => {});
        });
        
        // ============================================
        // CARTES EN ARRIÈRE-PLAN
        // ============================================
        
        function createBackgroundCards() {
            const container = document.getElementById('cardsBackground');
            if (!container) return;
            const suits = ['♠','♥','♦','♣'], colors = ['black','red','red','black'];
            const values = ['A','2','3','4','5','6','7','8','9','10','J','Q','K'];
            const animations = ['floatCard1','floatCard2','floatCard3'];
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
            const colors = ['#7c3aed','#06b6d4','#fc3e32','#007a3d','#6b21a8'];
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
            setTheme(savedTheme ? savedTheme.split('=')[1] : 'light');
            createBackgroundCards();
            createParticles();
        });
        
        // ============================================
        // CONFIGURATION DU JEU
        // ============================================
        
        const CONFIG = {
            gameId: <?php echo $gameId; ?>,
            userId: <?php echo $_SESSION['user_id']; ?>,
            isHost: <?php echo $isHost ? 'true' : 'false'; ?>,
            turnTimeout: <?php echo TURN_TIMEOUT; ?>,
            gameStatus: '<?php echo $gameData['status']; ?>'
        };
        
        let gameState = <?php echo json_encode($gameState); ?>;
        let selectedCards = [];
        let myTurn = <?php echo json_encode($gameState['is_my_turn'] ?? false); ?>;
        let pollingInterval = null;
        
        // ============================================
        // IMAGES DES CARTES
        // ============================================
        
        function getCardImageUrl(card) {
            if (card.is_joker) {
                return 'assets/images/cards/joker.png';
            }
            return `assets/images/cards/${card.suit}/${card.value}.png`;
        }
        
        // ============================================
        // UI
        // ============================================
        
        function updateUI() {
            const me = gameState.players?.find(p => p.user_id === CONFIG.userId);
            
            document.getElementById('playerCount').textContent = gameState.players?.length || 0;
            
            const statusMap = {'waiting': '⏳ En attente...', 'playing': '🎯 En jeu', 'finished': '🏆 Terminé', 'closed': '🔒 Fermé'};
            document.querySelector('.status-text').textContent = statusMap[gameState.game?.status] || gameState.game?.status;
            
            // Mettre à jour le point du statut
            const dot = document.querySelector('.status-dot');
            if (dot) {
                dot.className = `status-dot ${gameState.game?.status || 'waiting'}`;
            }
            
            updateMessage();
            updateHand(me);
            updateMelds(me);
            updatePlayers();
            updateButtons();
        }
        
        function updateHand(me) {
            const container = document.getElementById('playerHand');
            container.innerHTML = '';
            
            if (!me || !me.hand || me.hand.length === 0) {
                container.innerHTML = '<p class="text-[var(--text-secondary)] text-sm">Votre main est vide</p>';
                document.getElementById('handScore').textContent = '0';
                return;
            }
            
            let score = 0;
            me.hand.forEach((card, index) => {
                if (!card.is_joker) score += card.points || 0;
                const isSelected = selectedCards.includes(index);
                
                const wrapper = document.createElement('div');
                wrapper.className = `card-wrapper ${isSelected ? 'selected' : ''}`;
                wrapper.dataset.index = index;
                wrapper.innerHTML = `
                    <img src="${getCardImageUrl(card)}" 
                         alt="${card.value} de ${card.suit}" 
                         class="card-img"
                         onerror="this.src='assets/images/cards/card-back.png'">
                `;
                wrapper.addEventListener('click', () => toggleCard(index));
                container.appendChild(wrapper);
            });
            
            document.getElementById('handScore').textContent = score;
        }
        
        function updateMelds(me) {
            const container = document.getElementById('meldsContainer');
            container.innerHTML = '';
            
            if (!me || !me.melds || me.melds.length === 0) {
                container.innerHTML = '<p class="text-xs text-[var(--text-secondary)] text-center">Aucune combinaison posée</p>';
                return;
            }
            
            me.melds.forEach((meld, idx) => {
                const div = document.createElement('div');
                div.className = 'glass p-2 rounded-lg mb-2 flex flex-wrap gap-1 items-center';
                const cardsHtml = meld.map(c => {
                    if (c.is_joker) return '<span class="px-2 py-1 bg-purple-500/20 rounded text-xs text-purple-600 font-bold">⭐ JOKER</span>';
                    const symbol = c.suit === 'hearts' ? '♥' : c.suit === 'diamonds' ? '♦' : c.suit === 'clubs' ? '♣' : '♠';
                    const color = ['hearts', 'diamonds'].includes(c.suit) ? 'text-red-500' : 'text-gray-600';
                    return `<span class="px-2 py-1 bg-[var(--bg-secondary)] rounded text-xs border border-[var(--border-glass)] ${color} font-bold">${c.value}${symbol}</span>`;
                }).join(' + ');
                div.innerHTML = `<span class="text-xs text-[var(--text-secondary)] mr-1 font-bold">#${idx + 1}</span> ${cardsHtml}`;
                container.appendChild(div);
            });
        }
        
        function updatePlayers() {
            const container = document.getElementById('playersList');
            container.innerHTML = '';
            if (!gameState.players) return;
            
            gameState.players.forEach(player => {
                const isMe = player.user_id === CONFIG.userId;
                const isCurrentTurn = gameState.current_turn === player.position;
                const isWinner = player.is_winner;
                const hand = player.hand ? (Array.isArray(player.hand) ? player.hand : []) : [];
                
                const div = document.createElement('div');
                div.className = `player-card ${isCurrentTurn ? 'current-turn' : ''} ${isWinner ? 'winner' : ''}`;
                div.innerHTML = `
                    <div class="p-avatar">${player.username ? player.username.charAt(0).toUpperCase() : '?'}</div>
                    <p class="p-name" title="${player.username || ''}">${player.username || '?'}${isMe ? '<span class="text-[var(--accent-primary)]"> ★</span>' : ''}</p>
                    <p class="p-cards">${isMe ? hand.length : '?'} 🃏</p>
                    ${isWinner ? '<p class="p-badge">👑 Gagnant</p>' : ''}
                `;
                container.appendChild(div);
            });
        }
        
        function updateMessage() {
            const el = document.getElementById('gameMessage');
            const status = gameState.game?.status;
            
            if (status === 'waiting') {
                let html = '⏳ En attente de joueurs... (' + <?php echo MIN_PLAYERS; ?> + ' minimum)';
                if (CONFIG.isHost && (gameState.players?.length || 0) >= <?php echo MIN_PLAYERS; ?>) {
                    html += ' <button id="btnStart" class="ml-2 px-4 py-1.5 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white text-xs font-bold hover:scale-105 transition-transform">🚀 Démarrer</button>';
                }
                el.innerHTML = html;
                el.className = 'glass p-3 text-center text-sm text-[var(--text-secondary)] rounded-xl font-medium';
            } else if (status === 'playing') {
                el.textContent = myTurn ? '🎯 C\'est votre tour ! Choisissez une action.' : '⏳ Tour du joueur suivant...';
                el.className = `glass p-3 text-center text-sm rounded-xl font-medium ${myTurn ? 'text-[var(--accent-primary)] font-bold' : 'text-[var(--text-secondary)]'}`;
            } else if (status === 'finished') {
                const winner = gameState.players?.find(p => p.is_winner);
                el.textContent = winner ? '🏆 ' + winner.username + ' a gagné !' : '🏆 Partie terminée !';
                el.className = 'glass p-3 text-center text-sm text-yellow-600 font-bold rounded-xl';
            }
        }
        
        function updateButtons() {
            const isPlaying = gameState.game?.status === 'playing';
            const buttons = ['btnDraw', 'btnMeld', 'btnDiscard', 'btnEndTurn'];
            buttons.forEach(id => {
                const btn = document.getElementById(id);
                if (btn) btn.disabled = !isPlaying || !myTurn;
            });
        }
        
        function toggleCard(index) {
            if (!myTurn || gameState.game?.status !== 'playing') return;
            const idx = selectedCards.indexOf(index);
            if (idx > -1) selectedCards.splice(idx, 1);
            else selectedCards.push(index);
            
            const me = gameState.players?.find(p => p.user_id === CONFIG.userId);
            if (me) updateHand(me);
        }
        
        function showMessage(text, type = 'info') {
            const el = document.getElementById('gameMessage');
            const colors = { 
                info: 'text-[var(--text-secondary)]', 
                success: 'text-green-600', 
                error: 'text-red-600', 
                warning: 'text-yellow-600' 
            };
            el.textContent = text;
            el.className = `glass p-3 text-center text-sm rounded-xl font-medium ${colors[type] || colors.info}`;
        }
        
        // ============================================
        // API
        // ============================================
        
        async function fetchState() {
            try {
                const res = await fetch(`api/game/state.php?game_id=${CONFIG.gameId}&t=${Date.now()}`);
                const data = await res.json();
                if (data.success) {
                    gameState = data;
                    myTurn = data.is_my_turn;
                    updateUI();
                }
            } catch (error) { console.error(error); }
        }
        
        document.getElementById('btnDraw')?.addEventListener('click', async () => {
            try {
                const res = await fetch(`api/game/draw.php?game_id=${CONFIG.gameId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ player_id: CONFIG.userId })
                });
                const data = await res.json();
                if (data.success) { await fetchState(); showMessage('📥 Carte piochée !', 'success'); }
                else showMessage('❌ ' + (data.error || 'Erreur'), 'error');
            } catch (error) { showMessage('❌ Erreur de connexion', 'error'); }
        });
        
        document.getElementById('btnMeld')?.addEventListener('click', async () => {
            if (selectedCards.length < 3) { showMessage('❌ Sélectionnez au moins 3 cartes', 'error'); return; }
            try {
                const res = await fetch(`api/game/play.php?game_id=${CONFIG.gameId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ player_id: CONFIG.userId, card_indices: selectedCards })
                });
                const data = await res.json();
                if (data.success) { selectedCards = []; await fetchState(); showMessage('✅ Combinaison validée !', 'success'); }
                else showMessage('❌ ' + (data.error || 'Combinaison invalide'), 'error');
            } catch (error) { showMessage('❌ Erreur de connexion', 'error'); }
        });
        
        document.getElementById('btnDiscard')?.addEventListener('click', async () => {
            if (selectedCards.length !== 1) { showMessage('❌ Sélectionnez UNE carte', 'error'); return; }
            try {
                const res = await fetch(`api/game/discard.php?game_id=${CONFIG.gameId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ player_id: CONFIG.userId, card_index: selectedCards[0] })
                });
                const data = await res.json();
                if (data.success) { selectedCards = []; await fetchState(); showMessage('🗑️ Carte défaussée', 'info'); }
                else showMessage('❌ ' + (data.error || 'Erreur'), 'error');
            } catch (error) { showMessage('❌ Erreur de connexion', 'error'); }
        });
        
        document.getElementById('btnEndTurn')?.addEventListener('click', async () => {
            try {
                const res = await fetch(`api/game/endturn.php?game_id=${CONFIG.gameId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ player_id: CONFIG.userId })
                });
                const data = await res.json();
                if (data.success) { await fetchState(); showMessage('⏭️ Tour terminé', 'info'); }
                else showMessage('❌ ' + (data.error || 'Erreur'), 'error');
            } catch (error) { showMessage('❌ Erreur de connexion', 'error'); }
        });
        
        document.addEventListener('click', function(e) {
            if (e.target.id === 'btnStart') {
                (async () => {
                    try {
                        const res = await fetch(`api/game/start.php?game_id=${CONFIG.gameId}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ host_id: CONFIG.userId })
                        });
                        const data = await res.json();
                        if (data.success) { await fetchState(); showMessage('🚀 Partie démarrée !', 'success'); }
                        else showMessage('❌ ' + (data.error || 'Erreur'), 'error');
                    } catch (error) { showMessage('❌ Erreur de connexion', 'error'); }
                })();
            }
        });
        
        // ============================================
        // POLLING
        // ============================================
        
        if (CONFIG.gameStatus !== 'finished' && CONFIG.gameStatus !== 'closed') {
            pollingInterval = setInterval(fetchState, 3000);
        }
        
        updateUI();
        console.log('🎯 Rami 261 - Partie chargée !');
    </script>
</body>
</html>