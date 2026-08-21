<?php
// game.php - Interface de jeu avec header/footer global
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

$pageTitle = 'Rami 261 - Partie #' . htmlspecialchars($roomCode);

// Inclure le header
require_once 'includes/header.php';
?>

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
        <div id="drawPile" class="w-16 h-24 rounded-lg shadow-lg flex items-center justify-center cursor-pointer hover:scale-105 transition-transform border-2 border-transparent <?php echo ($gameData['status'] === 'playing' && ($gameState['is_my_turn'] ?? false)) ? 'hover:border-[var(--accent-primary)]' : 'opacity-50 cursor-not-allowed'; ?>">
            <img src="assets/images/cards/card-back.png" alt="Pioche" class="w-full h-full rounded-lg object-cover">
        </div>
        <span class="text-xs text-[var(--text-secondary)] mt-1 block" id="cardsLeft">108</span>
    </div>
    <div class="text-center">
        <span class="text-xs text-[var(--text-secondary)]">Défausse</span>
        <div id="discardPile" class="w-16 h-24 rounded-lg shadow-lg flex items-center justify-center">
            <img src="assets/images/cards/card-back.png" alt="Défausse" class="w-full h-full rounded-lg object-cover">
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

<script>
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
                if (c.is_joker) return '<span class="px-2 py-1 bg-purple-500/20 rounded text-xs text-purple-600">⭐ JOKER</span>';
                const symbol = c.suit === 'hearts' ? '♥' : c.suit === 'diamonds' ? '♦' : c.suit === 'clubs' ? '♣' : '♠';
                return `<span class="px-2 py-1 bg-[var(--bg-secondary)] rounded text-xs border border-[var(--border-glass)]">${c.value}${symbol}</span>`;
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
            el.className = 'glass p-3 text-center text-sm text-yellow-600 font-bold';
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
        const colors = { info: 'text-[var(--text-secondary)]', success: 'text-green-600', error: 'text-red-600', warning: 'text-yellow-600' };
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
    console.log('🎯 Rami 261 - Partie chargée !');
</script>

<?php
// Inclure le footer
require_once 'includes/footer.php';
?>