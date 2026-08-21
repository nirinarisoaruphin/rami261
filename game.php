<?php
// game.php - Avec images de cartes
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
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Rami 261 - Partie #<?php echo htmlspecialchars($roomCode); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cards.css">
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col">
        
        <!-- HEADER -->
        <header class="glass p-4 flex justify-between items-center z-10">
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold bg-gradient-to-r from-purple-400 to-cyan-400 bg-clip-text text-transparent">🃏 Rami 261</h1>
                    <span class="text-xs text-[var(--text-secondary)] font-mono">#<?php echo htmlspecialchars($roomCode); ?></span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-[var(--text-secondary)]">💰 <span id="potAmount"><?php echo $gameData['bet_amount'] ?? 0; ?></span>€</span>
                <button id="themeToggle" class="p-2 rounded-full glass text-[var(--text-secondary)] hover:bg-[var(--bg-glass)] transition-colors">🌙</button>
            </div>
        </header>
        
        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 pb-28">
            
            <!-- STATUT -->
            <div id="gameStatus" class="glass p-3 mb-4 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full <?php echo $gameData['status'] === 'playing' ? 'bg-green-400 animate-pulse' : 'bg-yellow-400'; ?>"></span>
                    <span class="text-sm font-medium text-[var(--text-primary)]" id="statusText">
                        <?php 
                            $statusMessages = ['waiting' => '⏳ En attente...', 'playing' => '🎯 En jeu', 'finished' => '🏆 Terminé', 'closed' => '🔒 Fermé'];
                            echo $statusMessages[$gameData['status']] ?? $gameData['status'];
                        ?>
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-xs text-[var(--text-secondary)]">Joueurs</span>
                    <p class="font-bold text-[var(--text-primary)]"><span id="playerCount"><?php echo count($gameState['players'] ?? []); ?></span>/<?php echo MAX_PLAYERS; ?></p>
                </div>
            </div>
            
            <!-- JOUEURS -->
            <div id="playersList" class="flex gap-2 mb-4 overflow-x-auto pb-2 scrollbar-hide">
                <?php if (isset($gameState['players'])): ?>
                    <?php foreach ($gameState['players'] as $player): ?>
                        <div class="glass p-2 rounded-lg text-center min-w-[70px] flex-shrink-0
                            <?php echo ($gameState['current_turn'] ?? 0) == $player['position'] ? 'border-2 border-[var(--accent-primary)]' : ''; ?>
                            <?php echo ($player['is_winner'] ?? false) ? 'border-2 border-yellow-400' : ''; ?>
                            <?php echo ($player['user_id'] ?? 0) == $_SESSION['user_id'] ? 'bg-[var(--bg-glass)]' : ''; ?>">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-cyan-500 flex items-center justify-center text-white text-lg font-bold mx-auto mb-1">
                                <?php echo isset($player['username']) ? strtoupper(substr($player['username'], 0, 1)) : '?'; ?>
                            </div>
                            <p class="text-xs font-bold text-[var(--text-primary)] truncate max-w-[60px]" title="<?php echo htmlspecialchars($player['username'] ?? '?'); ?>">
                                <?php echo htmlspecialchars($player['username'] ?? '?'); ?>
                                <?php if (($player['user_id'] ?? 0) == $_SESSION['user_id']): ?>
                                    <span class="text-[var(--accent-primary)]">★</span>
                                <?php endif; ?>
                            </p>
                            <p class="text-xs text-[var(--text-secondary)]">
                                <?php 
                                    $hand = isset($player['hand']) ? (is_array($player['hand']) ? $player['hand'] : json_decode($player['hand'], true)) : [];
                                    echo is_array($hand) ? count($hand) : 0; 
                                ?> 🃏
                            </p>
                            <?php if ($player['is_winner'] ?? false): ?>
                                <span class="text-yellow-400 text-xs">👑 Gagnant</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- TALON & DÉFAUSSE -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-center">
                    <span class="text-xs text-[var(--text-secondary)]">Pioche</span>
                    <div id="drawPile" class="w-16 h-24 rounded-lg shadow-lg flex items-center justify-center cursor-pointer hover:scale-105 transition-transform <?php echo ($gameData['status'] === 'playing' && ($gameState['is_my_turn'] ?? false)) ? 'hover:border-[var(--accent-primary)] border-2 border-transparent' : 'opacity-50 cursor-not-allowed'; ?>">
                        <div class="card-container card-deck">
                            <div class="card-3d">
                                <div class="card-front">
                                    <img src="assets/images/cards/card-back.png" alt="Pioche" class="card-img">
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="text-xs text-[var(--text-secondary)] mt-1 block" id="cardsLeft">108</span>
                </div>
                <div class="text-center">
                    <span class="text-xs text-[var(--text-secondary)]">Défausse</span>
                    <div id="discardPile" class="w-16 h-24 rounded-lg shadow-lg flex items-center justify-center">
                        <div class="card-container card-deck">
                            <div class="card-3d">
                                <div class="card-front">
                                    <img src="assets/images/cards/card-back.png" alt="Défausse" class="card-img">
                                </div>
                            </div>
                        </div>
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
                    <span class="text-xs text-[var(--text-secondary)]">Score: <span id="handScore">0</span></span>
                </div>
                <div id="playerHand" class="flex flex-wrap gap-2 justify-center min-h-[100px] p-2 glass rounded-lg">
                    <p class="text-[var(--text-secondary)] text-sm">Chargement...</p>
                </div>
            </div>
            
            <!-- BOUTONS -->
            <div class="grid grid-cols-4 gap-2 mb-4">
                <button id="btnDraw" class="glass p-3 rounded-lg text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-glass)] transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>🎯 Piocher</button>
                <button id="btnMeld" class="glass p-3 rounded-lg text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-glass)] transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>📦 Poser</button>
                <button id="btnDiscard" class="glass p-3 rounded-lg text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-glass)] transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>🗑️ Défausser</button>
                <button id="btnEndTurn" class="glass p-3 rounded-lg text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-glass)] transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>⏭️ Fin tour</button>
            </div>
            
            <!-- MESSAGE -->
            <div id="gameMessage" class="glass p-3 text-center text-sm text-[var(--text-secondary)]">
                <?php if ($gameData['status'] === 'waiting'): ?>
                    ⏳ En attente de joueurs... (<?php echo MIN_PLAYERS; ?> minimum)
                    <?php if ($isHost && count($gameState['players'] ?? []) >= MIN_PLAYERS): ?>
                        <button id="btnStart" class="ml-2 px-4 py-1 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white text-xs font-bold hover:scale-105 transition-transform">🚀 Démarrer</button>
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
        <nav class="fixed bottom-0 left-0 right-0 glass border-t border-[var(--border-glass)] z-20">
            <div class="flex justify-around max-w-md mx-auto p-2">
                <a href="index.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-xs">Accueil</span>
                </a>
                <a href="game.php<?php echo $gameId ? '?id='.$gameId : ''; ?>" class="flex flex-col items-center text-[var(--accent-primary)] py-1 px-3 rounded-lg transition-colors">
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
        
        // Thème
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
        
        // ============================================
        // FONCTIONS UI AVEC IMAGES
        // ============================================
        
        function getCardImageUrl(card) {
            if (card.is_joker) {
                return 'assets/images/cards/joker.png';
            }
            return `assets/images/cards/${card.suit}/${card.value}.png`;
        }
        
        function updateUI() {
            const me = gameState.players?.find(p => p.user_id === CONFIG.userId);
            
            document.getElementById('playerCount').textContent = gameState.players?.length || 0;
            document.getElementById('potAmount').textContent = gameState.game?.bet_amount || 0;
            
            const statusMap = {'waiting': '⏳ En attente...', 'playing': '🎯 En jeu', 'finished': '🏆 Terminé', 'closed': '🔒 Fermé'};
            document.getElementById('statusText').textContent = statusMap[gameState.game?.status] || gameState.game?.status;
            
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
                const isJoker = card.is_joker;
                
                const div = document.createElement('div');
                div.className = `card-wrapper ${isSelected ? 'selected' : ''}`;
                div.dataset.index = index;
                
                div.innerHTML = `
                    <div class="card-3d">
                        <div class="card-front ${isJoker ? 'card-joker' : ''}">
                            <img src="${getCardImageUrl(card)}" alt="${card.value} de ${card.suit}" class="card-img" onerror="this.src='assets/images/cards/card-back.png'">
                        </div>
                    </div>
                `;
                
                div.addEventListener('click', () => toggleCard(index));
                container.appendChild(div);
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
                    if (c.is_joker) return '<span class="px-2 py-1 bg-purple-500/20 rounded text-xs text-purple-400">⭐ JOKER</span>';
                    const symbol = c.suit === 'hearts' ? '♥' : c.suit === 'diamonds' ? '♦' : c.suit === 'clubs' ? '♣' : '♠';
                    return `<span class="px-2 py-1 bg-[var(--bg-card)] rounded text-xs">${c.value}${symbol}</span>`;
                }).join(' + ');
                div.innerHTML = `<span class="text-xs text-[var(--text-secondary)] mr-1">#${idx + 1}</span> ${cardsHtml}`;
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
                div.className = `glass p-2 rounded-lg text-center min-w-[70px] flex-shrink-0 ${isCurrentTurn ? 'border-2 border-[var(--accent-primary)]' : ''} ${isWinner ? 'border-2 border-yellow-400' : ''} ${isMe ? 'bg-[var(--bg-glass)]' : ''}`;
                div.innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-cyan-500 flex items-center justify-center text-white text-lg font-bold mx-auto mb-1">${player.username ? player.username.charAt(0).toUpperCase() : '?'}</div>
                    <p class="text-xs font-bold text-[var(--text-primary)] truncate max-w-[60px]" title="${player.username || ''}">${player.username || '?'}${isMe ? '<span class="text-[var(--accent-primary)]"> ★</span>' : ''}</p>
                    <p class="text-xs text-[var(--text-secondary)]">${isMe ? hand.length : '?'} 🃏</p>
                    ${isWinner ? '<span class="text-yellow-400 text-xs">👑 Gagnant</span>' : ''}
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
                    html += ' <button id="btnStart" class="ml-2 px-4 py-1 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white text-xs font-bold hover:scale-105 transition-transform">🚀 Démarrer</button>';
                }
                el.innerHTML = html;
                el.className = 'glass p-3 text-center text-sm text-[var(--text-secondary)]';
            } else if (status === 'playing') {
                el.textContent = myTurn ? '🎯 C\'est votre tour !' : '⏳ Tour du joueur suivant...';
                el.className = 'glass p-3 text-center text-sm ' + (myTurn ? 'text-[var(--accent-primary)] font-bold' : 'text-[var(--text-secondary)]');
            } else if (status === 'finished') {
                const winner = gameState.players?.find(p => p.is_winner);
                el.textContent = winner ? '🏆 ' + winner.username + ' a gagné !' : '🏆 Partie terminée !';
                el.className = 'glass p-3 text-center text-sm text-yellow-400 font-bold';
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
            const colors = { info: 'text-[var(--text-secondary)]', success: 'text-green-400', error: 'text-red-400', warning: 'text-yellow-400' };
            el.textContent = text;
            el.className = `glass p-3 text-center text-sm ${colors[type] || colors.info}`;
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
        console.log('🎯 Rami 261 - Partie chargée avec images !');
    </script>
</body>
</html>