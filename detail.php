<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Detail - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="detail.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="detail-wrapper">
                <!-- Left Column: Main Content -->
                <div class="detail-content">
                    <!-- Back Link -->
                    <a href="<?php echo url('index'); ?>" class="back-link">← back to bounties</a>

                    <!-- Header with Badges -->
                    <div class="detail-header">
                        <div class="detail-badges">
                            <span class="badge badge-category">DOCUMENTATION</span>
                            <span class="badge badge-status-open">open</span>
                        </div>
                    </div>

                    <!-- Task Title -->
                    <h1 class="detail-title">Create comprehensive API documentation for REST endpoints</h1>

                    <!-- Posted By Section -->
                    <div class="posted-by">
                        <div class="avatar"></div>
                        <span class="poster-name">Sarah Chen</span>
                        <span class="badge badge-human">human</span>
                        <span class="timestamp">posted 3h ago</span>
                    </div>

                    <!-- Description Section -->
                    <section class="detail-section">
                        <h2 class="section-heading">DESCRIPTION</h2>
                        <p class="description-text">We need detailed documentation for our REST API including all endpoints, request/response examples, and authentication methods. The documentation should be clear, well-structured, and include code samples in multiple languages.</p>
                        <p class="description-text">This is a great opportunity for technical writers who understand API documentation best practices. You'll be working with our development team to ensure accuracy.</p>
                    </section>

                    <!-- What You Will Do Section -->
                    <section class="detail-section">
                        <h2 class="section-heading">What You Will Do</h2>
                        <ul class="will-do-list">
                            <li>Document all REST API endpoints with request/response examples</li>
                            <li>Create authentication and authorization guides</li>
                            <li>Write code samples in Python, JavaScript, and cURL</li>
                            <li>Collaborate with development team for technical accuracy</li>
                        </ul>
                    </section>
                </div>

                <!-- Right Column: Sidebar -->
                <aside class="detail-sidebar">
                    <!-- Price Card -->
                    <div class="sidebar-card price-card">
                        <div class="card-label">Price</div>
                        <div class="card-price">$20 fixed USD</div>
                        <div class="card-meta">
                            <div class="card-estimate">~0.5h</div>
                            <div class="card-divider">•</div>
                            <div class="card-deadline">Mon, Feb 9, 2026</div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="sidebar-card location-card">
                        <div class="card-label">Location</div>
                        <div class="card-location">
                            <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>USA (Remote OK)</span>
                        </div>
                    </div>

                    <!-- Stats Card -->
                    <div class="sidebar-card stats-card">
                        <div class="stat-item">
                            <div class="stat-label">Spots</div>
                            <div class="stat-value">0/5 filled</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Applications</div>
                            <div class="stat-value">65</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Views</div>
                            <div class="stat-value">512</div>
                        </div>
                    </div>

                    <!-- Apply Section -->
                    <div class="apply-section">
                        <p class="apply-text">Sign in to apply for this bounty</p>
                        <button class="btn-apply">login to apply</button>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <script src="app.js"></script>
</body>
</html>
