// ============================================================================
// RENTAHUMAN.AI - Task Bounties JavaScript
// ============================================================================
// Vanilla JavaScript implementation for task bounties UI functionality
// No dependencies required
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {

    // ========================================================================
    // INITIALIZATION & DOM ELEMENTS
    // ========================================================================

    const filtersForm = document.querySelector('.filters-form');
    const categorySelect = document.getElementById('category');
    const skillInput = document.getElementById('skill');
    const minPriceInput = document.getElementById('min_price');
    const maxPriceInput = document.getElementById('max_price');
    const taskCards = document.querySelectorAll('.task-card');
    const tasksList = document.querySelector('.tasks-list');
    const tabs = document.querySelectorAll('.tab');
    const languageSelector = document.getElementById('language');

    // Store original order of task cards for "new" tab
    const originalOrder = Array.from(taskCards);


    // ========================================================================
    // VOTE SYSTEM - Initialize and Handle Voting
    // ========================================================================

    /**
     * Initialize vote button hover handlers
     */
    const initializeVotes = () => {
        // Add hover handlers for icon swapping
        document.querySelectorAll('.vote-btn').forEach(btn => {
            const icon = btn.querySelector('.vote-icon');

            btn.addEventListener('mouseenter', () => {
                if (!btn.classList.contains('voted')) {
                    icon.src = icon.dataset.hover;
                }
            });

            btn.addEventListener('mouseleave', () => {
                if (!btn.classList.contains('voted')) {
                    icon.src = icon.dataset.default;
                }
            });
        });
    };

    /**
     * Submit vote to server via AJAX
     * @param {number} bountyId - Bounty ID
     * @param {number} voteType - Vote type (1 or -1)
     * @param {HTMLElement} button - Vote button clicked
     */
    const submitVote = async (bountyId, voteType, button) => {
        try {
            const response = await fetch('/api/bounty-votes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `bounty_id=${bountyId}&vote_type=${voteType}`
            });

            const data = await response.json();

            if (data.success) {
                // Update vote count display
                const card = button.closest('.task-card');
                const voteCountSpan = card.querySelector('.vote-count');
                if (voteCountSpan) {
                    voteCountSpan.textContent = data.vote_count;
                }

                // Update button states
                const upButton = card.querySelector('.vote-up');
                const downButton = card.querySelector('.vote-down');
                const upIcon = upButton.querySelector('.vote-icon');
                const downIcon = downButton.querySelector('.vote-icon');

                // Reset both buttons
                upButton.classList.remove('voted');
                downButton.classList.remove('voted');
                upIcon.src = upIcon.dataset.default;
                downIcon.src = downIcon.dataset.default;

                // Set selected state based on user's vote
                if (data.user_vote === 1) {
                    upButton.classList.add('voted');
                    upIcon.src = upIcon.dataset.selected;
                } else if (data.user_vote === -1) {
                    downButton.classList.add('voted');
                    downIcon.src = downIcon.dataset.selected;
                }
            } else {
                if (data.error === 'Authentication required') {
                    alert('Please log in to vote');
                    window.location.href = '/connect.php';
                } else {
                    console.error('Vote failed:', data.error);
                }
            }
        } catch (error) {
            console.error('Error submitting vote:', error);
        }
    };

    /**
     * Handle vote button clicks
     * @param {Event} e - Click event
     */
    const handleVote = (e) => {
        e.preventDefault();
        e.stopPropagation();

        const voteBtn = e.currentTarget;
        const bountyId = voteBtn.getAttribute('data-index');
        const isUpvote = voteBtn.classList.contains('vote-up');
        const voteType = isUpvote ? 1 : -1;

        submitVote(bountyId, voteType, voteBtn);
    };

    // Attach vote handlers to all vote buttons
    taskCards.forEach((card) => {
        const upvoteBtn = card.querySelector('.vote-up');
        const downvoteBtn = card.querySelector('.vote-down');

        if (upvoteBtn) upvoteBtn.addEventListener('click', handleVote);
        if (downvoteBtn) downvoteBtn.addEventListener('click', handleVote);
    });

    // Initialize votes on page load
    initializeVotes();


    // ========================================================================
    // FILTER SYSTEM - Search and Filter Task Cards
    // ========================================================================

    /**
     * Extract price value from price element
     * @param {HTMLElement} card - Task card element
     * @returns {number} Price value
     */
    const getCardPrice = (card) => {
        const priceText = card.querySelector('.price').textContent;
        // Extract number from format like "$500" or "$1,200"
        return parseInt(priceText.replace(/[^0-9]/g, ''));
    };

    /**
     * Check if card matches filter criteria
     * @param {HTMLElement} card - Task card element
     * @param {Object} filters - Filter values
     * @returns {boolean} True if card matches filters
     */
    const cardMatchesFilters = (card, filters) => {
        const { category, skill, minPrice, maxPrice } = filters;

        // Category filter
        if (category) {
            const cardCategory = card.querySelector('.badge-category').textContent.toLowerCase();
            if (!cardCategory.includes(category.toLowerCase())) {
                return false;
            }
        }

        // Skill filter (matches any tag)
        if (skill) {
            const tags = Array.from(card.querySelectorAll('.tag'));
            const hasMatchingTag = tags.some(tag =>
                tag.textContent.toLowerCase().includes(skill.toLowerCase())
            );
            if (!hasMatchingTag) {
                return false;
            }
        }

        // Price range filter
        const cardPrice = getCardPrice(card);

        if (minPrice && cardPrice < minPrice) {
            return false;
        }

        if (maxPrice && cardPrice > maxPrice) {
            return false;
        }

        return true;
    };

    /**
     * Apply filters to task cards
     * @param {Object} filters - Filter values
     */
    const applyFilters = (filters) => {
        taskCards.forEach((card) => {
            if (cardMatchesFilters(card, filters)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    };

    /**
     * Get current filter values from form
     * @returns {Object} Filter values
     */
    const getFilterValues = () => {
        return {
            category: categorySelect.value.trim(),
            skill: skillInput.value.trim(),
            minPrice: minPriceInput.value ? parseInt(minPriceInput.value) : null,
            maxPrice: maxPriceInput.value ? parseInt(maxPriceInput.value) : null
        };
    };

    /**
     * Handle filter form submission
     * @param {Event} e - Submit event
     */
    const handleFilterSubmit = (e) => {
        e.preventDefault();

        const filters = getFilterValues();

        // Check if all filters are empty
        const hasFilters = filters.category || filters.skill ||
                          filters.minPrice !== null || filters.maxPrice !== null;

        if (hasFilters) {
            applyFilters(filters);
        } else {
            // Show all cards if no filters
            taskCards.forEach(card => card.style.display = '');
        }
    };

    // Attach filter form handler
    filtersForm.addEventListener('submit', handleFilterSubmit);


    // ========================================================================
    // TAB SYSTEM - Switch Between New and Top Tasks
    // ========================================================================

    /**
     * Get vote count for a card
     * @param {HTMLElement} card - Task card element
     * @returns {number} Vote count
     */
    const getVoteCount = (card) => {
        return parseInt(card.querySelector('.vote-count').textContent);
    };

    /**
     * Sort cards by vote count (descending)
     */
    const sortByTopVotes = () => {
        const visibleCards = Array.from(taskCards).filter(card =>
            card.style.display !== 'none'
        );

        // Sort by vote count descending
        visibleCards.sort((a, b) => getVoteCount(b) - getVoteCount(a));

        // Re-append in sorted order
        visibleCards.forEach(card => tasksList.appendChild(card));
    };

    /**
     * Restore original card order
     */
    const restoreOriginalOrder = () => {
        originalOrder.forEach(card => tasksList.appendChild(card));
    };

    /**
     * Handle tab switching
     * @param {Event} e - Click event
     */
    const handleTabSwitch = (e) => {
        const clickedTab = e.currentTarget;
        const tabType = clickedTab.dataset.tab;

        // Update active state
        tabs.forEach(tab => tab.classList.remove('active'));
        clickedTab.classList.add('active');

        // Apply sorting based on tab
        if (tabType === 'top') {
            sortByTopVotes();
        } else if (tabType === 'new') {
            restoreOriginalOrder();
        }
    };

    // Attach tab handlers
    tabs.forEach(tab => {
        tab.addEventListener('click', handleTabSwitch);
    });


    // ========================================================================
    // LANGUAGE SELECTOR - Handle Language Selection
    // ========================================================================

    /**
     * Initialize language selector from localStorage
     */
    const initializeLanguage = () => {
        const savedLanguage = localStorage.getItem('selected_language');
        if (savedLanguage && languageSelector) {
            languageSelector.value = savedLanguage;
        }
    };

    /**
     * Handle language selection change
     * @param {Event} e - Change event
     */
    const handleLanguageChange = (e) => {
        const selectedLanguage = e.target.value;

        // Store in localStorage
        localStorage.setItem('selected_language', selectedLanguage);

        // Log to console
        console.log(`Language changed to: ${selectedLanguage}`);
    };

    // Attach language selector handler
    if (languageSelector) {
        languageSelector.addEventListener('change', handleLanguageChange);
        initializeLanguage();
    }


    // ========================================================================
    // INITIALIZATION COMPLETE
    // ========================================================================

    console.log('Task Bounties UI initialized');

});
