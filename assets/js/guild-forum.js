/**
 * Guild Forum JavaScript
 * Handles thread creation, comments, modals, and AJAX operations
 */

// Toast container
let toastContainer;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Create toast container
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);

    // Initialize event listeners
    initializeEventListeners();
});

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
    // Close modal when clicking outside
    const modalOverlays = document.querySelectorAll('.modal-create-thread');
    modalOverlays.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCreateThreadModal();
            }
        });
    });

    // Handle comment form submission
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', submitComment);
    }

    // Handle thread creation form submission
    const createThreadForm = document.getElementById('create-thread-form');
    if (createThreadForm) {
        createThreadForm.addEventListener('submit', submitThread);
    }
}

/**
 * Open create thread modal
 */
function openCreateThreadModal() {
    const modal = document.getElementById('modal-create-thread');
    if (modal) {
        modal.classList.add('active');
        // Focus on title input
        const titleInput = modal.querySelector('input[name="title"]');
        if (titleInput) {
            setTimeout(() => titleInput.focus(), 100);
        }
    }
}

/**
 * Close create thread modal
 */
function closeCreateThreadModal() {
    const modal = document.getElementById('modal-create-thread');
    if (modal) {
        modal.classList.remove('active');
        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

/**
 * Submit new thread via AJAX
 * @param {Event} event Form submit event
 */
function submitThread(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const data = {
        guild_id: formData.get('guild_id'),
        title: formData.get('title'),
        content: formData.get('content')
    };

    // Validate data
    if (!data.title || !data.title.trim()) {
        showToast('Error', 'Thread title is required', 'error');
        return;
    }

    if (!data.content || !data.content.trim()) {
        showToast('Error', 'Thread content is required', 'error');
        return;
    }

    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';

    fetch('/api/guild_forum.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'create_thread',
            ...data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Thread created successfully', 'success');
            closeCreateThreadModal();
            // Redirect to new thread after short delay
            setTimeout(() => {
                if (data.thread_id) {
                    window.location.href = `/guild-forum.php?thread_id=${data.thread_id}`;
                } else {
                    window.location.reload();
                }
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to create thread', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

/**
 * Submit comment via AJAX
 * @param {Event} event Form submit event
 */
function submitComment(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const data = {
        thread_id: formData.get('thread_id'),
        content: formData.get('content')
    };

    // Validate data
    if (!data.content || !data.content.trim()) {
        showToast('Error', 'Comment content is required', 'error');
        return;
    }

    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting...';

    fetch('/api/guild_forum.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'create_comment',
            ...data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Comment posted successfully', 'success');
            // Reset form
            form.reset();
            // Reload page after short delay to show new comment
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to post comment', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

/**
 * Delete thread with confirmation
 * @param {number} threadId Thread ID
 * @param {string} guildId Guild ID
 */
function deleteThread(threadId, guildId) {
    if (confirm('Are you sure you want to delete this thread?\n\nThis action cannot be undone.')) {
        fetch('/api/guild_forum.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_thread',
                thread_id: threadId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', 'Thread deleted successfully', 'success');
                // Redirect to guild forum after short delay
                setTimeout(() => {
                    window.location.href = `/guild-forum.php?guild_id=${guildId}`;
                }, 1000);
            } else {
                showToast('Error', data.message || 'Failed to delete thread', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An error occurred: ' + error.message, 'error');
        });
    }
}

/**
 * Delete comment with confirmation
 * @param {number} commentId Comment ID
 */
function deleteComment(commentId) {
    if (confirm('Are you sure you want to delete this comment?\n\nThis action cannot be undone.')) {
        fetch('/api/guild_forum.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_comment',
                comment_id: commentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', 'Comment deleted successfully', 'success');
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Error', data.message || 'Failed to delete comment', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An error occurred: ' + error.message, 'error');
        });
    }
}

/**
 * Pin/Unpin thread
 * @param {number} threadId Thread ID
 * @param {boolean} isPinned Current pin status
 */
function togglePinThread(threadId, isPinned) {
    fetch('/api/guild_forum.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'toggle_pin_thread',
            thread_id: threadId,
            is_pinned: !isPinned
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', isPinned ? 'Thread unpinned' : 'Thread pinned', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to update thread', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred: ' + error.message, 'error');
    });
}

/**
 * Lock/Unlock thread
 * @param {number} threadId Thread ID
 * @param {boolean} isLocked Current lock status
 */
function toggleLockThread(threadId, isLocked) {
    fetch('/api/guild_forum.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'toggle_lock_thread',
            thread_id: threadId,
            is_locked: !isLocked
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', isLocked ? 'Thread unlocked' : 'Thread locked', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to update thread', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred: ' + error.message, 'error');
    });
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
 * Search threads
 * @param {string} query Search query
 */
function searchThreads(query) {
    const threads = document.querySelectorAll('.thread-list-table tbody tr');
    const searchLower = query.toLowerCase();

    threads.forEach(thread => {
        const title = thread.querySelector('.thread-title')?.textContent.toLowerCase() || '';
        const author = thread.querySelector('.thread-author')?.textContent.toLowerCase() || '';

        if (title.includes(searchLower) || author.includes(searchLower)) {
            thread.style.display = '';
        } else {
            thread.style.display = 'none';
        }
    });
}

/**
 * Format date for display
 * @param {string} dateString Date string
 * @return {string} Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 7) {
        return date.toLocaleDateString();
    } else if (days > 0) {
        return `${days} day${days > 1 ? 's' : ''} ago`;
    } else if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (minutes > 0) {
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else {
        return 'Just now';
    }
}
