<?php
// includes/header.php - Header global avec image drapeau
if (!isset($pdo)) {
    require_once __DIR__ . '/config.php';
}

$isLoggedIn = isLoggedIn();

if (!$isLoggedIn && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'register.php') {
    redirect('login.php');
}

$globalStats = null;
if ($isLoggedIn) {
    if (isset($_SESSION['user_stats']) && $_SESSION['user_stats']) {
        $globalStats = $_SESSION['user_stats'];
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    u.balance,
                    (SELECT COUNT(*) FROM game_players WHERE user_id = u.id AND is_winner = 1) as wins,
                    (SELECT COUNT(*) FROM game_players WHERE user_id = u.id) as games_played
                FROM users u
                WHERE u.id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $globalStats = $stmt->fetch();
            $_SESSION['user_stats'] = $globalStats;
        } catch (PDOException $e) {
            $globalStats = null;
        }
    }
}

$theme = $_SESSION['theme'] ?? $_COOKIE['theme'] ?? 'light';
$pageTitle = $pageTitle ?? 'Rami 261';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $pageTitle; ?></title>
    
    <link rel="icon" href="favicon.php" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.php" type="image/x-icon">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cards.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
    </style>
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col">
        
        <!-- BACKGROUND -->
        <div class="bg-layer-1"></div>
        <div class="bg-layer-flag"></div>
        <div class="bg-layer-cards" id="cardsBackground"></div>
        <div class="bg-layer-particles" id="particlesContainer"></div>
        
        <!-- HEADER AVEC DRAPEAU IMAGE -->
        <header class="glass px-4 py-3 flex justify-between items-center z-10">
            <div class="flex items-center gap-2.5">
                <!-- DRAPEAU IMAGE -->
                <div class="w-8 h-6 rounded overflow-hidden shadow-md flex-shrink-0 bg-white border border-gray-200 dark:border-gray-700">
                    <img src="assets/images/flags/madagascar.png" alt="Drapeau Madagascar" 
                         class="w-full h-full object-cover"
                         onerror="this.style.display='none'">
                </div>
                <div>
                    <h1 class="logo-text text-sm font-extrabold tracking-tight">🃏 Rami 261</h1>
                    <span class="logo-sub text-[10px] font-medium">🇲🇬 Jeu de cartes en ligne</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="themeToggle" class="theme-toggle" title="Changer de thème">
                    <span id="themeIcon"><?php echo $theme === 'dark' ? '🌙' : '☀️'; ?></span>
                </button>
                <?php if ($isLoggedIn): ?>
                    <a href="profile.php" class="flex items-center gap-1.5 glass px-2.5 py-1 rounded-full hover:bg-[var(--bg-glass)] transition-colors">
                        <span class="text-xs font-medium text-[var(--text-primary)]"><?php echo htmlspecialchars(getCurrentUsername()); ?></span>
                        <span class="user-avatar"><?php echo strtoupper(substr(getCurrentUsername(), 0, 1)); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </header>
        
        <!-- STATS BAR -->
        <?php if ($isLoggedIn && $globalStats): ?>
        <div class="stats-bar glass flex items-center justify-around z-10 animate-scale-in">
            <div class="stat-item">
                <span class="stat-icon">💰</span>
                <p class="stat-value" id="headerBalance"><?php echo formatCurrency($globalStats['balance'] ?? 0); ?></p>
                <p class="stat-label">Solde</p>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-icon">🏅</span>
                <p class="stat-value text-green-500"><?php echo $globalStats['wins'] ?? 0; ?></p>
                <p class="stat-label">Victoires</p>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-icon">📊</span>
                <p class="stat-value"><?php echo $globalStats['games_played'] ?? 0; ?></p>
                <p class="stat-label">Parties</p>
            </div>
        </div>
        <?php endif; ?>
        
        <main class="flex-1 overflow-y-auto p-4 pb-24">