<?php
/**
 * Comprehensive Seed Data Script
 * Populates database with realistic users, profiles, bounties, and applications
 *
 * Usage:
 *   php database/seed_data.php
 *   php database/seed_data.php --fresh (clear and reseed)
 */

// CLI only
if (PHP_SAPI !== 'cli') {
    die('This script can only be run from the command line.');
}

// Include database connection
$pdo = require_once __DIR__ . '/connection.php';

// Parse command line arguments
$fresh = in_array('--fresh', $argv ?? []);

// Helper function to generate unique profile ID
function generateProfileId($pdo) {
    $maxAttempts = 100;
    for ($i = 0; $i < $maxAttempts; $i++) {
        $letters = '';
        for ($j = 0; $j < 3; $j++) {
            $letters .= chr(rand(65, 90)); // A-Z
        }
        $digits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $profileId = $letters . '-' . $digits;

        // Check uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM profiles WHERE profile_id = ?");
        $stmt->execute([$profileId]);
        if ($stmt->fetchColumn() == 0) {
            return $profileId;
        }
    }
    throw new Exception('Unable to generate unique profile ID');
}

// Seed data arrays
$users = [
    ['name' => 'Sarah Chen', 'email' => 'sarah.chen@example.com', 'bio' => 'Full-stack developer with 8 years of experience in React and Node.js. Passionate about building scalable web applications and mentoring junior developers.', 'hourly_rate' => 150, 'status_id' => 1, 'skills' => [2, 4, 5], 'proficiency' => ['expert', 'expert', 'advanced']],
    ['name' => 'Marcus Rodriguez', 'email' => 'marcus.rodriguez@example.com', 'bio' => 'Senior PHP developer specializing in e-commerce platforms and API development. Built systems handling millions of transactions.', 'hourly_rate' => 125, 'status_id' => 2, 'skills' => [1, 6, 2], 'proficiency' => ['expert', 'advanced', 'intermediate']],
    ['name' => 'Emma Watson', 'email' => 'emma.watson@example.com', 'bio' => 'UI/UX designer focused on creating intuitive and accessible user experiences. Strong background in design systems and mobile-first design.', 'hourly_rate' => 95, 'status_id' => 1, 'skills' => [7, 8, 9], 'proficiency' => ['expert', 'advanced', 'intermediate']],
    ['name' => 'James Kim', 'email' => 'james.kim@example.com', 'bio' => 'Product designer with expertise in user research and prototyping. Love solving complex problems through thoughtful design.', 'hourly_rate' => 110, 'status_id' => 1, 'skills' => [7, 8], 'proficiency' => ['advanced', 'expert']],
    ['name' => 'Alex Thompson', 'email' => 'alex.thompson@example.com', 'bio' => 'Technical writer and documentation specialist. Helping developers communicate complex concepts clearly for over 6 years.', 'hourly_rate' => 75, 'status_id' => 1, 'skills' => [13, 14], 'proficiency' => ['expert', 'advanced']],
    ['name' => 'Priya Patel', 'email' => 'priya.patel@example.com', 'bio' => 'Content strategist and copywriter with a knack for SEO-optimized content. Former journalist turned digital marketing specialist.', 'hourly_rate' => 85, 'status_id' => 1, 'skills' => [14, 10, 12], 'proficiency' => ['expert', 'advanced', 'intermediate']],
    ['name' => 'David Morrison', 'email' => 'david.morrison@example.com', 'bio' => 'Python developer and data engineer. Building data pipelines and automation tools for startups and enterprises.', 'hourly_rate' => 140, 'status_id' => 4, 'skills' => [3, 5], 'proficiency' => ['expert', 'intermediate']],
    ['name' => 'Lisa Anderson', 'email' => 'lisa.anderson@example.com', 'bio' => 'Digital marketing strategist specializing in social media growth and brand awareness campaigns. Results-driven and data-focused.', 'hourly_rate' => 90, 'status_id' => 1, 'skills' => [11, 10, 12], 'proficiency' => ['expert', 'advanced', 'advanced']],
    ['name' => 'Ryan Foster', 'email' => 'ryan.foster@example.com', 'bio' => 'Frontend developer passionate about React and modern CSS. Creating beautiful, performant web experiences since 2018.', 'hourly_rate' => 105, 'status_id' => 2, 'skills' => [4, 2, 6], 'proficiency' => ['expert', 'advanced', 'expert']],
    ['name' => 'Maya Johnson', 'email' => 'maya.johnson@example.com', 'bio' => 'Graphic designer and brand identity specialist. Transforming business visions into memorable visual identities.', 'hourly_rate' => 80, 'status_id' => 1, 'skills' => [9, 7], 'proficiency' => ['expert', 'intermediate']],
    ['name' => 'Kevin Lee', 'email' => 'kevin.lee@example.com', 'bio' => 'Full-stack JavaScript developer with expertise in MERN stack. Love building real-time applications and APIs.', 'hourly_rate' => 120, 'status_id' => 1, 'skills' => [2, 4, 5], 'proficiency' => ['expert', 'advanced', 'expert']],
    ['name' => 'Sophie Martinez', 'email' => 'sophie.martinez@example.com', 'bio' => 'SEO specialist and content marketer helping businesses rank higher and convert better. Data-driven approach to digital growth.', 'hourly_rate' => 70, 'status_id' => 1, 'skills' => [10, 12, 14], 'proficiency' => ['expert', 'advanced', 'intermediate']],
    ['name' => 'Tom Harrison', 'email' => 'tom.harrison@example.com', 'bio' => 'Junior web developer eager to learn and contribute. Strong foundation in JavaScript and responsive design principles.', 'hourly_rate' => 45, 'status_id' => 1, 'skills' => [2, 6, 4], 'proficiency' => ['intermediate', 'intermediate', 'beginner']],
    ['name' => 'Nina Kowalski', 'email' => 'nina.kowalski@example.com', 'bio' => 'UX researcher with psychology background. Conducting user interviews and usability testing to inform design decisions.', 'hourly_rate' => 100, 'status_id' => 3, 'skills' => [8, 7], 'proficiency' => ['expert', 'advanced']],
    ['name' => 'Carlos Diaz', 'email' => 'carlos.diaz@example.com', 'bio' => 'Backend developer specializing in PHP and database optimization. Building robust server-side solutions for high-traffic applications.', 'hourly_rate' => 115, 'status_id' => 1, 'skills' => [1, 2, 3], 'proficiency' => ['expert', 'intermediate', 'beginner']],
];

