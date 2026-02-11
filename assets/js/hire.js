// ============================================
// HIRE PAGE - BOUNTY CREATION FORM LOGIC
// Handles login state, form validation, and submission
// ============================================

// === INITIALIZATION ===
window.addEventListener('DOMContentLoaded', () => {
    initializePage();
    setupEventListeners();
});

// === INITIALIZE PAGE STATE ===
function initializePage() {
    // Set minimum date for deadline to today
    const deadlineInput = document.getElementById('deadline');
    if (deadlineInput) {
        const today = new Date().toISOString().split('T')[0];
        deadlineInput.setAttribute('min', today);
    }

    // Load ranks for rank selection
    loadRanks();
}

// === LOAD RANKS FROM API ===
function loadRanks() {
    const rankSelect = document.getElementById('rank');
    if (!rankSelect) return;

    fetch('/api/ranks.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Clear loading option
                rankSelect.innerHTML = '';

                // Add default "no requirement" option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'no rank requirement';
                rankSelect.appendChild(defaultOption);

                // Deduplicate ranks by name (keep first occurrence)
                const uniqueRanks = [];
                const seenNames = new Set();

                data.data.forEach(rank => {
                    if (!seenNames.has(rank.name)) {
                        seenNames.add(rank.name);
                        uniqueRanks.push(rank);
                    }
                });

                // Sort by level (ascending - lowest first)
                uniqueRanks.sort((a, b) => a.level - b.level);

                // Add all ranks in a single flat list
                uniqueRanks.forEach(rank => {
                    const option = document.createElement('option');
                    option.value = rank.id;
                    option.textContent = rank.name;
                    rankSelect.appendChild(option);
                });
            } else {
                rankSelect.innerHTML = '<option value="">no ranks available</option>';
            }
        })
        .catch(error => {
            console.error('Error loading ranks:', error);
            rankSelect.innerHTML = '<option value="">error loading ranks</option>';
        });
}

// === SETUP EVENT LISTENERS ===
function setupEventListeners() {
    // Quick login button
    const quickLoginBtn = document.getElementById('quick-login-btn');
    if (quickLoginBtn) {
        quickLoginBtn.addEventListener('click', handleQuickLogin);
    }

    // Quick login on Enter key
    const quickEmail = document.getElementById('quick-email');
    const quickPassword = document.getElementById('quick-password');
    if (quickEmail) quickEmail.addEventListener('keypress', handleLoginKeypress);
    if (quickPassword) quickPassword.addEventListener('keypress', handleLoginKeypress);

    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }

    // Bounty form submission
    const bountyForm = document.getElementById('bounty-form');
    if (bountyForm) {
        bountyForm.addEventListener('submit', handleBountySubmit);
    }
}

// === QUICK LOGIN HANDLING ===
function handleLoginKeypress(e) {
    if (e.key === 'Enter') {
        handleQuickLogin();
    }
}

function handleQuickLogin() {
    const email = document.getElementById('quick-email').value.trim();
    const password = document.getElementById('quick-password').value;

    // Basic validation
    if (!email || !password) {
        showMessage('Please enter both email and password', 'error');
        return;
    }

    if (!validateEmail(email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }

    if (password.length < 6) {
        showMessage('Password must be at least 6 characters', 'error');
        return;
    }

    // Show loading state
    const loginBtn = document.getElementById('quick-login-btn');
    const originalText = loginBtn.textContent;
    loginBtn.textContent = 'logging in...';
    loginBtn.disabled = true;

    // Make API call to login endpoint
    fetch('/api/auth.php?action=login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || 'Successfully logged in!', 'success');
            // Reload page to update login state
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showMessage(data.error || 'Login failed', 'error');
            loginBtn.textContent = originalText;
            loginBtn.disabled = false;
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
        loginBtn.textContent = originalText;
        loginBtn.disabled = false;
        console.error('Login error:', error);
    });
}

function handleLogout() {
    // Make API call to logout endpoint
    fetch('/api/auth.php?action=logout', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || 'Successfully logged out', 'success');
            // Reload page to update login state
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showMessage(data.error || 'Logout failed', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
        console.error('Logout error:', error);
    });
}

