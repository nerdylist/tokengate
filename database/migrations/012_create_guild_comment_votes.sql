-- Create guild_comment_votes table to track user votes on comments
-- Ensures users can only vote once per comment and allows vote toggling

PRAGMA foreign_keys = ON;

-- Guild Comment Votes Table
CREATE TABLE IF NOT EXISTS guild_comment_votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    comment_id INTEGER NOT NULL,
    profile_id INTEGER NOT NULL,
    vote_type VARCHAR(10) NOT NULL CHECK(vote_type IN ('up', 'down')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES guild_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    UNIQUE(comment_id, profile_id)
);

CREATE INDEX idx_guild_comment_votes_comment ON guild_comment_votes(comment_id);
CREATE INDEX idx_guild_comment_votes_profile ON guild_comment_votes(profile_id);
CREATE INDEX idx_guild_comment_votes_type ON guild_comment_votes(vote_type);
