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

// Status dropdown functionality (replaces inline select editor)
function initStatusDropdown() {
    const statusDisplay = document.getElementById('status-display');
    if (!statusDisplay || !statusDisplay.classList.contains('clickable-status')) return;

    statusDisplay.addEventListener('click', async (e) => {
        e.stopPropagation();
        // Prevent opening dropdown if already open
        if (document.getElementById('status-dropdown')) return;

        await showStatusDropdown();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('status-dropdown');
        const statusDisplay = document.getElementById('status-display');

        if (dropdown && !dropdown.contains(e.target) && !statusDisplay.contains(e.target)) {
            dropdown.remove();
        }
    });
}

async function showStatusDropdown() {
    const statusDisplay = document.getElementById('status-display');
    const currentStatusId = statusDisplay.dataset.original;

    // Fetch statuses from API
    let statuses = [];
    try {
        const response = await fetch('/api/profile.php?action=get_statuses');
        const data = await response.json();
        if (data.success && data.statuses) {
            statuses = data.statuses;
        }
    } catch (error) {
        console.error('Failed to fetch statuses:', error);
        Toast.error('Failed to load status options');
        return;
    }

    // Create dropdown
    const dropdown = document.createElement('div');
    dropdown.id = 'status-dropdown';
    dropdown.className = 'status-dropdown';

    for (const status of statuses) {
        const option = document.createElement('div');
        option.className = 'status-dropdown-item';
        if (status.id == currentStatusId) {
            option.classList.add('active');
        }

        let optionHTML = '<span class="status-option-content" style="color: ' + status.color + '">';

        if (status.icon) {
            optionHTML += '<span class="status-icon status-icon-dropdown" data-icon="' + status.icon + '"></span>';
        }

        optionHTML += '<span class="status-option-name">' + status.name.toLowerCase() + '</span></span>';

        option.innerHTML = optionHTML;
        option.onclick = () => selectStatus(status.id);

        dropdown.appendChild(option);

        // Render icon
        if (status.icon) {
            const iconEl = option.querySelector('.status-icon-dropdown');
            if (iconEl) {
                iconEl.innerHTML = await IconPicker.renderIcon(status.icon);
            }
        }
    }

    // Position and append dropdown
    statusDisplay.appendChild(dropdown);
}

async function selectStatus(statusId) {
    const statusDisplay = document.getElementById('status-display');
    const dropdown = document.getElementById('status-dropdown');

    if (dropdown) dropdown.remove();

    // Show loading state
    statusDisplay.style.opacity = '0.5';

    try {
        const formData = new FormData();
        formData.append('status_id', statusId);

        const response = await fetch('/api/profile.php?action=update_status', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Update display
            let statusHTML = '<span class="profile-status status-' + data.statusSlug + '" style="color: ' + data.statusColor + '">';

            if (data.statusIcon) {
                statusHTML += '<span class="status-icon status-icon-hero" data-icon="' + data.statusIcon + '"></span>';
            }

            statusHTML += '<span>' + data.statusName + '</span></span>';

            statusDisplay.innerHTML = statusHTML;
            statusDisplay.dataset.original = data.statusId;

            // Render the icon
            if (data.statusIcon) {
                const heroIcon = statusDisplay.querySelector('.status-icon-hero');
                if (heroIcon) {
                    heroIcon.innerHTML = await IconPicker.renderIcon(data.statusIcon);
                }
            }

            // Update stats card
            const statEl = document.getElementById('status-stat');
            if (statEl) {
                let statHTML = '';

                if (data.statusIcon) {
                    statHTML += '<span class="status-icon status-icon-stat" data-icon="' + data.statusIcon + '"></span>';
                }

                statHTML += '<span>' + data.statusName + '</span>';

                statEl.innerHTML = statHTML;
                statEl.className = 'stat-value status-' + data.statusSlug;
                statEl.style.color = data.statusColor;

                if (data.statusIcon) {
                    const statIcon = statEl.querySelector('.status-icon-stat');
                    if (statIcon) {
                        statIcon.innerHTML = await IconPicker.renderIcon(data.statusIcon);
                    }
                }
            }

            statusDisplay.style.opacity = '1';
            Toast.success('Status updated successfully');
        } else {
            statusDisplay.style.opacity = '1';
            Toast.error(data.error || 'Failed to update status');
        }
    } catch (error) {
        console.error('Update error:', error);
        statusDisplay.style.opacity = '1';
        Toast.error('An error occurred while updating status');
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
    // Initialize status dropdown
    initStatusDropdown();

    const avatarInput = document.getElementById('avatar-upload');
    if (avatarInput) {
        avatarInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file size (2MB to match PHP upload_max_filesize)
            if (file.size > 2 * 1024 * 1024) {
                Toast.error('File is too large (max 2MB)');
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

                const response = await fetch('/api/profile.php?action=upload_avatar', {
                    method: 'POST',
                    body: formData
                });

                // Get response text first for better error handling
                const responseText = await response.text();

                // Parse JSON response
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text:', responseText);
                    throw new Error('Invalid server response');
                }

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
