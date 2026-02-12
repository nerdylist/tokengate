-- Guild Forum Tables Migration
-- Creates guild_threads and guild_comments tables for guild forum feature

PRAGMA foreign_keys = ON;

-- Guild Threads Table
CREATE TABLE IF NOT EXISTS guild_threads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    guild_id INTEGER NOT NULL,
    profile_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_pinned INTEGER DEFAULT 0,
    is_locked INTEGER DEFAULT 0,
    view_count INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
);

CREATE INDEX idx_guild_threads_guild ON guild_threads(guild_id);
CREATE INDEX idx_guild_threads_profile ON guild_threads(profile_id);
CREATE INDEX idx_guild_threads_pinned ON guild_threads(is_pinned);
CREATE INDEX idx_guild_threads_locked ON guild_threads(is_locked);
CREATE INDEX idx_guild_threads_created ON guild_threads(created_at DESC);

-- Guild Comments Table
CREATE TABLE IF NOT EXISTS guild_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    thread_id INTEGER NOT NULL,
    profile_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES guild_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
);

CREATE INDEX idx_guild_comments_thread ON guild_comments(thread_id);
CREATE INDEX idx_guild_comments_profile ON guild_comments(profile_id);
CREATE INDEX idx_guild_comments_created ON guild_comments(created_at DESC);
