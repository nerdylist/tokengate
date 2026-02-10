<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/User.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

switch ($action) {
    case 'register':
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Email and password are required']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid email format']);
            exit;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters long']);
            exit;
        }
        
        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
            exit;
        }
        
        $userModel = new User();
        $existing = $userModel->findByEmail($email);
        
        if ($existing) {
            echo json_encode(['success' => false, 'error' => 'An account with this email already exists']);
            exit;
        }
        
        $userData = User::createUser($email, $password);
        
        if (!$userData) {
            echo json_encode(['success' => false, 'error' => 'Failed to create user account']);
            exit;
        }
        
        if (Auth::login($email, $password)) {
            echo json_encode([
                'success' => true,
                'message' => 'Account created successfully!',
                'redirect' => url('index')
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Account created! Please log in.',
                'redirect' => url('connect')
            ]);
        }
        break;
    
    case 'login':
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Email and password are required']);
            exit;
        }
        
        if (Auth::login($email, $password)) {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => url('index')
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        }
        break;
    
    case 'logout':
        Auth::logout();
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => url('connect')
        ]);
        break;
    
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
