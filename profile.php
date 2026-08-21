<?php
// profile.php - Profil avec images
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

// Récupérer l'historique des parties
$stmt = $pdo->prepare("
    SELECT gh.*, u.username as winner_name,
           (SELECT COUNT(*) FROM game_players WHERE game_id = gh.game_id) as player_count
    FROM game_history gh
    JOIN users u ON gh.winner_id = u.id
    WHERE gh.winner_id = ? OR gh.game_id IN (SELECT game_id FROM game_players WHERE user_id = ?)
    ORDER BY gh.finished_at DESC LIMIT 10
");
$stmt->execute([$userId, $userId]);
$history = $stmt->fetchAll();

$updateSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    if (!empty($newUsername) && !empty($newEmail)) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$newUsername, $newEmail, $userId])) {
            $_SESSION['username'] = $newUsername;
            $_SESSION['email'] = $newEmail;
            $updateSuccess = 'Profil mis à jour !';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profil - Rami 261</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cards.css">
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col">
        
        <!-- FILIGRANE DRAPEAU -->
        <div class="flag-watermark"></div>
        
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
            <button id="themeToggle" class="p-2 rounded-full glass text-[var(--text-secondary)]">🌙</button>
        </header>
        
        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <?php if ($updateSuccess): ?>
                <div class="bg-green-500/20 border border-green-500/30 text-green-400 px-4 py-2 rounded-lg text-sm mb-4">
                    ✅ <?php echo $updateSuccess; ?>
                </div>
            <?php endif; ?>
            
            <!-- CARTE DE PROFIL -->
            <div class="glass p-6 rounded-2xl text-center mb-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-r from-purple-500 to-cyan-500 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-3 shadow-lg shadow-purple-500/25">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <h2 class="text-xl font-bold text-[var(--text-primary)]"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="text-sm text-[var(--text-secondary)]"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="text-sm text-[var(--text-secondary)] mt-1">📅 Membre depuis <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                
                <!-- STATISTIQUES -->
                <div class="grid grid-cols-3 gap-3 mt-4">
                    <div class="stat-card">
                        <span class="stat-icon">💰</span>
                        <p class="stat-value"><?php echo number_format($user['balance'] ?? 0, 2); ?>€</p>
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
                <div class="flex flex-wrap gap-3 justify-center mt-4">
                    <a href="settings.php" class="inline-flex items-center gap-2 px-4 py-2 glass rounded-lg text-[var(--text-primary)] text-sm hover:bg-[var(--bg-glass)] transition-colors">
                        ⚙️ Paramètres
                    </a>
                    <a href="logout.php" class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/20 border border-red-500/30 text-red-400 rounded-lg text-sm hover:bg-red-500/30 transition-colors">
                        🚪 Se déconnecter
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
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                               class="w-full px-3 py-2 bg-[var(--bg-card)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]">
                    </div>
                    <div>
                        <label class="text-xs text-[var(--text-secondary)] block mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                               class="w-full px-3 py-2 bg-[var(--bg-card)] rounded-lg text-[var(--text-primary)] text-sm border border-[var(--border-glass)] focus:outline-none focus:border-[var(--accent-primary)]">
                    </div>
                    <button type="submit" class="w-full py-2 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">
                        💾 Mettre à jour
                    </button>
                </form>
            </div>
            
            <!-- HISTORIQUE -->
            <div>
                <h3 class="text-sm font-bold text-[var(--text-primary)] mb-3">📜 Historique des parties</h3>
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
                                            +<?php echo number_format($h['net_win'] ?? 0, 2); ?>€
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
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>
</body>
</html>