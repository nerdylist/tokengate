<?php
/**
 * Database Seeder for g8.db
 * Populates the database with comprehensive sample data
 *
 * Usage:
 *   php database/seeder.php --fresh   - Clear all data and reseed (recommended)
 *
 * What gets seeded:
 *   - 4 profile statuses (Available, Busy, Away, Do Not Disturb)
 *   - 10 users including admin (password: password123)
 *   - 8 categories (Web Dev, Mobile, Design, Writing, Marketing, Data Science, Business, Photography)
 *   - 30 skills across all categories
 *   - 4 guilds with gamification ranks
 *   - 14 unified ranks (merged from modern + traditional, duplicates removed)
 *   - 10 user profiles with bios and hourly rates
 *   - 33 profile skills with XP
 *   - 10 profile guild memberships
 *   - 10 bounties (various statuses: open, in_progress, completed)
 *   - 19 bounty skill requirements
 *   - 8 applications
 *
 * Test Credentials:
 *   Email: paul@nerd.biz (admin)
 *   Email: sarah.dev@example.com
 *   Email: mike.design@example.com
 *   Password (all): password123
 *
 * Note: This seeder is designed for --fresh mode only. Running without --fresh
 *       will fail due to unique constraints on existing data.
 */

require_once __DIR__ . '/../config.php';

// Get the database path
$dbPath = __DIR__ . '/g8.db';

// Check if database exists
if (!file_exists($dbPath)) {
    die("Error: Database not found at {$dbPath}\nPlease run migrations first.\n");
}

// Check for --fresh flag
$fresh = in_array('--fresh', $argv ?? []);

