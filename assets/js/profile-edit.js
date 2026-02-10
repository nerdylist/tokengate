/**
 * Profile Inline Editing
 * Handles real-time editing of profile fields with AJAX updates
 */

// Toast notification system
const Toast = {
    show(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        document.body.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('toast-show'), 10);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('toast-show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'error');
    }
};

// Edit field functionality
async function editField(fieldType) {
    const fieldMap = {
        bio: {
            displayId: 'bio-display',
            getData: (el) => el.dataset.original,
            createInput: (value) => {
                const textarea = document.createElement('textarea');
                textarea.className = 'edit-textarea';
                textarea.value = value;
                textarea.rows = 8;
                textarea.maxLength = 5000;
                return textarea;
            },
            apiAction: 'update_bio',
            getPostData: (input) => ({ bio: input.value }),
            updateDisplay: (container, response) => {
                container.innerHTML = `<p class="profile-bio-text">${response.bio.replace(/\n/g, '<br>')}</p>`;
                container.dataset.original = response.bio;
            },
            successMessage: 'Bio updated successfully'
        },
        rate: {
            displayId: 'rate-display',
            getData: (el) => el.dataset.original,
            createInput: (value) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'rate-input-wrapper';

                const prefix = document.createElement('span');
                prefix.className = 'rate-prefix';
                prefix.textContent = '$';

                const input = document.createElement('input');
                input.type = 'number';
                input.className = 'edit-input rate-input';
                input.value = value;
                input.min = '0';
                input.max = '9999';
                input.step = '1';

                const suffix = document.createElement('span');
                suffix.className = 'rate-suffix';
                suffix.textContent = '/hr';

                wrapper.appendChild(prefix);
                wrapper.appendChild(input);
                wrapper.appendChild(suffix);

                return wrapper;
            },
            apiAction: 'update_rate',
            getPostData: (input) => ({ rate: input.querySelector('input').value }),
            updateDisplay: (container, response) => {
                const editBtn = container.querySelector('.edit-btn');
                container.innerHTML = `$${response.rate}/hr`;
                if (editBtn) container.appendChild(editBtn);
                container.dataset.original = response.rate.replace(/,/g, '');

                // Update stats card
                const statEl = document.getElementById('rate-stat');
                if (statEl) statEl.textContent = `$${response.rate}`;
            },
            successMessage: 'Hourly rate updated successfully'
        },
        status: {
            displayId: 'status-display',
            getData: (el) => el.dataset.original,
            createInput: async (value) => {
                const select = document.createElement('select');
                select.className = 'edit-select';

                // Fetch statuses from API
                try {
                    const response = await fetch('/api/profile.php?action=get_statuses');
                    const data = await response.json();

                    if (data.success && data.statuses) {
                        data.statuses.forEach(status => {
                            const option = document.createElement('option');
                            option.value = status.id;
                            option.textContent = status.name;
                            option.selected = status.id == value;
                            select.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Failed to fetch statuses from API:', error, 'Using fallback options');
                    // Fallback to basic options
                    const fallbackOptions = [
                        { value: '1', label: 'Available' },
                        { value: '2', label: 'Busy' },
                        { value: '3', label: 'Unavailable' },
                        { value: '4', label: 'Away' }
                    ];
                    fallbackOptions.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt.value;
                        option.textContent = opt.label;
                        option.selected = opt.value == value;
                        select.appendChild(option);
                    });
                }

                return select;
            },
            apiAction: 'update_status',
            getPostData: (input) => ({ status_id: input.value }),
            updateDisplay: (container, response) => {
                const editBtn = container.querySelector('.edit-btn');
                container.innerHTML = `<span class="profile-status status-${response.statusSlug}" style="color: ${response.statusColor}">${response.statusName}</span>`;
                if (editBtn) container.appendChild(editBtn);
                container.dataset.original = response.statusId;

                // Update stats card
                const statEl = document.getElementById('status-stat');
                if (statEl) {
                    statEl.textContent = response.statusName;
                    statEl.className = `stat-value status-${response.statusSlug}`;
                }
            },
            successMessage: 'Status updated successfully'
        }
    };

    const config = fieldMap[fieldType];
    if (!config) return;

    const displayContainer = document.getElementById(config.displayId);
    if (!displayContainer) return;

    // Prevent multiple edits
    if (displayContainer.classList.contains('editing')) return;

    displayContainer.classList.add('editing');

    const originalValue = config.getData(displayContainer);
    const originalHTML = displayContainer.innerHTML;

    // Create input (handle async createInput)
    const input = await config.createInput(originalValue);

    // Create action buttons
    const actions = document.createElement('div');
    actions.className = 'edit-actions';

    const saveBtn = document.createElement('button');
    saveBtn.className = 'btn-save';
    saveBtn.textContent = 'Save';
    saveBtn.onclick = () => saveField(fieldType, config, displayContainer, input, originalHTML);

    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn-cancel';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = () => cancelEdit(displayContainer, originalHTML);

    actions.appendChild(saveBtn);
    actions.appendChild(cancelBtn);

    // Replace content
    displayContainer.innerHTML = '';
    displayContainer.appendChild(input);
    displayContainer.appendChild(actions);

    // Focus input
    const focusEl = input.tagName === 'INPUT' || input.tagName === 'TEXTAREA' || input.tagName === 'SELECT'
        ? input
        : input.querySelector('input, textarea, select');
    if (focusEl) focusEl.focus();
}

