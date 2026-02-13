-- Create bounty_votes table to track user votes on bounties
-- Ensures users can only vote once per bounty and supports upvote/downvote system

PRAGMA foreign_keys = ON;

-- Bounty Votes Table
CREATE TABLE IF NOT EXISTS bounty_votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bounty_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    vote_type INTEGER NOT NULL CHECK(vote_type IN (1, -1)),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bounty_id) REFERENCES bounties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(bounty_id, user_id)
);

CREATE INDEX idx_bounty_votes_bounty ON bounty_votes(bounty_id);
CREATE INDEX idx_bounty_votes_user ON bounty_votes(user_id);
CREATE INDEX idx_bounty_votes_type ON bounty_votes(vote_type);
