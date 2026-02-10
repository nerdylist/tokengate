<?php
/**
 * Admin Settings Management
 * Configure site settings, user settings, and system settings
 */

require_once __DIR__ . '/../middleware/admin.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Settings</h1>

            <!-- Site Settings Section -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Site Settings</h2>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="site-name">Site Name</label>
                        <input type="text" id="site-name" placeholder="RentPeople.io" disabled>
                        <p class="help-text">The name of your site displayed throughout the application</p>
                    </div>

                    <div class="form-group">
                        <label for="site-description">Site Description</label>
                        <textarea id="site-description" placeholder="A platform for finding and hiring skilled professionals" disabled></textarea>
                        <p class="help-text">A brief description of your site for SEO and social sharing</p>
                    </div>

                    <div class="form-group">
                        <label for="site-url">Site URL</label>
                        <input type="text" id="site-url" placeholder="https://rentpeople.io" disabled>
                        <p class="help-text">The primary URL for your site</p>
                    </div>

                    <div class="form-group">
                        <label for="contact-email">Contact Email</label>
                        <input type="email" id="contact-email" placeholder="contact@rentpeople.io" disabled>
                        <p class="help-text">Primary contact email for support and inquiries</p>
                    </div>

                    <button class="btn btn-primary" disabled>Save Site Settings</button>
                </div>
            </div>

            <!-- User Settings Section -->
            <div class="table-container">
                <div class="table-header">
                    <h2>User Settings</h2>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="allow-registration">Allow User Registration</label>
                        <select id="allow-registration" disabled>
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                        <p class="help-text">Allow new users to register on the platform</p>
                    </div>

                    <div class="form-group">
                        <label for="require-email-verification">Require Email Verification</label>
                        <select id="require-email-verification" disabled>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <p class="help-text">Require users to verify their email address before accessing the platform</p>
                    </div>

                    <div class="form-group">
                        <label for="default-profile-visibility">Default Profile Visibility</label>
                        <select id="default-profile-visibility" disabled>
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                        </select>
                        <p class="help-text">Default visibility setting for new user profiles</p>
                    </div>

                    <div class="form-group">
                        <label for="max-profile-skills">Max Skills Per Profile</label>
                        <input type="number" id="max-profile-skills" placeholder="10" disabled>
                        <p class="help-text">Maximum number of skills a user can add to their profile</p>
                    </div>

                    <button class="btn btn-primary" disabled>Save User Settings</button>
                </div>
            </div>

            <!-- System Settings Section -->
            <div class="table-container">
                <div class="table-header">
                    <h2>System Settings</h2>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="maintenance-mode">Maintenance Mode</label>
                        <select id="maintenance-mode" disabled>
                            <option value="0">Disabled</option>
                            <option value="1">Enabled</option>
                        </select>
                        <p class="help-text">Enable maintenance mode to prevent users from accessing the site</p>
                    </div>

                    <div class="form-group">
                        <label for="maintenance-message">Maintenance Message</label>
                        <textarea id="maintenance-message" placeholder="We're currently performing maintenance. Please check back soon." disabled></textarea>
                        <p class="help-text">Message displayed to users when maintenance mode is enabled</p>
                    </div>

                    <div class="form-group">
                        <label for="enable-logging">Enable System Logging</label>
                        <select id="enable-logging" disabled>
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                        <p class="help-text">Enable detailed logging of system events and errors</p>
                    </div>

                    <div class="form-group">
                        <label for="cache-enabled">Enable Caching</label>
                        <select id="cache-enabled" disabled>
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                        <p class="help-text">Enable caching to improve performance</p>
                    </div>

                    <button class="btn btn-primary" disabled>Save System Settings</button>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