// === BOUNTY FORM HANDLING ===
function handleBountySubmit(e) {
    e.preventDefault();

    // Get form values
    const formData = {
        title: document.getElementById('title').value.trim(),
        category: document.getElementById('category').value,
        description: document.getElementById('description').value.trim(),
        skills: document.getElementById('skills').value.trim(),
        budget: document.getElementById('budget').value,
        paymentType: document.querySelector('input[name="payment_type"]:checked').value,
        estimatedHours: document.getElementById('estimated-hours').value,
        deadline: document.getElementById('deadline').value,
        spots: document.getElementById('spots').value,
        location: document.getElementById('location').value.trim(),
        remoteOk: document.getElementById('remote-ok').checked
    };

    // Validate form
    const validationErrors = validateBountyForm(formData);

    if (validationErrors.length > 0) {
        showMessage(validationErrors[0], 'error');
        return;
    }

    // Show loading state
    const submitBtn = document.querySelector('.btn-primary');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'posting...';
    submitBtn.disabled = true;

    // Parse skills from comma-separated string to array of skill names
    const skillNames = formData.skills
        ? formData.skills.split(',').map(s => s.trim()).filter(s => s.length > 0)
        : [];

    // Get category ID from category name
    // For now, we'll need to map category names to IDs
    const categoryMap = {
        'documentation': 1,
        'design': 2,
        'research': 3,
        'development': 4,
        'testing': 5,
        'devops': 6,
        'writing': 7,
        'other': 8
    };

    // Get selected rank ID (single selection)
    const rankSelect = document.getElementById('rank');
    const selectedRankId = rankSelect.value ? parseInt(rankSelect.value) : null;

    // Prepare API data
    const apiData = {
        title: formData.title,
        description: formData.description,
        category_id: categoryMap[formData.category] || 8,
        budget_min: parseFloat(formData.budget),
        budget_max: parseFloat(formData.budget),
        deadline: formData.deadline || null,
        skills: [], // TODO: Convert skill names to IDs via API
        rank_id: selectedRankId
    };

    // Make API call to create bounty
    fetch('/api/bounties.php?action=create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(apiData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage();
            // Clear form
            document.getElementById('bounty-form').reset();
            // Scroll to top to see success message
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            showMessage(data.error || 'Failed to create bounty', 'error');
        }
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        console.error('Bounty creation error:', error);
    });
}

// === FORM VALIDATION ===
function validateBountyForm(data) {
    const errors = [];

    if (!data.title) {
        errors.push('Task title is required');
    }

    if (!data.category) {
        errors.push('Please select a category');
    }

    if (!data.description) {
        errors.push('Description is required');
    }

    if (!data.budget || data.budget < 1) {
        errors.push('Budget must be at least $1');
    }

    if (!data.deadline) {
        errors.push('Deadline is required');
    } else {
        const deadlineDate = new Date(data.deadline);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (deadlineDate < today) {
            errors.push('Deadline must be today or in the future');
        }
    }

    if (!data.spots || data.spots < 1) {
        errors.push('Number of spots must be at least 1');
    }

    if (data.spots > 20) {
        errors.push('Number of spots cannot exceed 20');
    }

    return errors;
}

// === EMAIL VALIDATION ===
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// === MESSAGE DISPLAY ===
function showMessage(message, type) {
    // Remove any existing messages
    const existingMessage = document.querySelector('.message-alert');
    if (existingMessage) {
        existingMessage.remove();
    }

    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-alert message-${type}`;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        font-family: 'Share Tech Mono', monospace;
        font-size: 0.9375rem;
        font-weight: 600;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        max-width: 400px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    `;

    if (type === 'success') {
        messageDiv.style.backgroundColor = '#14532d';
        messageDiv.style.border = '1px solid #166534';
        messageDiv.style.color = '#4ade80';
    } else {
        messageDiv.style.backgroundColor = '#7f1d1d';
        messageDiv.style.border = '1px solid #991b1b';
        messageDiv.style.color = '#fca5a5';
    }

    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);

    // Auto-remove after 4 seconds
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            messageDiv.remove();
        }, 300);
    }, 4000);
}

function showSuccessMessage() {
    // Remove any existing success messages
    const existingSuccess = document.querySelector('.success-message');
    if (existingSuccess) {
        existingSuccess.remove();
    }

    // Create success message element
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.innerHTML = `
        <div class="success-message-icon">✓</div>
        <div class="success-message-text">
            Bounty posted successfully! Humans will start applying soon.
        </div>
    `;

    // Insert at the top of the form card
    const formCard = document.querySelector('.form-card');
    formCard.insertBefore(successDiv, formCard.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        successDiv.style.opacity = '0';
        successDiv.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            successDiv.remove();
        }, 300);
    }, 5000);
}

// === ANIMATIONS ===
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

console.log('Hire page initialized. Use handleLogout() from console if needed.');
