# Environment Configuration Guide
## rentpeople.io (.env System)

---

## Overview

This project uses a flexible environment configuration system that allows you to switch between development and production modes by simply changing one line in the `.env` file. The system automatically adjusts URL generation, asset paths, and routing behavior based on the environment setting.

---

## Quick Start

### 1. Configuration Files Location

All configuration files are in the project root:

```
/Volumes/Crucial/SITES/redot/
├── .env                 # Active configuration (DO NOT commit to git)
├── .env.example         # Template for new developers
├── config.php           # Configuration loader and helper functions
└── .htaccess           # Apache rewrite rules (for production)
```

### 2. Current Configuration

The `.env` file contains:
```env
APP_NAME=rentpeople.io
APP_ENV=dev
ADMIN_EMAIL=paul@nerd.biz
ADMIN_PASSWORD=Apple123!
```

### 3. Available Environments

| Environment | Value | URL Style | Use Case |
|-------------|-------|-----------|----------|
| Development | `dev` | `browse.php`, `detail.php?id=1` | Local development, testing |
| Production | `prod` | `/browse`, `/bounty/1` | Live servers, public deployment |

---

## Switching Environments

### Switch to Development Mode
1. Open `/Volumes/Crucial/SITES/redot/.env`
2. Set: `APP_ENV=dev`
3. Save the file
4. Refresh your browser - changes take effect immediately

**Result:** URLs will use query strings (e.g., `browse.php`, `detail.php?id=1`)

### Switch to Production Mode
1. Open `/Volumes/Crucial/SITES/redot/.env`
2. Set: `APP_ENV=prod`
3. Save the file
4. **Important:** Update server configuration (see Server Setup section)

**Result:** URLs will use clean format (e.g., `/browse`, `/bounty/1`)

---

## URL Helper Functions

The `config.php` file provides helper functions that automatically generate correct URLs based on the environment:

### url($route, $params = [])
Generates route URLs that adapt to the current environment.

**Examples:**
```php
// Navigation links
<a href="<?php echo url('browse'); ?>">Browse</a>
// Dev: browse.php
// Prod: /browse

// Links with parameters
<a href="<?php echo url('bounty', ['id' => 1]); ?>">View Task</a>
// Dev: detail.php?id=1
// Prod: /bounty/1

// Other routes
url('index')     // Dev: index.php  | Prod: /bounties
url('hire')      // Dev: hire.php   | Prod: /hire
url('connect')   // Dev: connect.php | Prod: /connect
```

### asset($path)
Generates asset URLs (CSS, JS, images).

**Examples:**
```php
<link rel="stylesheet" href="<?php echo asset('styles.css'); ?>">
// Dev: styles.css
// Prod: /styles.css

<script src="<?php echo asset('app.js'); ?>"></script>
// Dev: app.js
// Prod: /app.js
```

---

## Available Constants

After including `config.php`, these constants are available:

| Constant | Type | Description |
|----------|------|-------------|
| `APP_NAME` | string | Application name from .env |
| `APP_ENV` | string | Current environment (dev/prod) |
| `IS_DEV` | boolean | `true` if in development mode |
| `IS_PROD` | boolean | `true` if in production mode |
| `ADMIN_EMAIL` | string | Admin email from .env |
| `ADMIN_PASSWORD` | string | Admin password from .env |

**Usage:**
```php
<?php
require_once 'config.php';

echo APP_NAME; // "rentpeople.io"

if (IS_DEV) {
    // Development-only code
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
?>
```

---

## Server Configuration

### Current Setup: Caddy Server (Development)

The project is currently served at `https://redot.test/` using Caddy.

**Current Caddyfile configuration:**
```caddy
redot.test {
    root * /Volumes/Crucial/SITES/redot
    encode gzip
    php_fastcgi 127.0.0.1:9000
    file_server
    log { level ERROR }
}
```

**This works perfectly for DEV mode** (query string URLs).

### For Production Mode: Update Caddy

To use production mode with clean URLs, update the Caddyfile:

```caddy
redot.test {
    root * /Volumes/Crucial/SITES/redot
    encode gzip

    # Clean URL rewrites
    @browse path /browse
    rewrite @browse /browse.php

    @bounties path /bounties
    rewrite @bounties /index.php

    @hire path /hire
    rewrite @hire /hire.php

    @connect path /connect
    rewrite @connect /connect.php

    @bounty path_regexp bounty ^/bounty/([0-9]+)$
    rewrite @bounty /detail.php?id={re.bounty.1}

    php_fastcgi 127.0.0.1:9000
    file_server
    log { level ERROR }
}
```

**After updating:**
```bash
brew services restart caddy
```

### For Production Mode: Apache Server

If deploying to an Apache server, the included `.htaccess` file will handle URL rewriting automatically. No additional configuration needed.

**The .htaccess includes:**
- Clean URL rewriting
- .env file protection
- Gzip compression
- Directory browsing protection
- Security headers

---

## Route Mapping

### Development Mode (APP_ENV=dev)

| url() Call | Generated URL | Actual File |
|-----------|---------------|-------------|
| `url('browse')` | `browse.php` | browse.php |
| `url('bounties')` | `index.php` | index.php |
| `url('index')` | `index.php` | index.php |
| `url('hire')` | `hire.php` | hire.php |
| `url('connect')` | `connect.php` | connect.php |
| `url('bounty', ['id' => 1])` | `detail.php?id=1` | detail.php |

### Production Mode (APP_ENV=prod)

