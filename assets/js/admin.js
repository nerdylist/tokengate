/**
 * Admin Dashboard JavaScript
 * Handles table sorting, AJAX operations, modals, and toast notifications
 */

// Toast container
let toastContainer;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Create toast container
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);

    // Initialize table sorting
    initTableSorting();
});

/**
 * Initialize table sorting functionality
 */
function initTableSorting() {
    const tables = document.querySelectorAll('table');

    tables.forEach(table => {
        const headers = table.querySelectorAll('thead th.sortable');

        headers.forEach((header, index) => {
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        });
    });
}

/**
 * Sort table by column
 * @param {HTMLTableElement} table The table element
 * @param {number} columnIndex Column index to sort by
 */
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelectorAll('thead th')[columnIndex];

    // Determine sort direction
    let isAscending = true;
    if (header.classList.contains('sorted-asc')) {
        isAscending = false;
    }

    // Remove all sort classes
    table.querySelectorAll('thead th').forEach(th => {
        th.classList.remove('sorted-asc', 'sorted-desc');
    });

    // Add appropriate class
    header.classList.add(isAscending ? 'sorted-asc' : 'sorted-desc');

    // Sort rows
    rows.sort((a, b) => {
        const aCell = a.cells[columnIndex].textContent.trim();
        const bCell = b.cells[columnIndex].textContent.trim();

        // Try to parse as numbers
        const aNum = parseFloat(aCell.replace(/[^0-9.-]/g, ''));
        const bNum = parseFloat(bCell.replace(/[^0-9.-]/g, ''));

        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }

        // Sort as strings
        return isAscending
            ? aCell.localeCompare(bCell)
            : bCell.localeCompare(aCell);
    });

    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Show toast notification
 * @param {string} title Notification title
 * @param {string} message Notification message
 * @param {string} type Notification type (success, error, warning)
 */
function showToast(title, message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : '⚠';

    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">
            <div class="toast-title">${escapeHtml(title)}</div>
            <div class="toast-message">${escapeHtml(message)}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;

    toastContainer.appendChild(toast);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text Text to escape
 * @return {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Delete item with confirmation
 * @param {string} type Item type (user, bounty, profile, etc.)
 * @param {number} id Item ID
 * @param {string} name Item name for confirmation
 */
function confirmDelete(type, id, name) {
    if (confirm(`Are you sure you want to delete this ${type}: "${name}"?\n\nThis action cannot be undone.`)) {
        deleteItem(type, id);
    }
}

/**
 * Delete item via AJAX
 * @param {string} type Item type
 * @param {number} id Item ID
 */
function deleteItem(type, id) {
    const endpoint = `/api/admin.php?action=delete_${type}`;
    const payload = {};
    payload[`${type}_id`] = id;

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred: ' + error.message, 'error');
    });
}

/**
 * Update status via AJAX
 * @param {string} type Item type (bounty, application)
 * @param {number} id Item ID
 * @param {string} status New status
 */
function updateStatus(type, id, status) {
    const endpoint = `/api/admin.php?action=update_${type}_status`;
    const payload = {};
    payload[`${type}_id`] = id;
    payload['status'] = status;

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred: ' + error.message, 'error');
    });
}

/**
 * Toggle user admin status
 * @param {number} userId User ID
 * @param {number} currentStatus Current admin status (0 or 1)
 */
function toggleUserAdmin(userId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    const action = newStatus ? 'grant' : 'revoke';

    if (confirm(`Are you sure you want to ${action} admin privileges for this user?`)) {
        fetch('/api/admin.php?action=toggle_user_admin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                is_admin: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An error occurred: ' + error.message, 'error');
        });
    }
}

/**
 * Open modal
 * @param {string} modalId Modal element ID
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Close modal
 * @param {string} modalId Modal element ID
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

/**
 * Filter table rows
 * @param {string} filterValue Filter value
 * @param {number} columnIndex Column index to filter
 */
function filterTable(filterValue, columnIndex) {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const cell = row.cells[columnIndex];
        if (cell) {
            const cellText = cell.textContent.toLowerCase();
            const filterText = filterValue.toLowerCase();

            if (filterValue === '' || cellText.includes(filterText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

/**
 * Handle form submission via AJAX
 * @param {Event} event Form submit event
 * @param {string} endpoint API endpoint
 * @param {Function} successCallback Callback on success
 */
function handleFormSubmit(event, endpoint, successCallback) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            if (successCallback) {
                successCallback(data);
            }
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred: ' + error.message, 'error');
    });
}
