<?php
// withdraw.php - Page de retrait
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/TransactionManager.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$transactionManager = new TransactionManager();

// Récupérer le solde DIRECTEMENT depuis la BDD
$balance = $transactionManager->getBalance($userId);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) ($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? 'manual';
    $notes = trim($_POST['notes'] ?? '');
    
    if ($amount <= 0) {
        $error = 'Veuillez entrer un montant valide';
    } elseif ($amount > $balance) {
        $error = 'Solde insuffisant. Vous avez ' . formatCurrency($balance);
    } else {
        $result = $transactionManager->withdraw($userId, $amount, $method, $notes);
        
        if ($result['success']) {
            $message = '✅ Retrait de ' . formatCurrency($amount) . ' effectué avec succès !';
            $balance = $transactionManager->getBalance($userId);
            $_SESSION['user_stats']['balance'] = $balance;
        } else {
            $error = '❌ ' . $result['error'];
        }
    }
}

$pageTitle = 'Retrait - Rami 261';
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
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col">
        
        <div class="bg-layer-1"></div>
        <div class="bg-layer-flag"></div>
        <div class="bg-layer-cards" id="cardsBackground"></div>
        <div class="bg-layer-particles" id="particlesContainer"></div>
        
        <header class="glass p-4 flex justify-between items-center z-10">
            <div class="flex items-center gap-3">
                <a href="transactions.php" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-lg font-bold text-[var(--text-primary)]">🏦 Retrait</h1>
            </div>
            <button id="themeToggle" class="theme-toggle">
                <span id="themeIcon"><?php echo $theme === 'dark' ? '🌙' : '☀️'; ?></span>
            </button>
        </header>
        
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <div class="glass p-4 rounded-2xl text-center mb-4">
                <p class="text-xs text-[var(--text-secondary)]">Solde disponible</p>
                <p class="text-2xl font-bold text-[var(--text-primary)]" id="currentBalance">
                    <?php echo formatCurrency($balance); ?>
                </p>
            </div>
            
            <?php if ($message): ?>
                <div class="bg-green-500/20 border border-green-500/30 text-green-600 px-4 py-3 rounded-lg text-sm mb-4"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-600 px-4 py-3 rounded-lg text-sm mb-4"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="glass p-6 rounded-2xl">
                <h2 class="text-sm font-bold text-[var(--text-primary)] mb-4">📤 Retirer des fonds</h2>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="text-sm text-[var(--text-secondary)] block mb-1">Montant (Ar)</label>
                        <input type="number" name="amount" id="withdrawAmount" required min="100" step="100" 
                               class="input-modern" placeholder="1000" 
                               max="<?php echo $balance; ?>"
                               value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>">
                        <p class="text-xs text-[var(--text-secondary)] mt-1">
                            Minimum: 100 Ar | Maximum: <?php echo formatCurrency($balance); ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-[var(--text-secondary)] block mb-1">Méthode</label>
                        <select name="method" class="input-modern">
                            <option value="manual">💳 Manuel</option>
                            <option value="mobile">📱 Mobile Money</option>
                            <option value="bank">🏦 Virement bancaire</option>
                            <option value="card">💳 Carte bancaire</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="text-sm text-[var(--text-secondary)] block mb-1">Notes (optionnel)</label>
                        <textarea name="notes" class="input-modern" rows="2" placeholder="Informations complémentaires..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary w-full py-3 text-base" <?php echo $balance < 100 ? 'disabled' : ''; ?>>
                        🏦 Retirer <?php echo formatCurrency($_POST['amount'] ?? 0); ?>
                    </button>
                    
                    <?php if ($balance < 100): ?>
                        <p class="text-xs text-red-500 text-center">Solde insuffisant pour effectuer un retrait (minimum 100 Ar)</p>
                    <?php endif; ?>
                </form>
                
                <div class="mt-4 p-3 glass rounded-lg text-xs text-[var(--text-secondary)]">
                    <p>💡 Les retraits sont traités sous 24-48h.</p>
                    <p class="mt-1">📞 En cas de problème, contactez le support.</p>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="transactions.php" class="text-sm text-[var(--accent-primary)] hover:underline">← Retour aux transactions</a>
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
        
        document.getElementById('withdrawAmount')?.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const btn = document.querySelector('button[type="submit"]');
            if (btn) {
                btn.textContent = `🏦 Retirer ${formatCurrency(amount)}`;
            }
        });
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-MG', {
                style: 'currency',
                currency: 'MGA',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount).replace('MGA', 'Ar');
        }
        
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