<?php
/**
 * Admin Profile Statuses Management
 * Create, edit, and delete profile statuses
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch all profile statuses ordered by sort_order
$sql = "SELECT id, name, slug, color, icon, sort_order, is_active, created_at
        FROM profile_statuses
        ORDER BY sort_order ASC, name ASC";

$stmt = $db->query($sql);
$statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Statuses Management - REDOT Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/icon-picker.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Profile Statuses Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Profile Statuses (<?= count($statuses) ?>)</h2>
                    <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                        Create New Status
                    </button>
                </div>

                <div class="table-wrapper">
                    <table id="data-table">
                        <thead>
                            <tr>
                                <th class="sortable">ID</th>
                                <th class="sortable">Name</th>
                                <th class="sortable">Slug</th>
                                <th>Color</th>
                                <th>Icon</th>
                                <th class="sortable">Sort Order</th>
                                <th class="sortable">Active</th>
                                <th class="sortable">Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($statuses)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        No profile statuses found. Click "Create New Status" to add one.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($statuses as $status): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($status['id']) ?></td>
                                        <td>
                                            <div class="status-name-with-icon">
                                                <?php if (!empty($status['icon'])): ?>
                                                    <span class="status-icon-display" id="status-icon-<?= $status['id'] ?>" data-icon="<?= htmlspecialchars($status['icon']) ?>">
                                                        <script>
                                                            document.addEventListener('DOMContentLoaded', function() {
                                                                const iconEl = document.getElementById('status-icon-<?= $status['id'] ?>');
                                                                const iconValue = iconEl.dataset.icon;
                                                                iconEl.innerHTML = IconPicker.renderIcon(iconValue);
                                                            });
                                                        </script>
                                                    </span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($status['name']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($status['slug']) ?></td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="display: inline-block; width: 24px; height: 24px; background: <?= htmlspecialchars($status['color']) ?>; border-radius: 4px; border: 1px solid #333;"></span>
                                                <code style="color: #a0a0a0; font-size: 0.875rem;"><?= htmlspecialchars($status['color']) ?></code>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-small"
                                                    onclick='openIconPicker(<?= $status['id'] ?>, "<?= htmlspecialchars($status['icon'] ?? '', ENT_QUOTES) ?>", handleIconSelect)'
                                                    style="padding: 4px 8px; font-size: 0.875rem;">
                                                <?= !empty($status['icon']) ? 'Change Icon' : 'Add Icon' ?>
                                            </button>
                                        </td>
                                        <td><?= htmlspecialchars($status['sort_order']) ?></td>
                                        <td>
                                            <label class="toggle-switch">
                                                <input type="checkbox"
                                                       <?= $status['is_active'] ? 'checked' : '' ?>
                                                       onchange="toggleActive(<?= $status['id'] ?>, this.checked)">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($status['created_at'])) ?></td>
                                        <td class="actions">
                                            <div class="btn-group" style="display: flex; gap: 6px;">
                                                <button type="button" class="btn btn-small btn-primary"
                                                        onclick='openEditModal(<?= json_encode($status) ?>)'>
                                                    Edit
                                                </button>
                                                <button type="button" class="btn btn-small btn-danger"
                                                        onclick="confirmDelete('profile_status', <?= $status['id'] ?>, '<?= htmlspecialchars($status['name'], ENT_QUOTES) ?>')">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <!-- Create/Edit Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Create New Status</h2>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <form id="statusForm" onsubmit="saveStatus(event)">
                <input type="hidden" id="statusId" name="id">

                <div class="form-group">
                    <label for="statusName">Name *</label>
                    <input type="text" id="statusName" name="name" required
                           placeholder="e.g., Available" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="statusSlug">Slug *</label>
                    <input type="text" id="statusSlug" name="slug" required
                           placeholder="e.g., available" maxlength="100" pattern="[a-z0-9-]+">
                    <small style="color: #888; font-size: 0.875rem;">Lowercase letters, numbers, and hyphens only</small>
                </div>

                <div class="form-group">
                    <label for="statusColor">Color *</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="color" id="statusColor" name="color" required
                               style="width: 60px; height: 40px; border: 1px solid #333; background: #1a1a1a; cursor: pointer;">
                        <input type="text" id="statusColorText" name="color_text"
                               placeholder="#10b981" maxlength="7" pattern="#[0-9a-fA-F]{6}"
                               style="flex: 1; padding: 8px 12px; background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 4px;">
                    </div>
                    <small style="color: #888; font-size: 0.875rem;">Hex color code (e.g., #10b981)</small>
                </div>

                <div class="form-group">
                    <label for="statusSortOrder">Sort Order *</label>
                    <input type="number" id="statusSortOrder" name="sort_order" required
                           min="0" value="0" placeholder="0">
                    <small style="color: #888; font-size: 0.875rem;">Lower numbers appear first</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="statusIsActive" name="is_active" checked>
                        <span>Active Status</span>
                    </label>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Create Status</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/assets/js/admin.js"></script>
    <script src="/assets/js/icon-picker.js"></script>
    <script>
        // Sync color picker and text input
        const colorPicker = document.getElementById('statusColor');
        const colorText = document.getElementById('statusColorText');

        colorPicker.addEventListener('input', function() {
            colorText.value = this.value;
        });

        colorText.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                colorPicker.value = this.value;
            }
        });

        // Auto-generate slug from name
        document.getElementById('statusName').addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('statusSlug').value = slug;
        });

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Create New Status';
            document.getElementById('saveBtn').textContent = 'Create Status';
            document.getElementById('statusForm').reset();
            document.getElementById('statusId').value = '';
            document.getElementById('statusIsActive').checked = true;
            document.getElementById('statusSortOrder').value = '0';
            document.getElementById('statusModal').style.display = 'flex';
        }

        function openEditModal(status) {
            document.getElementById('modalTitle').textContent = 'Edit Status';
            document.getElementById('saveBtn').textContent = 'Update Status';
            document.getElementById('statusId').value = status.id;
            document.getElementById('statusName').value = status.name;
            document.getElementById('statusSlug').value = status.slug;
            document.getElementById('statusColor').value = status.color;
            document.getElementById('statusColorText').value = status.color;
            document.getElementById('statusSortOrder').value = status.sort_order;
            document.getElementById('statusIsActive').checked = status.is_active == 1;
            document.getElementById('statusModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        async function handleIconSelect(statusId, iconValue) {
            try {
                const response = await fetch('/api/admin.php?action=update_profile_status_icon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: statusId,
                        icon: iconValue
                    })
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }

        async function saveStatus(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const statusId = formData.get('id');
            const isEdit = statusId !== '';

            const data = {
                name: formData.get('name'),
                slug: formData.get('slug'),
                color: formData.get('color_text') || formData.get('color'),
                sort_order: parseInt(formData.get('sort_order')),
                is_active: formData.get('is_active') ? 1 : 0
            };

            if (isEdit) {
                data.id = parseInt(statusId);
            }

            try {
                const action = isEdit ? 'update_profile_status' : 'create_profile_status';
                const response = await fetch(`/api/admin.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || (isEdit ? 'Status updated successfully!' : 'Status created successfully!'));
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }

        async function toggleActive(statusId, isActive) {
            try {
                const response = await fetch('/api/admin.php?action=toggle_profile_status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: statusId,
                        is_active: isActive ? 1 : 0
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    alert('Error: ' + result.message);
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                window.location.reload();
            }
        }

        function confirmDelete(type, id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                deleteStatus(id);
            }
        }

        async function deleteStatus(statusId) {
            try {
                const response = await fetch('/api/admin.php?action=delete_profile_status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: statusId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || 'Status deleted successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }
    </script>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #333;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #fff;
        }

        .modal-close {
            font-size: 28px;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            line-height: 1;
        }

        .modal-close:hover {
            color: #fff;
        }

        .modal-content form {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #fff;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 4px;
            color: #fff;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #333;
            margin-top: 20px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #333;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: #10b981;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
    </style>
</body>
</html>
