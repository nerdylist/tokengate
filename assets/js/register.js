/* ============================================
   REGISTER.JS - REGISTRATION FUNCTIONALITY
   Form validation, password strength, skills autocomplete
   ============================================ */

// === STATE ===
let selectedSkills = [];
let skillsCache = [];
let debounceTimer = null;

// === DOM ELEMENTS ===
const registrationForm = document.getElementById('registration-form');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const displayNameInput = document.getElementById('display_name');
const bioInput = document.getElementById('bio');
const hourlyRateInput = document.getElementById('hourly_rate');
const skillsInput = document.getElementById('skills_input');
const skillsDropdown = document.getElementById('skills-dropdown');
const selectedSkillsContainer = document.getElementById('selected-skills');
const registerBtn = document.getElementById('register-btn');
const passwordStrength = document.getElementById('password-strength');
const strengthFill = document.getElementById('strength-fill');
const strengthText = document.getElementById('strength-text');

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
    return password.length >= 8;
}

// === PASSWORD STRENGTH CHECKER ===
function checkPasswordStrength(password) {
    if (password.length === 0) {
        passwordStrength.classList.remove('active');
        return;
    }

    passwordStrength.classList.add('active');

    let strength = 0;
    let strengthLevel = 'weak';

    // Length check
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;

    // Contains uppercase
    if (/[A-Z]/.test(password)) strength++;

    // Contains lowercase
    if (/[a-z]/.test(password)) strength++;

    // Contains number
    if (/[0-9]/.test(password)) strength++;

    // Contains special character
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    // Determine strength level
    if (strength <= 2) {
        strengthLevel = 'weak';
    } else if (strength <= 4) {
        strengthLevel = 'medium';
    } else {
        strengthLevel = 'strong';
    }

    // Update UI
    strengthFill.className = 'strength-fill ' + strengthLevel;
    strengthText.className = 'strength-text ' + strengthLevel;
    strengthText.textContent = strengthLevel;
}

// === SKILLS AUTOCOMPLETE ===
async function searchSkills(query) {
    if (query.length < 2) {
        skillsDropdown.classList.remove('active');
        return;
    }

    try {
        const response = await fetch(`/api/skills.php?action=search&query=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success && data.data) {
            skillsCache = data.data;
            displaySkillOptions(data.data);
        } else {
            skillsDropdown.classList.remove('active');
        }
    } catch (error) {
        console.error('Error fetching skills:', error);
        skillsDropdown.classList.remove('active');
    }
}

function displaySkillOptions(skills) {
    skillsDropdown.innerHTML = '';

    if (skills.length === 0) {
        skillsDropdown.classList.remove('active');
        return;
    }

    // Filter out already selected skills
    const availableSkills = skills.filter(skill =>
        !selectedSkills.find(s => s.id === skill.id)
    );

    if (availableSkills.length === 0) {
        skillsDropdown.classList.remove('active');
        return;
    }

    availableSkills.forEach(skill => {
        const option = document.createElement('div');
        option.className = 'skill-option';
        option.textContent = skill.name;
        option.dataset.skillId = skill.id;
        option.dataset.skillName = skill.name;
        option.addEventListener('click', () => selectSkill(skill));
        skillsDropdown.appendChild(option);
    });

    skillsDropdown.classList.add('active');
}

function selectSkill(skill) {
    // Check if already selected
    if (selectedSkills.find(s => s.id === skill.id)) {
        return;
    }

    // Add to selected skills
    selectedSkills.push(skill);

    // Update UI
    renderSelectedSkills();

    // Clear input and dropdown
    skillsInput.value = '';
    skillsDropdown.classList.remove('active');
}

function removeSkill(skillId) {
    selectedSkills = selectedSkills.filter(s => s.id !== skillId);
    renderSelectedSkills();
}

function renderSelectedSkills() {
    selectedSkillsContainer.innerHTML = '';

    if (selectedSkills.length === 0) {
        return;
    }

    selectedSkills.forEach(skill => {
        const chip = document.createElement('div');
        chip.className = 'skill-chip';

        const name = document.createElement('span');
        name.className = 'skill-chip-name';
        name.textContent = skill.name;

        const removeBtn = document.createElement('button');
        removeBtn.className = 'skill-chip-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.type = 'button';
        removeBtn.addEventListener('click', () => removeSkill(skill.id));

        chip.appendChild(name);
        chip.appendChild(removeBtn);
        selectedSkillsContainer.appendChild(chip);
    });
}

// === FORM SUBMISSION ===
async function handleSubmit(e) {
    e.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const displayName = displayNameInput.value.trim();
    const bio = bioInput.value.trim();
    const hourlyRate = hourlyRateInput.value;

    // Validate email
    if (!validateEmail(email)) {
        showToast('Please enter a valid email address', 'error');
        return;
    }

    // Validate display name
    if (displayName.length === 0) {
        showToast('Display name is required', 'error');
        return;
    }

    // Validate password
    if (!validatePassword(password)) {
        showToast('Password must be at least 8 characters long', 'error');
        return;
    }

    // Validate password confirmation
    if (password !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }

    // Disable submit button
    registerBtn.disabled = true;
    registerBtn.textContent = 'creating account...';

    // Build form data
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    formData.append('confirm_password', confirmPassword);
    formData.append('display_name', displayName);
    formData.append('bio', bio);

    if (hourlyRate) {
        formData.append('hourly_rate', hourlyRate);
    }

    // Add selected skills
    if (selectedSkills.length > 0) {
        selectedSkills.forEach(skill => {
            formData.append('skills[]', skill.id);
        });
    }

    try {
        const response = await fetch('/api/register.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showToast(data.error, 'error');
            registerBtn.disabled = false;
            registerBtn.textContent = 'create account';
        }
    } catch (error) {
        console.error('Registration error:', error);
        showToast('An error occurred. Please try again.', 'error');
        registerBtn.disabled = false;
        registerBtn.textContent = 'create account';
    }
}

// === EVENT LISTENERS ===

// Password strength indicator
passwordInput.addEventListener('input', (e) => {
    checkPasswordStrength(e.target.value);
});

// Skills autocomplete with debounce
skillsInput.addEventListener('input', (e) => {
    const query = e.target.value.trim();

    // Clear previous timer
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    // Set new timer
    debounceTimer = setTimeout(() => {
        searchSkills(query);
    }, 300);
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!skillsInput.contains(e.target) && !skillsDropdown.contains(e.target)) {
        skillsDropdown.classList.remove('active');
    }
});

// Form submission
registrationForm.addEventListener('submit', handleSubmit);
