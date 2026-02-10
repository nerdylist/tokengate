# Scripts

This directory contains utility scripts for managing the application.

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
