-- Migration: Merge pending_skills into skills table
-- Add new columns to skills table

-- Add submitted_by_profile_id column (nullable, foreign key to profiles)
ALTER TABLE skills ADD COLUMN submitted_by_profile_id INTEGER DEFAULT NULL;

-- Add status column (default 'approved', can be 'pending', 'approved', 'rejected')
ALTER TABLE skills ADD COLUMN status VARCHAR(50) DEFAULT 'approved';

-- Add reviewed_by_admin_id column (nullable, foreign key to users)
ALTER TABLE skills ADD COLUMN reviewed_by_admin_id INTEGER DEFAULT NULL;

-- Add reviewed_at column (nullable datetime)
ALTER TABLE skills ADD COLUMN reviewed_at DATETIME DEFAULT NULL;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_skills_status ON skills(status);
CREATE INDEX IF NOT EXISTS idx_skills_submitted_by ON skills(submitted_by_profile_id);
CREATE INDEX IF NOT EXISTS idx_skills_reviewed_by ON skills(reviewed_by_admin_id);

-- Add foreign key constraints (SQLite note: these are just documentation, enforcement depends on PRAGMA foreign_keys)
-- FOREIGN KEY (submitted_by_profile_id) REFERENCES profiles(id) ON DELETE SET NULL
-- FOREIGN KEY (reviewed_by_admin_id) REFERENCES users(id) ON DELETE SET NULL
