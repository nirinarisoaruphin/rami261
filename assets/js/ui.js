// assets/js/ui.js

export default class UI {
    constructor(game) {
        this.game = game;
        this.selectedCards = [];
        this.elements = {
            hand: document.getElementById('playerHand'),
            melds: document.getElementById('meldsContainer'),
            message: document.getElementById('gameMessage'),
            score: document.getElementById('handScore'),
            pot: document.getElementById('potAmount'),
            playerCount: document.getElementById('playerCount'),
            cardsLeft: document.getElementById('cardsLeft'),
            drawPile: document.getElementById('drawPile'),
            discardPile: document.getElementById('discardPile'),
            playersList: document.getElementById('playersList'),
            statusText: document.getElementById('statusText'),
            gameStatus: document.getElementById('gameStatus')
        };
    }
    
    // ============================================
    // MISE À JOUR GLOBALE
    // ============================================
    
    update() {
        const state = this.game.state;
        if (!state) return;
        
        this.updatePot();
        this.updatePlayerCount();
        this.updateCardsLeft();
        this.updateHand();
        this.updateMelds();
        this.updatePlayers();
        this.updateMessage();
        this.updateButtons();
        this.updateStatusIndicator();
    }
    
    // ============================================
    // MISE À JOUR SPÉCIFIQUE
    // ============================================
    
    updatePot() {
        const el = this.elements.pot;
        if (el) {
            el.textContent = this.game.state?.game?.bet_amount || 0;
        }
    }
    
    updatePlayerCount() {
        const el = this.elements.playerCount;
        if (el) {
            const players = this.game.getPlayers();
            el.textContent = players ? players.length : 0;
        }
    }
    
    updateCardsLeft() {
        const el = this.elements.cardsLeft;
        if (el) {
            const totalCards = 108;
            let usedCards = 0;
            const players = this.game.getPlayers();
            if (players) {
                players.forEach(p => {
                    if (Array.isArray(p.hand)) {
                        usedCards += p.hand.length;
                    }
                });
            }
            el.textContent = Math.max(0, totalCards - usedCards);
        }
    }
    
    updateStatusIndicator() {
        const el = this.elements.statusText;
        if (el) {
            const status = this.game.getGameStatus();
            const statusMap = {
                'waiting': '⏳ En attente...',
                'playing': '🎯 En jeu',
                'finished': '🏆 Terminé',
                'closed': '🔒 Fermé'
            };
            el.textContent = statusMap[status] || status;
        }
        
        const indicator = document.querySelector('#gameStatus .w-2');
        if (indicator) {
            const status = this.game.getGameStatus();
            const colors = {
                'waiting': 'bg-yellow-400',
                'playing': 'bg-green-400 animate-pulse',
                'finished': 'bg-blue-400',
                'closed': 'bg-red-400'
            };
            indicator.className = `w-2 h-2 rounded-full ${colors[status] || 'bg-gray-400'}`;
        }
    }
    
    // ============================================
    // MAIN DU JOUEUR
    // ============================================
    
    updateHand() {
        const container = this.elements.hand;
        if (!container) return;
        
        const hand = this.game.getMyHand();
        container.innerHTML = '';
        
        if (!hand || hand.length === 0) {
            container.innerHTML = '<p class="text-[var(--text-secondary)] text-sm">Votre main est vide</p>';
            this.updateScore(0);
            return;
        }
        
        let score = 0;
        hand.forEach((card, index) => {
            if (!card.is_joker) score += card.points || 0;
            const isSelected = this.selectedCards.includes(index);
            const isJoker = card.is_joker;
            
            const div = document.createElement('div');
            div.className = `
                w-14 h-20 rounded-lg shadow-lg flex flex-col items-center justify-center cursor-pointer 
                transition-all duration-200 hover:-translate-y-2 select-none
                ${isSelected ? 'border-2 border-[var(--accent-primary)] -translate-y-4 shadow-[var(--accent-primary)]/30 shadow-lg' : 'border-2 border-transparent'}
                ${isJoker ? 'bg-gradient-to-r from-purple-500 to-pink-500' : 'bg-[var(--bg-card)]'}
            `;
            div.dataset.index = index;
            div.title = card.is_joker ? 'Joker' : `${card.value} de ${card.suit}`;
            
            const suitColors = { hearts: 'text-red-400', diamonds: 'text-red-400', clubs: 'text-gray-400', spades: 'text-gray-400' };
            const suitSymbols = { hearts: '♥', diamonds: '♦', clubs: '♣', spades: '♠' };
            const color = isJoker ? 'text-white' : (suitColors[card.suit] || 'text-gray-400');
            const symbol = isJoker ? '⭐' : (suitSymbols[card.suit] || '');
            
            div.innerHTML = `
                <span class="text-xl font-bold ${color}">${card.value}</span>
                <span class="text-lg ${color}">${symbol}</span>
                ${isJoker ? '<span class="text-white text-xs">JOKER</span>' : ''}
            `;
            
            div.addEventListener('click', () => this.toggleCard(index));
            container.appendChild(div);
        });
        
        this.updateScore(score);
    }
    
