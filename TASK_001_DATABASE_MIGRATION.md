# Task 001: Create Database Migration for Bounty Fields

## Objective
Create a new database migration file to add missing columns to the bounties table.

## File to Create
`/Volumes/Crucial/SITES/redot/database/migrations/007_add_bounty_fields.sql`

## Requirements

Add the following columns to the `bounties` table using ALTER TABLE statements:

1. **payment_type** VARCHAR(50)
   - Will store either 'fixed' or 'hourly'
   - No default value

2. **estimated_hours** INTEGER
   - Will store estimated hours for the task
   - No default value (can be NULL)

3. **spots** INTEGER DEFAULT 1
   - Number of available positions for the bounty
   - Must have DEFAULT 1

4. **location** VARCHAR(255)
   - Location information for the bounty
   - No default value (can be NULL)

5. **remote_ok** INTEGER DEFAULT 1
   - Boolean flag (0 or 1) indicating if remote work is accepted
   - Must have DEFAULT 1

## SQL Format
Use ALTER TABLE ADD COLUMN statements for SQLite compatibility.

## Success Criteria
- File created at correct path
- All five columns added with correct data types
- Default values only on spots and remote_ok
- SQL is valid for SQLite

## Notes
- This follows the existing migration file naming convention (sequential numbering)
- The migration should be applied after the existing migrations
