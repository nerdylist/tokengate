-- Migration: Add status_id foreign key to profiles table
-- This migration adds support for profile_statuses lookup table

-- Add status_id column
ALTER TABLE profiles ADD COLUMN status_id INTEGER;

-- Set default value to 1 (Available) for all existing profiles with available=1
UPDATE profiles SET status_id = 1 WHERE available = 1;

-- Set status_id to 2 (Busy) for all existing profiles with available=0
UPDATE profiles SET status_id = 2 WHERE available = 0;

-- Create index on status_id
CREATE INDEX IF NOT EXISTS idx_profiles_status ON profiles(status_id);

-- Note: Foreign key constraints can't be added to existing tables in SQLite
-- The constraint will be enforced at the application level
-- New table creation with the constraint is in schema.sql
