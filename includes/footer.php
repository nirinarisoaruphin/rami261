<?php
// includes/footer.php - Footer global
?>
        </main>
        
        <!-- BOTTOM NAVIGATION -->
        <?php if (isLoggedIn()): ?>
        <nav class="fixed bottom-0 left-0 right-0 glass border-t border-[var(--border-glass)] z-20 safe-bottom">
            <div class="flex justify-around max-w-md mx-auto py-1.5 px-2">
                <a href="index.php" class="flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-[var(--accent-primary)]' : 'text-[var(--text-secondary)]'; ?> py-1 px-3 rounded-lg transition-all hover:text-[var(--text-primary)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-[10px] font-medium">Accueil</span>
                </a>
                <a href="game.php" class="flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'game.php' ? 'text-[var(--accent-primary)]' : 'text-[var(--text-secondary)]'; ?> py-1 px-3 rounded-lg transition-all hover:text-[var(--text-primary)]">
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
        // GÉNÉRER LES CARTES EN ARRIÈRE-PLAN
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
    </script>
</body>
</html>