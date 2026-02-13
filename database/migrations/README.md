# Production Database Migration Guide

## Overview

The `production_migration.php` script handles the complete database migration for production deployment, including:

1. **Database Consolidation** - Merges legacy databases into g8.db (specified in .env as DB_NAME)
2. **Schema Updates** - Adds columns for guild/skill hierarchy system
3. **Admin User Seeding** - Creates admin user from `.env` credentials

## Prerequisites

Before running the migration, ensure your `.env` file contains:

```env
DB_NAME=g8.db
ADMIN_EMAIL=paul@nerd.biz
ADMIN_PASSWORD=YourSecurePassword
```

## Running the Migration

From the project root directory:

```bash
php database/migrations/production_migration.php
```

## What It Does

### Step 1: Backup
- Creates timestamped backups of all existing databases in `database/backups/`
- Backups are named: `{database}_backup_YYYY-MM-DD_HH-MM-SS.db`

### Step 2: Create New Database
- Creates fresh `g8.db` with base schema from `database/schema.sql`

### Step 3: Migrate Data
- Automatically detects which legacy source database exists (if any old databases are present)
- Migrates all data from source to g8.db (specified by DB_NAME in .env)
- Uses `INSERT OR IGNORE` to prevent duplicate entries
- Note: Legacy databases (redot.db, rentpeople.db) are no longer used

### Step 4: Update Schema
Adds the following columns and tables:

**Updated Tables:**
- `users.profile_id` - VARCHAR(50)
- `skills.description` - TEXT
- `skills.parent_skill_id` - INTEGER (FK to skills.id)
- `skills.is_addable` - INTEGER (default 1)
- `profile_skills.xp` - INTEGER (default 0)

**New Tables:**
- `guild_skills` - Junction table mapping skills to guilds
- `guild_ranks` - Custom rank thresholds per guild

### Step 5: Seed Admin User
- Creates admin user from `.env` (`ADMIN_EMAIL` and `ADMIN_PASSWORD`)
- Auto-generates profile with ID pattern: `NERD-001`, `NERD-002`, etc.
- If admin already exists, ensures they have admin privileges
- Handles missing `profile_statuses` data gracefully

## Safety Features

✅ **Idempotent** - Safe to run multiple times
✅ **Automatic Backups** - Original databases are backed up before any changes
✅ **Smart Column Addition** - Checks if columns exist before adding
✅ **Foreign Key Handling** - Gracefully handles missing reference data
✅ **Error Reporting** - Clear error messages with backup location

## After Migration

1. Update production `.env`:
   ```env
   DB_NAME=g8.db
   ```

2. Verify admin login:
   - Email: Value from `ADMIN_EMAIL`
   - Password: Value from `ADMIN_PASSWORD`
   - Profile ID: `NERD-001` (or next sequential)

3. Old databases can be safely archived or deleted after verifying migration success

## Rollback

If anything goes wrong:

1. Backups are stored in `database/backups/`
2. Restore from backup:
   ```bash
   # Replace YYYY-MM-DD_HH-MM-SS with your backup timestamp
   cp database/backups/{database}_backup_YYYY-MM-DD_HH-MM-SS.db database/g8.db
   ```

3. Verify `.env` points to g8.db

## Troubleshooting

**"ADMIN_EMAIL or ADMIN_PASSWORD not set"**
- Ensure `.env` file exists in project root
- Verify `ADMIN_EMAIL` and `ADMIN_PASSWORD` are set

**"No source database found"**
- This is normal for fresh installations
- Migration will proceed with empty database and seed admin user

**"Foreign key constraint failed"**
- Script automatically handles this by temporarily disabling foreign keys
- If you see this error, it should self-resolve in the output

## Production Deployment Checklist

- [ ] Backup current production database manually (extra safety)
- [ ] Update production `.env` with correct credentials
- [ ] Run migration: `php database/migrations/production_migration.php`
- [ ] Verify admin can log in
- [ ] Test key functionality (user profiles, bounties, guilds)
- [ ] Archive old database files
- [ ] Update `DB_NAME=g8.db` in production `.env`

## Schema Changes Summary

This migration prepares the database for the **Guild System - Skill-Based Progression** feature documented in `/docs/JOIN-A-GUILD.md`.

Key additions:
- Hierarchical skills (parent/child relationships)
- Guild-skill associations
- XP tracking per skill
- Custom guild rank thresholds

## Numbered Migration Files

The following migrations are available for incremental database updates:

**001_add_profile_statuses_table.php**
- Creates `profile_statuses` table with name, slug, color, sort_order, is_active
- Adds indexes for performance

**002_add_guild_xp_tracking.sql**
- Adds `xp` column to `profile_skills` table
- Adds `is_primary_guild` column to `profile_skills` table
- Creates indexes for query performance

**003_create_guild_forum_tables.sql**
- Creates `guild_threads` table for forum threads
- Creates `guild_comments` table for forum comments
- Adds foreign keys and indexes

**004_create_bounty_ranks.sql**
- Creates `bounty_ranks` junction table
- Links bounties to ranks with many-to-many relationship

**005_add_pending_skills.sql**
- Creates `pending_skills` table for skill request workflow
- Supports admin review and approval process

**006_merge_pending_skills_into_skills.sql**
- Adds `submitted_by_profile_id`, `status`, `reviewed_by_admin_id`, `reviewed_at` to `skills` table
- Merges pending skill functionality into main skills table

**007_migrate_pending_skills_data.sql**
- Migrates data from `pending_skills` table to `skills` table
- Handles pending and rejected skills

**008_add_icon_to_profile_statuses.sql**
- Adds `icon` column to `profile_statuses` table for emoji/icon support

**009_add_bounty_hire_form_fields.sql**
- Adds `payment_type`, `estimated_hours`, `spots`, `location`, `remote_ok` to `bounties` table

**010_add_color_to_guilds.sql**
- Adds `color` column to `guilds` table with default value #FFCC00

**011_add_votes_to_guild_comments.sql**
- Adds `votes` column to `guild_comments` table
- Creates index for sorting by votes

**012_create_guild_comment_votes.sql**
- Creates `guild_comment_votes` table to track individual user votes
- Ensures users can only vote once per comment
- Supports upvote/downvote toggling

## Additional Migration Files

**replace_ranks_with_proper_ones.php**
- Data migration to replace ranks with unified rank system
- Parses ranks from `/data/ranks.md`
- Creates universal rank progression with XP thresholds
- Run manually when needed: `php database/migrations/replace_ranks_with_proper_ones.php`

## Running Migrations

To run individual migrations in order:

```bash
# PHP migrations
php database/migrations/001_add_profile_statuses_table.php

# SQL migrations (using sqlite3)
sqlite3 database/g8.db < database/migrations/002_add_guild_xp_tracking.sql
```

Note: Migrations 002-012 are SQL files. Migration 001 is a PHP script with idempotent checks.
