<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/ProfileGuild.php';

// Check if user is logged in
if (!Auth::check()) {
    header('Location: ' . url('connect'));
    exit;
}

// Get guild_id from request
$guild_id = $_GET['id'] ?? null;

if (!$guild_id) {
    header('Location: ' . url('my-guilds'));
    exit;
}

// Admin bypass - admins can access any guild
if (Auth::isAdmin()) {
    return;
}

// Get user's profile
require_once __DIR__ . '/../classes/Profile.php';
$profileModel = new Profile();
$userProfile = $profileModel->where('user_id', '=', Auth::id())->first();

if (!$userProfile) {
    header('Location: ' . url('browse'));
    exit;
}

// Check if user is a member of this guild
$profileGuildModel = new ProfileGuild();
$membership = $profileGuildModel->where('profile_id', '=', $userProfile['id'])
                                 ->where('guild_id', '=', $guild_id)
                                 ->first();

if (!$membership) {
    // Not a member of this guild
    header('Location: ' . url('my-guilds'));
    exit;
}
