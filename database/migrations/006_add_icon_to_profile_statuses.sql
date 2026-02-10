-- Migration: Add icon column to profile_statuses table
-- Date: 2026-02-10
-- Description: Adds icon/emoji support to profile statuses

ALTER TABLE profile_statuses ADD COLUMN icon VARCHAR(50);
