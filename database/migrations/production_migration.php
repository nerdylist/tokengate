<?php
/**
 * Production Migration Script
 *
 * This script handles:
 * 1. Database consolidation (rentpeople.db + redot.db → g8.db)
 * 2. Schema updates for guild/skill hierarchy
 * 3. Admin user creation from .env
 *
 * Usage: php database/migrations/production_migration.php
 */

require_once __DIR__ . '/../../config.php';

class ProductionMigration
{
    private $pdo;
    private $backupDir;
    private $dbPath;

    public function __construct()
    {
        $this->backupDir = __DIR__ . '/../backups';
        $this->dbPath = __DIR__ . '/../' . DB_NAME;

        // Create backups directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function run()
    {
        echo "=== Production Migration Script ===\n\n";

        try {
            // Step 1: Backup existing databases
            $this->backupExistingDatabases();

            // Step 2: Create new g8.db with updated schema
            $this->createNewDatabase();

            // Step 3: Migrate data from old databases
            $this->migrateData();

            // Step 4: Update schema for guild/skill hierarchy
            $this->updateSchema();

            // Step 5: Seed admin user from .env
            $this->seedAdminUser();

            echo "\n✓ Migration completed successfully!\n";
            echo "✓ Database: " . DB_NAME . "\n";
            echo "✓ Backups saved to: {$this->backupDir}\n\n";

        } catch (Exception $e) {
            echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
            echo "✓ Your original databases are backed up in: {$this->backupDir}\n\n";
            exit(1);
        }
    }

    private function backupExistingDatabases()
    {
        echo "Step 1: Backing up existing databases...\n";

        $timestamp = date('Y-m-d_H-i-s');
        $databases = ['rentpeople.db', 'redot.db', DB_NAME];

        foreach ($databases as $db) {
            $dbFile = __DIR__ . '/../' . $db;
            if (file_exists($dbFile)) {
                $backupFile = $this->backupDir . '/' . str_replace('.db', "_backup_{$timestamp}.db", $db);
                copy($dbFile, $backupFile);
                echo "  ✓ Backed up: {$db} → " . basename($backupFile) . "\n";
            }
        }

        echo "\n";
    }

    private function createNewDatabase()
    {
        echo "Step 2: Creating new " . DB_NAME . " with updated schema...\n";

        // Remove existing database if it exists
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }

        // Create new database connection
        $this->pdo = new PDO('sqlite:' . $this->dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('PRAGMA foreign_keys = ON');

        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/../schema.sql');
        $this->pdo->exec($schema);

        echo "  ✓ Created " . DB_NAME . " with base schema\n\n";
    }

    private function migrateData()
    {
        echo "Step 3: Migrating data from old databases...\n";

        // Check which database has more data
        $rentpeopleDb = __DIR__ . '/../rentpeople.db';
        $redotDb = __DIR__ . '/../redot.db';

        $sourceDb = null;
        $sourceName = null;

        if (file_exists($rentpeopleDb)) {
            $sourceDb = $rentpeopleDb;
            $sourceName = 'rentpeople.db';
        } elseif (file_exists($redotDb)) {
            $sourceDb = $redotDb;
            $sourceName = 'redot.db';
        }

        if (!$sourceDb) {
            echo "  ⚠ No source database found (legacy rentpeople.db or redot.db)\n";
            echo "  ✓ Starting with fresh database\n\n";
            return;
        }

        echo "  → Using {$sourceName} as data source\n";

        // Attach source database
        $this->pdo->exec("ATTACH DATABASE '{$sourceDb}' AS source");

        // Get list of tables to migrate
        $tables = [
            'users', 'categories', 'skills', 'pending_skills', 'bounties', 'bounty_skills',
            'profiles', 'profile_skills', 'applications', 'votes', 'sessions',
            'guilds', 'ranks', 'profile_guilds', 'quests', 'quest_bounties',
            'profile_statuses', 'bounty_ranks'
        ];

        foreach ($tables as $table) {
            try {
                // Check if table exists in source
                $check = $this->pdo->query("SELECT name FROM source.sqlite_master WHERE type='table' AND name='{$table}'");
                if (!$check->fetch()) {
                    continue;
                }

                // Get column names from source table
                $columns = $this->pdo->query("PRAGMA source.table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);
                $columnNames = array_column($columns, 'name');
                $columnList = implode(', ', $columnNames);

                // Copy data
                $this->pdo->exec("INSERT OR IGNORE INTO main.{$table} ({$columnList}) SELECT {$columnList} FROM source.{$table}");

                $count = $this->pdo->query("SELECT COUNT(*) FROM main.{$table}")->fetchColumn();
                echo "  ✓ Migrated {$table}: {$count} rows\n";

            } catch (Exception $e) {
                echo "  ⚠ Skipped {$table}: " . $e->getMessage() . "\n";
            }
        }

        // Detach source database
        $this->pdo->exec("DETACH DATABASE source");

        echo "\n";
    }

    private function updateSchema()
    {
        echo "Step 4: Updating schema for guild/skill hierarchy...\n";

        // Add profile_id to users table if it doesn't exist
        try {
            $this->pdo->exec("ALTER TABLE users ADD COLUMN profile_id VARCHAR(50)");
            echo "  ✓ Added profile_id column to users table\n";
        } catch (Exception $e) {
            echo "  → profile_id column already exists in users table\n";
        }

        // Add description to skills table
        try {
            $this->pdo->exec("ALTER TABLE skills ADD COLUMN description TEXT");
            echo "  ✓ Added description column to skills table\n";
        } catch (Exception $e) {
            echo "  → description column already exists in skills table\n";
        }

        // Add parent_skill_id to skills table
        try {
            $this->pdo->exec("ALTER TABLE skills ADD COLUMN parent_skill_id INTEGER REFERENCES skills(id)");
            echo "  ✓ Added parent_skill_id column to skills table\n";
        } catch (Exception $e) {
            echo "  → parent_skill_id column already exists in skills table\n";
        }

        // Add is_addable to skills table
        try {
            $this->pdo->exec("ALTER TABLE skills ADD COLUMN is_addable INTEGER DEFAULT 1");
            echo "  ✓ Added is_addable column to skills table\n";
        } catch (Exception $e) {
            echo "  → is_addable column already exists in skills table\n";
        }

        // Add xp to profile_skills table
        try {
            $this->pdo->exec("ALTER TABLE profile_skills ADD COLUMN xp INTEGER DEFAULT 0");
            echo "  ✓ Added xp column to profile_skills table\n";
        } catch (Exception $e) {
            echo "  → xp column already exists in profile_skills table\n";
        }

        // Create guild_skills junction table
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS guild_skills (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    guild_id INTEGER NOT NULL,
                    skill_id INTEGER NOT NULL,
                    is_core INTEGER DEFAULT 0,
                    sort_order INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
                    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
                    UNIQUE(guild_id, skill_id)
                )
            ");
            echo "  ✓ Created guild_skills junction table\n";
        } catch (Exception $e) {
            echo "  → guild_skills table already exists\n";
        }

        // Create guild_ranks table
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS guild_ranks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    guild_id INTEGER NOT NULL,
                    rank_name VARCHAR(100) NOT NULL,
                    min_xp INTEGER NOT NULL DEFAULT 0,
                    skill_count_required INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE
                )
            ");
            echo "  ✓ Created guild_ranks table\n";
        } catch (Exception $e) {
            echo "  → guild_ranks table already exists\n";
        }