    updateScore(score) {
        const el = this.elements.score;
        if (el) {
            el.textContent = score || 0;
        }
    }
    
    // ============================================
    // COMBINAISONS POSÉES
    // ============================================
    
    updateMelds() {
        const container = this.elements.melds;
        if (!container) return;
        
        const melds = this.game.getMyMelds();
        container.innerHTML = '';
        
        if (!melds || melds.length === 0) {
            container.innerHTML = '<p class="text-xs text-[var(--text-secondary)] text-center">Aucune combinaison posée</p>';
            return;
        }
        
        melds.forEach((meld, idx) => {
            const div = document.createElement('div');
            div.className = 'glass p-2 rounded-lg mb-2 flex flex-wrap gap-1 items-center animate-slide-up';
            
            const cardsHtml = meld.map(c => {
                if (c.is_joker) {
                    return '<span class="px-2 py-1 bg-purple-500/20 rounded text-xs text-purple-400">⭐ JOKER</span>';
                }
                const symbol = c.suit === 'hearts' ? '♥' : c.suit === 'diamonds' ? '♦' : c.suit === 'clubs' ? '♣' : '♠';
                const color = ['hearts', 'diamonds'].includes(c.suit) ? 'text-red-400' : 'text-gray-400';
                return `<span class="px-2 py-1 bg-[var(--bg-card)] rounded text-xs ${color}">${c.value}${symbol}</span>`;
            }).join(' + ');
            
            div.innerHTML = `
                <span class="text-xs text-[var(--text-secondary)] mr-1">#${idx + 1}</span>
                ${cardsHtml}
            `;
            container.appendChild(div);
        });
    }
    
    // ============================================
    // JOUEURS
    // ============================================
    
