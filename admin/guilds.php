<?php
/**
 * Admin Guilds Management
 * List, add, edit, and delete guilds
 */

require_once __DIR__ . '/../middleware/admin.php';

// Fetch guilds
$sql = "SELECT id, name, slug, type, created_at
        FROM guilds
        ORDER BY name ASC";
$stmt = $db->query($sql);
$guilds = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guilds Management - RentPeople.io Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin-sidebar.php'; ?>

        <main class="admin-content">
            <h1>Guilds Management</h1>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Guilds (<?= count($guilds) ?>)</h2>
                    <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                        Create New Guild
                    </button>
                </div>

                <?php
                require_once __DIR__ . '/partials/data-table.php';

                $columns = [
                    'id' => 'ID',
                    'name' => 'Name',
                    'slug' => 'Slug',
                    'type' => 'Type',
                    'created_at' => 'Created At'
                ];

                $actions = [
                    [
                        'type' => 'button',
                        'label' => 'Edit',
                        'class' => 'btn-primary',
                        'onclick' => 'openEditModal({row})'
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Delete',
                        'class' => 'btn-danger',
                        'onclick' => 'confirmDelete("guild", {id}, "{name}")'
                    ]
                ];

                renderDataTable($columns, $guilds, $actions);
                ?>
            </div>
        </main>

        <?php include __DIR__ . '/partials/admin-footer.php'; ?>
    </div>

    <!-- Create/Edit Modal -->
    <div id="guildModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Create New Guild</h2>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <form id="guildForm" onsubmit="saveGuild(event)">
                <input type="hidden" id="guildId" name="id">

                <div class="form-group">
                    <label for="guildName">Name *</label>
                    <input type="text" id="guildName" name="name" required
                           placeholder="e.g., Designers Guild" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="guildSlug">Slug *</label>
                    <input type="text" id="guildSlug" name="slug" required
                           placeholder="e.g., designers-guild" maxlength="100" pattern="[a-z0-9-]+">
                    <small style="color: #888; font-size: 0.875rem;">Lowercase letters, numbers, and hyphens only</small>
                </div>

                <div class="form-group">
                    <label for="guildType">Type *</label>
                    <select id="guildType" name="type" required
                            style="width: 100%; padding: 10px 12px; background: #0a0a0a; border: 1px solid #333; border-radius: 4px; color: #fff; font-size: 1rem;">
                        <option value="">Select Type</option>
                        <option value="professional">Professional</option>
                        <option value="hobby">Hobby</option>
                        <option value="community">Community</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="guildDescription">Description</label>
                    <textarea id="guildDescription" name="description" rows="4"
                              placeholder="Enter guild description..."
                              style="width: 100%; padding: 10px 12px; background: #0a0a0a; border: 1px solid #333; border-radius: 4px; color: #fff; font-size: 1rem; resize: vertical;"></textarea>
                </div>

                <div class="form-group">
                    <label for="guildIcon">Icon</label>
                    <input type="text" id="guildIcon" name="icon"
                           placeholder="e.g., fa-palette or emoji 🎨" maxlength="50">
                    <small style="color: #888; font-size: 0.875rem;">Font Awesome class or emoji</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Create Guild</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/assets/js/admin.js"></script>
    <script>
        // Auto-generate slug from name
        document.getElementById('guildName').addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('guildSlug').value = slug;
        });

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Create New Guild';
            document.getElementById('saveBtn').textContent = 'Create Guild';
            document.getElementById('guildForm').reset();
            document.getElementById('guildId').value = '';
            document.getElementById('guildModal').style.display = 'flex';
        }

        function openEditModal(guild) {
            document.getElementById('modalTitle').textContent = 'Edit Guild';
            document.getElementById('saveBtn').textContent = 'Update Guild';
            document.getElementById('guildId').value = guild.id;
            document.getElementById('guildName').value = guild.name;
            document.getElementById('guildSlug').value = guild.slug;
            document.getElementById('guildType').value = guild.type;
            document.getElementById('guildDescription').value = guild.description || '';
            document.getElementById('guildIcon').value = guild.icon || '';
            document.getElementById('guildModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('guildModal').style.display = 'none';
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('guildModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        async function saveGuild(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const guildId = formData.get('id');
            const isEdit = guildId !== '';

            const data = {
                name: formData.get('name'),
                slug: formData.get('slug'),
                type: formData.get('type'),
                description: formData.get('description'),
                icon: formData.get('icon')
            };

            if (isEdit) {
                data.id = parseInt(guildId);
            }

            try {
                const action = isEdit ? 'update_guild' : 'create_guild';
                const response = await fetch(`/api/admin.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || (isEdit ? 'Guild updated successfully!' : 'Guild created successfully!'));
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }

        function confirmDelete(type, id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                deleteGuild(id);
            }
        }

        async function deleteGuild(guildId) {
            try {
                const response = await fetch('/api/admin.php?action=delete_guild', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: guildId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || 'Guild deleted successfully!');
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
        #guildModal.modal {
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
            max-width: none;
            border: none;
            border-radius: 0;
            box-shadow: none;
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
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 4px;
            color: #fff;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #333;
            margin-top: 20px;
        }
    </style>
</body>
</html>
