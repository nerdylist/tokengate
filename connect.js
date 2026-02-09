/* ============================================
   CONNECT.JS - AUTH PAGE FUNCTIONALITY
   Login/Register toggle, validation, localStorage
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
    // Check if user already exists
    const existingUser = localStorage.getItem(`user_${email}`);

    if (existingUser) {
        showToast('An account with this email already exists', 'error');
        return;
    }

    // Store user data
    const userData = {
        email: email,
        password: password, // In production, this would be hashed
        createdAt: new Date().toISOString()
    };

    localStorage.setItem(`user_${email}`, JSON.stringify(userData));

    // Set logged in state
    localStorage.setItem('isLoggedIn', 'true');
    localStorage.setItem('userEmail', email);

    showToast('Account created successfully! Redirecting...', 'success');

    // Redirect after 1.5 seconds
    setTimeout(() => {
        window.location.href = 'hire.php';
    }, 1500);
}

// === LOGIN HANDLER ===
function handleLogin(email, password) {
    // Check if user exists
    const userDataStr = localStorage.getItem(`user_${email}`);

    if (!userDataStr) {
        showToast('No account found with this email', 'error');
        return;
    }

    const userData = JSON.parse(userDataStr);

    // Check password
    if (userData.password !== password) {
        showToast('Incorrect password', 'error');
        return;
    }

    // Set logged in state
    localStorage.setItem('isLoggedIn', 'true');
    localStorage.setItem('userEmail', email);

    showToast('Login successful! Redirecting...', 'success');

    // Redirect after 1.5 seconds
    setTimeout(() => {
        window.location.href = 'hire.php';
    }, 1500);
}

// === CHECK IF ALREADY LOGGED IN ===
function checkLoginStatus() {
    const isLoggedIn = localStorage.getItem('isLoggedIn');

    if (isLoggedIn === 'true') {
        // User is already logged in, redirect to hire page
        window.location.href = 'hire.php';
    }
}

// === EVENT LISTENERS ===
toggleBtn.addEventListener('click', toggleMode);
authForm.addEventListener('submit', handleSubmit);

// === INITIALIZATION ===
checkLoginStatus();