$bounties = [
    ['title' => 'Build E-commerce Checkout Flow', 'description' => 'Need an experienced React developer to build a multi-step checkout flow with payment integration. Must have experience with Stripe API and form validation. Project includes cart review, shipping info, payment processing, and order confirmation.', 'category_id' => 1, 'budget_min' => 2000, 'budget_max' => 3000, 'deadline_days' => 21, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [4, 2]],
    ['title' => 'Design Mobile App UI Kit', 'description' => 'Looking for a UI designer to create a comprehensive mobile app UI kit for a fitness tracking application. Deliverables include design system, component library, and prototypes for 15+ screens. Must be proficient in Figma.', 'category_id' => 2, 'budget_min' => 1500, 'budget_max' => 2500, 'deadline_days' => 30, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [7, 9]],
    ['title' => 'Write Technical Documentation for API', 'description' => 'Seeking technical writer to document our REST API. Must understand API concepts and be able to write clear, concise documentation with code examples. Includes endpoint descriptions, authentication guides, and integration tutorials.', 'category_id' => 4, 'budget_min' => 800, 'budget_max' => 1200, 'deadline_days' => 14, 'status' => 'open', 'payment_type' => 'hourly', 'estimated_hours' => 20, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [13]],
    ['title' => 'SEO Content Strategy & Implementation', 'description' => 'Need an SEO expert to develop and implement a content strategy for our SaaS blog. Includes keyword research, content calendar, and writing 10 optimized blog posts. Must have proven track record of improving organic traffic.', 'category_id' => 3, 'budget_min' => 2500, 'budget_max' => 3500, 'deadline_days' => 60, 'status' => 'in_progress', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [10, 12, 14]],
    ['title' => 'Migrate PHP Application to Laravel', 'description' => 'Looking for experienced PHP developer to migrate legacy PHP application to Laravel framework. Must have deep Laravel knowledge and experience with database migrations. Code refactoring and testing required.', 'category_id' => 1, 'budget_min' => 3500, 'budget_max' => 5000, 'deadline_days' => 45, 'status' => 'open', 'payment_type' => 'hourly', 'estimated_hours' => 80, 'spots' => 2, 'location' => null, 'remote_ok' => 1, 'skills' => [1, 2]],
    ['title' => 'Social Media Campaign Management', 'description' => 'Need social media manager for 3-month campaign across Instagram, Twitter, and LinkedIn. Responsibilities include content creation, community management, and monthly analytics reports. Experience with B2B marketing preferred.', 'category_id' => 3, 'budget_min' => 1800, 'budget_max' => 2400, 'deadline_days' => 90, 'status' => 'open', 'payment_type' => 'hourly', 'estimated_hours' => 60, 'spots' => 1, 'location' => 'San Francisco, CA', 'remote_ok' => 1, 'skills' => [11, 12]],
    ['title' => 'UX Research for SaaS Dashboard', 'description' => 'Seeking UX researcher to conduct user interviews and usability testing for our analytics dashboard. Deliverables include research plan, user interview synthesis, usability test results, and actionable recommendations.', 'category_id' => 2, 'budget_min' => 1200, 'budget_max' => 1800, 'deadline_days' => 21, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [8, 7]],
    ['title' => 'Build Python Data Pipeline', 'description' => 'Need Python developer to build automated data pipeline for processing customer analytics. Must have experience with pandas, SQL, and task scheduling. Pipeline will run daily to aggregate data from multiple sources.', 'category_id' => 1, 'budget_min' => 2200, 'budget_max' => 3000, 'deadline_days' => 28, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [3]],
    ['title' => 'Design Brand Identity Package', 'description' => 'Looking for graphic designer to create complete brand identity for new startup. Includes logo design, color palette, typography, business cards, and brand guidelines. Must provide 3 initial concepts.', 'category_id' => 2, 'budget_min' => 1000, 'budget_max' => 1500, 'deadline_days' => 21, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [9]],
    ['title' => 'React Native Mobile App Development', 'description' => 'Seeking React Native developer to build cross-platform mobile app for local services marketplace. Must have portfolio of published apps and experience with maps integration, push notifications, and in-app payments.', 'category_id' => 1, 'budget_min' => 4500, 'budget_max' => 6000, 'deadline_days' => 75, 'status' => 'in_progress', 'payment_type' => 'hourly', 'estimated_hours' => 120, 'spots' => 2, 'location' => 'Austin, TX', 'remote_ok' => 1, 'skills' => [4, 2]],
    ['title' => 'Copywriting for Product Launch', 'description' => 'Need copywriter for upcoming product launch. Deliverables include landing page copy, email sequence, ad copy, and social media posts. Must understand conversion optimization and have B2B SaaS experience.', 'category_id' => 4, 'budget_min' => 1200, 'budget_max' => 1800, 'deadline_days' => 14, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [14, 12]],
    ['title' => 'WordPress Site Speed Optimization', 'description' => 'Looking for developer to optimize WordPress site performance. Currently loading in 8+ seconds, need to get under 3 seconds. Includes image optimization, caching setup, code minification, and CDN configuration.', 'category_id' => 1, 'budget_min' => 500, 'budget_max' => 800, 'deadline_days' => 7, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [1, 2, 6]],
    ['title' => 'UI Design for Dashboard Analytics', 'description' => 'Need UI designer to redesign our analytics dashboard interface. Current design is cluttered and hard to navigate. Looking for modern, clean design with focus on data visualization and user-friendly navigation.', 'category_id' => 2, 'budget_min' => 1800, 'budget_max' => 2500, 'deadline_days' => 30, 'status' => 'open', 'payment_type' => 'hourly', 'estimated_hours' => 40, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [7, 9]],
    ['title' => 'Write User Onboarding Email Sequence', 'description' => 'Seeking copywriter to create 7-email onboarding sequence for new users. Emails should educate, engage, and drive feature adoption. Must understand email marketing best practices and conversion copywriting.', 'category_id' => 4, 'budget_min' => 600, 'budget_max' => 900, 'deadline_days' => 10, 'status' => 'completed', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [14]],
    ['title' => 'Node.js API Development', 'description' => 'Need backend developer to build RESTful API for mobile app. Must include authentication, data validation, rate limiting, and comprehensive error handling. Experience with Express.js and PostgreSQL required.', 'category_id' => 1, 'budget_min' => 2800, 'budget_max' => 3800, 'deadline_days' => 35, 'status' => 'in_progress', 'payment_type' => 'hourly', 'estimated_hours' => 70, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [5, 2]],
    ['title' => 'Content Calendar & Blog Posts', 'description' => 'Looking for content strategist to plan 3-month content calendar and write 12 blog posts focused on digital marketing trends. Must have strong research skills and ability to write engaging, SEO-optimized content.', 'category_id' => 3, 'budget_min' => 1500, 'budget_max' => 2200, 'deadline_days' => 60, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [12, 14, 10]],
    ['title' => 'CSS Animations & Microinteractions', 'description' => 'Seeking frontend developer to add polished animations and microinteractions to our web app. Must have strong CSS skills and understanding of performance. Examples needed in portfolio.', 'category_id' => 1, 'budget_min' => 800, 'budget_max' => 1200, 'deadline_days' => 14, 'status' => 'open', 'payment_type' => 'hourly', 'estimated_hours' => 25, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [6, 2]],
    ['title' => 'User Testing Sessions (In-Person)', 'description' => 'Need UX researcher to conduct 10 in-person user testing sessions in New York. Will provide prototype and test script. Researcher responsible for recruiting participants, conducting sessions, and delivering insights report.', 'category_id' => 2, 'budget_min' => 2000, 'budget_max' => 2800, 'deadline_days' => 21, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 1, 'location' => 'New York, NY', 'remote_ok' => 0, 'skills' => [8]],
    ['title' => 'Social Media Graphics Bundle', 'description' => 'Looking for graphic designer to create 30 social media graphics for Instagram and Facebook. Must follow brand guidelines and create engaging, on-brand visuals. Fast turnaround needed.', 'category_id' => 2, 'budget_min' => 400, 'budget_max' => 600, 'deadline_days' => 7, 'status' => 'open', 'payment_type' => 'fixed', 'estimated_hours' => null, 'spots' => 2, 'location' => null, 'remote_ok' => 1, 'skills' => [9, 11]],
    ['title' => 'Technical Support Documentation', 'description' => 'Need technical writer to create comprehensive support documentation for software product. Includes user guides, troubleshooting articles, FAQ, and video script outlines. Must be able to understand technical concepts quickly.', 'category_id' => 4, 'budget_min' => 1200, 'budget_max' => 1600, 'deadline_days' => 30, 'status' => 'closed', 'payment_type' => 'hourly', 'estimated_hours' => 35, 'spots' => 1, 'location' => null, 'remote_ok' => 1, 'skills' => [13]],
];

