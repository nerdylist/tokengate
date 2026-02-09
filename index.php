<?php
require_once 'config.php';

// Sample task data
$tasks = [
    [
        'id' => 1,
        'votes' => 42,
        'category' => 'documentation',
        'due_days' => 2,
        'spots_filled' => 0,
        'spots_total' => 5,
        'price' => 500,
        'title' => 'Create comprehensive API documentation for REST endpoints',
        'description' => 'We need detailed documentation for our REST API including all endpoints, request/response examples, and authentication methods. The documentation should be clear, well-structured, and include code samples in multiple languages.',
        'tags' => ['technical writing', 'api documentation', 'rest api', 'markdown', 'swagger'],
        'location' => 'remote',
        'duration' => '3-5 days',
        'applications' => 12,
        'posted_time' => '3 hours ago'
    ],
    [
        'id' => 2,
        'votes' => 28,
        'category' => 'design',
        'due_days' => 5,
        'spots_filled' => 2,
        'spots_total' => 3,
        'price' => 750,
        'title' => 'Design modern landing page mockups for SaaS product',
        'description' => 'Looking for talented designers to create sleek, modern landing page designs for our new SaaS product. Need desktop and mobile versions with a focus on conversion optimization. Dark mode preferred with clean aesthetics similar to Linear or Vercel.',
        'tags' => ['ui design', 'figma', 'landing page', 'dark mode'],
        'location' => 'remote',
        'duration' => '5-7 days',
        'applications' => 8,
        'posted_time' => '5 hours ago'
    ],
    [
        'id' => 3,
        'votes' => 35,
        'category' => 'research',
        'due_days' => 7,
        'spots_filled' => 1,
        'spots_total' => 2,
        'price' => 1200,
        'title' => 'Conduct user research and competitive analysis for fintech app',
        'description' => 'Need experienced UX researchers to conduct user interviews, analyze competitor products, and deliver insights for our new fintech application. Deliverables include research report, user personas, and journey maps. Experience in financial services preferred.',
        'tags' => ['user research', 'competitive analysis', 'ux research', 'fintech', 'personas'],
        'location' => 'remote',
        'duration' => '7-10 days',
        'applications' => 15,
        'posted_time' => '1 day ago'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Bounties - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-title-wrapper">
                    <h1 class="page-title">
                        task bounties
                        <span class="badge badge-new">new</span>
                    </h1>
                </div>
                <p class="page-subtitle">post tasks, humans apply</p>
            </section>

            <?php include 'partials/filters.php'; ?>

            <section class="tabs-section">
                <div class="tabs">
                    <button class="tab active" data-tab="new">new</button>
                    <button class="tab" data-tab="top">top</button>
                </div>
            </section>

            <section class="tasks-section">
                <div class="tasks-list">
                    <?php foreach ($tasks as $task): ?>
                        <?php include 'partials/task-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <script src="app.js"></script>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
