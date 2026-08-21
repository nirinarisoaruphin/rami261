-- ============================================
-- BASE DE DONNÉES RAMI 261
-- ============================================

CREATE DATABASE IF NOT EXISTS rami261;
USE rami261;

-- ============================================
-- TABLE: users (Joueurs)
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default.png',
    balance DECIMAL(10,2) DEFAULT 10.00,
    total_wins INT DEFAULT 0,
    total_games INT DEFAULT 0,
    is_online BOOLEAN DEFAULT FALSE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- ============================================
-- TABLE: games (Parties)
-- ============================================
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_code VARCHAR(6) UNIQUE NOT NULL,
    host_id INT NOT NULL,
    status ENUM('waiting', 'playing', 'finished', 'closed') DEFAULT 'waiting',
    max_players INT DEFAULT 5,
    min_players INT DEFAULT 2,
    current_turn INT NULL,
    turn_number INT DEFAULT 0,
    bet_amount DECIMAL(10,2) DEFAULT 1.00,
    pot_amount DECIMAL(10,2) DEFAULT 0.00,
    commission DECIMAL(10,2) DEFAULT 0.00,
    winner_id INT NULL,
    win_type VARCHAR(50) NULL,
    win_bonus DECIMAL(10,2) DEFAULT 0.00,
    started_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_room_code (room_code)
);

-- ============================================
-- TABLE: game_players (Joueurs dans une partie)
-- ============================================
CREATE TABLE game_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    position INT NOT NULL,
    hand TEXT,
    melds TEXT,
    is_ready BOOLEAN DEFAULT FALSE,
    is_winner BOOLEAN DEFAULT FALSE,
    is_connected BOOLEAN DEFAULT TRUE,
    has_drawn BOOLEAN DEFAULT FALSE,
    last_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_game_user (game_id, user_id),
    INDEX idx_game (game_id),
    INDEX idx_user (user_id)
);

-- ============================================
-- TABLE: moves (Historique des coups)
-- ============================================
CREATE TABLE moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    player_id INT NOT NULL,
    action ENUM('draw', 'play_meld', 'discard', 'end_turn', 'win') NOT NULL,
    card_data TEXT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES game_players(id) ON DELETE CASCADE,
    INDEX idx_game (game_id),
    INDEX idx_player (player_id)
);

-- ============================================
-- TABLE: game_history (Historique complet)
-- ============================================
CREATE TABLE game_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    winner_id INT NOT NULL,
    loser_ids TEXT,
    win_type VARCHAR(50),
    bet_amount DECIMAL(10,2),
    bonus_amount DECIMAL(10,2),
    commission_amount DECIMAL(10,2),
    total_pot DECIMAL(10,2),
    net_win DECIMAL(10,2),
    players_data TEXT,
    finished_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_winner (winner_id),
    INDEX idx_date (finished_at)
);

-- ============================================
-- TABLE: transactions (Transactions)
-- ============================================
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NULL,
    type ENUM('deposit', 'withdraw', 'bet', 'win', 'commission') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    balance_before DECIMAL(10,2),
    balance_after DECIMAL(10,2),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_type (type)
);

-- ============================================
-- TABLE: reconnection_tokens (Reconnexion)
-- ============================================
CREATE TABLE reconnection_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_game (user_id, game_id)
);

-- ============================================
-- TABLE: system_config (Configuration)
-- ============================================
CREATE TABLE system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(50) UNIQUE NOT NULL,
    config_value TEXT NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- DONNÉES INITIALES
-- ============================================
INSERT INTO system_config (config_key, config_value, description) VALUES
('turn_timeout', '30', 'Temps maximum pour un tour (secondes)'),
('reconnect_timeout', '60', 'Délai de reconnexion (secondes)'),
('commission_rate', '0.05', 'Taux de commission (5%)'),
('min_players', '2', 'Nombre minimum de joueurs'),
('max_players', '5', 'Nombre maximum de joueurs'),
('cards_per_player', '13', 'Nombre de cartes distribuées'),
('deck_size', '108', 'Taille du jeu (2x52 + 4 jokers)'),
('tri_joker_bonus', '50', 'Bonus pour 3 jokers'),
('quadri_joker_bonus', '100', 'Bonus pour 4 jokers');