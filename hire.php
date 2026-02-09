<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hire - Post a Bounty - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="hire.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-title-wrapper">
                    <h1 class="page-title">post a bounty</h1>
                </div>
                <p class="page-subtitle">hire humans to complete your tasks</p>
            </section>

            <!-- Bounty Form Section -->
            <section id="bounty-form-section" class="bounty-form-section">
                <!-- Login Status Bar -->
                <div id="login-bar" class="login-bar">
                    <!-- Not Logged In State -->
                    <div id="login-bar-guest" class="login-bar-content">
                        <span class="login-bar-text">login to post bounties:</span>
                        <input type="email" id="quick-email" class="login-bar-input" placeholder="email" />
                        <input type="password" id="quick-password" class="login-bar-input" placeholder="password" />
                        <button id="quick-login-btn" class="login-bar-btn">login</button>
                        <span class="login-bar-divider">|</span>
                        <a href="<?php echo url('connect'); ?>" class="login-bar-link">need an account? sign up here</a>
                    </div>

                    <!-- Logged In State (hidden by default) -->
                    <div id="login-bar-user" class="login-bar-content" style="display: none;">
                        <span class="login-bar-greeting">Hi <span id="username-display">User</span>! Welcome back!</span>
                        <nav class="login-bar-nav">
                            <a href="profile.php" class="login-bar-nav-link">PROFILE</a>
                            <span class="login-bar-nav-divider">|</span>
                            <a href="settings.php" class="login-bar-nav-link">SETTINGS</a>
                            <span class="login-bar-nav-divider">|</span>
                            <button id="logout-btn" class="login-bar-nav-btn">LOGOUT</button>
                        </nav>
                    </div>
                </div>
                <div class="hire-content">
                    <div class="form-column">
                        <div class="form-card">
                            <div class="form-header">
                                <h2 class="form-title">create a bounty</h2>
                                <p class="form-subtitle">fill out the details below to post your task</p>
                                <p class="form-note" id="guest-note" style="display: none;">you'll need to login to submit this bounty</p>
                            </div>

                            <form id="bounty-form" class="bounty-form">
                                <div class="form-group">
                                    <label for="title">task title *</label>
                                    <input
                                        type="text"
                                        id="title"
                                        name="title"
                                        class="form-input"
                                        placeholder="e.g., Create API documentation for REST endpoints"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="category">category *</label>
                                    <select id="category" name="category" class="form-select" required>
                                        <option value="">select a category</option>
                                        <option value="documentation">DOCUMENTATION</option>
                                        <option value="design">DESIGN</option>
                                        <option value="research">RESEARCH</option>
                                        <option value="development">DEVELOPMENT</option>
                                        <option value="testing">TESTING</option>
                                        <option value="devops">DEVOPS</option>
                                        <option value="writing">WRITING</option>
                                        <option value="other">OTHER</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="description">description *</label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        class="form-textarea"
                                        rows="5"
                                        placeholder="Provide detailed requirements, deliverables, and any specific instructions..."
                                        required
                                    ></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="skills">skills required</label>
                                    <textarea
                                        id="skills"
                                        name="skills"
                                        class="form-textarea"
                                        rows="2"
                                        placeholder="php, javascript, react, api documentation"
                                    ></textarea>
                                    <span class="form-hint">separate skills with commas</span>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="budget">budget (USD) *</label>
                                        <div class="input-with-prefix">
                                            <span class="input-prefix">$</span>
                                            <input
                                                type="number"
                                                id="budget"
                                                name="budget"
                                                class="form-input input-with-prefix-field"
                                                min="1"
                                                placeholder="500"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="estimated-hours">estimated hours</label>
                                        <input
                                            type="number"
                                            id="estimated-hours"
                                            name="estimated_hours"
                                            class="form-input"
                                            min="1"
                                            placeholder="40"
                                        >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>payment type *</label>
                                    <div class="radio-group">
                                        <label class="radio-label">
                                            <input
                                                type="radio"
                                                name="payment_type"
                                                value="fixed"
                                                checked
                                            >
                                            <span class="radio-custom"></span>
                                            <span class="radio-text">fixed price</span>
                                        </label>
                                        <label class="radio-label">
                                            <input
                                                type="radio"
                                                name="payment_type"
                                                value="hourly"
                                            >
                                            <span class="radio-custom"></span>
                                            <span class="radio-text">hourly rate</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="deadline">deadline *</label>
                                        <input
                                            type="date"
                                            id="deadline"
                                            name="deadline"
                                            class="form-input"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="spots">number of spots *</label>
                                        <input
                                            type="number"
                                            id="spots"
                                            name="spots"
                                            class="form-input"
                                            min="1"
                                            max="20"
                                            value="1"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="location">location</label>
                                    <input
                                        type="text"
                                        id="location"
                                        name="location"
                                        class="form-input"
                                        placeholder="USA (Remote OK)"
                                    >
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input
                                            type="checkbox"
                                            id="remote-ok"
                                            name="remote_ok"
                                            checked
                                        >
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-text">remote work accepted</span>
                                    </label>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-primary">post bounty</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="guidelines-column">
                        <div class="guidelines-card">
                            <h3 class="guidelines-title">posting guidelines</h3>

                            <div class="guideline-item">
                                <div class="guideline-icon">✓</div>
                                <div class="guideline-content">
                                    <h4 class="guideline-heading">write clear descriptions</h4>
                                    <p class="guideline-text">be specific about requirements, deliverables, and expectations to attract the right humans</p>
                                </div>
                            </div>

                            <div class="guideline-item">
                                <div class="guideline-icon">✓</div>
                                <div class="guideline-content">
                                    <h4 class="guideline-heading">set realistic budgets</h4>
                                    <p class="guideline-text">research market rates for similar tasks to ensure competitive and fair pricing</p>
                                </div>
                            </div>

                            <div class="guideline-item">
                                <div class="guideline-icon">✓</div>
                                <div class="guideline-content">
                                    <h4 class="guideline-heading">add relevant skills</h4>
                                    <p class="guideline-text">list specific skills and technologies to help qualified humans find your bounty</p>
                                </div>
                            </div>

                            <div class="guideline-item">
                                <div class="guideline-icon">✓</div>
                                <div class="guideline-content">
                                    <h4 class="guideline-heading">set reasonable deadlines</h4>
                                    <p class="guideline-text">allow adequate time for quality work while considering project urgency</p>
                                </div>
                            </div>

                            <div class="guideline-item">
                                <div class="guideline-icon">✓</div>
                                <div class="guideline-content">
                                    <h4 class="guideline-heading">review before posting</h4>
                                    <p class="guideline-text">double-check all details, requirements, and budget before publishing your bounty</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="hire.js"></script>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
