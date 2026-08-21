// assets/js/game.js

export default class RamiGame {
    constructor(config) {
        this.config = config;
        this.state = null;
        this.wasMyTurn = false;
        this.selectedCards = [];
        this.gameId = config.gameId;
        this.userId = config.userId;
        this.isHost = config.isHost;
        this.isLoading = false;
        
        this.init();
    }
    
    async init() {
        if (this.gameId) {
            await this.fetchState();
        }
    }
    
    // ============================================
    // RÉCUPÉRATION DE L'ÉTAT
    // ============================================
    
    async fetchState() {
        if (this.isLoading) return null;
        this.isLoading = true;
        
        try {
            const response = await fetch(`api/game/state.php?game_id=${this.gameId}&t=${Date.now()}`);
            const data = await response.json();
            
            if (data.success) {
                this.state = data;
                this.wasMyTurn = data.is_my_turn;
                return data;
            }
            return null;
        } catch (error) {
            console.error('Erreur fetchState:', error);
            return null;
        } finally {
            this.isLoading = false;
        }
    }
    
    // ============================================
    // ACTIONS DE JEU
    // ============================================
    
    async draw() {
        try {
            const response = await fetch(`api/game/draw.php?game_id=${this.gameId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ player_id: this.userId })
            });
            const data = await response.json();
            
            if (data.success) {
                await this.fetchState();
                return data.card;
            }
            return null;
        } catch (error) {
            console.error('Erreur draw:', error);
            return null;
        }
    }
    
    async playMeld(cardIndices) {
        try {
            const response = await fetch(`api/game/play.php?game_id=${this.gameId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    player_id: this.userId,
                    card_indices: cardIndices
                })
            });
            const data = await response.json();
            
            if (data.success) {
                await this.fetchState();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Erreur playMeld:', error);
            return false;
        }
    }
    
    async discard(cardIndex) {
        try {
            const response = await fetch(`api/game/discard.php?game_id=${this.gameId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    player_id: this.userId,
                    card_index: cardIndex
                })
            });
            const data = await response.json();
            
            if (data.success) {
                await this.fetchState();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Erreur discard:', error);
            return false;
        }
    }
    
    async endTurn() {
        try {
            const response = await fetch(`api/game/endturn.php?game_id=${this.gameId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ player_id: this.userId })
            });
            const data = await response.json();
            
            if (data.success) {
                await this.fetchState();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Erreur endTurn:', error);
            return false;
        }
    }
    
    async start() {
        try {
            const response = await fetch(`api/game/start.php?game_id=${this.gameId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ host_id: this.userId })
            });
            const data = await response.json();
            
            if (data.success) {
                await this.fetchState();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Erreur start:', error);
            return false;
        }
    }
    
    async leave() {
        try {
            const response = await fetch(`api/game/leave.php?game_id=${this.gameId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ player_id: this.userId })
            });
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Erreur leave:', error);
            return false;
        }
    }
    
    // ============================================
    // MÉTHODES UTILITAIRES
    // ============================================
    
    getMyPlayer() {
        if (!this.state || !this.state.players) return null;
        return this.state.players.find(p => p.user_id === this.userId);
    }
    
    getMyHand() {
        const me = this.getMyPlayer();
        if (!me) return [];
        return Array.isArray(me.hand) ? me.hand : [];
    }
    
    getMyMelds() {
        const me = this.getMyPlayer();
        if (!me) return [];
        return Array.isArray(me.melds) ? me.melds : [];
    }
    
    getMyScore() {
        const hand = this.getMyHand();
        let score = 0;
        hand.forEach(card => {
            if (!card.is_joker) score += card.points || 0;
        });
        return score;
    }
    
    getOtherPlayers() {
        if (!this.state || !this.state.players) return [];
        return this.state.players.filter(p => p.user_id !== this.userId);
    }
    
    isMyTurn() {
        return this.state ? this.state.is_my_turn : false;
    }
    
    getGameStatus() {
        return this.state ? this.state.game?.status : 'waiting';
    }
    
    getCurrentTurn() {
        return this.state ? this.state.current_turn : 0;
    }
    
    getPlayers() {
        return this.state ? this.state.players : [];
    }
    
    getWinner() {
        if (!this.state || !this.state.players) return null;
        return this.state.players.find(p => p.is_winner);
    }
    
    isGameFinished() {
        const status = this.getGameStatus();
        return status === 'finished' || status === 'closed';
    }
    
    isGamePlaying() {
        return this.getGameStatus() === 'playing';
    }
    
    isGameWaiting() {
        return this.getGameStatus() === 'waiting';
    }
    
    // ============================================
    // AUTO-PLAY (TIMEOUT)
    // ============================================
    
    async autoPlay() {
        console.log('🔄 Auto-play déclenché');
        
        // Pioche automatique
        await this.draw();
        
        // Défausse la première carte
        const hand = this.getMyHand();
        if (hand && hand.length > 0) {
            await this.discard(0);
        }
        
        // Fin du tour
        await this.endTurn();
    }
}