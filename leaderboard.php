<?php
// leaderboard.php - Classement avec images
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Récupérer le classement
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.username,
        u.balance,
        (SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 1) as wins,
        (SELECT COUNT(*) FROM game_players WHERE user_id = u.id) as games_played,
        ROUND(
            (SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 1) / 
            NULLIF((SELECT COUNT(*) FROM game_players WHERE user_id = u.id), 0) * 100, 
            1
        ) as win_rate
    FROM users u
    WHERE u.id IN (SELECT DISTINCT user_id FROM game_players)
    ORDER BY wins DESC, win_rate DESC
    LIMIT 100
");
$stmt->execute();
$players = $stmt->fetchAll();

$ranked = [];
$rank = 1;
foreach ($players as $p) {
    $ranked[] = array_merge($p, ['rank' => $rank++]);
}

// Statistiques globales
$stats = $pdo->query("
    SELECT 
        COUNT(DISTINCT user_id) as total_players,
        SUM(wins) as total_wins,
        AVG(win_rate) as avg_win_rate
    FROM (
        SELECT 
            user_id,
            COUNT(*) as wins,
            ROUND(COUNT(*) / (SELECT COUNT(*) FROM game_players gp2 WHERE gp2.user_id = gp.user_id) * 100, 1) as win_rate
        FROM game_players gp
        WHERE is_winner = 1
        GROUP BY user_id
    ) as subquery
")->fetch();
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Classement - Rami 261</title>
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
                <h1 class="text-lg font-bold text-[var(--text-primary)]">🏆 Classement</h1>
            </div>
            <button id="themeToggle" class="p-2 rounded-full glass text-[var(--text-secondary)]">🌙</button>
        </header>
        
        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <!-- STATS GLOBALES -->
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="stat-card">
                    <p class="stat-value"><?php echo $stats['total_players'] ?? 0; ?></p>
                    <p class="stat-label">👥 Joueurs</p>
                </div>
                <div class="stat-card" style="border-color: rgba(234,179,8,0.3);">
                    <p class="stat-value" style="color: #eab308;"><?php echo number_format($stats['total_wins'] ?? 0, 0); ?></p>
                    <p class="stat-label">🏅 Victoires</p>
                </div>
                <div class="stat-card" style="border-color: rgba(34,197,94,0.3);">
                    <p class="stat-value" style="color: #22c55e;"><?php echo number_format($stats['avg_win_rate'] ?? 0, 1); ?>%</p>
                    <p class="stat-label">📊 Win rate</p>
                </div>
            </div>
            
            <!-- TOP 3 -->
            <?php if (count($ranked) >= 3): ?>
                <div class="flex justify-center items-end gap-4 mb-6 h-52">
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-400/20 flex items-center justify-center text-2xl mx-auto mb-1">🥈</div>
                        <div class="glass p-2 rounded-lg">
                            <p class="text-sm font-bold text-[var(--text-primary)]"><?php echo htmlspecialchars($ranked[1]['username'] ?? '?'); ?></p>
                            <p class="text-xs text-[var(--text-secondary)]"><?php echo $ranked[1]['wins'] ?? 0; ?> victoires</p>
                            <p class="text-xs text-[var(--text-secondary)]"><?php echo $ranked[1]['win_rate'] ?? 0; ?>%</p>
                        </div>
                    </div>
                    <div class="text-center -mt-8">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 flex items-center justify-center text-3xl mx-auto mb-1 animate-pulse shadow-lg shadow-yellow-500/25">👑</div>
                        <div class="glass p-3 rounded-xl border-2 border-yellow-400/50 bg-yellow-400/5">
                            <p class="text-lg font-bold text-[var(--text-primary)]"><?php echo htmlspecialchars($ranked[0]['username'] ?? '?'); ?></p>
                            <p class="text-sm font-bold text-yellow-400"><?php echo $ranked[0]['wins'] ?? 0; ?> victoires</p>
                            <p class="text-xs text-[var(--text-secondary)]"><?php echo $ranked[0]['win_rate'] ?? 0; ?>%</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-full bg-orange-400/20 flex items-center justify-center text-2xl mx-auto mb-1">🥉</div>
                        <div class="glass p-2 rounded-lg">
                            <p class="text-sm font-bold text-[var(--text-primary)]"><?php echo htmlspecialchars($ranked[2]['username'] ?? '?'); ?></p>
                            <p class="text-xs text-[var(--text-secondary)]"><?php echo $ranked[2]['wins'] ?? 0; ?> victoires</p>
                            <p class="text-xs text-[var(--text-secondary)]"><?php echo $ranked[2]['win_rate'] ?? 0; ?>%</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- LISTE COMPLÈTE -->
            <div class="glass rounded-2xl overflow-hidden">
                <div class="grid grid-cols-12 gap-2 px-4 py-2 bg-[var(--bg-glass)] text-xs text-[var(--text-secondary)] font-medium border-b border-[var(--border-glass)]">
                    <div class="col-span-2">#</div>
                    <div class="col-span-5">Joueur</div>
                    <div class="col-span-3 text-center">🏅</div>
                    <div class="col-span-2 text-right">📊</div>
                </div>
                
                <?php if (empty($ranked)): ?>
                    <div class="p-8 text-center text-[var(--text-secondary)] text-sm">
                        Aucun joueur classé
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($ranked, 0, 50) as $player): ?>
                        <div class="grid grid-cols-12 gap-2 px-4 py-3 border-b border-[var(--border-glass)] hover:bg-[var(--bg-glass)] transition-colors
                                    <?php echo (isset($_SESSION['user_id']) && $player['id'] == $_SESSION['user_id']) ? 'bg-[var(--accent-primary)]/10 border-l-4 border-[var(--accent-primary)]' : ''; ?>">
                            <div class="col-span-2 text-sm font-bold text-[var(--text-primary)]">
                                <?php 
                                    if ($player['rank'] == 1) echo '🥇';
                                    elseif ($player['rank'] == 2) echo '🥈';
                                    elseif ($player['rank'] == 3) echo '🥉';
                                    else echo '<span class="text-[var(--text-secondary)]">#' . $player['rank'] . '</span>';
                                ?>
                            </div>
                            <div class="col-span-5 text-sm text-[var(--text-primary)] truncate flex items-center gap-2">
                                <?php echo htmlspecialchars($player['username']); ?>
                                <?php if (isset($_SESSION['user_id']) && $player['id'] == $_SESSION['user_id']): ?>
                                    <span class="text-xs text-[var(--accent-primary)] font-bold">(vous)</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-span-3 text-center text-sm text-[var(--text-primary)]">
                                <?php echo $player['wins']; ?>
                            </div>
                            <div class="col-span-2 text-right text-sm font-medium <?php echo ($player['win_rate'] ?? 0) >= 50 ? 'text-green-400' : 'text-[var(--text-secondary)]'; ?>">
                                <?php echo $player['win_rate'] ?? 0; ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="mt-4 text-center text-xs text-[var(--text-secondary)] opacity-60">
                <p>🏅 Victoires • 📊 Taux de victoire</p>
                <p class="mt-1">Classement mis à jour en temps réel</p>
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
                <a href="leaderboard.php" class="flex flex-col items-center text-[var(--accent-primary)] py-1 px-3 rounded-lg transition-colors">
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