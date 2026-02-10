/**
 * Icon Picker Component
 * Handles icon/emoji selection with validation
 */

class IconPicker {
    constructor() {
        this.selectedIcon = null;
        this.currentStatusId = null;
        this.onSelectCallback = null;

        // SVG icon definitions for status indicators
        this.svgIcons = {
            'check-circle': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
            'clock': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2"/></svg>',
            'pause-circle': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M10 8v8M14 8v8"/></svg>',
            'x-circle': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M15 9l-6 6M9 9l6 6"/></svg>',
            'alert-circle': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01"/></svg>',
            'info-circle': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 16v-4M12 8h.01"/></svg>',
            'star': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
            'heart': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
            'thumbs-up': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>',
            'thumbs-down': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zM17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>',
            'zap': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
            'moon': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
            'sun': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>',
            'coffee': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>',
            'eye': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
            'eye-off': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24M1 1l22 22"/></svg>',
            'user-check': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M17 11l2 2 4-4"/></svg>',
            'user-x': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M18 8l5 5M23 8l-5 5"/></svg>',
            'phone': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
            'phone-off': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.42 19.42 0 0 1-3.33-2.67m-2.67-3.34a19.79 19.79 0 0 1-3.07-8.63A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91M23 1L1 23"/></svg>',
            'mail': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>',
            'message-circle': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
            'wifi': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5 12.55a11 11 0 0 1 14.08 0M1.42 9a16 16 0 0 1 21.16 0M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01"/></svg>',
            'wifi-off': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0 1 19 12.55M5 12.55a10.94 10.94 0 0 1 5.17-2.39M10.71 5.05A16 16 0 0 1 22.58 9M1.42 9a15.91 15.91 0 0 1 4.7-2.88M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01"/></svg>',
            'shield': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
            'target': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
            'activity': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
            'trending-up': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M23 6l-9.5 9.5-5-5L1 18M17 6h6v6"/></svg>',
            'trending-down': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M23 18l-9.5-9.5-5 5L1 6M17 18h6v-6"/></svg>',
            'battery': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="1" y="6" width="18" height="12" rx="2" ry="2"/><path d="M23 13v-2"/></svg>'
        };
    }

    init() {
        this.createModal();
        this.attachEventListeners();
    }

