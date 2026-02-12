<?php
/**
 * Admin API Endpoints
 * Handles AJAX requests for admin operations
 */

require_once __DIR__ . '/../middleware/admin.php';
require_once __DIR__ . '/../controllers/AdminController.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$controller = new AdminController($db);

try {
    // Get request body for POST requests
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Fallback to POST if JSON parsing fails
        $input = $_POST;
    }

    switch ($action) {
        case 'toggle_user_admin':
            $userId = $input['user_id'] ?? null;
            $isAdmin = $input['is_admin'] ?? null;

            if ($userId === null || $isAdmin === null) {
                throw new Exception('Missing required parameters');
            }

            $result = $controller->updateUserRole($userId, $isAdmin);
            echo json_encode($result);
            break;

        case 'update_bounty_status':
            $bountyId = $input['bounty_id'] ?? null;
            $status = $input['status'] ?? null;

            if ($bountyId === null || $status === null) {
                throw new Exception('Missing required parameters');
            }

            $result = $controller->updateBountyStatus($bountyId, $status);
            echo json_encode($result);
            break;

        case 'update_application_status':
            $applicationId = $input['application_id'] ?? null;
            $status = $input['status'] ?? null;

            if ($applicationId === null || $status === null) {
                throw new Exception('Missing required parameters');
            }

            $result = $controller->updateApplicationStatus($applicationId, $status);
            echo json_encode($result);
            break;

        case 'delete_user':
            $userId = $input['user_id'] ?? null;

            if ($userId === null) {
                throw new Exception('Missing user ID');
            }

            // Prevent deleting yourself
            if ($userId == $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account');
            }

            $result = $controller->deleteUser($userId);
            echo json_encode($result);
            break;

        case 'delete_bounty':
            $bountyId = $input['bounty_id'] ?? null;

            if ($bountyId === null) {
                throw new Exception('Missing bounty ID');
            }

            $result = $controller->deleteBounty($bountyId);
            echo json_encode($result);
            break;

        case 'delete_profile':
            $profileId = $input['profile_id'] ?? null;

            if ($profileId === null) {
                throw new Exception('Missing profile ID');
            }

            $result = $controller->deleteProfile($profileId);
            echo json_encode($result);
            break;

        case 'delete_category':
            $categoryId = $input['category_id'] ?? null;

            if ($categoryId === null) {
                throw new Exception('Missing category ID');
            }

            $result = $controller->deleteCategory($categoryId);
            echo json_encode($result);
            break;

        case 'delete_skill':
            $skillId = $input['skill_id'] ?? null;

            if ($skillId === null) {
                throw new Exception('Missing skill ID');
            }

            $result = $controller->deleteSkill($skillId);
            echo json_encode($result);
            break;

        case 'delete_application':
            $applicationId = $input['application_id'] ?? null;

            if ($applicationId === null) {
                throw new Exception('Missing application ID');
            }

            $result = $controller->deleteApplication($applicationId);
            echo json_encode($result);
            break;

        case 'create_guild':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['name']) || empty($data['slug']) || empty($data['type'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            $result = $controller->createGuild($data);
            echo json_encode($result);
            break;

        case 'update_guild':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id']) || empty($data['name']) || empty($data['slug']) || empty($data['type'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            $result = $controller->updateGuild($data);
            echo json_encode($result);
            break;

        case 'delete_guild':
            $guildId = $input['guild_id'] ?? null;

            if ($guildId === null) {
                throw new Exception('Missing guild ID');
            }

            $result = $controller->deleteGuild($guildId);
            echo json_encode($result);
            break;

        case 'delete_rank':
            $rankId = $input['rank_id'] ?? null;

            if ($rankId === null) {
                throw new Exception('Missing rank ID');
            }

            $result = $controller->deleteRank($rankId);
            echo json_encode($result);
            break;

        case 'approve_skill':
            $skillId = $input['skill_id'] ?? null;

            if ($skillId === null) {
                throw new Exception('Missing skill ID');
            }

            $result = $controller->approvePendingSkill($skillId, $_SESSION['user_id']);
            echo json_encode($result);
            break;

        case 'reject_skill':
            $skillId = $input['skill_id'] ?? null;

            if ($skillId === null) {
                throw new Exception('Missing skill ID');
            }

            $result = $controller->rejectPendingSkill($skillId, $_SESSION['user_id']);
            echo json_encode($result);
            break;

        case 'create_profile_status':
            $name = $input['name'] ?? null;
            $slug = $input['slug'] ?? null;
            $color = $input['color'] ?? null;
            $sortOrder = $input['sort_order'] ?? 0;
            $isActive = $input['is_active'] ?? 1;

            if (!$name || !$slug || !$color) {
                throw new Exception('Missing required fields: name, slug, color');
            }

            $result = $controller->createProfileStatus([
                'name' => $name,
                'slug' => $slug,
                'color' => $color,
                'sort_order' => $sortOrder,
                'is_active' => $isActive
            ]);
            echo json_encode($result);
            break;

        case 'update_profile_status':
            $id = $input['id'] ?? null;
            $name = $input['name'] ?? null;
            $slug = $input['slug'] ?? null;
            $color = $input['color'] ?? null;
            $sortOrder = $input['sort_order'] ?? 0;
            $isActive = $input['is_active'] ?? 1;

            if (!$id || !$name || !$slug || !$color) {
                throw new Exception('Missing required fields: id, name, slug, color');
            }

            $result = $controller->updateProfileStatus([
                'id' => $id,
                'name' => $name,
                'slug' => $slug,
                'color' => $color,
                'sort_order' => $sortOrder,
                'is_active' => $isActive
            ]);
            echo json_encode($result);
            break;

        case 'toggle_profile_status':
            $id = $input['id'] ?? null;
            $isActive = $input['is_active'] ?? null;

            if ($id === null || $isActive === null) {
                throw new Exception('Missing required parameters: id, is_active');
            }

            $result = $controller->toggleProfileStatus($id, $isActive);
            echo json_encode($result);
            break;

        case 'delete_profile_status':
            $id = $input['id'] ?? null;

            if ($id === null) {
                throw new Exception('Missing profile status ID');
            }

            $result = $controller->deleteProfileStatus($id);
            echo json_encode($result);
            break;

        case 'update_profile_status_icon':
            $id = $input['id'] ?? null;
            $icon = $input['icon'] ?? null;

            if ($id === null) {
                throw new Exception('Missing profile status ID');
            }

            $result = $controller->updateProfileStatusIcon($id, $icon);
            echo json_encode($result);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
