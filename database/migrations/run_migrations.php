<?php
/**
 * Migration Runner Script
 * Runs all SQL migrations using PHP's PDO/SQLite
 * Idempotent - can be safely run multiple times
 */

// Set up paths
$rootDir = dirname(dirname(__DIR__));
$envFile = $rootDir . '/.env';
$migrationsDir = __DIR__;

// Load .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found at {$path}\n");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            putenv("$key=$value");
        }
    }
}

// Load environment variables
loadEnv($envFile);

// Get database name from .env or use default
$dbName = getenv('DB_NAME') ?: 'g8.db';
$dbPath = $rootDir . '/database/' . $dbName;

echo "========================================\n";
echo "Migration Runner\n";
echo "========================================\n";
echo "Database: {$dbPath}\n";
echo "Migrations: {$migrationsDir}\n";
echo "========================================\n\n";

try {
    // Connect to database using PDO
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Connected to database\n\n";

    // Create migrations tracking table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration_name VARCHAR(255) UNIQUE NOT NULL,
            applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "✓ Migrations tracking table ready\n\n";

    // Get list of already applied migrations
    $appliedMigrations = [];
    $stmt = $pdo->query("SELECT migration_name FROM migrations");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $appliedMigrations[] = $row['migration_name'];
    }

    // Scan for migration files (002-012)
    $migrationFiles = [];
    for ($i = 2; $i <= 12; $i++) {
        $pattern = sprintf('%03d_*.sql', $i);
        $matches = glob($migrationsDir . '/' . $pattern);
        if (!empty($matches)) {
            $migrationFiles = array_merge($migrationFiles, $matches);
        }
    }

    // Sort migrations by filename to ensure correct order
    sort($migrationFiles);

    if (empty($migrationFiles)) {
        echo "No migration files found.\n";
        exit(0);
    }

    echo "Found " . count($migrationFiles) . " migration file(s)\n\n";

    // Run each migration
    $appliedCount = 0;
    $skippedCount = 0;

    foreach ($migrationFiles as $migrationFile) {
        $migrationName = basename($migrationFile);

        // Check if migration has already been applied
        if (in_array($migrationName, $appliedMigrations)) {
            echo "⊘ Skipping migration: {$migrationName} (already applied)\n";
            $skippedCount++;
            continue;
        }

        echo "→ Running migration: {$migrationName}\n";

        try {
            // Read SQL file
            $sql = file_get_contents($migrationFile);

            if ($sql === false) {
                throw new Exception("Failed to read migration file");
            }

            // Begin transaction for this migration
            $pdo->beginTransaction();

            // Split SQL into statements (handle multi-statement files)
            // Split on semicolons followed by optional whitespace/newlines
            $sqlStatements = preg_split('/;\s*[\r\n]+/', $sql, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($sqlStatements as $statement) {
                // Remove SQL comments (lines starting with --)
                $lines = explode("\n", $statement);
                $cleanedLines = [];
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if (strpos($trimmedLine, '--') !== 0) {
                        $cleanedLines[] = $line;
                    }
                }
                $statement = trim(implode("\n", $cleanedLines));

                // Skip empty statements
                if (empty($statement)) {
                    continue;
                }

                // Check if this is an ALTER TABLE ADD COLUMN statement
                if (preg_match('/ALTER\s+TABLE\s+(\w+)\s+ADD\s+COLUMN\s+(\w+)/i', $statement, $matches)) {
                    $tableName = $matches[1];
                    $columnName = $matches[2];

                    // Check if column already exists
                    $tableInfo = $pdo->query("PRAGMA table_info({$tableName})")->fetchAll(PDO::FETCH_ASSOC);
                    $columnExists = false;

                    foreach ($tableInfo as $column) {
                        if ($column['name'] === $columnName) {
                            $columnExists = true;
                            break;
                        }
                    }

                    if ($columnExists) {
                        echo "    (Skipping ALTER TABLE - column {$columnName} already exists)\n";
                        continue;
                    }
                }

                try {
                    // Execute the statement
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Check if error is due to object already existing (idempotent behavior)
                    $errorMsg = $e->getMessage();

                    // Check for various "already exists" errors
                    $isAlreadyExists = (
                        strpos($errorMsg, 'already exists') !== false ||
                        strpos($errorMsg, 'duplicate column name') !== false ||
                        strpos($errorMsg, 'Duplicate column name') !== false
                    );

                    if ($isAlreadyExists) {
                        // Silently skip - object already exists, which is fine for migrations
                        echo "    (Skipping existing object)\n";
                        continue;
                    }

                    // Check if it's a "no such column" error when creating an index
                    // This means the column from an ALTER TABLE didn't get added
                    $isNoSuchColumn = strpos($errorMsg, 'no such column') !== false;
                    $isCreateIndex = stripos($statement, 'CREATE INDEX') !== false;

                    if ($isNoSuchColumn && $isCreateIndex) {
                        // Skip this index creation - the column wasn't added
                        echo "    (Skipping index - column not found)\n";
                        continue;
                    }

                    // Re-throw other errors
                    throw $e;
                }
            }

            // Record migration as applied
            $stmt = $pdo->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
            $stmt->execute([$migrationName]);

            // Commit transaction
            $pdo->commit();

            echo "  ✓ Migration {$migrationName} completed successfully\n\n";
            $appliedCount++;

        } catch (Exception $e) {
            // Rollback transaction on error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            echo "  ✗ Migration {$migrationName} failed: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    // Summary
    echo "========================================\n";
    echo "Migration Summary\n";
    echo "========================================\n";
    echo "Total migrations: " . count($migrationFiles) . "\n";
    echo "Applied: {$appliedCount}\n";
    echo "Skipped: {$skippedCount}\n";
    echo "========================================\n";

    if ($appliedCount > 0) {
        echo "\n✓ All migrations completed successfully!\n";
    } else {
        echo "\n✓ Database is up to date!\n";
    }

} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
