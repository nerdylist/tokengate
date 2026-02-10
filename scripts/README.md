# Scripts

This directory contains utility scripts for managing the application.

## setup-database.php

Initializes a fresh database for production deployment.

### Usage

```bash
php scripts/setup-database.php
```

### What it does

1. Creates the database file if it doesn't exist
2. Runs `database/schema.sql` to create all tables with proper indexes and foreign keys
3. Seeds default profile statuses:
   - Available (green)
   - Busy (yellow)
   - Unavailable (red)
   - Away (gray)
4. Seeds basic categories and skills:
   - Development (PHP, JavaScript, Python, SQL, React)
   - Design (UI/UX Design, Graphic Design, Web Design, Figma)
   - Writing (Content Writing, Copywriting, Technical Writing)
   - Marketing (SEO, Social Media, Email Marketing, PPC)
   - Business (Project Management, Consulting, Business Analysis)
5. Creates admin user from `.env` credentials (if doesn't exist)
6. Creates a profile for the admin user with a unique profile ID
7. All operations are idempotent (safe to run multiple times)

### When to use

- **Initial production deployment** - First time setting up the database
- **Fresh database setup** - Starting with a clean database
- **After database deletion/corruption** - Rebuilding from scratch
- **Setting up development environment** - Quick setup for new developers

### Requirements

- PHP CLI access
- Valid `.env` file with `ADMIN_EMAIL` and `ADMIN_PASSWORD` defined
- Writable database directory (`/database/`)
- Database schema file at `/database/schema.sql`

### Example

```bash
# 1. Configure your environment
nano .env

# 2. Run the setup script
php scripts/setup-database.php

# 3. Verify the database was created
ls -lh database/redot.db
```

### Output

The script provides clear progress messages for each step:

```
===========================================
  Database Setup Script
===========================================

Database connection successful.

Step 1: Creating database schema...
  ✓ Database schema created successfully.

Step 2: Seeding profile statuses...
  ✓ Profile statuses seeded successfully.

Step 3: Seeding categories and skills...
  ✓ Categories and skills seeded successfully.

Step 4: Creating admin user...
  ✓ Admin user created successfully:
    Email: admin@example.com
    Name: admin
    Admin: Yes

Step 5: Creating admin profile...
  ✓ Profile created successfully:
    Profile ID: user-1-a1b2c3d4
    Status: Available

===========================================
  Database setup complete!
===========================================
```

### Security Notes

- The script can only be run from the command line (CLI), not through a web browser
- Passwords are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT` algorithm
- The script validates email format and password length before processing
- Checks if admin user already exists before creating to prevent duplicates
- All database operations use prepared statements to prevent SQL injection
- The script is idempotent - running it multiple times won't create duplicate data

---

## reset-admin-password.php

Resets the admin user password from the `.env` file configuration.

### Usage

```bash
php scripts/reset-admin-password.php
```

### What it does

1. Reads `ADMIN_EMAIL` and `ADMIN_PASSWORD` from the `.env` file
2. Validates the email format and password strength (minimum 8 characters)
3. Checks if a user with that email exists in the database
4. If the user exists:
   - Updates their password with a secure hash
   - Ensures they have admin privileges
5. If the user doesn't exist:
   - Prompts to create a new admin user
   - Creates the user if confirmed

### Requirements

- PHP CLI access
- Valid `.env` file with `ADMIN_EMAIL` and `ADMIN_PASSWORD` defined
- Writable database at `/database/rentpeople.db` or `/database/redot.db`

### Example

```bash
# Update .env file with new password
nano .env

# Run the script
php scripts/reset-admin-password.php
```

### Production Usage

This script is particularly useful in production environments where you need to:
- Reset a forgotten admin password
- Change credentials after a security incident
- Set up a new admin user on a fresh deployment

Simply update the `.env` file with the desired credentials and run the script from the command line.

### Security Notes

- The script can only be run from the command line (CLI), not through a web browser
- Passwords are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT` algorithm
- The script validates email format and password length before processing
- All database operations use prepared statements to prevent SQL injection
