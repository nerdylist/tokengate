-- Add votes column to guild_comments table
-- Migration to support upvote/downvote functionality for forum comments

PRAGMA foreign_keys = ON;

-- Add votes column to track comment scores
ALTER TABLE guild_comments ADD COLUMN votes INTEGER DEFAULT 0;

-- Create index for sorting by votes
CREATE INDEX IF NOT EXISTS idx_guild_comments_votes ON guild_comments(votes DESC);
