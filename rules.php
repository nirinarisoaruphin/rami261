<?php
// rules.php
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Règles - Rami 261</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container max-w-md mx-auto min-h-screen flex flex-col bg-[var(--bg-primary)]">
        
        <!-- HEADER -->
        <header class="glass p-4 flex justify-between items-center z-10">
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-lg font-bold text-[var(--text-primary)]">📖 Règles du Rami 261</h1>
            </div>
            <button id="themeToggle" class="p-2 rounded-full glass text-[var(--text-secondary)] hover:bg-[var(--bg-glass)] transition-colors">🌙</button>
        </header>
        
        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 pb-24">
            
            <div class="glass p-6 rounded-2xl space-y-6">
                
                <!-- INTRODUCTION -->
                <div class="text-center">
                    <span class="text-4xl block mb-2">🃏</span>
                    <h2 class="text-xl font-bold text-[var(--text-primary)]">Rami 261</h2>
                    <p class="text-[var(--text-secondary)] text-sm">Le jeu de cartes classique revisité en ligne</p>
                </div>
                
                <div class="h-px bg-[var(--border-glass)]"></div>
                
                <!-- OBJECTIF -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-2 flex items-center gap-2">🎯 Objectif du jeu</h3>
                    <p class="text-[var(--text-secondary)] text-sm leading-relaxed">
                        Le but du Rami 261 est de <strong class="text-[var(--text-primary)]">se débarrasser de toutes ses cartes</strong> 
                        en formant des combinaisons (suites ou groupes). Le premier joueur à atteindre 
                        <strong class="text-yellow-400">261 points</strong> remporte la partie.
                    </p>
                </div>
                
                <!-- CARTES -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-2 flex items-center gap-2">🃏 Le jeu de cartes</h3>
                    <p class="text-[var(--text-secondary)] text-sm leading-relaxed">
                        Le jeu est composé de <strong class="text-[var(--text-primary)]">108 cartes</strong> :
                    </p>
                    <ul class="mt-2 text-sm text-[var(--text-secondary)] space-y-1 list-disc list-inside leading-relaxed">
                        <li><strong class="text-[var(--text-primary)]">2 jeux</strong> de 52 cartes standards (104 cartes)</li>
                        <li><strong class="text-[var(--text-primary)]">4 Jokers</strong> ⭐</li>
                        <li>Les cartes <strong class="text-[var(--text-primary)]">2 à 10</strong> valent leur valeur faciale</li>
                        <li>Les figures (<strong class="text-[var(--text-primary)]">J, Q, K</strong>) valent 10 points</li>
                        <li>L'<strong class="text-[var(--text-primary)]">As</strong> vaut 1 point (ou 14 dans une suite Q-K-A)</li>
                        <li>Les <strong class="text-purple-400">Jokers</strong> valent 0 point</li>
                    </ul>
                </div>
                
                <!-- JOUEURS -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-2 flex items-center gap-2">👥 Nombre de joueurs</h3>
                    <p class="text-[var(--text-secondary)] text-sm">
                        De <strong class="text-[var(--text-primary)]">2 à 5 joueurs</strong> peuvent participer à une partie.
                        La partie commence dès que le nombre minimum de joueurs est atteint.
                    </p>
                </div>
                
                <!-- DISTRIBUTION -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-2 flex items-center gap-2">📤 Distribution</h3>
                    <p class="text-[var(--text-secondary)] text-sm">
                        Chaque joueur reçoit <strong class="text-[var(--text-primary)]">13 cartes</strong> au début de la partie.
                        Le reste des cartes forme la <strong class="text-[var(--text-primary)]">pioche</strong>.
                    </p>
                </div>
                
                <div class="h-px bg-[var(--border-glass)]"></div>
                
                <!-- COMBINAISONS -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-3 flex items-center gap-2">✅ Les combinaisons</h3>
                    
                    <div class="glass p-4 rounded-xl mb-3 bg-[var(--bg-glass)] border border-[var(--border-glass)]">
                        <h4 class="font-bold text-[var(--text-primary)] text-sm mb-1">📈 Les suites (couleur identique)</h4>
                        <p class="text-[var(--text-secondary)] text-sm">
                            Exemple : <span class="text-red-400">5♥, 6♥, 7♥, 8♥</span><br>
                            Minimum <strong class="text-[var(--text-primary)]">3 cartes</strong> consécutives de la même couleur.
                        </p>
                    </div>
                    
                    <div class="glass p-4 rounded-xl mb-3 bg-[var(--bg-glass)] border border-[var(--border-glass)]">
                        <h4 class="font-bold text-[var(--text-primary)] text-sm mb-1">📊 Les groupes (même valeur)</h4>
                        <p class="text-[var(--text-secondary)] text-sm">
                            Exemple : <span class="text-blue-400">7♣, 7♥, 7♦</span><br>
                            Minimum <strong class="text-[var(--text-primary)]">3 cartes</strong> de même valeur.
                        </p>
                    </div>
                    
                    <div class="glass p-4 rounded-xl bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-500/20">
                        <h4 class="font-bold text-[var(--text-primary)] text-sm mb-1">⭐ Les jokers</h4>
                        <p class="text-[var(--text-secondary)] text-sm">
                            Les jokers peuvent <strong class="text-[var(--text-primary)]">remplacer n'importe quelle carte</strong> 
                            manquante dans une combinaison.
                        </p>
                    </div>
                </div>
                
                <div class="h-px bg-[var(--border-glass)]"></div>
                
                <!-- SCORE 261 -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-2 flex items-center gap-2">🔢 Le score 261</h3>
                    <p class="text-[var(--text-secondary)] text-sm leading-relaxed">
                        Le joueur qui atteint <strong class="text-yellow-400">261 points</strong> cumulés 
                        sur plusieurs manches remporte la partie.
                    </p>
                    <div class="mt-2 glass p-3 rounded-lg bg-yellow-400/5 border border-yellow-400/20 text-center">
                        <p class="text-sm text-[var(--text-secondary)]">
                            💡 <strong class="text-yellow-400">Astuce :</strong> Plus vous posez de combinaisons, 
                            plus vous marquez de points !
                        </p>
                    </div>
                </div>
                
                <!-- VICTOIRES SPÉCIALES -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-2 flex items-center gap-2">🌟 Victoires spéciales</h3>
                    <div class="space-y-2">
                        <div class="glass p-3 rounded-lg border border-purple-500/20">
                            <p class="text-sm">
                                <span class="text-2xl mr-2">⭐</span>
                                <strong class="text-purple-400">Triple Joker</strong>
                                <span class="text-[var(--text-secondary)] text-xs ml-2">Avoir 3 jokers en main</span>
                            </p>
                            <p class="text-xs text-[var(--text-secondary)] mt-1">→ Victoire immédiate + <strong class="text-green-400">50€ bonus</strong></p>
                        </div>
                        <div class="glass p-3 rounded-lg border border-purple-500/20">
                            <p class="text-sm">
                                <span class="text-2xl mr-2">⭐⭐</span>
                                <strong class="text-purple-400">Quadruple Joker</strong>
                                <span class="text-[var(--text-secondary)] text-xs ml-2">Avoir 4 jokers en main</span>
                            </p>
                            <p class="text-xs text-[var(--text-secondary)] mt-1">→ Victoire immédiate + <strong class="text-green-400">100€ bonus</strong></p>
                        </div>
                    </div>
                </div>
                
                <!-- TIMEOUT -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-2 flex items-center gap-2">⏱️ Timeout</h3>
                    <p class="text-[var(--text-secondary)] text-sm leading-relaxed">
                        Chaque joueur dispose de <strong class="text-[var(--text-primary)]">30 secondes</strong> pour jouer son tour.
                        Passé ce délai, une <strong class="text-[var(--text-primary)]">action automatique</strong> est effectuée 
                        (pioche + défausse de la première carte).
                    </p>
                </div>
                
                <!-- GAINS -->
                <div>
                    <h3 class="text-md font-bold text-[var(--text-primary)] mb-2 flex items-center gap-2">💰 Calcul des gains</h3>
                    <ul class="text-sm text-[var(--text-secondary)] space-y-1 list-disc list-inside leading-relaxed">
                        <li>Le <strong class="text-[var(--text-primary)]">pot</strong> est la somme des mises de tous les joueurs</li>
                        <li>Une <strong class="text-[var(--text-primary)]">commission</strong> de 5% est prélevée</li>
                        <li>Le gagnant remporte le reste du pot + les éventuels <strong class="text-green-400">bonus</strong></li>
                    </ul>
                </div>
                
                <div class="h-px bg-[var(--border-glass)]"></div>
                
                <!-- BOUTON RETOUR -->
                <div class="text-center">
                    <a href="index.php" class="inline-block px-6 py-2 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white font-bold text-sm hover:scale-105 transition-transform">
                        🏠 Retour à l'accueil
                    </a>
                </div>
                
            </div>
            
        </main>
        
        <!-- BOTTOM NAVIGATION -->
        <nav class="fixed bottom-0 left-0 right-0 glass border-t border-[var(--border-glass)] z-20">
            <div class="flex justify-around max-w-md mx-auto p-2">
                <a href="index.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs">Accueil</span>
                </a>
                <a href="game.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.5 6.5L5 3l3 2.5M5 3l-2 5 3-2.5z"/>
                    </svg>
                    <span class="text-xs">Partie</span>
                </a>
                <a href="leaderboard.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-xs">Classement</span>
                </a>
                <a href="profile.php" class="flex flex-col items-center text-[var(--text-secondary)] py-1 px-3 rounded-lg hover:text-[var(--text-primary)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs">Profil</span>
                </a>
            </div>
        </nav>
        
    </div>
    
    <script>
        // ============================================
        // THÈME
        // ============================================
        
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