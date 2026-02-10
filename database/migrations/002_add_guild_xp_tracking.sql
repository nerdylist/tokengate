-- Guild XP Tracking Migration
-- Adds XP tracking and primary guild designation to profile_skills table

PRAGMA foreign_keys = ON;

-- Add xp column to track experience points for each skill
ALTER TABLE profile_skills ADD COLUMN xp INTEGER DEFAULT 0;

-- Add is_primary_guild column to designate which category is the primary guild
ALTER TABLE profile_skills ADD COLUMN is_primary_guild INTEGER DEFAULT 0;

-- Create index on is_primary_guild for query performance
CREATE INDEX IF NOT EXISTS idx_profile_skills_primary_guild ON profile_skills(is_primary_guild);

-- Create index on xp for ranking queries
CREATE INDEX IF NOT EXISTS idx_profile_skills_xp ON profile_skills(xp DESC);
