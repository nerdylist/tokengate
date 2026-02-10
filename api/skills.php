<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // GET requests
    if ($method === 'GET') {
        switch ($action) {
            case 'search':
                // Search for skills by query term
                $query = $_GET['query'] ?? '';

                if (empty($query)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Missing required parameter: query']);
                    exit;
                }

                // Search approved skills
                $sql = "SELECT id, name, 'approved' as type
                        FROM skills
                        WHERE name LIKE ?
                        ORDER BY name ASC
                        LIMIT 20";

                $results = $db->query($sql, ['%' . $query . '%']);

                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $results]);
                break;

            case 'user_skills':
                // Get user's approved and pending skills
                $profileId = $_GET['profile_id'] ?? '';

                if (empty($profileId)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Missing required parameter: profile_id']);
                    exit;
                }

                // Get approved skills from profile_skills
                $sql = "SELECT s.id, s.name, s.slug, ps.proficiency_level, 'approved' as type
                        FROM profile_skills ps
                        JOIN skills s ON ps.skill_id = s.id
                        WHERE ps.profile_id = ? AND s.status = 'approved'
                        ORDER BY s.name ASC";

                $skills = $db->query($sql, [$profileId]);

                // Get pending skills submitted by this profile
                $pendingSql = "SELECT id, name, slug, 'pending' as type, created_at
                               FROM skills
                               WHERE submitted_by_profile_id = ? AND status = 'pending'
                               ORDER BY name ASC";

                $pendingSkills = $db->query($pendingSql, [$profileId]);
                $skills = array_merge($skills, $pendingSkills);

                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $skills]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                break;
        }
        exit;
    }

    // POST requests
    if ($method === 'POST') {
        // Parse JSON input if content-type is JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $_POST = $input ?? $_POST;
        }

        switch ($action) {
            case 'add_to_profile':
                // Add skill to user's profile
                $skillName = $_POST['skill_name'] ?? '';
                $profileId = $_POST['profile_id'] ?? '';

                if (empty($skillName) || empty($profileId)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Missing required parameters: skill_name, profile_id']);
                    exit;
                }

                // Check if skill exists in skills table (case-insensitive)
                $existingSkill = $db->queryOne(
                    "SELECT id FROM skills WHERE LOWER(name) = LOWER(?)",
                    [$skillName]
                );

                if ($existingSkill) {
                    // Skill exists - check if it's approved
                    $skillId = $existingSkill['id'];

                    // Check if skill is approved
                    $skillStatus = $db->queryOne(
                        "SELECT status FROM skills WHERE id = ?",
                        [$skillId]
                    );

                    if ($skillStatus && $skillStatus['status'] === 'approved') {
                        // Check if already added to profile
                        $alreadyAdded = $db->queryOne(
                            "SELECT 1 FROM profile_skills WHERE profile_id = ? AND skill_id = ?",
                            [$profileId, $skillId]
                        );

                        if ($alreadyAdded) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'error' => 'Skill already added to profile']);
                            exit;
                        }

                        // Add to profile
                        $db->execute(
                            "INSERT INTO profile_skills (profile_id, skill_id, proficiency_level) VALUES (?, ?, 'intermediate')",
                            [$profileId, $skillId]
                        );

                        http_response_code(201);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Skill added to profile successfully',
                            'data' => ['skill_id' => $skillId, 'type' => 'approved']
                        ]);
                    } else {
                        // Skill exists but is pending or rejected
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'This skill is not yet approved. Please wait for admin approval.'
                        ]);
                    }
                } else {
                    // Skill doesn't exist - add to skills table with status='pending'
                    // Generate slug from name
                    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($skillName)));

                    // Check if slug already exists (prevent duplicates)
                    $existingSlug = $db->queryOne(
                        "SELECT id FROM skills WHERE slug = ?",
                        [$slug]
                    );

                    if ($existingSlug) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'A skill with a similar name already exists'
                        ]);
                        exit;
                    }

                    // Use category_id = 1 as default
                    $db->execute(
                        "INSERT INTO skills (name, slug, category_id, submitted_by_profile_id, status) VALUES (?, ?, 1, ?, 'pending')",
                        [$skillName, $slug, $profileId]
                    );

                    $skillId = $db->getConnection()->lastInsertId();

                    http_response_code(201);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Skill submitted for approval',
                        'data' => ['skill_id' => $skillId, 'type' => 'pending']
                    ]);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                break;
        }
        exit;
    }

    // DELETE requests
    if ($method === 'DELETE' || ($method === 'POST' && $action === 'remove_from_profile')) {
        // Parse JSON input if content-type is JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $_POST = $input ?? $_POST;
        }

        // Remove skill from user's profile
        $profileId = $_POST['profile_id'] ?? $_GET['profile_id'] ?? '';
        $skillId = $_POST['skill_id'] ?? $_GET['skill_id'] ?? '';

        if (empty($profileId) || empty($skillId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required parameters: profile_id, skill_id']);
            exit;
        }

        // Remove from profile_skills
        $affectedRows = $db->execute(
            "DELETE FROM profile_skills WHERE profile_id = ? AND skill_id = ?",
            [$profileId, $skillId]
        );

        if ($affectedRows === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Skill not found in profile']);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Skill removed from profile successfully',
            'data' => ['affected_rows' => $affectedRows]
        ]);
        exit;
    }

    // Method not allowed
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);

} catch (Exception $e) {
    // Determine appropriate status code
    $errorMessage = $e->getMessage();

    if (strpos($errorMessage, 'Authentication required') !== false) {
        http_response_code(401);
    } elseif (strpos($errorMessage, 'Permission denied') !== false || strpos($errorMessage, "don't own") !== false) {
        http_response_code(403);
    } elseif (strpos($errorMessage, 'not found') !== false) {
        http_response_code(404);
    } elseif (strpos($errorMessage, 'Missing required') !== false || strpos($errorMessage, 'Invalid') !== false || strpos($errorMessage, 'already') !== false) {
        http_response_code(400);
    } else {
        http_response_code(500);
    }

    echo json_encode(['success' => false, 'error' => $errorMessage]);
}