| url() Call | Generated URL | Rewrites To |
|-----------|---------------|-------------|
| `url('browse')` | `/browse` | browse.php |
| `url('bounties')` | `/bounties` | index.php |
| `url('index')` | `/bounties` | index.php |
| `url('hire')` | `/hire` | hire.php |
| `url('connect')` | `/connect` | connect.php |
| `url('bounty', ['id' => 1])` | `/bounty/1` | detail.php?id=1 |

---

## Security Notes

### .env File Protection

**CRITICAL:** Never commit the `.env` file to version control!

1. The `.env` file is protected via `.htaccess` (Apache)
2. Add `.env` to `.gitignore`
3. Use `.env.example` as a template for new developers
4. Each environment should have its own `.env` with appropriate values

### Sensitive Information

The `.env` file contains sensitive information:
- Admin credentials
- API keys (when added)
- Database credentials (when added)
- Environment-specific settings

**Best practices:**
- Use strong passwords
- Rotate credentials regularly
- Never expose .env contents in error messages
- Use different credentials for dev/staging/production

---

## Adding New Routes

To add a new route that supports both environments:

### 1. Create the PHP file
Create your new page (e.g., `dashboard.php`)

### 2. Update config.php
Add to the `url()` function in both dev and prod sections:

```php
// In the IS_DEV section:
case 'dashboard':
    return 'dashboard.php';

// In the IS_PROD section:
case 'dashboard':
    return '/dashboard';
```

### 3. Update .htaccess (for Apache production)
Add the rewrite rule:
```apache
# /dashboard -> dashboard.php
RewriteRule ^dashboard$ dashboard.php [L,QSA]
```

### 4. Update Caddyfile (for Caddy production)
Add the rewrite directive:
```caddy
@dashboard path /dashboard
rewrite @dashboard /dashboard.php
```

### 5. Use in your code
```php
<a href="<?php echo url('dashboard'); ?>">Dashboard</a>
```

---

## Testing

### Verify Current Environment
Create a test file to check configuration:

```php
<?php
require_once 'config.php';

echo "Environment: " . APP_ENV . "\n";
echo "App Name: " . APP_NAME . "\n";
echo "Is Dev: " . (IS_DEV ? 'Yes' : 'No') . "\n";
echo "Is Prod: " . (IS_PROD ? 'Yes' : 'No') . "\n";
?>
```

### Test URL Generation
```php
<?php
require_once 'config.php';

echo "Browse: " . url('browse') . "\n";
echo "Bounty #1: " . url('bounty', ['id' => 1]) . "\n";
echo "Asset: " . asset('styles.css') . "\n";
?>
```

---

## Troubleshooting

### Issue: "No input file specified" (Caddy)
**Cause:** Incorrect rewrite rules or missing php_fastcgi configuration
**Solution:** Ensure Caddyfile has correct rewrite rules and php_fastcgi directive

### Issue: URLs not working in production mode
**Cause:** Server rewrite rules not configured
**Solution:**
- For Caddy: Update Caddyfile with rewrite rules and restart
- For Apache: Ensure .htaccess is in project root and mod_rewrite is enabled

### Issue: Changes to .env not taking effect
**Cause:** PHP caching or syntax error in .env
**Solution:**
- Check for syntax errors in .env (no spaces around =)
- Restart PHP-FPM: `brew services restart php`
- Clear browser cache

### Issue: .env file errors
**Cause:** Missing .env file or incorrect format
**Solution:**
- Copy `.env.example` to `.env`
- Ensure format is `KEY=value` (no spaces around =)
- Remove quotes from values unless needed

---

## Production Deployment Checklist

When deploying to production:

- [ ] Copy `.env.example` to `.env` on production server
- [ ] Set `APP_ENV=prod` in production .env
- [ ] Update APP_NAME if needed
- [ ] Set production admin credentials
- [ ] Configure server rewrite rules (Apache .htaccess or Caddy config)
- [ ] Test all routes work correctly
- [ ] Verify .env file is not publicly accessible
- [ ] Enable error logging (disable display_errors)
- [ ] Test asset loading (CSS, JS)
- [ ] Verify database connections (when added)
- [ ] Set up SSL certificate
- [ ] Configure backup system for .env

---

## Support

### Documentation Files
- `/Volumes/Crucial/SITES/redot/.env.example` - Configuration template
- `/Volumes/Crucial/SITES/redot/ENVIRONMENT_SETUP_GUIDE.md` - This file
- `/Volumes/Crucial/SITES/redot/tests/TESTING_REPORT.md` - Test results

### Key Files
- `/Volumes/Crucial/SITES/redot/.env` - Active configuration
- `/Volumes/Crucial/SITES/redot/config.php` - Configuration system
- `/Volumes/Crucial/SITES/redot/.htaccess` - Apache rewrite rules
- `/opt/homebrew/etc/Caddyfile` - Caddy server configuration

---

## Additional Resources

### Local Development URLs
- Site: `https://redot.test/`
- Browse: `https://redot.test/browse.php`
- Bounties: `https://redot.test/index.php`

### Server Management
```bash
# Restart PHP-FPM
brew services restart php

# Restart Caddy
brew services restart caddy

# View Caddy logs
tail -f /opt/homebrew/var/log/caddy.log

# Test Caddy configuration
caddy validate --config /opt/homebrew/etc/Caddyfile
```

---

**Last Updated:** February 8, 2026
**Version:** 1.0
**Status:** Tested and Verified
