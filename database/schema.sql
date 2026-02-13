-- TokenG8.com Database Schema
-- SQLite Database Schema

-- Enable foreign key constraints
PRAGMA foreign_keys = ON;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_admin INTEGER DEFAULT 0,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_admin ON users(is_admin);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categories_slug ON categories(slug);

-- Skills Table
CREATE TABLE IF NOT EXISTS skills (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    category_id INTEGER NOT NULL,
    status VARCHAR(50) DEFAULT 'approved',
    submitted_by_profile_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by_profile_id) REFERENCES profiles(id) ON DELETE SET NULL
);

CREATE INDEX idx_skills_slug ON skills(slug);
CREATE INDEX idx_skills_category ON skills(category_id);
CREATE INDEX idx_skills_status ON skills(status);

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

CREATE INDEX idx_pending_skills_status ON pending_skills(status);
CREATE INDEX idx_pending_skills_requested_by ON pending_skills(requested_by_profile_id);
CREATE INDEX idx_pending_skills_category ON pending_skills(category_id);
CREATE INDEX idx_pending_skills_slug ON pending_skills(slug);
CREATE INDEX idx_pending_skills_created ON pending_skills(created_at DESC);

-- Bounties Table
CREATE TABLE IF NOT EXISTS bounties (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    budget_min DECIMAL(10, 2),
    budget_max DECIMAL(10, 2),
    deadline DATE,
    status VARCHAR(50) DEFAULT 'open',
    payment_type VARCHAR(20) DEFAULT 'fixed',
    estimated_hours INTEGER,
    spots INTEGER DEFAULT 1,
    location VARCHAR(255),
    remote_ok INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

CREATE INDEX idx_bounties_user ON bounties(user_id);
CREATE INDEX idx_bounties_category ON bounties(category_id);
CREATE INDEX idx_bounties_status ON bounties(status);
CREATE INDEX idx_bounties_created ON bounties(created_at DESC);
CREATE INDEX idx_bounties_deadline ON bounties(deadline);

-- Bounty Skills Junction Table
CREATE TABLE IF NOT EXISTS bounty_skills (
    bounty_id INTEGER NOT NULL,
    skill_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (bounty_id, skill_id),
    FOREIGN KEY (bounty_id) REFERENCES bounties(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

CREATE INDEX idx_bounty_skills_skill ON bounty_skills(skill_id);

-- Profiles Table
CREATE TABLE IF NOT EXISTS profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    profile_id VARCHAR(20) NOT NULL UNIQUE,
    bio TEXT,
    hourly_rate DECIMAL(10, 2),
    available INTEGER DEFAULT 1,
    status_id INTEGER DEFAULT 1,
    avatar_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES profile_statuses(id) ON DELETE RESTRICT
);

CREATE INDEX idx_profiles_user ON profiles(user_id);
CREATE INDEX idx_profiles_profile_id ON profiles(profile_id);
CREATE INDEX idx_profiles_available ON profiles(available);
CREATE INDEX idx_profiles_status ON profiles(status_id);

-- Profile Skills Junction Table
CREATE TABLE IF NOT EXISTS profile_skills (
    profile_id INTEGER NOT NULL,
    skill_id INTEGER NOT NULL,
    proficiency_level VARCHAR(50) DEFAULT 'intermediate',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (profile_id, skill_id),
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

CREATE INDEX idx_profile_skills_skill ON profile_skills(skill_id);
CREATE INDEX idx_profile_skills_proficiency ON profile_skills(proficiency_level);

-- Applications Table
CREATE TABLE IF NOT EXISTS applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bounty_id INTEGER NOT NULL,
    profile_id INTEGER NOT NULL,
    cover_letter TEXT NOT NULL,
    proposed_rate DECIMAL(10, 2),
    status VARCHAR(50) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bounty_id) REFERENCES bounties(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    UNIQUE(bounty_id, profile_id)
);

CREATE INDEX idx_applications_bounty ON applications(bounty_id);
CREATE INDEX idx_applications_profile ON applications(profile_id);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_created ON applications(created_at DESC);

-- Votes Table
CREATE TABLE IF NOT EXISTS votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bounty_id INTEGER NOT NULL,
    profile_id INTEGER NOT NULL,
    voter_user_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bounty_id) REFERENCES bounties(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (voter_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(bounty_id, profile_id, voter_user_id)
);

CREATE INDEX idx_votes_bounty ON votes(bounty_id);
CREATE INDEX idx_votes_profile ON votes(profile_id);
CREATE INDEX idx_votes_voter ON votes(voter_user_id);

-- Sessions Table
CREATE TABLE IF NOT EXISTS sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_sessions_token ON sessions(token);
CREATE INDEX idx_sessions_user ON sessions(user_id);
CREATE INDEX idx_sessions_expires ON sessions(expires_at);

-- Guilds Table
CREATE TABLE IF NOT EXISTS guilds (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    type VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    color VARCHAR(7) DEFAULT '#FFCC00',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_guilds_slug ON guilds(slug);
CREATE INDEX idx_guilds_type ON guilds(type);

-- Ranks Table
CREATE TABLE IF NOT EXISTS ranks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    level INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL,
    xp_required INTEGER NOT NULL DEFAULT 0,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_ranks_type ON ranks(type);
CREATE INDEX idx_ranks_level ON ranks(level);
CREATE INDEX idx_ranks_type_level ON ranks(type, level);

-- Profile Guilds Junction Table
CREATE TABLE IF NOT EXISTS profile_guilds (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    profile_id INTEGER NOT NULL,
    guild_id INTEGER NOT NULL,
    rank_id INTEGER NOT NULL,
    xp INTEGER DEFAULT 0,
    is_primary INTEGER DEFAULT 0,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (rank_id) REFERENCES ranks(id) ON DELETE RESTRICT,
    UNIQUE(profile_id, guild_id)
);

CREATE INDEX idx_profile_guilds_profile ON profile_guilds(profile_id);
CREATE INDEX idx_profile_guilds_guild ON profile_guilds(guild_id);
CREATE INDEX idx_profile_guilds_rank ON profile_guilds(rank_id);
CREATE INDEX idx_profile_guilds_primary ON profile_guilds(is_primary);
CREATE INDEX idx_profile_guilds_xp ON profile_guilds(xp DESC);

-- Quests Table
CREATE TABLE IF NOT EXISTS quests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    guild_id INTEGER,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50),
    min_rank_id INTEGER,
    xp_reward INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (min_rank_id) REFERENCES ranks(id) ON DELETE SET NULL
);

CREATE INDEX idx_quests_guild ON quests(guild_id);
CREATE INDEX idx_quests_type ON quests(type);
CREATE INDEX idx_quests_active ON quests(is_active);
CREATE INDEX idx_quests_min_rank ON quests(min_rank_id);

-- Quest Bounties Junction Table
CREATE TABLE IF NOT EXISTS quest_bounties (
    quest_id INTEGER NOT NULL,
    bounty_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (quest_id, bounty_id),
    FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE,
    FOREIGN KEY (bounty_id) REFERENCES bounties(id) ON DELETE CASCADE
);

CREATE INDEX idx_quest_bounties_bounty ON quest_bounties(bounty_id);

-- Profile Statuses Table
CREATE TABLE IF NOT EXISTS profile_statuses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    color VARCHAR(7) NOT NULL,
    icon VARCHAR(50),
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_profile_statuses_slug ON profile_statuses(slug);
CREATE INDEX idx_profile_statuses_active ON profile_statuses(is_active);
CREATE INDEX idx_profile_statuses_sort ON profile_statuses(sort_order);

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
