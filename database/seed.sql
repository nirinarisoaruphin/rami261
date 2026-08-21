-- database/seed.sql
-- Données de test pour Rami 261

USE rami261;

-- ============================================
-- UTILISATEURS DE TEST
-- ============================================

INSERT INTO users (username, email, password, balance, total_wins, total_games, is_online, created_at) VALUES
('admin', 'admin@rami261.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 100.00, 10, 25, 0, NOW()),
('joueur1', 'joueur1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 50.00, 5, 12, 0, NOW()),
('joueur2', 'joueur2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 25.00, 2, 8, 0, NOW()),
('test', 'test@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 10.00, 0, 0, 0, NOW());

-- Les mots de passe sont 'password' pour tous

-- ============================================
-- PARTIES DE TEST
-- ============================================

INSERT INTO games (room_code, host_id, status, max_players, min_players, bet_amount, pot_amount, created_at) VALUES
('TEST01', 1, 'finished', 4, 2, 1.00, 3.00, NOW() - INTERVAL 1 DAY),
('TEST02', 1, 'finished', 4, 2, 2.00, 6.00, NOW() - INTERVAL 2 DAY);

-- ============================================
-- JOUEURS DES PARTIES DE TEST
-- ============================================

INSERT INTO game_players (game_id, user_id, position, hand, melds, is_winner, is_connected, joined_at) VALUES
-- Partie TEST01
(1, 1, 0, '[]', '[]', 1, 0, NOW() - INTERVAL 1 DAY),
(1, 2, 1, '[]', '[]', 0, 0, NOW() - INTERVAL 1 DAY),
(1, 3, 2, '[]', '[]', 0, 0, NOW() - INTERVAL 1 DAY),
-- Partie TEST02
(2, 1, 0, '[]', '[]', 1, 0, NOW() - INTERVAL 2 DAY),
(2, 2, 1, '[]', '[]', 0, 0, NOW() - INTERVAL 2 DAY);

-- ============================================
-- HISTORIQUE DE TEST
-- ============================================

INSERT INTO game_history (game_id, winner_id, loser_ids, win_type, bet_amount, bonus_amount, commission_amount, total_pot, net_win, players_data, finished_at) VALUES
(1, 1, '[2,3]', 'normal', 1.00, 0.00, 0.15, 3.00, 2.85, '{"players":[{"id":1,"username":"admin"},{"id":2,"username":"joueur1"},{"id":3,"username":"joueur2"}]}', NOW() - INTERVAL 1 DAY),
(2, 1, '[2]', 'tri_joker', 2.00, 50.00, 0.30, 6.00, 55.70, '{"players":[{"id":1,"username":"admin"},{"id":2,"username":"joueur1"}]}', NOW() - INTERVAL 2 DAY);

-- ============================================
-- TRANSACTIONS DE TEST
-- ============================================

INSERT INTO transactions (user_id, game_id, type, amount, description, created_at) VALUES
(1, 1, 'win', 2.85, 'Victoire Rami 261 - normal', NOW() - INTERVAL 1 DAY),
(1, 2, 'win', 55.70, 'Victoire Rami 261 - tri_joker', NOW() - INTERVAL 2 DAY);