try {
    // Create PDO connection
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting database seeding...\n";
    echo "Database: {$dbPath}\n";

    if ($fresh) {
        echo "Fresh mode: Clearing all existing data...\n\n";

        // Disable foreign keys temporarily
        $pdo->exec('PRAGMA foreign_keys = OFF');

        // Clear all tables in reverse dependency order
        $tables = [
            'quest_bounties',
            'bounty_ranks',
            'guild_ranks',
            'guild_skills',
            'quests',
            'profile_guilds',
            'votes',
            'applications',
            'profile_skills',
            'bounty_skills',
            'pending_skills',
            'bounties',
            'profiles',
            'sessions',
            'skills',
            'categories',
            'ranks',
            'guilds',
            'profile_statuses',
            'users'
        ];

        foreach ($tables as $table) {
            $pdo->exec("DELETE FROM {$table}");
            echo "  ✓ Cleared {$table}\n";
        }

        // Reset all sequences to start from 1
        $pdo->exec("DELETE FROM sqlite_sequence");
        echo "  ✓ Reset all auto-increment sequences\n";

        // Re-enable foreign keys
        $pdo->exec('PRAGMA foreign_keys = ON');
        echo "\n";
    }

    // Begin transaction
    $pdo->beginTransaction();

    // ========================================
    // SEED PROFILE STATUSES
    // ========================================
    echo "Seeding profile_statuses...\n";
    $statuses = [
        ['name' => 'Available', 'slug' => 'available', 'color' => '#22c55e', 'icon' => '✓', 'sort_order' => 1],
        ['name' => 'Busy', 'slug' => 'busy', 'color' => '#f59e0b', 'icon' => '◐', 'sort_order' => 2],
        ['name' => 'Away', 'slug' => 'away', 'color' => '#6b7280', 'icon' => '○', 'sort_order' => 3],
        ['name' => 'Do Not Disturb', 'slug' => 'dnd', 'color' => '#ef4444', 'icon' => '⊘', 'sort_order' => 4]
    ];

    foreach ($statuses as $status) {
        $pdo->prepare("INSERT INTO profile_statuses (name, slug, color, icon, sort_order) VALUES (?, ?, ?, ?, ?)")
            ->execute([$status['name'], $status['slug'], $status['color'], $status['icon'], $status['sort_order']]);
    }
    echo "  ✓ Created " . count($statuses) . " profile statuses\n\n";

    // ========================================
    // SEED USERS
    // ========================================
    echo "Seeding users...\n";
    $password = password_hash('password123', PASSWORD_DEFAULT);

    $users = [
        ['email' => 'paul@nerd.biz', 'name' => 'Paul Nerd', 'is_admin' => 1],
        ['email' => 'sarah.dev@example.com', 'name' => 'Sarah Developer', 'is_admin' => 0],
        ['email' => 'mike.design@example.com', 'name' => 'Mike Designer', 'is_admin' => 0],
        ['email' => 'lisa.write@example.com', 'name' => 'Lisa Writer', 'is_admin' => 0],
        ['email' => 'john.code@example.com', 'name' => 'John Coder', 'is_admin' => 0],
        ['email' => 'emma.photo@example.com', 'name' => 'Emma Photographer', 'is_admin' => 0],
        ['email' => 'alex.market@example.com', 'name' => 'Alex Marketer', 'is_admin' => 0],
        ['email' => 'chris.manage@example.com', 'name' => 'Chris Manager', 'is_admin' => 0],
        ['email' => 'nina.consult@example.com', 'name' => 'Nina Consultant', 'is_admin' => 0],
        ['email' => 'david.data@example.com', 'name' => 'David Data Analyst', 'is_admin' => 0]
    ];

    foreach ($users as $user) {
        $pdo->prepare("INSERT INTO users (email, password_hash, name, is_admin) VALUES (?, ?, ?, ?)")
            ->execute([$user['email'], $password, $user['name'], $user['is_admin']]);
    }
    echo "  ✓ Created " . count($users) . " users (password: password123)\n\n";

    // ========================================
    // SEED CATEGORIES
    // ========================================
    echo "Seeding categories...\n";
    $categories = [
        ['name' => 'Web Development', 'slug' => 'web-development', 'description' => 'Frontend and backend web development services'],
        ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'description' => 'iOS and Android app development'],
        ['name' => 'Design', 'slug' => 'design', 'description' => 'UI/UX design, graphic design, and branding'],
        ['name' => 'Writing', 'slug' => 'writing', 'description' => 'Content writing, copywriting, and technical documentation'],
        ['name' => 'Marketing', 'slug' => 'marketing', 'description' => 'Digital marketing, SEO, and social media'],
        ['name' => 'Data Science', 'slug' => 'data-science', 'description' => 'Data analysis, machine learning, and AI'],
        ['name' => 'Business', 'slug' => 'business', 'description' => 'Business consulting and project management'],
        ['name' => 'Photography', 'slug' => 'photography', 'description' => 'Professional photography and editing']
    ];

    foreach ($categories as $category) {
        $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)")
            ->execute([$category['name'], $category['slug'], $category['description']]);
    }
    echo "  ✓ Created " . count($categories) . " categories\n\n";

    // ========================================
    // SEED SKILLS
    // ========================================
    echo "Seeding skills...\n";
    $skills = [
        // Web Development (category_id: 1)
        ['name' => 'PHP', 'slug' => 'php', 'category_id' => 1, 'description' => 'Server-side PHP programming'],
        ['name' => 'JavaScript', 'slug' => 'javascript', 'category_id' => 1, 'description' => 'Client-side JavaScript programming'],
        ['name' => 'React', 'slug' => 'react', 'category_id' => 1, 'description' => 'React frontend framework'],
        ['name' => 'Vue.js', 'slug' => 'vuejs', 'category_id' => 1, 'description' => 'Vue.js frontend framework'],
        ['name' => 'Node.js', 'slug' => 'nodejs', 'category_id' => 1, 'description' => 'Node.js backend development'],
        ['name' => 'Laravel', 'slug' => 'laravel', 'category_id' => 1, 'description' => 'Laravel PHP framework'],
        ['name' => 'CSS', 'slug' => 'css', 'category_id' => 1, 'description' => 'Cascading Style Sheets'],
        ['name' => 'HTML', 'slug' => 'html', 'category_id' => 1, 'description' => 'HTML markup'],

        // Mobile Development (category_id: 2)
        ['name' => 'Swift', 'slug' => 'swift', 'category_id' => 2, 'description' => 'iOS development with Swift'],
        ['name' => 'Kotlin', 'slug' => 'kotlin', 'category_id' => 2, 'description' => 'Android development with Kotlin'],
        ['name' => 'React Native', 'slug' => 'react-native', 'category_id' => 2, 'description' => 'Cross-platform mobile development'],
        ['name' => 'Flutter', 'slug' => 'flutter', 'category_id' => 2, 'description' => 'Flutter framework'],

        // Design (category_id: 3)
        ['name' => 'UI Design', 'slug' => 'ui-design', 'category_id' => 3, 'description' => 'User interface design'],
        ['name' => 'UX Design', 'slug' => 'ux-design', 'category_id' => 3, 'description' => 'User experience design'],
        ['name' => 'Figma', 'slug' => 'figma', 'category_id' => 3, 'description' => 'Figma design tool'],
        ['name' => 'Adobe Photoshop', 'slug' => 'photoshop', 'category_id' => 3, 'description' => 'Photo editing'],
        ['name' => 'Adobe Illustrator', 'slug' => 'illustrator', 'category_id' => 3, 'description' => 'Vector graphics'],

        // Writing (category_id: 4)
        ['name' => 'Content Writing', 'slug' => 'content-writing', 'category_id' => 4, 'description' => 'Blog posts and articles'],
        ['name' => 'Copywriting', 'slug' => 'copywriting', 'category_id' => 4, 'description' => 'Marketing copy'],
        ['name' => 'Technical Writing', 'slug' => 'technical-writing', 'category_id' => 4, 'description' => 'Documentation'],

        // Marketing (category_id: 5)
        ['name' => 'SEO', 'slug' => 'seo', 'category_id' => 5, 'description' => 'Search engine optimization'],
        ['name' => 'Social Media Marketing', 'slug' => 'social-media', 'category_id' => 5, 'description' => 'Social media strategy'],
        ['name' => 'Email Marketing', 'slug' => 'email-marketing', 'category_id' => 5, 'description' => 'Email campaigns'],

        // Data Science (category_id: 6)
        ['name' => 'Python', 'slug' => 'python', 'category_id' => 6, 'description' => 'Python programming'],
        ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'category_id' => 6, 'description' => 'ML algorithms'],
        ['name' => 'Data Analysis', 'slug' => 'data-analysis', 'category_id' => 6, 'description' => 'Data analytics'],

        // Business (category_id: 7)
        ['name' => 'Project Management', 'slug' => 'project-management', 'category_id' => 7, 'description' => 'Managing projects'],
        ['name' => 'Business Strategy', 'slug' => 'business-strategy', 'category_id' => 7, 'description' => 'Strategic planning'],

        // Photography (category_id: 8)
        ['name' => 'Portrait Photography', 'slug' => 'portrait', 'category_id' => 8, 'description' => 'Portrait photos'],
        ['name' => 'Product Photography', 'slug' => 'product', 'category_id' => 8, 'description' => 'Product photos']
    ];

    foreach ($skills as $skill) {
        $pdo->prepare("INSERT INTO skills (name, slug, category_id, description, status) VALUES (?, ?, ?, ?, 'approved')")
            ->execute([$skill['name'], $skill['slug'], $skill['category_id'], $skill['description']]);
    }
    echo "  ✓ Created " . count($skills) . " skills\n\n";

    // ========================================
    // SEED GUILDS
    // ========================================
    echo "Seeding guilds...\n";
    $guilds = [
        ['name' => 'Code Warriors', 'slug' => 'code-warriors', 'type' => 'development', 'description' => 'Elite developers pushing code boundaries', 'icon' => '⚔️'],
        ['name' => 'Design Dragons', 'slug' => 'design-dragons', 'type' => 'design', 'description' => 'Creative designers crafting beautiful experiences', 'icon' => '🐉'],
        ['name' => 'Word Wizards', 'slug' => 'word-wizards', 'type' => 'writing', 'description' => 'Masterful writers weaving compelling narratives', 'icon' => '🪄'],
        ['name' => 'Data Druids', 'slug' => 'data-druids', 'type' => 'data', 'description' => 'Analytics experts revealing hidden insights', 'icon' => '🌲']
    ];

    foreach ($guilds as $guild) {
        $pdo->prepare("INSERT INTO guilds (name, slug, type, description, icon) VALUES (?, ?, ?, ?, ?)")
            ->execute([$guild['name'], $guild['slug'], $guild['type'], $guild['description'], $guild['icon']]);
    }
    echo "  ✓ Created " . count($guilds) . " guilds\n\n";

    // ========================================
    // SEED RANKS (from data/ranks.md - unified rank system)
    // ========================================
    echo "Seeding ranks...\n";

    // Parse ranks from unified ranks.md file
    function parseRanks($file) {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        $ranks = [];
        $inRankSection = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'UNIVERSAL GUILD RANK SYSTEM') !== false) {
                $inRankSection = true;
                continue;
            }

            if ($inRankSection && strpos($line, '===') !== false) {
                continue;
            }

            if ($inRankSection && !empty($line) && strpos($line, '===') === false) {
                $ranks[] = $line;
            }
        }
        return $ranks;
    }

    $rankNames = parseRanks(__DIR__ . '/../data/ranks.md');
    $xpLevels = [0, 100, 500, 1500, 3500, 7000, 12000, 20000, 35000, 60000, 100000, 150000, 250000, 400000];

    $ranks = [];
    foreach ($rankNames as $index => $rankName) {
        $ranks[] = [
            'name' => $rankName,
            'level' => $index + 1,
            'type' => 'universal',
            'xp_required' => $xpLevels[$index] ?? ($xpLevels[count($xpLevels) - 1] * 2)
        ];
    }

    foreach ($ranks as $rank) {
        $pdo->prepare("INSERT INTO ranks (name, level, type, xp_required) VALUES (?, ?, ?, ?)")
            ->execute([$rank['name'], $rank['level'], $rank['type'], $rank['xp_required']]);
    }
    echo "  ✓ Created " . count($ranks) . " unified ranks\n\n";

    // ========================================
    // SEED PROFILES
    // ========================================
    echo "Seeding profiles...\n";
    $profiles = [
        ['user_id' => 1, 'profile_id' => 'NERD-001', 'bio' => 'Full-stack developer and tech entrepreneur', 'hourly_rate' => 150.00, 'status_id' => 1],
        ['user_id' => 2, 'profile_id' => 'DEV-002', 'bio' => 'React specialist with 5 years experience', 'hourly_rate' => 95.00, 'status_id' => 1],
        ['user_id' => 3, 'profile_id' => 'DES-003', 'bio' => 'UI/UX designer passionate about user-centered design', 'hourly_rate' => 85.00, 'status_id' => 2],
        ['user_id' => 4, 'profile_id' => 'WRI-004', 'bio' => 'Content writer specializing in tech and SaaS', 'hourly_rate' => 65.00, 'status_id' => 1],
        ['user_id' => 5, 'profile_id' => 'DEV-005', 'bio' => 'Backend engineer, Python and Node.js expert', 'hourly_rate' => 105.00, 'status_id' => 1],
        ['user_id' => 6, 'profile_id' => 'PHO-006', 'bio' => 'Professional photographer, product and portrait', 'hourly_rate' => 125.00, 'status_id' => 3],
        ['user_id' => 7, 'profile_id' => 'MKT-007', 'bio' => 'Digital marketing strategist, SEO specialist', 'hourly_rate' => 75.00, 'status_id' => 1],
        ['user_id' => 8, 'profile_id' => 'MGR-008', 'bio' => 'Project manager with agile certification', 'hourly_rate' => 95.00, 'status_id' => 2],
        ['user_id' => 9, 'profile_id' => 'CON-009', 'bio' => 'Business consultant, growth and strategy', 'hourly_rate' => 135.00, 'status_id' => 1],
        ['user_id' => 10, 'profile_id' => 'DAT-010', 'bio' => 'Data scientist, ML and analytics expert', 'hourly_rate' => 115.00, 'status_id' => 1]
    ];

    foreach ($profiles as $profile) {
        $pdo->prepare("INSERT INTO profiles (user_id, profile_id, bio, hourly_rate, status_id) VALUES (?, ?, ?, ?, ?)")
            ->execute([$profile['user_id'], $profile['profile_id'], $profile['bio'], $profile['hourly_rate'], $profile['status_id']]);
    }
    echo "  ✓ Created " . count($profiles) . " profiles\n\n";

    // ========================================
    // SEED PROFILE SKILLS
    // ========================================
    echo "Seeding profile_skills...\n";
    $profileSkills = [
        // Paul (profile 1) - Full-stack
        [1, 1, 'expert', 250], [1, 2, 'expert', 300], [1, 5, 'advanced', 200], [1, 7, 'expert', 180], [1, 8, 'expert', 150],

        // Sarah (profile 2) - React dev
        [2, 2, 'expert', 220], [2, 3, 'expert', 280], [2, 7, 'advanced', 150], [2, 8, 'advanced', 120],

        // Mike (profile 3) - Designer
        [3, 13, 'expert', 300], [3, 14, 'expert', 280], [3, 15, 'expert', 350], [3, 16, 'advanced', 200],

        // Lisa (profile 4) - Writer
        [4, 18, 'expert', 240], [4, 19, 'advanced', 180], [4, 20, 'expert', 200],

        // John (profile 5) - Backend
        [5, 1, 'expert', 280], [5, 5, 'expert', 320], [5, 6, 'advanced', 180], [5, 24, 'expert', 260],

        // Emma (profile 6) - Photographer
        [6, 16, 'expert', 220], [6, 29, 'expert', 300], [6, 30, 'expert', 280],

        // Alex (profile 7) - Marketer
        [7, 21, 'expert', 240], [7, 22, 'expert', 200], [7, 23, 'advanced', 180],

        // Chris (profile 8) - Manager
        [8, 27, 'expert', 300], [8, 28, 'advanced', 180],

        // Nina (profile 9) - Consultant
        [9, 27, 'advanced', 200], [9, 28, 'expert', 280],

        // David (profile 10) - Data
        [10, 24, 'expert', 300], [10, 25, 'expert', 280], [10, 26, 'expert', 320]
    ];

    foreach ($profileSkills as $ps) {
        $pdo->prepare("INSERT INTO profile_skills (profile_id, skill_id, proficiency_level, xp) VALUES (?, ?, ?, ?)")
            ->execute([$ps[0], $ps[1], $ps[2], $ps[3]]);
    }
    echo "  ✓ Created " . count($profileSkills) . " profile skills\n\n";

    // ========================================
    // SEED PROFILE GUILDS
    // ========================================
    echo "Seeding profile_guilds...\n";

    // Get rank IDs for assignment (using universal ranks)
    $ranksByLevel = [];
    $rankResult = $pdo->query("SELECT id, level FROM ranks ORDER BY level");
    while ($row = $rankResult->fetch(PDO::FETCH_ASSOC)) {
        $ranksByLevel[$row['level']] = $row['id'];
    }

    // Assign profiles to guilds with appropriate ranks based on XP
    $profileGuilds = [
        ['profile_id' => 1, 'guild_id' => 1, 'rank_id' => $ranksByLevel[6] ?? 1, 'xp' => 8500, 'is_primary' => 1],  // Expert level
        ['profile_id' => 2, 'guild_id' => 1, 'rank_id' => $ranksByLevel[4] ?? 1, 'xp' => 2200, 'is_primary' => 1],  // Journeyman
        ['profile_id' => 3, 'guild_id' => 2, 'rank_id' => $ranksByLevel[5] ?? 1, 'xp' => 4800, 'is_primary' => 1],  // Specialist
        ['profile_id' => 4, 'guild_id' => 3, 'rank_id' => $ranksByLevel[4] ?? 1, 'xp' => 1800, 'is_primary' => 1],  // Journeyman
        ['profile_id' => 5, 'guild_id' => 1, 'rank_id' => $ranksByLevel[6] ?? 1, 'xp' => 9200, 'is_primary' => 1],  // Expert
        ['profile_id' => 6, 'guild_id' => 2, 'rank_id' => $ranksByLevel[3] ?? 1, 'xp' => 800, 'is_primary' => 1],   // Apprentice
        ['profile_id' => 7, 'guild_id' => 3, 'rank_id' => $ranksByLevel[3] ?? 1, 'xp' => 650, 'is_primary' => 1],   // Apprentice
        ['profile_id' => 8, 'guild_id' => 1, 'rank_id' => $ranksByLevel[4] ?? 1, 'xp' => 1750, 'is_primary' => 1],  // Journeyman
        ['profile_id' => 9, 'guild_id' => 1, 'rank_id' => $ranksByLevel[2] ?? 1, 'xp' => 180, 'is_primary' => 1],   // Novice
        ['profile_id' => 10, 'guild_id' => 4, 'rank_id' => $ranksByLevel[5] ?? 1, 'xp' => 4200, 'is_primary' => 1]  // Specialist
    ];

    foreach ($profileGuilds as $pg) {
        $pdo->prepare("INSERT INTO profile_guilds (profile_id, guild_id, rank_id, xp, is_primary) VALUES (?, ?, ?, ?, ?)")
            ->execute([$pg['profile_id'], $pg['guild_id'], $pg['rank_id'], $pg['xp'], $pg['is_primary']]);
    }
    echo "  ✓ Created " . count($profileGuilds) . " profile guild memberships\n\n";

    // ========================================
    // SEED BOUNTIES
    // ========================================
    echo "Seeding bounties...\n";
    $bounties = [
        [
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Build React Dashboard for Analytics',
            'description' => 'Need an experienced React developer to build a modern analytics dashboard with charts and data visualization.',
            'budget_min' => 2000.00,
            'budget_max' => 3500.00,
            'deadline' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'open',
            'payment_type' => 'fixed',
            'estimated_hours' => 40,
            'spots' => 1,
            'remote_ok' => 1
        ],
        [
            'user_id' => 8,
            'category_id' => 3,
            'title' => 'UI/UX Design for Mobile App',
            'description' => 'Looking for a talented UI/UX designer to create mockups and prototypes for a new mobile fitness app.',
            'budget_min' => 1500.00,
            'budget_max' => 2500.00,
            'deadline' => date('Y-m-d', strtotime('+21 days')),
            'status' => 'open',
            'payment_type' => 'fixed',
            'estimated_hours' => 30,
            'spots' => 1,
            'remote_ok' => 1
        ],
        [
            'user_id' => 9,
            'category_id' => 4,
            'title' => 'Write Technical Documentation',
            'description' => 'Need a technical writer to document our API and create user guides for our SaaS platform.',
            'budget_min' => 800.00,
            'budget_max' => 1200.00,
            'deadline' => date('Y-m-d', strtotime('+14 days')),
            'status' => 'open',
            'payment_type' => 'hourly',
            'estimated_hours' => 15,
            'spots' => 1,
            'remote_ok' => 1
        ],
        [
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'PHP Laravel API Development',
            'description' => 'Build RESTful API using Laravel for e-commerce platform. Must have experience with payment gateways.',
            'budget_min' => 3000.00,
            'budget_max' => 5000.00,
            'deadline' => date('Y-m-d', strtotime('+45 days')),
            'status' => 'open',
            'payment_type' => 'fixed',
            'estimated_hours' => 60,
            'spots' => 2,
            'remote_ok' => 1
        ],
        [
            'user_id' => 8,
            'category_id' => 5,
            'title' => 'SEO Optimization Campaign',
            'description' => 'Optimize website for search engines, improve rankings, and increase organic traffic.',
            'budget_min' => 1000.00,
            'budget_max' => 2000.00,
            'deadline' => date('Y-m-d', strtotime('+60 days')),
            'status' => 'in_progress',
            'payment_type' => 'hourly',
            'estimated_hours' => 25,
            'spots' => 1,
            'remote_ok' => 1
        ],
        [
            'user_id' => 9,
            'category_id' => 6,
            'title' => 'Data Analysis and Machine Learning Model',
            'description' => 'Analyze customer data and build predictive ML model for churn prevention.',
            'budget_min' => 4000.00,
            'budget_max' => 6000.00,
            'deadline' => date('Y-m-d', strtotime('+90 days')),
            'status' => 'open',
            'payment_type' => 'fixed',
            'estimated_hours' => 80,
            'spots' => 1,
            'remote_ok' => 1
        ],
        [
            'user_id' => 1,
            'category_id' => 8,
            'title' => 'Product Photography for E-commerce',
            'description' => 'Need professional product photos for 50 items. Studio setup required.',
            'budget_min' => 1200.00,
            'budget_max' => 1800.00,
            'deadline' => date('Y-m-d', strtotime('+20 days')),
            'status' => 'completed',
            'payment_type' => 'fixed',
            'estimated_hours' => 16,
            'spots' => 1,
            'remote_ok' => 0,
            'location' => 'San Francisco, CA'
        ],
        [
            'user_id' => 8,
            'category_id' => 2,
            'title' => 'iOS App Development',
            'description' => 'Develop native iOS app for task management with cloud sync.',
            'budget_min' => 5000.00,
            'budget_max' => 8000.00,
            'deadline' => date('Y-m-d', strtotime('+120 days')),
            'status' => 'open',
            'payment_type' => 'fixed',
            'estimated_hours' => 120,
            'spots' => 1,
            'remote_ok' => 1
        ],
        [
            'user_id' => 9,
            'category_id' => 7,
            'title' => 'Business Strategy Consultation',
            'description' => 'Need help developing go-to-market strategy for new SaaS product.',
            'budget_min' => 2500.00,
            'budget_max' => 4000.00,
            'deadline' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'open',
            'payment_type' => 'hourly',
            'estimated_hours' => 30,
            'spots' => 1,
            'remote_ok' => 1
        ],
        [
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Vue.js Frontend Refactor',
            'description' => 'Refactor existing jQuery application to modern Vue.js architecture.',
            'budget_min' => 3500.00,
            'budget_max' => 5500.00,
            'deadline' => date('Y-m-d', strtotime('+60 days')),
            'status' => 'open',
            'payment_type' => 'fixed',
            'estimated_hours' => 70,
            'spots' => 1,
            'remote_ok' => 1
        ]
    ];

    foreach ($bounties as $bounty) {
        $pdo->prepare("INSERT INTO bounties (user_id, category_id, title, description, budget_min, budget_max, deadline, status, payment_type, estimated_hours, spots, remote_ok, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $bounty['user_id'],
                $bounty['category_id'],
                $bounty['title'],
                $bounty['description'],
                $bounty['budget_min'],
                $bounty['budget_max'],
                $bounty['deadline'],
                $bounty['status'],
                $bounty['payment_type'],
                $bounty['estimated_hours'],
                $bounty['spots'],
                $bounty['remote_ok'],
                $bounty['location'] ?? null
            ]);
    }
    echo "  ✓ Created " . count($bounties) . " bounties\n\n";

    // ========================================
    // SEED BOUNTY SKILLS
    // ========================================
    echo "Seeding bounty_skills...\n";
    $bountySkills = [
        [1, 2], [1, 3], [1, 7], // React Dashboard
        [2, 13], [2, 14], [2, 15], // UI/UX Design
        [3, 20], // Technical Writing
        [4, 1], [4, 6], // Laravel API
        [5, 21], // SEO
        [6, 24], [6, 25], [6, 26], // Data Science
        [7, 29], [7, 30], // Product Photography
        [8, 9], // iOS Development
        [9, 28], // Business Strategy
        [10, 2], [10, 4] // Vue.js
    ];

    foreach ($bountySkills as $bs) {
        $pdo->prepare("INSERT INTO bounty_skills (bounty_id, skill_id) VALUES (?, ?)")
            ->execute([$bs[0], $bs[1]]);
    }
    echo "  ✓ Created " . count($bountySkills) . " bounty skill requirements\n\n";

    // ========================================
    // SEED APPLICATIONS
    // ========================================
    echo "Seeding applications...\n";
    $applications = [
        [
            'bounty_id' => 1,
            'profile_id' => 2,
            'cover_letter' => 'I have 5 years of React experience and have built several analytics dashboards. I can deliver this within your timeline.',
            'proposed_rate' => 2800.00,
            'status' => 'pending'
        ],
        [
            'bounty_id' => 1,
            'profile_id' => 5,
            'cover_letter' => 'Full-stack developer here. While I specialize in backend, I have strong React skills and would love to work on this dashboard.',
            'proposed_rate' => 3200.00,
            'status' => 'pending'
        ],
        [
            'bounty_id' => 2,
            'profile_id' => 3,
            'cover_letter' => 'UI/UX designer with extensive mobile app experience. I use Figma and can create interactive prototypes for user testing.',
            'proposed_rate' => 2200.00,
            'status' => 'accepted'
        ],
        [
            'bounty_id' => 3,
            'profile_id' => 4,
            'cover_letter' => 'Technical writer specializing in API documentation. I can create comprehensive docs and user guides.',
            'proposed_rate' => 1000.00,
            'status' => 'pending'
        ],
        [
            'bounty_id' => 4,
            'profile_id' => 1,
            'cover_letter' => 'Laravel expert with payment gateway integration experience. Have worked with Stripe, PayPal, and Square.',
            'proposed_rate' => 4500.00,
            'status' => 'pending'
        ],
        [
            'bounty_id' => 5,
            'profile_id' => 7,
            'cover_letter' => 'SEO specialist with proven track record of improving rankings. Can provide case studies.',
            'proposed_rate' => 1500.00,
            'status' => 'accepted'
        ],
        [
            'bounty_id' => 6,
            'profile_id' => 10,
            'cover_letter' => 'Data scientist with ML expertise. I have built several churn prediction models for SaaS companies.',
            'proposed_rate' => 5500.00,
            'status' => 'pending'
        ],
        [
            'bounty_id' => 7,
            'profile_id' => 6,
            'cover_letter' => 'Professional photographer with product photography experience. Have my own studio in SF.',
            'proposed_rate' => 1500.00,
            'status' => 'completed'
        ]
    ];

    foreach ($applications as $app) {
        $pdo->prepare("INSERT INTO applications (bounty_id, profile_id, cover_letter, proposed_rate, status) VALUES (?, ?, ?, ?, ?)")
            ->execute([
                $app['bounty_id'],
                $app['profile_id'],
                $app['cover_letter'],
                $app['proposed_rate'],
                $app['status']
            ]);
    }
    echo "  ✓ Created " . count($applications) . " applications\n\n";

    // Commit transaction
    $pdo->commit();

    echo "Seeding completed successfully!\n\n";

    // Verify data was inserted
    $tables = [
        'profile_statuses',
        'users',
        'categories',
        'skills',
        'guilds',
        'ranks',
        'profiles',
        'profile_skills',
        'profile_guilds',
        'bounties',
        'bounty_skills',
        'applications'
    ];

    echo "Data summary:\n";
    foreach ($tables as $table) {
        $result = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  ✓ {$table}: {$count} records\n";
    }

    echo "\n✅ Database is fully seeded!\n";
    echo "\nTest credentials:\n";
    echo "  Email: paul@nerd.biz (admin)\n";
    echo "  Email: sarah.dev@example.com\n";
    echo "  Email: mike.design@example.com\n";
    echo "  Password (all users): password123\n";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Seeding failed: " . $e->getMessage() . "\n");
}
