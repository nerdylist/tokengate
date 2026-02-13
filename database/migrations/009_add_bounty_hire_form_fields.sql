-- Bounty Hire Form Fields Migration
-- Adds missing columns to bounties table to support all fields from hire.php form

PRAGMA foreign_keys = ON;

-- Add payment_type column (fixed or hourly)
ALTER TABLE bounties ADD COLUMN payment_type VARCHAR(20) DEFAULT 'fixed';

-- Add estimated_hours column (nullable, for time estimates)
ALTER TABLE bounties ADD COLUMN estimated_hours INTEGER;

-- Add spots column (number of positions available, default 1)
ALTER TABLE bounties ADD COLUMN spots INTEGER DEFAULT 1;

-- Add location column (nullable, stores location information)
ALTER TABLE bounties ADD COLUMN location VARCHAR(255);

-- Add remote_ok column (boolean flag: 1=accepts remote, 0=does not)
ALTER TABLE bounties ADD COLUMN remote_ok INTEGER DEFAULT 1;
