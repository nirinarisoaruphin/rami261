<?php
// includes/GameManager.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CardManager.php';
require_once __DIR__ . '/CombinationValidator.php';

class GameManager {
    private Database $db;
    private ?int $gameId = null;
    private ?array $gameData = null;
    
    public function __construct(?int $gameId = null) {
        $this->db = Database::getInstance();
        if ($gameId) {
            $this->gameId = $gameId;
            $this->loadGame();
        }
    }
    
    // ============================================
    // CRÉATION ET GESTION DES PARTIES
    // ============================================
    
    public function createGame(int $hostId, float $bet): string {
        $roomCode = $this->generateRoomCode();
        
        $this->gameId = $this->db->insert('games', [
            'room_code' => $roomCode,
            'host_id' => $hostId,
            'status' => 'waiting',
            'bet_amount' => $bet,
            'max_players' => MAX_PLAYERS,
            'min_players' => MIN_PLAYERS
        ]);
        
        $this->db->insert('game_players', [
            'game_id' => $this->gameId,
            'user_id' => $hostId,
            'position' => 0,
            'is_ready' => true
        ]);
        
        $this->loadGame();
        return $roomCode;
    }
    
    public function joinGame(string $roomCode, int $userId): bool {
        $game = $this->db->fetch(
            "SELECT * FROM games WHERE room_code = ? AND status = 'waiting'",
            [$roomCode]
        );
        if (!$game) return false;
        
        $playerCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM game_players WHERE game_id = ?",
            [$game['id']]
        )['count'];
        
        if ($playerCount >= $game['max_players']) return false;
        
        $this->gameId = $game['id'];
        $this->db->insert('game_players', [
            'game_id' => $this->gameId,
            'user_id' => $userId,
            'position' => $playerCount,
            'is_ready' => false
        ]);
        
