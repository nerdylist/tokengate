-- Add color column to guilds table
-- Allows guilds to have a theme color for visual identification

PRAGMA foreign_keys = ON;

-- Add color column with default value #FFCC00
ALTER TABLE guilds ADD COLUMN color VARCHAR(7) DEFAULT '#FFCC00';
