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
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    const userEmail = localStorage.getItem('userEmail');

    // Update login bar visibility
    updateLoginBarState(isLoggedIn, userEmail);

    // Set minimum date for deadline to today
    const deadlineInput = document.getElementById('deadline');
    if (deadlineInput) {
        const today = new Date().toISOString().split('T')[0];
        deadlineInput.setAttribute('min', today);
    }
}

// === UPDATE LOGIN BAR STATE ===
function updateLoginBarState(isLoggedIn, userEmail) {
    const guestBar = document.getElementById('login-bar-guest');
    const userBar = document.getElementById('login-bar-user');
    const guestNote = document.getElementById('guest-note');
    const usernameDisplay = document.getElementById('username-display');

    if (isLoggedIn && userEmail) {
        // Show logged-in state
        guestBar.style.display = 'none';
        userBar.style.display = 'flex';
        if (guestNote) guestNote.style.display = 'none';

        // Extract username from email (part before @)
        const username = userEmail.split('@')[0];
        if (usernameDisplay) usernameDisplay.textContent = username;
    } else {
        // Show guest state
        guestBar.style.display = 'flex';
        userBar.style.display = 'none';
        if (guestNote) guestNote.style.display = 'block';
    }
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

    // Simulate AJAX login (in real app, this would be an API call)
    // TODO: Replace with actual API call to login endpoint
    localStorage.setItem('isLoggedIn', 'true');
    localStorage.setItem('userEmail', email);

    // Show success message
    showMessage('Successfully logged in!', 'success');

    // Update UI
    updateLoginBarState(true, email);

    // Clear password field
    document.getElementById('quick-password').value = '';
}

function handleLogout() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('userEmail');

    showMessage('Successfully logged out', 'success');

    // Update UI
    updateLoginBarState(false, null);

    // Clear login fields
    document.getElementById('quick-email').value = '';
    document.getElementById('quick-password').value = '';
}

// === BOUNTY FORM HANDLING ===
function handleBountySubmit(e) {
    e.preventDefault();

    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    if (!isLoggedIn) {
        showMessage('Please login to submit a bounty', 'error');
        // Optionally redirect to connect.php
        // setTimeout(() => { window.location.href = 'connect.php'; }, 1500);
        return;
    }

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

    // Simulate API submission (in real app, this would be a POST request)
    console.log('Bounty data:', formData);

    // Show success message
    showSuccessMessage();

    // Clear form
    document.getElementById('bounty-form').reset();

    // Scroll to top to see success message
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
