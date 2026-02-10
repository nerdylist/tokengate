<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Profile.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $displayName = trim($_POST['display_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $hourlyRate = $_POST['hourly_rate'] ?? null;
    $skills = $_POST['skills'] ?? []; // Array of skill IDs

    // Validate required fields
    if (empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        exit;
    }

    if (empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Password is required']);
        exit;
    }

    if (empty($displayName)) {
        echo json_encode(['success' => false, 'error' => 'Display name is required']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }

    // Validate password length (8+ characters)
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters long']);
        exit;
    }

    // Validate passwords match
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        exit;
    }

    // Check if email already exists
    $userModel = new User();
    $existingUser = $userModel->findByEmail($email);

    if ($existingUser) {
        echo json_encode(['success' => false, 'error' => 'An account with this email already exists']);
        exit;
    }

    // Validate hourly rate if provided
    if (!empty($hourlyRate)) {
        $hourlyRate = floatval($hourlyRate);
        if ($hourlyRate < 0) {
            echo json_encode(['success' => false, 'error' => 'Hourly rate must be a positive number']);
            exit;
        }
    } else {
        $hourlyRate = null;
    }

    // Create user
    $userData = User::createUser($email, $password, $displayName, false);

    if (!$userData) {
        echo json_encode(['success' => false, 'error' => 'Failed to create user account']);
        exit;
    }

    $userId = $userData['id'];

    // Generate unique profile ID
    $profileId = Profile::generateProfileId();

    // Create profile
    $profileModel = new Profile();
    $profileData = [
        'user_id' => $userId,
        'profile_id' => $profileId,
        'bio' => !empty($bio) ? $bio : null,
        'hourly_rate' => $hourlyRate,
        'available' => 1,
        'status_id' => 1 // Default status
    ];

    $profileDbId = $profileModel->create($profileData);

    if (!$profileDbId) {
        // Rollback: delete the user if profile creation failed
        $userModel->delete($userId);
        echo json_encode(['success' => false, 'error' => 'Failed to create profile']);
        exit;
    }

    // Associate skills with profile if any were selected
    if (!empty($skills) && is_array($skills)) {
        foreach ($skills as $skillId) {
            $skillId = intval($skillId);
            if ($skillId > 0) {
                $profileModel->addSkill($profileDbId, $skillId, 'intermediate');
            }
        }
    }

    // Auto-login the user
    if (!Auth::login($email, $password)) {
        // Profile created but login failed - still a success, just redirect to login
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Please log in.',
            'redirect' => url('connect')
        ]);
        exit;
    }

    // Success! Redirect to their new profile
    echo json_encode([
        'success' => true,
        'message' => 'Welcome! Your account has been created.',
        'redirect' => url('profile', ['id' => $profileId])
    ]);

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred during registration. Please try again.'
    ]);
}