$applications = [
    ['bounty_index' => 0, 'user_index' => 0, 'cover_letter' => 'I have 8 years of experience building React applications and have integrated Stripe payments in multiple projects. I can deliver a polished, well-tested checkout flow that converts. My recent project achieved a 28% improvement in checkout completion rates.', 'proposed_rate' => 2800, 'status' => 'accepted'],
    ['bounty_index' => 0, 'user_index' => 8, 'cover_letter' => 'React developer here with strong experience in e-commerce. I\'ve built several checkout flows and understand the importance of smooth UX. Would love to discuss your requirements in detail.', 'proposed_rate' => 2500, 'status' => 'pending'],
    ['bounty_index' => 1, 'user_index' => 2, 'cover_letter' => 'I specialize in mobile UI design and have created design systems for 3 fitness apps. I can deliver a modern, accessible UI kit that makes development smooth. My Figma files are always well-organized and developer-friendly.', 'proposed_rate' => 2200, 'status' => 'pending'],
    ['bounty_index' => 1, 'user_index' => 3, 'cover_letter' => 'Product designer with extensive experience in fitness app design. I focus on creating intuitive experiences that users love. Happy to share my portfolio of similar projects.', 'proposed_rate' => 2000, 'status' => 'pending'],
    ['bounty_index' => 2, 'user_index' => 4, 'cover_letter' => 'Technical writer with 6 years of API documentation experience. I understand developers need clear, accurate docs with good examples. I can make your API documentation comprehensive and easy to follow.', 'proposed_rate' => 75, 'status' => 'accepted'],
    ['bounty_index' => 3, 'user_index' => 5, 'cover_letter' => 'SEO specialist who has helped 15+ SaaS companies grow organic traffic. I combine keyword research with content strategy to drive real results. My last client saw 200% traffic growth in 6 months.', 'proposed_rate' => 3000, 'status' => 'accepted'],
    ['bounty_index' => 4, 'user_index' => 1, 'cover_letter' => 'Senior PHP developer with extensive Laravel experience. I\'ve migrated several legacy applications to modern frameworks. I focus on clean code, proper testing, and smooth transitions with minimal downtime.', 'proposed_rate' => 125, 'status' => 'pending'],
    ['bounty_index' => 4, 'user_index' => 14, 'cover_letter' => 'Backend PHP specialist here. Laravel is my go-to framework and I have experience with complex migrations. I can help modernize your codebase while maintaining functionality.', 'proposed_rate' => 115, 'status' => 'pending'],
    ['bounty_index' => 5, 'user_index' => 7, 'cover_letter' => 'Digital marketing strategist with proven B2B results. I create engaging content, build communities, and deliver measurable growth. My data-driven approach ensures we hit campaign goals.', 'proposed_rate' => 90, 'status' => 'pending'],
    ['bounty_index' => 6, 'user_index' => 13, 'cover_letter' => 'UX researcher passionate about understanding users. I conduct thorough research that leads to actionable insights. My previous research led to a dashboard redesign that improved user satisfaction by 45%.', 'proposed_rate' => 1500, 'status' => 'pending'],
    ['bounty_index' => 7, 'user_index' => 6, 'cover_letter' => 'Python developer specializing in data pipelines and automation. I build reliable, well-tested systems that run smoothly. Experience with pandas, SQL, and various scheduling tools.', 'proposed_rate' => 2600, 'status' => 'rejected'],
    ['bounty_index' => 9, 'user_index' => 0, 'cover_letter' => 'Full-stack developer with React Native expertise. I\'ve published 5 apps on both iOS and Android. I can handle the entire development process including app store deployment.', 'proposed_rate' => 150, 'status' => 'accepted'],
    ['bounty_index' => 10, 'user_index' => 5, 'cover_letter' => 'Copywriter specializing in SaaS product launches. I write conversion-focused copy that resonates with your target audience. My copy has helped generate over $2M in revenue for clients.', 'proposed_rate' => 1600, 'status' => 'pending'],
    ['bounty_index' => 14, 'user_index' => 10, 'cover_letter' => 'Full-stack JavaScript developer with strong Node.js and Express expertise. I build secure, scalable APIs with proper authentication and error handling. Can provide references from previous API projects.', 'proposed_rate' => 120, 'status' => 'accepted'],
    ['bounty_index' => 16, 'user_index' => 8, 'cover_letter' => 'Frontend developer passionate about animations and delightful user experiences. I use CSS and JavaScript to create performant, beautiful interactions. Check out my portfolio for examples.', 'proposed_rate' => 105, 'status' => 'pending'],
];

