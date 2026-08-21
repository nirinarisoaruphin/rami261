<?php
// transactions.php - Page d'historique des transactions
require_once 'includes/config.php';
require_once 'includes/functions.php'; // ← AJOUT IMPORTANT
require_once 'includes/TransactionManager.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$transactionManager = new TransactionManager();

// Récupérer les paramètres de pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$type = isset($_GET['type']) ? $_GET['type'] : null;
$limit = 20;
$offset = ($page - 1) * $limit;

// Récupérer l'historique
$history = $transactionManager->getHistory($userId, $limit, $offset, $type);
$stats = $transactionManager->getStats($userId);
$balance = $transactionManager->getBalance($userId);

$pageTitle = 'Transactions - Rami 261';
$theme = $_SESSION['theme'] ?? $_COOKIE['theme'] ?? 'light';
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
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col">
        
        <div class="bg-layer-1"></div>
        <div class="bg-layer-flag"></div>
        <div class="bg-layer-cards" id="cardsBackground"></div>
        <div class="bg-layer-particles" id="particlesContainer"></div>
        
        <header class="glass p-4 flex justify-between items-center z-10">
            <div class="flex items-center gap-3">
                <a href="profile.php" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="flag-header"></div>
                <h1 class="text-lg font-bold text-[var(--text-primary)]">💰 Transactions</h1>
            </div>
            <button id="themeToggle" class="theme-toggle">
                <span id="themeIcon"><?php echo $theme === 'dark' ? '🌙' : '☀️'; ?></span>
            </button>
        </header>
        
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <!-- SOLDE -->
            <div class="glass p-4 rounded-2xl text-center mb-4">
                <p class="text-xs text-[var(--text-secondary)]">Solde actuel</p>
                <p class="text-2xl font-bold text-[var(--text-primary)]"><?php echo formatCurrency($balance); ?></p>
                <div class="flex gap-3 justify-center mt-3">
                    <a href="deposit.php" class="btn-primary text-sm px-4 py-2">💰 Déposer</a>
                    <a href="withdraw.php" class="btn-secondary text-sm px-4 py-2">🏦 Retirer</a>
                </div>
            </div>
            
            <!-- STATISTIQUES -->
            <div class="grid grid-cols-4 gap-2 mb-4">
                <div class="glass p-2 text-center">
                    <p class="text-xs text-[var(--text-secondary)]">Dépôts</p>
                    <p class="text-sm font-bold text-green-500"><?php echo formatCurrency($stats['total_deposits'] ?? 0); ?></p>
                </div>
                <div class="glass p-2 text-center">
                    <p class="text-xs text-[var(--text-secondary)]">Retraits</p>
                    <p class="text-sm font-bold text-red-500"><?php echo formatCurrency($stats['total_withdraws'] ?? 0); ?></p>
                </div>
                <div class="glass p-2 text-center">
                    <p class="text-xs text-[var(--text-secondary)]">Gains</p>
                    <p class="text-sm font-bold text-yellow-500"><?php echo formatCurrency($stats['total_wins'] ?? 0); ?></p>
                </div>
                <div class="glass p-2 text-center">
                    <p class="text-xs text-[var(--text-secondary)]">Mises</p>
                    <p class="text-sm font-bold text-blue-500"><?php echo formatCurrency($stats['total_bets'] ?? 0); ?></p>
                </div>
            </div>
            
            <!-- FILTRES -->
            <div class="flex gap-2 overflow-x-auto pb-2 mb-4 scrollbar-hide">
                <a href="transactions.php" class="px-3 py-1.5 rounded-full text-xs font-medium <?php echo !$type ? 'bg-[var(--accent-primary)] text-white' : 'glass text-[var(--text-secondary)]'; ?> whitespace-nowrap">
                    Tous
                </a>
                <a href="transactions.php?type=deposit" class="px-3 py-1.5 rounded-full text-xs font-medium <?php echo $type === 'deposit' ? 'bg-[var(--accent-primary)] text-white' : 'glass text-[var(--text-secondary)]'; ?> whitespace-nowrap">
                    💰 Dépôts
                </a>
                <a href="transactions.php?type=withdraw" class="px-3 py-1.5 rounded-full text-xs font-medium <?php echo $type === 'withdraw' ? 'bg-[var(--accent-primary)] text-white' : 'glass text-[var(--text-secondary)]'; ?> whitespace-nowrap">
                    🏦 Retraits
                </a>
                <a href="transactions.php?type=win" class="px-3 py-1.5 rounded-full text-xs font-medium <?php echo $type === 'win' ? 'bg-[var(--accent-primary)] text-white' : 'glass text-[var(--text-secondary)]'; ?> whitespace-nowrap">
                    🏆 Gains
                </a>
                <a href="transactions.php?type=bet" class="px-3 py-1.5 rounded-full text-xs font-medium <?php echo $type === 'bet' ? 'bg-[var(--accent-primary)] text-white' : 'glass text-[var(--text-secondary)]'; ?> whitespace-nowrap">
                    🎯 Mises
                </a>
            </div>
            
            <!-- HISTORIQUE -->
            <div class="space-y-2">
                <?php if (!$history['success'] || empty($history['transactions'])): ?>
                    <div class="glass p-6 text-center text-[var(--text-secondary)] text-sm">
                        <span class="text-2xl block mb-2">📭</span>
                        Aucune transaction trouvée
                    </div>
                <?php else: ?>
                    <?php foreach ($history['transactions'] as $transaction): ?>
                        <div class="glass p-3 rounded-xl flex justify-between items-center">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">
                                        <?php 
                                            switch($transaction['type']) {
                                                case 'deposit': echo '💰'; break;
                                                case 'withdraw': echo '🏦'; break;
                                                case 'win': echo '🏆'; break;
                                                case 'bet': echo '🎯'; break;
                                                default: echo '📊';
                                            }
                                        ?>
                                    </span>
                                    <p class="text-sm font-medium text-[var(--text-primary)]">
                                        <?php echo $transaction['type_label']; ?>
                                    </p>
                                    <span class="text-xs px-2 py-0.5 rounded-full <?php 
                                        echo $transaction['status'] === 'completed' ? 'bg-green-500/20 text-green-600' : 
                                            ($transaction['status'] === 'pending' ? 'bg-yellow-500/20 text-yellow-600' : 
                                            'bg-red-500/20 text-red-600'); 
                                    ?>">
                                        <?php echo $transaction['status']; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-[var(--text-secondary)]">
                                    <?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?>
                                </p>
                                <?php if ($transaction['reference']): ?>
                                    <p class="text-xs text-[var(--text-secondary)] opacity-60">#<?php echo $transaction['reference']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold <?php 
                                    echo in_array($transaction['type'], ['deposit', 'win']) ? 'text-green-500' : 'text-red-500'; 
                                ?>">
                                    <?php echo in_array($transaction['type'], ['deposit', 'win']) ? '+' : '-'; ?>
                                    <?php echo formatCurrency($transaction['amount']); ?>
                                </p>
                                <p class="text-xs text-[var(--text-secondary)]">
                                    Solde: <?php echo formatCurrency($transaction['balance_after']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- PAGINATION -->
                    <?php if ($history['total'] > $history['limit']): ?>
                        <div class="flex justify-center gap-2 mt-4">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $type ? '&type='.$type : ''; ?>" 
                                   class="px-3 py-1 glass rounded-lg text-sm text-[var(--text-secondary)]">←</a>
                            <?php endif; ?>
                            
                            <span class="px-3 py-1 rounded-lg text-sm text-[var(--text-primary)]">
                                Page <?php echo $page; ?> / <?php echo ceil($history['total'] / $history['limit']); ?>
                            </span>
                            
                            <?php if ($page < ceil($history['total'] / $history['limit'])): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $type ? '&type='.$type : ''; ?>" 
                                   class="px-3 py-1 glass rounded-lg text-sm text-[var(--text-secondary)]">→</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
            
        </main>
        
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
    </script>
</body>
</html>