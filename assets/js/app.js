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
     * Initialize vote counts from localStorage on page load
     */
    const initializeVotes = () => {
        taskCards.forEach((card, index) => {
            const voteData = getVoteData(index);
            const voteCountSpan = card.querySelector('.vote-count');
            const upvoteBtn = card.querySelector('.vote-up');
            const downvoteBtn = card.querySelector('.vote-down');
            const upIcon = upvoteBtn.querySelector('.vote-icon');
            const downIcon = downvoteBtn.querySelector('.vote-icon');

            // Update vote count if stored
            if (voteData.count !== null) {
                voteCountSpan.textContent = voteData.count;
            }

            // Apply voted class and icon based on user's previous vote
            if (voteData.userVote === 'up') {
                upvoteBtn.classList.add('voted');
                upIcon.src = upIcon.dataset.selected;
            } else if (voteData.userVote === 'down') {
                downvoteBtn.classList.add('voted');
                downIcon.src = downIcon.dataset.selected;
            }
        });

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
     * Get vote data from localStorage
     * @param {number} index - Card index
     * @returns {Object} Vote data with count and userVote
     */
    const getVoteData = (index) => {
        const key = `vote_bounty_${index}`;
        const data = localStorage.getItem(key);

        if (data) {
            return JSON.parse(data);
        }

        // Default: get current count from DOM
        const card = taskCards[index];
        const currentCount = parseInt(card.querySelector('.vote-count').textContent);

        return {
            count: currentCount,
            userVote: null
        };
    };

    /**
     * Save vote data to localStorage
     * @param {number} index - Card index
     * @param {Object} voteData - Vote data object
     */
    const saveVoteData = (index, voteData) => {
        const key = `vote_bounty_${index}`;
        localStorage.setItem(key, JSON.stringify(voteData));
    };

    /**
     * Handle vote button clicks
     * @param {Event} e - Click event
     */
    const handleVote = (e) => {
        const voteBtn = e.currentTarget;
        const card = voteBtn.closest('.task-card');
        const cardIndex = Array.from(taskCards).indexOf(card);
        const voteCountSpan = card.querySelector('.vote-count');
        const upvoteBtn = card.querySelector('.vote-up');
        const downvoteBtn = card.querySelector('.vote-down');
        const upIcon = upvoteBtn.querySelector('.vote-icon');
        const downIcon = downvoteBtn.querySelector('.vote-icon');
        const isUpvote = voteBtn.classList.contains('vote-up');

        // Get current vote data
        const voteData = getVoteData(cardIndex);
        let newCount = voteData.count;
        let newUserVote = voteData.userVote;

        // Determine new vote state
        if (isUpvote) {
            if (newUserVote === 'up') {
                // Remove upvote
                newCount--;
                newUserVote = null;
                upvoteBtn.classList.remove('voted');
                upIcon.src = upIcon.dataset.default;
            } else if (newUserVote === 'down') {
                // Switch from downvote to upvote
                newCount += 2;
                newUserVote = 'up';
                downvoteBtn.classList.remove('voted');
                downIcon.src = downIcon.dataset.default;
                upvoteBtn.classList.add('voted');
                upIcon.src = upIcon.dataset.selected;
            } else {
                // Add upvote
                newCount++;
                newUserVote = 'up';
                upvoteBtn.classList.add('voted');
                upIcon.src = upIcon.dataset.selected;
            }
        } else {
            // Downvote
            if (newUserVote === 'down') {
                // Remove downvote
                newCount++;
                newUserVote = null;
                downvoteBtn.classList.remove('voted');
                downIcon.src = downIcon.dataset.default;
            } else if (newUserVote === 'up') {
                // Switch from upvote to downvote
                newCount -= 2;
                newUserVote = 'down';
                upvoteBtn.classList.remove('voted');
                upIcon.src = upIcon.dataset.default;
                downvoteBtn.classList.add('voted');
                downIcon.src = downIcon.dataset.selected;
            } else {
                // Add downvote
                newCount--;
                newUserVote = 'down';
                downvoteBtn.classList.add('voted');
                downIcon.src = downIcon.dataset.selected;
            }
        }

        // Update UI
        voteCountSpan.textContent = newCount;

        // Save to localStorage
        saveVoteData(cardIndex, {
            count: newCount,
            userVote: newUserVote
        });
    };

    // Attach vote handlers to all vote buttons
    taskCards.forEach((card) => {
        const upvoteBtn = card.querySelector('.vote-up');
        const downvoteBtn = card.querySelector('.vote-down');

        upvoteBtn.addEventListener('click', handleVote);
        downvoteBtn.addEventListener('click', handleVote);
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
