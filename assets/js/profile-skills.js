/**
 * Profile Skills Management
 * Handles the skills modal, autocomplete, and API interactions
 */

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSkillsManagement);
} else {
    initSkillsManagement();
}

let allSkills = [];
let currentSkills = [];
let selectedIndex = -1;

function initSkillsManagement() {
    // Load all available skills from API
    loadAvailableSkills();
    
    // Set up search input listener
    const searchInput = document.getElementById('skillSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
        searchInput.addEventListener('keydown', handleKeyboardNavigation);
    }
    
    // Close modal when clicking outside
    const modal = document.getElementById('skillsModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeSkillsModal();
            }
        });
    }
}

/**
 * Open the skills management modal
 */
function openSkillsModal() {
    const modal = document.getElementById('skillsModal');
    if (modal) {
        modal.classList.add('active');
        loadCurrentSkills();
        
        // Focus search input
        const searchInput = document.getElementById('skillSearchInput');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 100);
        }
    }
}

/**
 * Close the skills management modal
 */
function closeSkillsModal() {
    const modal = document.getElementById('skillsModal');
    if (modal) {
        modal.classList.remove('active');
        
        // Clear search input
        const searchInput = document.getElementById('skillSearchInput');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Hide autocomplete
        const autocomplete = document.getElementById('skillAutocomplete');
        if (autocomplete) {
            autocomplete.classList.remove('active');
            autocomplete.innerHTML = '';
        }
        
        // Reload page to show updated skills
        window.location.reload();
    }
}

/**
 * Load all available skills from the API
 */
async function loadAvailableSkills() {
    try {
        const response = await fetch('/api/skills.php?action=search&query=%');
        const data = await response.json();

        if (data.success) {
            allSkills = data.data || [];
        } else {
            console.error('Failed to load skills:', data.error);
        }
    } catch (error) {
        console.error('Error loading skills:', error);
    }
}

/**
 * Load current skills for the profile
 */
async function loadCurrentSkills() {
    try {
        const response = await fetch(`/api/skills.php?action=user_skills&profile_id=${PROFILE_ID}`);
        const data = await response.json();

        if (data.success) {
            currentSkills = data.data || [];
            renderCurrentSkills();
        } else {
            console.error('Failed to load current skills:', data.error);
        }
    } catch (error) {
        console.error('Error loading current skills:', error);
    }
}

/**
 * Render the current skills list in the modal
 */
function renderCurrentSkills() {
    const container = document.getElementById('currentSkillsList');
    if (!container) return;

    if (currentSkills.length === 0) {
        container.innerHTML = '<div class="current-skills-empty">no skills added yet. search above to add skills.</div>';
        return;
    }

    container.innerHTML = currentSkills.map(skill => {
        const statusClass = skill.type === 'pending' ? 'pending' : 'approved';
        const nameClass = skill.type === 'pending' ? 'pending' : '';

        return `
            <div class="current-skill-item ${statusClass}">
                <div class="current-skill-info">
                    <span class="current-skill-name ${nameClass}">#${skill.name}</span>
                    <span class="current-skill-status ${statusClass}">${skill.type}</span>
                    ${skill.proficiency_level ? `<span class="current-skill-proficiency">${skill.proficiency_level}</span>` : ''}
                </div>
                <button class="remove-skill-btn" onclick="removeSkill(${skill.id})" aria-label="Remove skill">
                    remove
                </button>
            </div>
        `;
    }).join('');
}

/**
 * Handle search input for skill autocomplete
 */
