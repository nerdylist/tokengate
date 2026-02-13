<?php

/**
 * Complete Production Database Build Script
 *
 * This script will:
 * 1. Delete existing database if locked or corrupt
 * 2. Create fresh database with complete schema
 * 3. Seed categories, guilds, ranks, and profile statuses
 * 4. Create admin user from .env
 */

require_once __DIR__ . '/../config.php';

echo "=== Redot Production Database Builder ===\n\n";

$dbPath = __DIR__ . '/' . DB_NAME;
$backupPath = __DIR__ . '/backups/';

// Create backups directory if it doesn't exist
if (!file_exists($backupPath)) {
    mkdir($backupPath, 0755, true);
}

// Step 1: Backup existing database if it exists
if (file_exists($dbPath)) {
    $backupFile = $backupPath . 'g8_backup_' . date('Y-m-d_H-i-s') . '.db';

    try {
        if (copy($dbPath, $backupFile)) {
            echo "✓ Backed up existing database to: " . basename($backupFile) . "\n";
        }
    } catch (Exception $e) {
        echo "⚠ Could not backup database (continuing anyway): " . $e->getMessage() . "\n";
    }

    // Delete existing database
    try {
        unlink($dbPath);
        echo "✓ Removed existing database\n";
    } catch (Exception $e) {
        echo "✗ Could not delete existing database: " . $e->getMessage() . "\n";
        echo "  Please delete it manually and run this script again.\n";
        exit(1);
    }
}

echo "\nCreating fresh database...\n";