    updatePlayers() {
        const container = this.elements.playersList;
        if (!container) return;
        
        const players = this.game.getPlayers();
        container.innerHTML = '';
        
        if (!players || players.length === 0) return;
        
        const currentTurn = this.game.getCurrentTurn();
        const userId = this.game.userId;
        
        players.forEach(player => {
            const isMe = player.user_id === userId;
            const isCurrentTurn = currentTurn === player.position;
            const isWinner = player.is_winner;
            const hand = Array.isArray(player.hand) ? player.hand : [];
            const handCount = isMe ? hand.length : '?';
            
            const div = document.createElement('div');
            div.className = `
                glass p-2 rounded-lg text-center min-w-[70px] flex-shrink-0 
                ${isCurrentTurn ? 'border-2 border-[var(--accent-primary)]' : ''} 
                ${isWinner ? 'border-2 border-yellow-400' : ''}
                ${isMe ? 'bg-[var(--bg-glass)]' : ''}
                transition-all duration-300
            `;
            
            div.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-cyan-500 flex items-center justify-center text-white text-lg font-bold mx-auto mb-1">
                    ${player.username ? player.username.charAt(0).toUpperCase() : '?'}
                </div>
                <p class="text-xs font-bold text-[var(--text-primary)] truncate max-w-[60px]" title="${player.username || ''}">
                    ${player.username || '?'}
                    ${isMe ? '<span class="text-[var(--accent-primary)]"> ★</span>' : ''}
                </p>
                <p class="text-xs text-[var(--text-secondary)]">${handCount} 🃏</p>
                ${isWinner ? '<span class="text-yellow-400 text-xs">👑 Gagnant</span>' : ''}
                ${isCurrentTurn && !isWinner ? '<span class="text-[var(--accent-primary)] text-xs">▶️ Tour</span>' : ''}
            `;
            container.appendChild(div);
        });
    }
    
    // ============================================
    // MESSAGE
    // ============================================
    
    updateMessage() {
        const el = this.elements.message;
        if (!el) return;
        
        const status = this.game.getGameStatus();
        const isMyTurn = this.game.isMyTurn();
        const winner = this.game.getWinner();
        
        if (status === 'waiting') {
            const players = this.game.getPlayers();
            const count = players ? players.length : 0;
            let html = `⏳ En attente de joueurs... (${2} minimum)`;
            if (this.game.isHost && count >= 2) {
                html += ' <button id="btnStart" class="ml-2 px-4 py-1 bg-gradient-to-r from-purple-500 to-cyan-500 rounded-lg text-white text-xs font-bold hover:scale-105 transition-transform">🚀 Démarrer</button>';
            }
            el.innerHTML = html;
            el.className = 'glass p-3 text-center text-sm text-[var(--text-secondary)]';
        } else if (status === 'playing') {
            el.textContent = isMyTurn ? '🎯 C\'est votre tour ! Choisissez une action.' : '⏳ Tour du joueur suivant...';
            el.className = `glass p-3 text-center text-sm ${isMyTurn ? 'text-[var(--accent-primary)] font-bold' : 'text-[var(--text-secondary)]'}`;
        } else if (status === 'finished') {
            if (winner) {
                let message = `🏆 ${winner.username} a gagné !`;
                if (winner.user_id === this.game.userId) {
                    message += ' 🎉 Félicitations !';
                }
                el.textContent = message;
                el.className = 'glass p-3 text-center text-sm text-yellow-400 font-bold';
            } else {
                el.textContent = '🏆 Partie terminée !';
                el.className = 'glass p-3 text-center text-sm text-yellow-400 font-bold';
            }
        } else if (status === 'closed') {
            el.textContent = '🔒 Partie fermée';
            el.className = 'glass p-3 text-center text-sm text-red-400';
        }
    }
    
    // ============================================
    // BOUTONS
    // ============================================
    
    updateButtons() {
        const isPlaying = this.game.isGamePlaying();
        const isMyTurn = this.game.isMyTurn();
        const isFinished = this.game.isGameFinished();
        
        const buttons = ['btnDraw', 'btnMeld', 'btnDiscard', 'btnEndTurn'];
        buttons.forEach(id => {
            const btn = document.getElementById(id);
            if (btn) {
                btn.disabled = !isPlaying || !isMyTurn || isFinished;
            }
        });
    }
    
    // ============================================
    // SÉLECTION DES CARTES
    // ============================================
    
    toggleCard(index) {
        if (!this.game.isMyTurn() || !this.game.isGamePlaying()) return;
        
        const idx = this.selectedCards.indexOf(index);
        if (idx > -1) {
            this.selectedCards.splice(idx, 1);
        } else {
            this.selectedCards.push(index);
        }
        this.updateHand();
    }
    
    getSelectedCards() {
        return this.selectedCards;
    }
    
    clearSelection() {
        this.selectedCards = [];
        this.updateHand();
    }
    
    // ============================================
    // MESSAGES TEMPORAIRES
    // ============================================
    
    showMessage(text, type = 'info') {
        const el = this.elements.message;
        if (!el) return;
        
        const colors = {
            info: 'text-[var(--text-secondary)]',
            success: 'text-green-400',
            error: 'text-red-400',
            warning: 'text-yellow-400'
        };
        
        el.textContent = text;
        el.className = `glass p-3 text-center text-sm ${colors[type] || colors.info}`;
        
        if (type !== 'error' && type !== 'warning') {
            setTimeout(() => {
                if (el.textContent === text) {
                    this.updateMessage();
                }
            }, 4000);
        }
    }
    
    // ============================================
    // INDICATEUR DE CHARGEMENT
    // ============================================
    
    showLoading() {
        const el = this.elements.hand;
        if (el) {
            el.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-[var(--accent-primary)] border-t-transparent"></div><p class="text-xs text-[var(--text-secondary)] mt-2">Chargement...</p></div>';
        }
    }
    
    hideLoading() {
        // Rien à faire, l'update() va rafraîchir
    }
}