function handleSearchInput(e) {
    const query = e.target.value.trim().toLowerCase();
    const autocomplete = document.getElementById('skillAutocomplete');
    
    if (!autocomplete) return;
    
    if (query.length === 0) {
        autocomplete.classList.remove('active');
        autocomplete.innerHTML = '';
        selectedIndex = -1;
        return;
    }
    
    // Filter skills that match the query and aren't already added
    const currentSkillIds = currentSkills.map(s => s.id);
    const filtered = allSkills.filter(skill =>
        skill.name.toLowerCase().includes(query) &&
        !currentSkillIds.includes(skill.id)
    );
    
    if (filtered.length === 0) {
        autocomplete.innerHTML = '<div class="autocomplete-no-results">no matching skills found</div>';
        autocomplete.classList.add('active');
        selectedIndex = -1;
        return;
    }
    
    // Render autocomplete items
    autocomplete.innerHTML = filtered.map((skill, index) => 
        `<div class="autocomplete-item" data-skill-id="${skill.id}" data-index="${index}">${skill.name}</div>`
    ).join('');
    
    autocomplete.classList.add('active');
    selectedIndex = -1;
    
    // Add click listeners to autocomplete items
    autocomplete.querySelectorAll('.autocomplete-item').forEach(item => {
        item.addEventListener('click', () => {
            const skillId = parseInt(item.getAttribute('data-skill-id'));
            addSkill(skillId);
        });
    });
}

/**
 * Handle keyboard navigation in autocomplete
 */
function handleKeyboardNavigation(e) {
    const autocomplete = document.getElementById('skillAutocomplete');
    if (!autocomplete || !autocomplete.classList.contains('active')) return;
    
    const items = autocomplete.querySelectorAll('.autocomplete-item');
    if (items.length === 0) return;
    
    switch(e.key) {
        case 'ArrowDown':
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            updateSelectedItem(items);
            break;
            
        case 'ArrowUp':
            e.preventDefault();
            selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
            updateSelectedItem(items);
            break;
            
        case 'Enter':
            e.preventDefault();
            if (selectedIndex >= 0 && selectedIndex < items.length) {
                const skillId = parseInt(items[selectedIndex].getAttribute('data-skill-id'));
                addSkill(skillId);
            }
            break;
            
        case 'Escape':
            e.preventDefault();
            autocomplete.classList.remove('active');
            selectedIndex = -1;
            break;
    }
}

/**
 * Update visual selection in autocomplete
 */
function updateSelectedItem(items) {
    items.forEach((item, index) => {
        if (index === selectedIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('selected');
        }
    });
}

/**
 * Add a skill to the profile
 */
async function addSkill(skillId) {
    try {
        // Find the skill name from allSkills
        const skill = allSkills.find(s => s.id === skillId);
        if (!skill) {
            alert('Skill not found');
            return;
        }

        const response = await fetch('/api/skills.php?action=add_to_profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                profile_id: PROFILE_ID,
                skill_name: skill.name
            })
        });

        const data = await response.json();

        if (data.success) {
            // Clear search input
            const searchInput = document.getElementById('skillSearchInput');
            if (searchInput) {
                searchInput.value = '';
            }

            // Hide autocomplete
            const autocomplete = document.getElementById('skillAutocomplete');
            if (autocomplete) {
                autocomplete.classList.remove('active');
                autocomplete.innerHTML = '';
            }

            // Reload current skills
            await loadCurrentSkills();
        } else {
            alert('Failed to add skill: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error adding skill:', error);
        alert('Failed to add skill. Please try again.');
    }
}

/**
 * Remove a skill from the profile
 */
async function removeSkill(skillId) {
    if (!confirm('Are you sure you want to remove this skill?')) {
        return;
    }

    try {
        const response = await fetch('/api/skills.php?action=remove_from_profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                profile_id: PROFILE_ID,
                skill_id: skillId
            })
        });

        const data = await response.json();

        if (data.success) {
            // Reload current skills
            await loadCurrentSkills();
        } else {
            alert('Failed to remove skill: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error removing skill:', error);
        alert('Failed to remove skill. Please try again.');
    }
}

// Make functions globally available
window.openSkillsModal = openSkillsModal;
window.closeSkillsModal = closeSkillsModal;
window.removeSkill = removeSkill;