// Step 2: Create new database with schema
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute schema
    $schemaPath = __DIR__ . '/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Schema file not found: $schemaPath");
    }

    $schema = file_get_contents($schemaPath);
    $db->exec($schema);

    // Add is_verified column to users table (from migration 001)
    $db->exec("ALTER TABLE users ADD COLUMN is_verified INTEGER DEFAULT 0");

    echo "✓ Database schema created\n";

} catch (Exception $e) {
    echo "✗ Failed to create database: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Seed Profile Statuses
echo "\nSeeding profile statuses...\n";

try {
    $statuses = [
        ['name' => 'Available', 'slug' => 'available', 'color' => '#22c55e', 'icon' => '✓', 'sort_order' => 1],
        ['name' => 'Busy', 'slug' => 'busy', 'color' => '#f59e0b', 'icon' => '⏳', 'sort_order' => 2],
        ['name' => 'Unavailable', 'slug' => 'unavailable', 'color' => '#ef4444', 'icon' => '✗', 'sort_order' => 3],
        ['name' => 'On Vacation', 'slug' => 'on-vacation', 'color' => '#3b82f6', 'icon' => '🏖', 'sort_order' => 4],
    ];

    $stmt = $db->prepare("
        INSERT INTO profile_statuses (name, slug, color, icon, sort_order, is_active)
        VALUES (?, ?, ?, ?, ?, 1)
    ");

    foreach ($statuses as $status) {
        $stmt->execute([
            $status['name'],
            $status['slug'],
            $status['color'],
            $status['icon'],
            $status['sort_order']
        ]);
    }

    echo "✓ Profile statuses seeded (" . count($statuses) . " statuses)\n";

} catch (Exception $e) {
    echo "✗ Failed to seed profile statuses: " . $e->getMessage() . "\n";
}

// Step 4: Seed Categories
echo "\nSeeding categories...\n";

try {
    $categories = [
        ['name' => 'Digital & Tech', 'slug' => 'digital-tech', 'description' => 'Web development, software, and technical services'],
        ['name' => 'Design & Media', 'slug' => 'design-media', 'description' => 'Graphic design, video, and creative services'],
        ['name' => 'Data & AI', 'slug' => 'data-ai', 'description' => 'Data analysis, AI training, and automation'],
        ['name' => 'Marketing', 'slug' => 'marketing', 'description' => 'Digital marketing, SEO, and advertising'],
        ['name' => 'Social Media', 'slug' => 'social-media', 'description' => 'Content creation and community management'],
        ['name' => 'Copy & Content', 'slug' => 'copy-content', 'description' => 'Writing, copywriting, and content strategy'],
        ['name' => 'Business & Admin', 'slug' => 'business-admin', 'description' => 'Virtual assistance and administrative support'],
        ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Bookkeeping, accounting, and financial services'],
        ['name' => 'Field & Labor', 'slug' => 'field-labor', 'description' => 'Physical tasks, errands, and local services'],
        ['name' => 'Creative & Performance', 'slug' => 'creative-performance', 'description' => 'Voice acting, podcasting, and entertainment'],
        ['name' => 'Education & Support', 'slug' => 'education-support', 'description' => 'Tutoring, customer support, and training'],
        ['name' => 'Crafting & Repair', 'slug' => 'crafting-repair', 'description' => '3D printing, electronics, and maker services'],
    ];

    $stmt = $db->prepare("
        INSERT INTO categories (name, slug, description)
        VALUES (?, ?, ?)
    ");

    foreach ($categories as $category) {
        $stmt->execute([
            $category['name'],
            $category['slug'],
            $category['description']
        ]);
    }

    echo "✓ Categories seeded (" . count($categories) . " categories)\n";

} catch (Exception $e) {
    echo "✗ Failed to seed categories: " . $e->getMessage() . "\n";
}

// Step 5: Seed Universal Ranks
echo "\nSeeding universal ranks...\n";

try {
    $universalRanks = [
        ['name' => 'Initiate', 'level' => 1, 'type' => 'universal', 'xp' => 0],
        ['name' => 'Novice', 'level' => 2, 'type' => 'universal', 'xp' => 100],
        ['name' => 'Apprentice', 'level' => 3, 'type' => 'universal', 'xp' => 500],
        ['name' => 'Journeyman', 'level' => 4, 'type' => 'universal', 'xp' => 1500],
        ['name' => 'Fellow', 'level' => 5, 'type' => 'universal', 'xp' => 3000],
        ['name' => 'Specialist', 'level' => 6, 'type' => 'universal', 'xp' => 5000],
        ['name' => 'Expert', 'level' => 7, 'type' => 'universal', 'xp' => 8000],
        ['name' => 'Master', 'level' => 8, 'type' => 'universal', 'xp' => 12000],
        ['name' => 'Master Craftsman', 'level' => 9, 'type' => 'universal', 'xp' => 18000],
        ['name' => 'Warden', 'level' => 10, 'type' => 'universal', 'xp' => 25000],
        ['name' => 'Council Member', 'level' => 11, 'type' => 'universal', 'xp' => 35000],
        ['name' => 'Grandmaster', 'level' => 12, 'type' => 'universal', 'xp' => 50000],
        ['name' => 'Guild Officer', 'level' => 13, 'type' => 'universal', 'xp' => 75000],
        ['name' => 'Guildmaster', 'level' => 14, 'type' => 'universal', 'xp' => 100000],
    ];

    $stmt = $db->prepare("
        INSERT INTO ranks (name, level, type, xp_required, description)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($universalRanks as $rank) {
        $stmt->execute([
            $rank['name'],
            $rank['level'],
            $rank['type'],
            $rank['xp'],
            'Universal rank level ' . $rank['level']
        ]);
    }

    echo "✓ Universal ranks seeded (" . count($universalRanks) . " ranks)\n";

} catch (Exception $e) {
    echo "✗ Failed to seed ranks: " . $e->getMessage() . "\n";
}

// Step 6: Seed Guilds from modern.md
echo "\nSeeding guilds...\n";

try {
    $guilds = [
        // Digital & Tech Guilds
        ['name' => 'Frontend Developer', 'slug' => 'frontend-developer', 'type' => 'digital-tech', 'description' => 'Building user interfaces and client-side applications', 'icon' => '💻'],
        ['name' => 'Backend Developer', 'slug' => 'backend-developer', 'type' => 'digital-tech', 'description' => 'Server-side development and API creation', 'icon' => '⚙️'],
        ['name' => 'Full Stack Developer', 'slug' => 'full-stack-developer', 'type' => 'digital-tech', 'description' => 'End-to-end web application development', 'icon' => '🔧'],
        ['name' => 'WordPress Builder', 'slug' => 'wordpress-builder', 'type' => 'digital-tech', 'description' => 'WordPress site creation and customization', 'icon' => '📝'],
        ['name' => 'Shopify Developer', 'slug' => 'shopify-developer', 'type' => 'digital-tech', 'description' => 'E-commerce store development on Shopify', 'icon' => '🛒'],
        ['name' => 'API Integrator', 'slug' => 'api-integrator', 'type' => 'digital-tech', 'description' => 'Connecting systems and services', 'icon' => '🔌'],
        ['name' => 'Automation Engineer', 'slug' => 'automation-engineer', 'type' => 'digital-tech', 'description' => 'Building automated workflows and scripts', 'icon' => '🤖'],
        ['name' => 'Bug Hunter', 'slug' => 'bug-hunter', 'type' => 'digital-tech', 'description' => 'Finding and reporting software bugs', 'icon' => '🐛'],
        ['name' => 'QA Tester', 'slug' => 'qa-tester', 'type' => 'digital-tech', 'description' => 'Quality assurance and testing', 'icon' => '✅'],
        ['name' => 'DevOps Technician', 'slug' => 'devops-technician', 'type' => 'digital-tech', 'description' => 'Deployment and infrastructure management', 'icon' => '🚀'],

        // Design & Media Guild
        ['name' => 'UI/UX Designer', 'slug' => 'ui-ux-designer', 'type' => 'design-media', 'description' => 'User interface and experience design', 'icon' => '🎨'],
        ['name' => 'Graphic Designer', 'slug' => 'graphic-designer', 'type' => 'design-media', 'description' => 'Visual design and branding', 'icon' => '🖼️'],
        ['name' => 'Brand Designer', 'slug' => 'brand-designer', 'type' => 'design-media', 'description' => 'Brand identity and strategy', 'icon' => '🏷️'],
        ['name' => 'Motion Designer', 'slug' => 'motion-designer', 'type' => 'design-media', 'description' => 'Animation and motion graphics', 'icon' => '🎬'],
        ['name' => 'Video Editor', 'slug' => 'video-editor', 'type' => 'design-media', 'description' => 'Video editing and post-production', 'icon' => '🎥'],
        ['name' => 'Illustrator', 'slug' => 'illustrator', 'type' => 'design-media', 'description' => 'Custom illustrations and artwork', 'icon' => '✏️'],
        ['name' => 'Photo Editor', 'slug' => 'photo-editor', 'type' => 'design-media', 'description' => 'Photo retouching and enhancement', 'icon' => '📷'],
        ['name' => '3D Artist', 'slug' => '3d-artist', 'type' => 'design-media', 'description' => '3D modeling and rendering', 'icon' => '🎭'],

        // Data & AI Guild
        ['name' => 'Data Analyst', 'slug' => 'data-analyst', 'type' => 'data-ai', 'description' => 'Data analysis and insights', 'icon' => '📊'],
        ['name' => 'Prompt Engineer', 'slug' => 'prompt-engineer', 'type' => 'data-ai', 'description' => 'AI prompt optimization', 'icon' => '🧠'],
        ['name' => 'AI Trainer', 'slug' => 'ai-trainer', 'type' => 'data-ai', 'description' => 'Training and fine-tuning AI models', 'icon' => '🎓'],
        ['name' => 'Model Tester', 'slug' => 'model-tester', 'type' => 'data-ai', 'description' => 'Testing AI model outputs', 'icon' => '🔬'],
        ['name' => 'Spreadsheet Engineer', 'slug' => 'spreadsheet-engineer', 'type' => 'data-ai', 'description' => 'Complex spreadsheet automation', 'icon' => '📈'],
        ['name' => 'Database Manager', 'slug' => 'database-manager', 'type' => 'data-ai', 'description' => 'Database design and optimization', 'icon' => '🗄️'],

        // Marketing Guild
        ['name' => 'Digital Marketer', 'slug' => 'digital-marketer', 'type' => 'marketing', 'description' => 'Online marketing campaigns', 'icon' => '📱'],
        ['name' => 'SEO Specialist', 'slug' => 'seo-specialist', 'type' => 'marketing', 'description' => 'Search engine optimization', 'icon' => '🔍'],
        ['name' => 'PPC Manager', 'slug' => 'ppc-manager', 'type' => 'marketing', 'description' => 'Pay-per-click advertising', 'icon' => '💰'],
        ['name' => 'Email Marketer', 'slug' => 'email-marketer', 'type' => 'marketing', 'description' => 'Email campaign management', 'icon' => '📧'],
        ['name' => 'Funnel Architect', 'slug' => 'funnel-architect', 'type' => 'marketing', 'description' => 'Sales funnel design and optimization', 'icon' => '🎯'],
        ['name' => 'Landing Page Builder', 'slug' => 'landing-page-builder', 'type' => 'marketing', 'description' => 'High-converting landing pages', 'icon' => '📄'],
        ['name' => 'Conversion Optimizer', 'slug' => 'conversion-optimizer', 'type' => 'marketing', 'description' => 'Improving conversion rates', 'icon' => '📈'],

        // Social Media Guild
        ['name' => 'Content Creator', 'slug' => 'content-creator', 'type' => 'social-media', 'description' => 'Creating engaging social content', 'icon' => '📸'],
        ['name' => 'Community Manager', 'slug' => 'community-manager', 'type' => 'social-media', 'description' => 'Managing online communities', 'icon' => '👥'],
        ['name' => 'Short-Form Editor', 'slug' => 'short-form-editor', 'type' => 'social-media', 'description' => 'Editing short-form video content', 'icon' => '🎞️'],
        ['name' => 'Livestream Host', 'slug' => 'livestream-host', 'type' => 'social-media', 'description' => 'Hosting live streaming sessions', 'icon' => '📡'],
        ['name' => 'Influencer Manager', 'slug' => 'influencer-manager', 'type' => 'social-media', 'description' => 'Managing influencer partnerships', 'icon' => '⭐'],
        ['name' => 'Trend Analyst', 'slug' => 'trend-analyst', 'type' => 'social-media', 'description' => 'Identifying and analyzing trends', 'icon' => '📊'],

        // Copy & Content Guild
        ['name' => 'Copywriter', 'slug' => 'copywriter', 'type' => 'copy-content', 'description' => 'Persuasive marketing copy', 'icon' => '✍️'],
        ['name' => 'Technical Writer', 'slug' => 'technical-writer', 'type' => 'copy-content', 'description' => 'Documentation and technical content', 'icon' => '📚'],
        ['name' => 'Scriptwriter', 'slug' => 'scriptwriter', 'type' => 'copy-content', 'description' => 'Writing scripts for video and audio', 'icon' => '🎬'],
        ['name' => 'Blog Author', 'slug' => 'blog-author', 'type' => 'copy-content', 'description' => 'Writing blog posts and articles', 'icon' => '📝'],
        ['name' => 'Newsletter Curator', 'slug' => 'newsletter-curator', 'type' => 'copy-content', 'description' => 'Curating newsletter content', 'icon' => '📰'],
        ['name' => 'Content Strategist', 'slug' => 'content-strategist', 'type' => 'copy-content', 'description' => 'Planning content strategies', 'icon' => '🗺️'],

        // Business & Admin Guild
        ['name' => 'Virtual Assistant', 'slug' => 'virtual-assistant', 'type' => 'business-admin', 'description' => 'Administrative support services', 'icon' => '💼'],
        ['name' => 'Project Coordinator', 'slug' => 'project-coordinator', 'type' => 'business-admin', 'description' => 'Project management and coordination', 'icon' => '📋'],
        ['name' => 'CRM Manager', 'slug' => 'crm-manager', 'type' => 'business-admin', 'description' => 'Customer relationship management', 'icon' => '👤'],
        ['name' => 'Operations Analyst', 'slug' => 'operations-analyst', 'type' => 'business-admin', 'description' => 'Business operations analysis', 'icon' => '📊'],
        ['name' => 'Scheduler', 'slug' => 'scheduler', 'type' => 'business-admin', 'description' => 'Calendar and meeting management', 'icon' => '📅'],
        ['name' => 'Documentation Clerk', 'slug' => 'documentation-clerk', 'type' => 'business-admin', 'description' => 'Document organization and management', 'icon' => '📁'],

        // Finance Guild
        ['name' => 'Bookkeeper', 'slug' => 'bookkeeper', 'type' => 'finance', 'description' => 'Financial record keeping', 'icon' => '📖'],
        ['name' => 'Invoice Manager', 'slug' => 'invoice-manager', 'type' => 'finance', 'description' => 'Invoice creation and tracking', 'icon' => '💵'],
        ['name' => 'Payroll Assistant', 'slug' => 'payroll-assistant', 'type' => 'finance', 'description' => 'Payroll processing support', 'icon' => '💳'],
        ['name' => 'Budget Analyst', 'slug' => 'budget-analyst', 'type' => 'finance', 'description' => 'Budget planning and analysis', 'icon' => '💰'],
        ['name' => 'Tax Prep Assistant', 'slug' => 'tax-prep-assistant', 'type' => 'finance', 'description' => 'Tax preparation support', 'icon' => '📊'],
    ];

    $stmt = $db->prepare("
        INSERT INTO guilds (name, slug, type, description, icon)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($guilds as $guild) {
        $stmt->execute([
            $guild['name'],
            $guild['slug'],
            $guild['type'],
            $guild['description'],
            $guild['icon']
        ]);
    }

    echo "✓ Guilds seeded (" . count($guilds) . " guilds)\n";

} catch (Exception $e) {
    echo "✗ Failed to seed guilds: " . $e->getMessage() . "\n";
}

// Step 7: Create admin user from .env
echo "\nCreating admin user...\n";

$adminEmail = getenv('ADMIN_EMAIL');
$adminPassword = getenv('ADMIN_PASSWORD');

if (!$adminEmail || !$adminPassword) {
    echo "⚠ Warning: ADMIN_EMAIL or ADMIN_PASSWORD not set in .env\n";
    echo "  Admin user not created.\n";
} else {
    try {
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, name, is_admin, is_verified, created_at)
            VALUES (?, ?, ?, 1, 1, datetime('now'))
        ");

        $stmt->execute([$adminEmail, $passwordHash, 'Administrator']);

        $adminUserId = $db->lastInsertId();

        echo "✓ Admin user created\n";
        echo "  Email: $adminEmail\n";
        echo "  User ID: $adminUserId\n";

        // Create profile for admin
        $profileId = 'NERD-001';

        $stmt = $db->prepare("
            INSERT INTO profiles (user_id, profile_id, bio, available, status_id, created_at)
            VALUES (?, ?, ?, 1, 1, datetime('now'))
        ");

        $stmt->execute([
            $adminUserId,
            $profileId,
            'Platform Administrator'
        ]);

        echo "✓ Admin profile created\n";
        echo "  Profile ID: $profileId\n";

    } catch (Exception $e) {
        echo "✗ Failed to create admin user: " . $e->getMessage() . "\n";
    }
}

// Set proper permissions
chmod($dbPath, 0664);

echo "\n=== Database Build Complete ===\n";
echo "✓ Production database is ready at: " . basename($dbPath) . "\n";
echo "✓ Login with: $adminEmail\n";
echo "\n";
