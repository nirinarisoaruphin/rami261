<?php
// includes/TransactionManager.php
class TransactionManager {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Créer un dépôt
     */
    public function deposit($userId, $amount, $method = 'manual', $notes = '') {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Le montant doit être supérieur à 0'];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $reference = 'DEP-' . date('Ymd') . '-' . strtoupper(uniqid());
            
            // Récupérer le solde actuel
            $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            $balanceBefore = $user['balance'];
            $balanceAfter = $balanceBefore + $amount;
            
            // ============================================
            // CORRECTION: Ajouter toutes les colonnes
            // ============================================
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions 
                (user_id, type, amount, balance_before, balance_after, reference, method, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId, 
                'deposit', 
                $amount, 
                $balanceBefore, 
                $balanceAfter, 
                $reference, 
                $method, 
                $notes, 
                'completed'
            ]);
            
            // Mettre à jour le solde
            $stmt = $this->pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$balanceAfter, $userId]);
            
            $this->pdo->commit();
            
            // Mettre à jour la session
            $_SESSION['user_stats']['balance'] = $balanceAfter;
            
            return [
                'success' => true,
                'reference' => $reference,
                'amount' => $amount,
                'balance' => $balanceAfter,
                'message' => 'Dépôt effectué avec succès'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Effectuer un retrait
     */
    public function withdraw($userId, $amount, $method = 'manual', $notes = '') {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Le montant doit être supérieur à 0'];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            if ($user['balance'] < $amount) {
                throw new Exception('Solde insuffisant');
            }
            
            $reference = 'WIT-' . date('Ymd') . '-' . strtoupper(uniqid());
            $balanceBefore = $user['balance'];
            $balanceAfter = $balanceBefore - $amount;
            
            // ============================================
            // CORRECTION: Ajouter toutes les colonnes
            // ============================================
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions 
                (user_id, type, amount, balance_before, balance_after, reference, method, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId, 
                'withdraw', 
                $amount, 
                $balanceBefore, 
                $balanceAfter, 
                $reference, 
                $method, 
                $notes, 
                'pending'
            ]);
            
            $stmt = $this->pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$balanceAfter, $userId]);
            
            $this->pdo->commit();
            
            $_SESSION['user_stats']['balance'] = $balanceAfter;
            
            return [
                'success' => true,
                'reference' => $reference,
                'amount' => $amount,
                'balance' => $balanceAfter,
                'message' => 'Retrait effectué avec succès'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enregistrer une mise
     */
    public function recordBet($userId, $gameId, $amount) {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Le montant doit être supérieur à 0'];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            if ($user['balance'] < $amount) {
                throw new Exception('Solde insuffisant');
            }
            
            $reference = 'BET-' . date('Ymd') . '-' . strtoupper(uniqid());
            $balanceBefore = $user['balance'];
            $balanceAfter = $balanceBefore - $amount;
            
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions 
                (user_id, game_id, type, amount, balance_before, balance_after, reference, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $gameId, 'bet', $amount, $balanceBefore, $balanceAfter, $reference, 'completed']);
            
            $stmt = $this->pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$balanceAfter, $userId]);
            
            $this->pdo->commit();
            
            $_SESSION['user_stats']['balance'] = $balanceAfter;
            
            return ['success' => true, 'balance' => $balanceAfter];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enregistrer un gain
     */
    public function recordWin($userId, $gameId, $amount, $winType = 'normal') {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Le montant doit être supérieur à 0'];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            $reference = 'WIN-' . date('Ymd') . '-' . strtoupper(uniqid());
            $balanceBefore = $user['balance'];
            $balanceAfter = $balanceBefore + $amount;
            
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions 
                (user_id, game_id, type, amount, balance_before, balance_after, reference, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $gameId, 'win', $amount, $balanceBefore, $balanceAfter, $reference, $winType, 'completed']);
            
            $stmt = $this->pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$balanceAfter, $userId]);
            
            $this->pdo->commit();
            
            $_SESSION['user_stats']['balance'] = $balanceAfter;
            
            return ['success' => true, 'balance' => $balanceAfter];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Récupérer le solde d'un utilisateur
     */
    public function getBalance($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            return $user ? $user['balance'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Récupérer les statistiques de transactions
     */
    public function getStats($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END) as total_deposits,
                    SUM(CASE WHEN type = 'withdraw' THEN amount ELSE 0 END) as total_withdraws,
                    SUM(CASE WHEN type = 'win' THEN amount ELSE 0 END) as total_wins,
                    SUM(CASE WHEN type = 'bet' THEN amount ELSE 0 END) as total_bets,
                    COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
                    COUNT(CASE WHEN type = 'withdraw' THEN 1 END) as withdraw_count,
                    COUNT(CASE WHEN type = 'win' THEN 1 END) as win_count,
                    COUNT(CASE WHEN type = 'bet' THEN 1 END) as bet_count
                FROM transactions
                WHERE user_id = ? AND status = 'completed'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Récupérer l'historique des transactions
     */
    public function getHistory($userId, $limit = 50, $offset = 0, $type = null) {
        try {
            $sql = "
                SELECT t.*, 
                       CASE 
                           WHEN t.type = 'deposit' THEN 'Dépôt'
                           WHEN t.type = 'withdraw' THEN 'Retrait'
                           WHEN t.type = 'bet' THEN 'Mise'
                           WHEN t.type = 'win' THEN 'Gain'
                           WHEN t.type = 'commission' THEN 'Commission'
                           ELSE t.type
                       END as type_label
                FROM transactions t
                WHERE t.user_id = ?
            ";
            
            if ($type) {
                $sql .= " AND t.type = ?";
                $params = [$userId, $type];
            } else {
                $params = [$userId];
            }
            
            $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll();
            
            $countSql = "SELECT COUNT(*) as total FROM transactions WHERE user_id = ?";
            if ($type) {
                $countSql .= " AND type = ?";
                $countParams = [$userId, $type];
            } else {
                $countParams = [$userId];
            }
            $stmt = $this->pdo->prepare($countSql);
            $stmt->execute($countParams);
            $total = $stmt->fetch()['total'] ?? 0;
            
            return [
                'success' => true,
                'transactions' => $transactions,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}