    createModal() {
        const modalHTML = `
            <div id="iconPickerModal" class="icon-picker-modal">
                <div class="icon-picker-content">
                    <div class="icon-picker-header">
                        <h3>Select Icon</h3>
                        <span class="icon-picker-close">&times;</span>
                    </div>

                    <div class="icon-picker-tabs">
                        <button class="icon-picker-tab active" data-tab="svg">SVG Icons</button>
                        <button class="icon-picker-tab" data-tab="emoji">Emoji</button>
                    </div>

                    <div class="icon-picker-body">
                        <div id="svgTab" class="icon-picker-tab-content active">
                            <div class="icon-grid" id="iconGrid"></div>
                        </div>

                        <div id="emojiTab" class="icon-picker-tab-content">
                            <div class="emoji-input-container">
                                <label for="emojiInput">Enter up to 4 emojis</label>
                                <div class="emoji-input-wrapper">
                                    <input type="text" id="emojiInput" class="emoji-input" placeholder="😀 🎉 ✨ 🚀" maxlength="16">
                                </div>
                                <div class="emoji-validation-message" id="emojiValidation">
                                    Invalid input. Please enter only emojis (max 4).
                                </div>
                            </div>

                            <div class="emoji-preview">
                                <span class="emoji-preview-label">Preview</span>
                                <div class="emoji-preview-display" id="emojiPreview"></div>
                            </div>
                        </div>
                    </div>

                    <div class="icon-picker-footer">
                        <button type="button" class="btn btn-secondary icon-picker-clear-btn" id="clearIconBtn">Clear Icon</button>
                        <button type="button" class="btn btn-secondary" id="cancelIconBtn">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveIconBtn" disabled>Save</button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.renderSVGIcons();
    }

    renderSVGIcons() {
        const iconGrid = document.getElementById('iconGrid');

        Object.keys(this.svgIcons).forEach(iconName => {
            const iconOption = document.createElement('div');
            iconOption.className = 'icon-option';
            iconOption.dataset.icon = iconName;
            iconOption.innerHTML = this.svgIcons[iconName];
            iconGrid.appendChild(iconOption);
        });
    }

    attachEventListeners() {
        const modal = document.getElementById('iconPickerModal');
        const closeBtn = modal.querySelector('.icon-picker-close');
        const cancelBtn = document.getElementById('cancelIconBtn');
        const saveBtn = document.getElementById('saveIconBtn');
        const clearBtn = document.getElementById('clearIconBtn');
        const tabs = modal.querySelectorAll('.icon-picker-tab');
        const emojiInput = document.getElementById('emojiInput');
        const iconGrid = document.getElementById('iconGrid');

        closeBtn.addEventListener('click', () => this.close());
        cancelBtn.addEventListener('click', () => this.close());
        saveBtn.addEventListener('click', () => this.save());
        clearBtn.addEventListener('click', () => this.clearIcon());

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => this.switchTab(e.target.dataset.tab));
        });

        iconGrid.addEventListener('click', (e) => {
            const iconOption = e.target.closest('.icon-option');
            if (iconOption) {
                this.selectSVGIcon(iconOption.dataset.icon);
            }
        });

        emojiInput.addEventListener('input', (e) => this.validateEmoji(e.target.value));

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.close();
            }
        });
    }

    switchTab(tabName) {
        const tabs = document.querySelectorAll('.icon-picker-tab');
        const tabContents = document.querySelectorAll('.icon-picker-tab-content');

        tabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        tabContents.forEach(content => {
            content.classList.toggle('active', content.id === tabName + 'Tab');
        });

        this.resetSelection();
    }

    selectSVGIcon(iconName) {
        const iconOptions = document.querySelectorAll('.icon-option');
        iconOptions.forEach(option => {
            option.classList.toggle('selected', option.dataset.icon === iconName);
        });

        this.selectedIcon = `svg:${iconName}`;
        document.getElementById('saveIconBtn').disabled = false;
    }

    validateEmoji(value) {
        const emojiInput = document.getElementById('emojiInput');
        const validation = document.getElementById('emojiValidation');
        const preview = document.getElementById('emojiPreview');
        const saveBtn = document.getElementById('saveIconBtn');

        const emojiRegex = /^[\p{Emoji}\p{Emoji_Modifier}\p{Emoji_Component}\p{Emoji_Presentation}\s]{0,16}$/u;
        const emojiOnlyRegex = /^[\p{Emoji}\p{Emoji_Modifier}\p{Emoji_Component}\p{Emoji_Presentation}\s]*$/u;

        const emojis = Array.from(value.trim());
        const emojiCount = emojis.filter(char => /\p{Emoji}/u.test(char)).length;

        const isValid = emojiOnlyRegex.test(value) && emojiCount <= 4;

        if (value && !isValid) {
            emojiInput.classList.add('invalid');
            validation.classList.add('show');
            saveBtn.disabled = true;
            preview.textContent = '';
            this.selectedIcon = null;
        } else if (value && isValid) {
            emojiInput.classList.remove('invalid');
            validation.classList.remove('show');
            saveBtn.disabled = false;
            preview.textContent = value;
            this.selectedIcon = `emoji:${value}`;
        } else {
            emojiInput.classList.remove('invalid');
            validation.classList.remove('show');
            saveBtn.disabled = true;
            preview.textContent = '';
            this.selectedIcon = null;
        }
    }

    resetSelection() {
        this.selectedIcon = null;
        document.getElementById('saveIconBtn').disabled = true;

        document.querySelectorAll('.icon-option').forEach(option => {
            option.classList.remove('selected');
        });

        const emojiInput = document.getElementById('emojiInput');
        emojiInput.value = '';
        emojiInput.classList.remove('invalid');
        document.getElementById('emojiValidation').classList.remove('show');
        document.getElementById('emojiPreview').textContent = '';
    }

    open(statusId, currentIcon, callback) {
        this.currentStatusId = statusId;
        this.onSelectCallback = callback;
        this.resetSelection();

        if (currentIcon) {
            if (currentIcon.startsWith('svg:')) {
                const iconName = currentIcon.substring(4);
                this.switchTab('svg');
                this.selectSVGIcon(iconName);
            } else if (currentIcon.startsWith('emoji:')) {
                const emoji = currentIcon.substring(6);
                this.switchTab('emoji');
                document.getElementById('emojiInput').value = emoji;
                this.validateEmoji(emoji);
            }
        }

        document.getElementById('iconPickerModal').style.display = 'flex';
    }

    close() {
        document.getElementById('iconPickerModal').style.display = 'none';
        this.resetSelection();
    }

    save() {
        if (this.selectedIcon && this.onSelectCallback) {
            this.onSelectCallback(this.currentStatusId, this.selectedIcon);
        }
        this.close();
    }

    clearIcon() {
        if (this.onSelectCallback) {
            this.onSelectCallback(this.currentStatusId, null);
        }
        this.close();
    }

    static renderIcon(iconValue) {
        if (!iconValue) return '';

        if (iconValue.startsWith('svg:')) {
            const iconName = iconValue.substring(4);
            const picker = new IconPicker();
            return picker.svgIcons[iconName] || '';
        } else if (iconValue.startsWith('emoji:')) {
            return iconValue.substring(6);
        }

        return iconValue;
    }
}

let iconPickerInstance = null;

function initIconPicker() {
    if (!iconPickerInstance) {
        iconPickerInstance = new IconPicker();
        iconPickerInstance.init();
    }
    return iconPickerInstance;
}

function openIconPicker(statusId, currentIcon, callback) {
    const picker = initIconPicker();
    picker.open(statusId, currentIcon, callback);
}