function cancelEdit(container, originalHTML) {
    container.innerHTML = originalHTML;
    container.classList.remove('editing');
}

async function saveField(fieldType, config, container, input, originalHTML) {
    const postData = config.getPostData(input);

    // Disable buttons during save
    const saveBtn = container.querySelector('.btn-save');
    const cancelBtn = container.querySelector('.btn-cancel');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
    }
    if (cancelBtn) cancelBtn.disabled = true;

    try {
        const formData = new FormData();
        Object.keys(postData).forEach(key => {
            formData.append(key, postData[key]);
        });

        const response = await fetch(`api/profile.php?action=${config.apiAction}`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            config.updateDisplay(container, data);
            container.classList.remove('editing');
            Toast.success(config.successMessage);
        } else {
            Toast.error(data.error || 'Failed to update');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
            }
            if (cancelBtn) cancelBtn.disabled = false;
        }
    } catch (error) {
        console.error('Update error:', error);
        Toast.error('An error occurred while updating');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
        }
        if (cancelBtn) cancelBtn.disabled = false;
    }
}

// Avatar upload functionality
function triggerAvatarUpload() {
    const fileInput = document.getElementById('avatar-upload');
    if (fileInput) {
        fileInput.click();
    }
}

// Handle avatar file selection
document.addEventListener('DOMContentLoaded', () => {
    const avatarInput = document.getElementById('avatar-upload');
    if (avatarInput) {
        avatarInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                Toast.error('File is too large (max 5MB)');
                avatarInput.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                Toast.error('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
                avatarInput.value = '';
                return;
            }

            // Show loading state
            const avatarContainer = document.getElementById('avatar-container');
            const originalContent = avatarContainer.innerHTML;
            avatarContainer.classList.add('uploading');

            try {
                const formData = new FormData();
                formData.append('avatar', file);

                const response = await fetch('api/profile.php?action=upload_avatar', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Update avatar display
                    const avatarCircle = avatarContainer.querySelector('.avatar-circle-large');
                    if (avatarCircle) {
                        const img = document.createElement('img');
                        img.src = data.avatar_url;
                        img.alt = 'Profile avatar';
                        img.className = 'avatar-image-large';
                        avatarCircle.replaceWith(img);
                    } else {
                        // If already an image, just update src
                        const existingImg = avatarContainer.querySelector('.avatar-image-large');
                        if (existingImg) {
                            existingImg.src = data.avatar_url + '?t=' + Date.now(); // Cache bust
                        }
                    }

                    avatarContainer.classList.remove('uploading');
                    Toast.success('Avatar updated successfully');
                } else {
                    avatarContainer.classList.remove('uploading');
                    Toast.error(data.error || 'Failed to upload avatar');
                }
            } catch (error) {
                console.error('Upload error:', error);
                avatarContainer.classList.remove('uploading');
                Toast.error('An error occurred while uploading');
            }

            // Reset input
            avatarInput.value = '';
        });
    }
});
