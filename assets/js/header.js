/**
 * Header JavaScript
 * Handles search modal and user dropdown functionality
 */

document.addEventListener('DOMContentLoaded', () => {
    // ========================================
    // SEARCH MODAL FUNCTIONALITY
    // ========================================
    const searchBtn = document.getElementById('search-icon-btn');
    const searchModal = document.getElementById('search-modal');
    const searchModalClose = document.getElementById('search-modal-close');
    const searchInput = document.getElementById('search-input');

    if (searchBtn && searchModal) {
        // Open search modal
        searchBtn.addEventListener('click', () => {
            searchModal.classList.add('active');
            // Focus on input after a brief delay to ensure modal is visible
            setTimeout(() => {
                if (searchInput) {
                    searchInput.focus();
                }
            }, 100);
        });

        // Close modal when clicking the close button
        if (searchModalClose) {
            searchModalClose.addEventListener('click', () => {
                searchModal.classList.remove('active');
            });
        }

        // Close modal when clicking outside the modal content
        searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) {
                searchModal.classList.remove('active');
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && searchModal.classList.contains('active')) {
                searchModal.classList.remove('active');
            }
        });
    }

    // ========================================
    // USER DROPDOWN FUNCTIONALITY
    // ========================================
    const userBtn = document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-dropdown');

    if (userBtn && userDropdown) {
        // Toggle dropdown on button click
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // Close dropdown when clicking a link inside it
        const dropdownLinks = userDropdown.querySelectorAll('a');
        dropdownLinks.forEach(link => {
            link.addEventListener('click', () => {
                userDropdown.classList.remove('active');
            });
        });
    }
});