        $this->loadGame();
        return true;
    }
    
    public function leaveGame(int $userId): bool {
        if (!$this->gameId) return false;
        
        $this->db->delete('game_players', 'game_id = ? AND user_id = ?', [$this->gameId, $userId]);
        
        $playerCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM game_players WHERE game_id = ?",
            [$this->gameId]
        )['count'];
        
        if ($playerCount == 0) {
            $this->db->update('games', ['status' => 'closed'], 'id = ?', [$this->gameId]);
        }
        
        $this->loadGame();
        return true;
    }
    
    // ============================================
    // DÉMARRAGE DE LA PARTIE
    // ============================================
    
    public function startGame(): bool {
        if ($this->gameData['status'] !== 'waiting') return false;
        
        $playerCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM game_players WHERE game_id = ?",
            [$this->gameId]
        )['count'];
        
        if ($playerCount < MIN_PLAYERS) return false;
        
        $deck = CardManager::createDeck();
        $deck = CardManager::shuffleDeck($deck);
        $hands = CardManager::dealCards($deck, $playerCount);
        
        $players = $this->db->fetchAll(
            "SELECT id, user_id FROM game_players WHERE game_id = ? ORDER BY position",
            [$this->gameId]
        );
        
        foreach ($players as $index => $player) {
            $handJson = json_encode($hands[$index]);
            $this->db->update(
                'game_players',
                ['hand' => $handJson, 'is_ready' => true],
                'id = ?',
                [$player['id']]
            );
            
            $jokerResult = CardManager::checkJokerWin($hands[$index]);
            if ($jokerResult) {
                $this->declareWinner($player['user_id'], $jokerResult);
                return true;
            }
        }
        
        $this->db->update('games', [
            'status' => 'playing',
            'started_at' => date('Y-m-d H:i:s'),
            'current_turn' => 0,
            'turn_number' => 1
        ], 'id = ?', [$this->gameId]);
        
        $this->loadGame();
        return true;
    }
    
    // ============================================
    // ACTIONS DE JEU
    // ============================================
    
    public function drawCard(int $userId): ?array {
        $player = $this->getPlayerByUserId($userId);
        if (!$player) return null;
        
        if ($this->gameData['current_turn'] != $player['position']) return null;
        if ($player['has_drawn']) return null;
        
        $hand = json_decode($player['hand'], true);
        
        $card = [
            'id' => rand(1000, 9999),
            'value' => ['A','2','3','4','5','6','7','8','9','10','J','Q','K'][rand(0, 12)],
            'suit' => ['hearts','diamonds','clubs','spades'][rand(0, 3)],
            'points' => rand(1, 10),
            'is_joker' => rand(1, 20) === 1
        ];
        
        $hand[] = $card;
        
        $this->db->update('game_players', [
            'hand' => json_encode($hand),
            'has_drawn' => true,
            'last_action' => date('Y-m-d H:i:s')
        ], 'id = ?', [$player['id']]);
        
        $this->db->insert('moves', [
            'game_id' => $this->gameId,
            'player_id' => $player['id'],
            'action' => 'draw',
            'card_data' => json_encode($card)
        ]);
        
        $this->loadGame();
        return $card;
    }
    
    public function playMeld(int $userId, array $cardIndices): bool {
        $player = $this->getPlayerByUserId($userId);
        if (!$player) return false;
        
        if ($this->gameData['current_turn'] != $player['position']) return false;
        if (!$player['has_drawn']) return false;
        
        $hand = json_decode($player['hand'], true);
        $selectedCards = [];
        
        foreach ($cardIndices as $index) {
            if (isset($hand[$index])) {
                $selectedCards[] = $hand[$index];
            }
        }
        
        if (count($selectedCards) < 3) return false;
        
        if (!CombinationValidator::validate($selectedCards)) return false;
        
        $remainingHand = array_values(array_diff_key($hand, array_flip($cardIndices)));
        $melds = json_decode($player['melds'] ?? '[]', true);
        $melds[] = $selectedCards;
        
        $this->db->update('game_players', [
            'hand' => json_encode($remainingHand),
            'melds' => json_encode($melds)
        ], 'id = ?', [$player['id']]);
        
        $this->db->insert('moves', [
            'game_id' => $this->gameId,
            'player_id' => $player['id'],
            'action' => 'play_meld',
            'card_data' => json_encode($selectedCards)
        ]);
        
        if (empty($remainingHand)) {
            $this->declareWinner($userId, 'normal');
        }
        
        $this->loadGame();
        return true;
    }
    
    public function discardCard(int $userId, int $cardIndex): bool {
        $player = $this->getPlayerByUserId($userId);
        if (!$player) return false;
        
        if ($this->gameData['current_turn'] != $player['position']) return false;
        if (!$player['has_drawn']) return false;
        
        $hand = json_decode($player['hand'], true);
        if (!isset($hand[$cardIndex])) return false;
        
        $discardedCard = $hand[$cardIndex];
        array_splice($hand, $cardIndex, 1);
        
        $this->db->update('game_players', [
            'hand' => json_encode($hand),
            'has_drawn' => false
        ], 'id = ?', [$player['id']]);
        
        $this->db->insert('moves', [
            'game_id' => $this->gameId,
            'player_id' => $player['id'],
            'action' => 'discard',
            'card_data' => json_encode($discardedCard)
        ]);
        
        $this->nextTurn();
        return true;
    }
    
    public function endTurn(int $userId): bool {
        $player = $this->getPlayerByUserId($userId);
        if (!$player) return false;
        
        if ($this->gameData['current_turn'] != $player['position']) return false;
        
        $this->db->update('game_players', [
            'has_drawn' => false
        ], 'id = ?', [$player['id']]);
        
        $this->db->insert('moves', [
            'game_id' => $this->gameId,
            'player_id' => $player['id'],
            'action' => 'end_turn'
        ]);
        
        $this->nextTurn();
        return true;
    }
    
    // ============================================
    // MÉTHODES INTERNES
    // ============================================
    
    private function nextTurn(): void {
        $players = $this->db->fetchAll(
            "SELECT * FROM game_players WHERE game_id = ? ORDER BY position",
            [$this->gameId]
        );
        
        if (empty($players)) return;
        
        $currentPos = $this->gameData['current_turn'];
        $nextPos = ($currentPos + 1) % count($players);
        
        foreach ($players as $player) {
            $this->db->update('game_players', [
                'has_drawn' => false
            ], 'id = ?', [$player['id']]);
        }
        
        $this->db->update('games', [
            'current_turn' => $nextPos,
            'turn_number' => $this->gameData['turn_number'] + 1
        ], 'id = ?', [$this->gameId]);
        
        $this->loadGame();
    }
    
    private function declareWinner(int $userId, string $winType): void {
        $player = $this->db->fetch(
            "SELECT * FROM game_players WHERE game_id = ? AND user_id = ?",
            [$this->gameId, $userId]
        );
        
        $bonus = 0;
        if ($winType === 'tri_joker') $bonus = TRI_JOKER_BONUS;
        if ($winType === 'quadri_joker') $bonus = QUADRI_JOKER_BONUS;
        
        $this->db->update('games', [
            'status' => 'finished',
            'winner_id' => $userId,
            'win_type' => $winType,
            'win_bonus' => $bonus,
            'finished_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$this->gameId]);
        
        $this->db->update('game_players', [
            'is_winner' => true
        ], 'id = ?', [$player['id']]);
        
        $this->db->insert('moves', [
            'game_id' => $this->gameId,
            'player_id' => $player['id'],
            'action' => 'win',
            'details' => $winType
        ]);
        
        $this->calculateWinnings($userId, $winType, $bonus);
        $this->loadGame();
    }
    
    private function calculateWinnings(int $winnerId, string $winType, float $bonus): void {
        $game = $this->gameData;
        $players = $this->db->fetchAll(
            "SELECT user_id FROM game_players WHERE game_id = ?",
            [$this->gameId]
        );
        
        $numPlayers = count($players);
        $pot = $game['bet_amount'] * $numPlayers;
        $commission = $pot * COMMISSION_RATE;
        $netPot = $pot - $commission;
        $totalWin = $netPot + $bonus;
        
        $this->db->update('games', [
            'pot_amount' => $pot,
            'commission' => $commission
        ], 'id = ?', [$this->gameId]);
        
        $this->db->insert('game_history', [
            'game_id' => $this->gameId,
            'winner_id' => $winnerId,
            'loser_ids' => json_encode(array_filter(array_column($players, 'user_id'), fn($id) => $id != $winnerId)),
            'win_type' => $winType,
            'bet_amount' => $game['bet_amount'],
            'bonus_amount' => $bonus,
            'commission_amount' => $commission,
            'total_pot' => $pot,
            'net_win' => $totalWin,
            'players_data' => json_encode($players)
        ]);
        
        $this->db->update('users', [
            'balance' => new \PDO\Expression('balance + ' . $totalWin),
            'total_wins' => new \PDO\Expression('total_wins + 1'),
            'total_games' => new \PDO\Expression('total_games + 1')
        ], 'id = ?', [$winnerId]);
        
        $this->db->insert('transactions', [
            'user_id' => $winnerId,
            'game_id' => $this->gameId,
            'type' => 'win',
            'amount' => $totalWin,
            'description' => "Victoire au Rami 261 - $winType"
        ]);
    }
    
    private function generateRoomCode(): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[rand(0, strlen($chars) - 1)];
            }
        } while ($this->db->fetch("SELECT id FROM games WHERE room_code = ?", [$code]));
        return $code;
    }
    
    private function loadGame(): void {
        if ($this->gameId) {
            $this->gameData = $this->db->fetch(
                "SELECT * FROM games WHERE id = ?",
                [$this->gameId]
            );
        }
    }
    
    private function getPlayerByUserId(int $userId): ?array {
        return $this->db->fetch(
            "SELECT * FROM game_players WHERE game_id = ? AND user_id = ?",
            [$this->gameId, $userId]
        );
    }
    
    // ============================================
    // MÉTHODES PUBLIQUES
    // ============================================
    
    public function getGameState(int $userId): array {
        $players = $this->db->fetchAll(
            "SELECT gp.*, u.username, u.avatar 
             FROM game_players gp 
             JOIN users u ON gp.user_id = u.id 
             WHERE gp.game_id = ? 
             ORDER BY gp.position",
            [$this->gameId]
        );
        
        foreach ($players as &$p) {
            if ($p['user_id'] != $userId) {
                $hand = json_decode($p['hand'] ?? '[]', true);
                $p['hand'] = array_fill(0, count($hand), ['hidden' => true]);
            } else {
                $p['hand'] = json_decode($p['hand'] ?? '[]', true);
            }
            $p['melds'] = json_decode($p['melds'] ?? '[]', true);
        }
        
        return [
            'game' => $this->gameData,
            'players' => $players,
            'current_turn' => $this->gameData['current_turn'] ?? 0,
            'is_my_turn' => isset($this->gameData['current_turn']) && 
                           $this->gameData['current_turn'] == array_search($userId, array_column($players, 'user_id'))
        ];
    }
    
    public function getGameId(): ?int {
        return $this->gameId;
    }
    
    public function getGameData(): ?array {
        return $this->gameData;
    }
}