// ============================================================================
// REVIEW APPLICANTS - JavaScript
// ============================================================================
// Vanilla JavaScript for handling accept/decline actions
// No dependencies required
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {

    // ========================================================================
    // INITIALIZATION & DOM ELEMENTS
    // ========================================================================

    const acceptButtons = document.querySelectorAll('.btn-accept');
    const declineButtons = document.querySelectorAll('.btn-decline');

    // ========================================================================
    // TOAST NOTIFICATION SYSTEM
    // ========================================================================

    /**
     * Show toast notification
     * @param {string} message - Message to display
     * @param {string} type - Toast type (success, error, info)
     * @param {number} duration - Duration in milliseconds (default: 3000)
     */
    const showToast = (message, type = 'info', duration = 3000) => {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;

        // Add to body
        document.body.appendChild(toast);

        // Auto-remove after duration
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    // ========================================================================
    // UPDATE APPLICATION STATUS IN UI
    // ========================================================================

    /**
     * Update the application card UI with new status
     * @param {HTMLElement} card - The applicant card element
     * @param {string} status - New status (accepted or rejected)
     */
    const updateApplicationStatus = (card, status) => {
        // Find and update status badge
        const statusBadge = card.querySelector('.status-badge');
        if (statusBadge) {
            statusBadge.className = `status-badge status-${status}`;
            statusBadge.textContent = status;
        }

        // Remove action buttons
        const actionsContainer = card.querySelector('.applicant-actions');
        if (actionsContainer) {
            actionsContainer.remove();
        }

        // Add visual feedback
        card.style.opacity = '0.7';
        setTimeout(() => {
            card.style.opacity = '1';
        }, 300);
    };

    // ========================================================================
    // ACCEPT APPLICATION
    // ========================================================================

    /**
     * Handle accept button click
     * @param {Event} e - Click event
     */
    const handleAccept = async (e) => {
        const button = e.currentTarget;
        const applicationId = button.dataset.applicationId;
        const card = button.closest('.applicant-card');

        // Confirmation dialog
        const confirmed = confirm('Are you sure you want to accept this application? This will reject all other pending applications for this bounty.');

        if (!confirmed) {
            return;
        }

        // Disable button
        button.disabled = true;
        button.textContent = 'accepting...';

        try {
            // Send POST request to API
            const response = await fetch('/api/applications.php?action=accept', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: applicationId
                })
            });

            const data = await response.json();

            if (data.success) {
                // Update UI
                updateApplicationStatus(card, 'accepted');

                // Show success toast
                showToast('Application accepted successfully!', 'success');

                // Reload page after 2 seconds to show all status updates
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                // Show error toast
                showToast(data.error || 'Failed to accept application', 'error');

                // Re-enable button
                button.disabled = false;
                button.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    accept
                `;
            }
        } catch (error) {
            console.error('Error accepting application:', error);
            showToast('An error occurred. Please try again.', 'error');

            // Re-enable button
            button.disabled = false;
            button.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                accept
            `;
        }
    };

    // ========================================================================
    // DECLINE APPLICATION
    // ========================================================================

    /**
     * Handle decline button click
     * @param {Event} e - Click event
     */
    const handleDecline = async (e) => {
        const button = e.currentTarget;
        const applicationId = button.dataset.applicationId;
        const card = button.closest('.applicant-card');

        // Confirmation dialog
        const confirmed = confirm('Are you sure you want to decline this application?');

        if (!confirmed) {
            return;
        }

        // Disable button
        button.disabled = true;
        button.textContent = 'declining...';

        try {
            // Send POST request to API
            const response = await fetch('/api/applications.php?action=reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: applicationId
                })
            });

            const data = await response.json();

            if (data.success) {
                // Update UI
                updateApplicationStatus(card, 'rejected');

                // Show success toast
                showToast('Application declined', 'info');
            } else {
                // Show error toast
                showToast(data.error || 'Failed to decline application', 'error');

                // Re-enable button
                button.disabled = false;
                button.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    decline
                `;
            }
        } catch (error) {
            console.error('Error declining application:', error);
            showToast('An error occurred. Please try again.', 'error');

            // Re-enable button
            button.disabled = false;
            button.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                decline
            `;
        }
    };

    // ========================================================================
    // ATTACH EVENT LISTENERS
    // ========================================================================

    // Attach accept handlers
    acceptButtons.forEach(button => {
        button.addEventListener('click', handleAccept);
    });

    // Attach decline handlers
    declineButtons.forEach(button => {
        button.addEventListener('click', handleDecline);
    });

    // ========================================================================
    // INITIALIZATION COMPLETE
    // ========================================================================

    console.log('Review Applicants UI initialized');
    console.log(`Found ${acceptButtons.length} accept buttons and ${declineButtons.length} decline buttons`);

});
