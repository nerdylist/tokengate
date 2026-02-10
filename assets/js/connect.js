/* ============================================
   CONNECT.JS - AUTH PAGE FUNCTIONALITY
   Login/Register toggle, validation, AJAX authentication
   ============================================ */

// === STATE ===
let isLoginMode = true;

// === DOM ELEMENTS ===
const authForm = document.getElementById('auth-form');
const authTitle = document.getElementById('auth-title');
const authSubtitle = document.getElementById('auth-subtitle');
const submitBtn = document.getElementById('submit-btn');
const toggleBtn = document.getElementById('toggle-btn');
const toggleText = document.getElementById('toggle-text');
const confirmPasswordGroup = document.getElementById('confirm-password-group');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm-password');

// === TOAST FUNCTIONS ===
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icon = document.createElement('span');
    icon.className = 'toast-icon';
    icon.textContent = type === 'success' ? '✓' : '✕';

    const messageSpan = document.createElement('span');
    messageSpan.className = 'toast-message';
    messageSpan.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.className = 'toast-close';
    closeBtn.innerHTML = '&times;';
    closeBtn.onclick = () => toast.remove();

    toast.appendChild(icon);
    toast.appendChild(messageSpan);
    toast.appendChild(closeBtn);

    toastContainer.appendChild(toast);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// === VALIDATION FUNCTIONS ===
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePassword(password) {
    // At least 6 characters
    return password.length >= 6;
}

// === TOGGLE BETWEEN LOGIN/REGISTER ===
function toggleMode() {
    isLoginMode = !isLoginMode;

    if (isLoginMode) {
        // Switch to login mode
        authTitle.textContent = 'login';
        authSubtitle.textContent = 'enter your credentials to continue';
        submitBtn.textContent = 'login';
        toggleText.textContent = "don't have an account?";
        toggleBtn.textContent = 'sign up here';
        confirmPasswordGroup.style.display = 'none';
        confirmPasswordInput.removeAttribute('required');
    } else {
        // Switch to register mode
        authTitle.textContent = 'register';
        authSubtitle.textContent = 'create a new account to get started';
        submitBtn.textContent = 'sign up';
        toggleText.textContent = 'already have an account?';
        toggleBtn.textContent = 'login here';
        confirmPasswordGroup.style.display = 'flex';
        confirmPasswordInput.setAttribute('required', 'required');
    }
}

// === FORM SUBMISSION ===
function handleSubmit(e) {
    e.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Validate email
    if (!validateEmail(email)) {
        showToast('Please enter a valid email address', 'error');
        return;
    }

    // Validate password
    if (!validatePassword(password)) {
        showToast('Password must be at least 6 characters long', 'error');
        return;
    }

    // If register mode, check password confirmation
    if (!isLoginMode) {
        if (password !== confirmPassword) {
            showToast('Passwords do not match', 'error');
            return;
        }

        // Register user
        handleRegister(email, password);
    } else {
        // Login user
        handleLogin(email, password);
    }
}

// === REGISTER HANDLER ===
function handleRegister(email, password) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating account...';

    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    formData.append('confirm_password', confirmPasswordInput.value);

    fetch('api/auth.php?action=register', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showToast(data.error, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'sign up';
        }
    })
    .catch(error => {
        showToast('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'sign up';
    });
}

// === LOGIN HANDLER ===
function handleLogin(email, password) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';

    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    fetch('api/auth.php?action=login', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showToast(data.error, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'login';
        }
    })
    .catch(error => {
        showToast('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'login';
    });
}

// === EVENT LISTENERS ===
toggleBtn.addEventListener('click', toggleMode);
authForm.addEventListener('submit', handleSubmit);
