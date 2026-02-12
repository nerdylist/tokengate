# Production Database Migration Guide

## Overview

The `production_migration.php` script handles the complete database migration for production deployment, including:

1. **Database Consolidation** - Merges legacy `rentpeople.db` and `redot.db` into the database specified in .env (DB_NAME)
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
- Automatically detects which legacy source database exists (`rentpeople.db` or `redot.db`)
- Migrates all data from source to new database (specified by DB_NAME in .env)
- Uses `INSERT OR IGNORE` to prevent duplicate entries

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
   cp database/backups/{database}_backup_YYYY-MM-DD_HH-MM-SS.db database/{DB_NAME}
   ```
3. Verify `.env` points to correct database

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
