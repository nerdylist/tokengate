<?php

require_once __DIR__ . '/User.php';

class Auth
{
    private static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Load session configuration with 1-year persistence
            require_once __DIR__ . '/../config/session.php';
        }
    }

    public static function login($email, $password)
    {
        self::startSession();

        $userModel = new User();
        $userData = $userModel->findByEmail($email);

        if (!$userData) {
            return false;
        }

        // Check if this is the admin from .env and sync password
        $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : (getenv('ADMIN_EMAIL') ?: null);
        $adminPassword = defined('ADMIN_PASSWORD') ? ADMIN_PASSWORD : (getenv('ADMIN_PASSWORD') ?: null);

        if ($adminEmail && $adminPassword && $email === $adminEmail) {
            // If the provided password matches .env but NOT the database, update the database
            if ($password === $adminPassword && !$userModel->verifyPassword($password, $userData)) {
                $newHash = password_hash($adminPassword, PASSWORD_DEFAULT);
                $userModel->update($userData['id'], ['password_hash' => $newHash]);
                // Refresh user data after password update
                $userData = $userModel->findByEmail($email);
            }
        }

        if (!$userModel->verifyPassword($password, $userData)) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['is_admin'] = $userData['is_admin'];

        $userModel->updateLastLogin($userData['id']);

        return true;
    }

    public static function logout()
    {
        self::startSession();
        
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        
        return true;
    }

    public static function check()
    {
        self::startSession();
        
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function user()
    {
        self::startSession();
        
        if (!self::check()) {
            return null;
        }
        
        $userModel = new User();
        return $userModel->find($_SESSION['user_id']);
    }

    public static function id()
    {
        self::startSession();
        
        return self::check() ? $_SESSION['user_id'] : null;
    }

    public static function isAdmin()
    {
        self::startSession();
        
        if (!self::check()) {
            return false;
        }
        
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }

    public static function syncAdminUser()
    {
        $adminEmail = getenv('ADMIN_EMAIL');
        $adminPassword = getenv('ADMIN_PASSWORD');
        
        if (!$adminEmail || !$adminPassword) {
            return false;
        }
        
        $userModel = new User();
        $existing = $userModel->findByEmail($adminEmail);
        
        if ($existing) {
            if ($existing['is_admin'] != 1) {
                $userModel->update($existing['id'], ['is_admin' => 1]);
            }
            
            if (!$userModel->verifyPassword($adminPassword, $existing)) {
                $newHash = password_hash($adminPassword, PASSWORD_DEFAULT);
                $userModel->update($existing['id'], ['password_hash' => $newHash]);
            }
            
            return true;
        }
        
        $adminData = User::createUser($adminEmail, $adminPassword, 'Administrator', true);
        
        return $adminData !== false;
    }
}
