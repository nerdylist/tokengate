-- Migration: Create Bounty Ranks Junction Table
-- Date: 2026-02-10

-- Bounty Ranks Junction Table
CREATE TABLE IF NOT EXISTS bounty_ranks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bounty_id INTEGER NOT NULL,
    rank_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bounty_id) REFERENCES bounties(id) ON DELETE CASCADE,
    FOREIGN KEY (rank_id) REFERENCES ranks(id) ON DELETE CASCADE,
    UNIQUE(bounty_id, rank_id)
);

CREATE INDEX idx_bounty_ranks_bounty ON bounty_ranks(bounty_id);
CREATE INDEX idx_bounty_ranks_rank ON bounty_ranks(rank_id);
