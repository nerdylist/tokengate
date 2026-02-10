-- Pending Skills Migration
-- Adds pending_skills table for skill request and review workflow

PRAGMA foreign_keys = ON;

-- Pending Skills Table
CREATE TABLE IF NOT EXISTS pending_skills (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    requested_by_profile_id INTEGER NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    category_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME,
    reviewed_by_admin_id INTEGER,
    FOREIGN KEY (requested_by_profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (reviewed_by_admin_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for query performance
CREATE INDEX IF NOT EXISTS idx_pending_skills_status ON pending_skills(status);
CREATE INDEX IF NOT EXISTS idx_pending_skills_requested_by ON pending_skills(requested_by_profile_id);
CREATE INDEX IF NOT EXISTS idx_pending_skills_category ON pending_skills(category_id);
CREATE INDEX IF NOT EXISTS idx_pending_skills_slug ON pending_skills(slug);
CREATE INDEX IF NOT EXISTS idx_pending_skills_created ON pending_skills(created_at DESC);