try {
    echo "Comprehensive Seed Data Script\n";
    echo "==============================\n\n";

    // Check existing data
    echo "Checking existing data...\n";
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $profileCount = $pdo->query("SELECT COUNT(*) FROM profiles")->fetchColumn();
    $bountyCount = $pdo->query("SELECT COUNT(*) FROM bounties")->fetchColumn();
    $applicationCount = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

    echo "Found: $userCount users, $profileCount profiles, $bountyCount bounties, $applicationCount applications\n\n";

    // Handle fresh mode
    if ($fresh) {
        echo "Running in --fresh mode: clearing existing data...\n";
        $pdo->exec("DELETE FROM applications");
        $pdo->exec("DELETE FROM bounty_skills");
        $pdo->exec("DELETE FROM profile_skills");
        $pdo->exec("DELETE FROM bounties");
        $pdo->exec("DELETE FROM profiles");
        $pdo->exec("DELETE FROM users WHERE is_admin = 0");
        echo "Existing data cleared.\n\n";
    } elseif ($userCount > 0 || $profileCount > 0 || $bountyCount > 0) {
        echo "Data already exists. Use --fresh flag to clear and reseed.\n";
        echo "Exiting without changes.\n";
        exit(0);
    }

    // Begin transaction
    $pdo->beginTransaction();

    echo "Starting seed process...\n\n";

    // Password hash for all users
    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');

    // Create users and profiles
    echo "Creating users and profiles...\n";
    $createdUsers = [];
    $createdProfiles = [];

    foreach ($users as $userData) {
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, is_admin, created_at, updated_at) VALUES (?, ?, ?, 0, ?, ?)");
        $stmt->execute([$userData['email'], $passwordHash, $userData['name'], $now, $now]);
        $userId = $pdo->lastInsertId();
        $createdUsers[] = $userId;

        // Generate unique profile ID
        $profileId = generateProfileId($pdo);

        // Insert profile
        $stmt = $pdo->prepare("INSERT INTO profiles (user_id, profile_id, bio, hourly_rate, available, status_id, created_at, updated_at) VALUES (?, ?, ?, ?, 1, ?, ?, ?)");
        $stmt->execute([$userId, $profileId, $userData['bio'], $userData['hourly_rate'], $userData['status_id'], $now, $now]);
        $profileDbId = $pdo->lastInsertId();
        $createdProfiles[] = $profileDbId;

        // Insert profile skills
        foreach ($userData['skills'] as $index => $skillId) {
            $proficiency = $userData['proficiency'][$index] ?? 'intermediate';
            $stmt = $pdo->prepare("INSERT INTO profile_skills (profile_id, skill_id, proficiency_level, created_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$profileDbId, $skillId, $proficiency, $now]);
        }
    }

    echo "Created " . count($createdUsers) . " users and profiles\n";
    echo "Assigned skills to profiles\n\n";

    // Create bounties
    echo "Creating bounties...\n";
    $createdBounties = [];

    foreach ($bounties as $bountyData) {
        // Randomly assign to a created user
        $userId = $createdUsers[array_rand($createdUsers)];

        // Calculate deadline
        $deadline = date('Y-m-d', strtotime("+{$bountyData['deadline_days']} days"));

        // Insert bounty
        $stmt = $pdo->prepare("INSERT INTO bounties (user_id, category_id, title, description, budget_min, budget_max, deadline, status, payment_type, estimated_hours, spots, location, remote_ok, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $bountyData['category_id'],
            $bountyData['title'],
            $bountyData['description'],
            $bountyData['budget_min'],
            $bountyData['budget_max'],
            $deadline,
            $bountyData['status'],
            $bountyData['payment_type'],
            $bountyData['estimated_hours'],
            $bountyData['spots'],
            $bountyData['location'],
            $bountyData['remote_ok'],
            $now,
            $now
        ]);
        $bountyId = $pdo->lastInsertId();
        $createdBounties[] = $bountyId;

        // Insert bounty skills
        foreach ($bountyData['skills'] as $skillId) {
            $stmt = $pdo->prepare("INSERT INTO bounty_skills (bounty_id, skill_id, created_at) VALUES (?, ?, ?)");
            $stmt->execute([$bountyId, $skillId, $now]);
        }
    }

    echo "Created " . count($createdBounties) . " bounties\n";
    echo "Assigned skills to bounties\n\n";

    // Create applications
    echo "Creating applications...\n";
    $createdApplications = 0;

    foreach ($applications as $appData) {
        $bountyId = $createdBounties[$appData['bounty_index']];
        $profileId = $createdProfiles[$appData['user_index']];

        $stmt = $pdo->prepare("INSERT INTO applications (bounty_id, profile_id, cover_letter, proposed_rate, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $bountyId,
            $profileId,
            $appData['cover_letter'],
            $appData['proposed_rate'],
            $appData['status'],
            $now,
            $now
        ]);
        $createdApplications++;
    }

    echo "Created $createdApplications applications\n\n";

    // Commit transaction
    $pdo->commit();

    echo "==============================\n";
    echo "Seed completed successfully!\n";
    echo "==============================\n\n";

    echo "Summary:\n";
    echo "  - Users: " . count($createdUsers) . "\n";
    echo "  - Profiles: " . count($createdProfiles) . "\n";
    echo "  - Bounties: " . count($createdBounties) . "\n";
    echo "  - Applications: $createdApplications\n\n";

    echo "All users have password: password123\n";
    echo "Profile statuses distributed across Available, Busy, Away, and Unavailable\n";
    echo "Bounties include mix of fixed/hourly payment types and remote/on-site locations\n\n";

    exit(0);

} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }

    echo "\nError occurred: " . $e->getMessage() . "\n";
    echo "All changes have been rolled back.\n";
    exit(1);
}