        echo "\n";
    }

    private function seedAdminUser()
    {
        echo "Step 5: Seeding admin user from .env...\n";

        $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : (getenv('ADMIN_EMAIL') ?: null);
        $adminPassword = defined('ADMIN_PASSWORD') ? ADMIN_PASSWORD : (getenv('ADMIN_PASSWORD') ?: null);

        if (!$adminEmail || !$adminPassword) {
            echo "  ⚠ ADMIN_EMAIL or ADMIN_PASSWORD not set in .env\n";
            echo "  → Skipping admin user creation\n\n";
            return;
        }

        // Check if admin user already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$adminEmail]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            echo "  → Admin user already exists: {$adminEmail}\n";

            // Update to ensure they're admin and update password
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $this->pdo->prepare("UPDATE users SET is_admin = 1, password_hash = ? WHERE email = ?")
                ->execute([$passwordHash, $adminEmail]);
            echo "  ✓ Updated admin privileges and password for: {$adminEmail}\n";

            // Check if they have a profile
            $stmt = $this->pdo->prepare("SELECT id, profile_id FROM profiles WHERE user_id = ?");
            $stmt->execute([$existingUser['id']]);
            $profile = $stmt->fetch();

            if (!$profile) {
                // Create profile for existing admin
                $profileId = $this->generateProfileId();

                // Check if profile_statuses table has data
                $hasStatuses = $this->pdo->query("SELECT COUNT(*) FROM profile_statuses")->fetchColumn() > 0;

                if ($hasStatuses) {
                    $this->pdo->prepare("
                        INSERT INTO profiles (user_id, profile_id, bio, available, status_id, created_at)
                        VALUES (?, ?, 'Platform Administrator', 1, 1, CURRENT_TIMESTAMP)
                    ")->execute([$existingUser['id'], $profileId]);
                } else {
                    // Temporarily disable foreign keys to insert without status_id
                    $this->pdo->exec('PRAGMA foreign_keys = OFF');
                    $this->pdo->prepare("
                        INSERT INTO profiles (user_id, profile_id, bio, available, created_at)
                        VALUES (?, ?, 'Platform Administrator', 1, CURRENT_TIMESTAMP)
                    ")->execute([$existingUser['id'], $profileId]);
                    $this->pdo->exec('PRAGMA foreign_keys = ON');
                }

                echo "  ✓ Created profile: {$profileId}\n";
            } else {
                echo "  → Profile already exists: {$profile['profile_id']}\n";
            }

        } else {
            // Create new admin user
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $adminName = explode('@', $adminEmail)[0];

            $this->pdo->prepare("
                INSERT INTO users (email, password_hash, name, is_admin, created_at)
                VALUES (?, ?, ?, 1, CURRENT_TIMESTAMP)
            ")->execute([$adminEmail, $passwordHash, $adminName]);

            $userId = $this->pdo->lastInsertId();
            echo "  ✓ Created admin user: {$adminEmail}\n";

            // Create profile for admin (without status_id if profile_statuses is empty)
            $profileId = $this->generateProfileId();

            // Check if profile_statuses table has data
            $hasStatuses = $this->pdo->query("SELECT COUNT(*) FROM profile_statuses")->fetchColumn() > 0;

            if ($hasStatuses) {
                $this->pdo->prepare("
                    INSERT INTO profiles (user_id, profile_id, bio, available, status_id, created_at)
                    VALUES (?, ?, 'Platform Administrator', 1, 1, CURRENT_TIMESTAMP)
                ")->execute([$userId, $profileId]);
            } else {
                // Temporarily disable foreign keys to insert without status_id
                $this->pdo->exec('PRAGMA foreign_keys = OFF');
                $this->pdo->prepare("
                    INSERT INTO profiles (user_id, profile_id, bio, available, created_at)
                    VALUES (?, ?, 'Platform Administrator', 1, CURRENT_TIMESTAMP)
                ")->execute([$userId, $profileId]);
                $this->pdo->exec('PRAGMA foreign_keys = ON');
            }

            echo "  ✓ Created profile: {$profileId}\n";

            // Update users.profile_id
            $this->pdo->prepare("UPDATE users SET profile_id = ? WHERE id = ?")->execute([$profileId, $userId]);
        }

        echo "\n";
    }

    private function generateProfileId()
    {
        // Get count of existing profiles to generate sequential ID
        $count = $this->pdo->query("SELECT COUNT(*) FROM profiles")->fetchColumn();
        $nextNumber = $count + 1;

        return 'NERD-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}

// Run migration
$migration = new ProductionMigration();
$migration->run();
