-- database/update_transactions.sql
-- Mettre à jour la structure de la table transactions

USE rami261;

-- Vérifier et ajouter les colonnes manquantes
ALTER TABLE transactions 
    ADD COLUMN IF NOT EXISTS reference VARCHAR(50) UNIQUE,
    ADD COLUMN IF NOT EXISTS method VARCHAR(30) DEFAULT 'manual',
    ADD COLUMN IF NOT EXISTS notes TEXT;

-- Modifier le type de la colonne amount pour accepter les nombres
ALTER TABLE transactions MODIFY COLUMN amount DECIMAL(15,2) NOT NULL;
ALTER TABLE transactions MODIFY COLUMN balance_before DECIMAL(15,2) NOT NULL;
ALTER TABLE transactions MODIFY COLUMN balance_after DECIMAL(15,2) NOT NULL;

-- Ajouter des index pour les recherches
ALTER TABLE transactions ADD INDEX IF NOT EXISTS idx_user_date (user_id, created_at);
ALTER TABLE transactions ADD INDEX IF NOT EXISTS idx_reference (reference);