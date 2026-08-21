<?php
// index.php - Page d'accueil avec image drapeau
require_once 'includes/config.php';
require_once 'includes/functions.php';

$isLoggedIn = isLoggedIn();

if (!$isLoggedIn) {
    redirect('login.php');
}

$pageTitle = 'Rami 261 - Accueil';
$userId = getCurrentUserId();

$activeGames = [];
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
    } catch (PDOException $e) {
        $activeGames = [];
    }
}

require_once 'includes/header.php';
?>

<!-- HERO BANNER AVEC DRAPEAU IMAGE -->
<div class="hero-banner flex items-center gap-4 animate-slide-up">
    <!-- DRAPEAU IMAGE -->
    <div class="w-16 h-11 rounded overflow-hidden shadow-md flex-shrink-0 bg-white border border-gray-200 dark:border-gray-700">
        <img src="assets/images/flags/madagascar.png" alt="Drapeau Madagascar" 
             class="w-full h-full object-cover"
             onerror="this.style.display='none'">
    </div>
    <div>
        <p class="title">🇲🇬 Rami 261 Madagascar</p>
        <p class="subtitle">Le jeu de cartes préféré des Malgaches</p>
    </div>
</div>

<!-- CRÉER UNE PARTIE -->
<div class="glass p-4 rounded-2xl mb-4 animate-slide-up">
    <h3 class="section-title mb-3">
        <span>🚀</span> Créer une partie
    </h3>
    <form id="createGameForm" class="flex gap-2">
        <input type="number" id="betAmount" placeholder="Mise (Ar)" 
               class="input-modern flex-1" min="100" step="100" value="1000">
        <button type="submit" class="btn-primary px-5 py-2.5 text-sm whitespace-nowrap">Créer</button>
    </form>
</div>

<!-- REJOINDRE UNE PARTIE -->
<div class="glass p-4 rounded-2xl mb-4 animate-slide-up" style="animation-delay:0.1s">
    <h3 class="section-title mb-3">
        <span>🔑</span> Rejoindre une partie
    </h3>
    <form id="joinGameForm" class="flex gap-2">
        <input type="text" id="roomCode" placeholder="Code (ex: ABC123)" 
               class="input-modern flex-1 uppercase" maxlength="6">
        <button type="submit" class="btn-secondary px-5 py-2.5 text-sm whitespace-nowrap">Rejoindre</button>
    </form>
</div>

<!-- PARTIES ACTIVES -->
<div class="animate-slide-up" style="animation-delay:0.2s">
    <div class="flex justify-between items-center mb-3">
        <h3 class="section-title">
            <span>📋</span> Parties actives
        </h3>
        <span class="section-count"><?php echo count($activeGames); ?> trouvée(s)</span>
    </div>
    
    <?php if (empty($activeGames)): ?>
        <div class="glass p-6 rounded-xl empty-state">
            <span class="icon">🃏</span>
            <p class="text">Aucune partie active. Créez-en une !</p>
        </div>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($activeGames as $game): ?>
                <a href="game.php?id=<?php echo $game['id']; ?>" 
                   class="game-card">
                    <div>
                        <p class="game-code">#<?php echo htmlspecialchars($game['room_code']); ?></p>
                        <p class="game-details">
                            👤 <?php echo htmlspecialchars($game['host_name']); ?> 
                            • 💰 <?php echo formatCurrency($game['bet_amount']); ?>
                            • 👥 <?php echo $game['player_count']; ?>/<?php echo $game['max_players']; ?>
                        </p>
                    </div>
                    <span class="game-status <?php echo $game['status'] === 'waiting' ? 'waiting' : 'playing'; ?>">
                        <?php echo $game['status'] === 'waiting' ? '⏳ En attente' : '🔄 En cours'; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('createGameForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const bet = document.getElementById('betAmount').value;
    try {
        const res = await fetch('api/game/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bet_amount: parseFloat(bet) })
        });
        const data = await res.json();
        if (data.success) window.location.href = `game.php?id=${data.game_id}`;
        else alert('❌ ' + (data.error || 'Erreur'));
    } catch (error) { alert('❌ Erreur de connexion'); }
});

document.getElementById('joinGameForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const code = document.getElementById('roomCode').value.toUpperCase().trim();
    if (code.length !== 6) { alert('❌ Le code doit faire 6 caractères'); return; }
    try {
        const res = await fetch('api/game/join.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ room_code: code })
        });
        const data = await res.json();
        if (data.success) window.location.href = `game.php?id=${data.game_id}`;
        else alert('❌ ' + (data.error || 'Code invalide'));
    } catch (error) { alert('❌ Erreur de connexion'); }
});

document.getElementById('roomCode')?.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php require_once 'includes/footer.php'; ?>