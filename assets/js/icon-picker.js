/**
 * Icon Picker Component
 * Handles icon/emoji selection with validation
 */

class IconPicker {
    constructor() {
        this.selectedIcon = null;
        this.currentStatusId = null;
        this.onSelectCallback = null;

        // FontAwesome icon names from https://nerd.biz/assets/fa/svgs/solid/
        this.iconNames = [
            'circle-check', 'circle-xmark', 'circle-pause', 'circle-dot', 'circle-info', 'circle-user',
            'skull', 'skull-crossbones', 'dice', 'coffee', 'star', 'heart', 'comment', 'bolt', 'clock',
            'bell', 'phone', 'phone-slash', 'eye', 'eye-slash', 'moon', 'sun', 'wifi', 'signal',
            'user-check', 'user-xmark', 'fire', 'flame', 'rocket', 'gem', 'crown', 'gift', 'trophy', 'medal',
            'flag', 'bookmark', 'calendar', 'envelope', 'folder', 'camera', 'music', 'video', 'gamepad',
            'puzzle-piece', 'lightbulb', 'wrench', 'hammer', 'screwdriver', 'paintbrush', 'palette', 'brush',
            'pen', 'pencil', 'code', 'terminal', 'laptop', 'mobile', 'desktop', 'tablet',
            'car', 'plane', 'ship', 'bicycle', 'train', 'bus', 'motorcycle', 'truck',
            'tree', 'leaf', 'seedling', 'mountain', 'snowflake', 'cloud', 'umbrella', 'droplet',
            'flask', 'atom', 'magnet', 'fingerprint', 'dna', 'microscope',
            'lock', 'unlock', 'key', 'shield', 'shield-halved', 'user', 'users',
            'location-dot', 'map', 'compass', 'globe', 'building', 'house', 'store', 'hospital',
            'school', 'graduation-cap', 'book', 'newspaper', 'book-open',
            'chart-line', 'chart-bar', 'chart-pie', 'chart-column', 'chart-area',
            'thumbs-up', 'thumbs-down', 'hands-clapping', 'handshake', 'peace',
            'face-smile', 'face-frown', 'face-meh', 'face-angry', 'face-surprised',
            'ban', 'exclamation', 'question', 'info', 'check', 'xmark',
            'plus', 'minus', 'equals', 'divide', 'percent',
            'arrow-up', 'arrow-down', 'arrow-left', 'arrow-right',
            'download', 'upload', 'share', 'paper-plane', 'inbox',
            'trash', 'trash-can',
            'hourglass', 'stopwatch', 'timer', 'alarm-clock',
            'battery-quarter', 'battery-half', 'battery-three-quarters', 'battery-full', 'battery-empty', 'plug', 'power-off',
            'volume-high', 'volume-low', 'volume-xmark', 'headphones',
            'image', 'file', 'file-lines', 'file-pdf', 'file-word',
            'folder-open', 'box', 'boxes-stacked'
        ];

        this.svgCache = {}; // Cache for fetched SVGs
        this.FA_BASE_URL = 'https://nerd.biz/assets/fa/svgs/solid/';
    }

    async fetchSVG(iconName) {
        if (this.svgCache[iconName]) {
            return this.svgCache[iconName];
        }

        try {
            const response = await fetch(this.FA_BASE_URL + iconName + '.svg');
            if (!response.ok) throw new Error('Icon not found');
            const svgText = await response.text();
            this.svgCache[iconName] = svgText;
            return svgText;
        } catch (error) {
            console.error(`Failed to fetch icon: ${iconName}`, error);
            return '<svg viewBox="0 0 512 512"><circle cx="256" cy="256" r="200" fill="currentColor"/></svg>';
        }
    }

    async init() {
        await this.createModal();
        this.attachEventListeners();
    }

    async createModal() {
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
        await this.renderSVGIcons();
    }

    async renderSVGIcons() {
        const iconGrid = document.getElementById('iconGrid');

        for (const iconName of this.iconNames) {
            const iconOption = document.createElement('div');
            iconOption.className = 'icon-option';
            iconOption.dataset.icon = iconName;
            iconOption.innerHTML = '<div class="icon-loading"></div>'; // Temp placeholder
            iconGrid.appendChild(iconOption);

            // Fetch and inject SVG
            const svg = await this.fetchSVG(iconName);
            iconOption.innerHTML = svg;
        }
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

    static async renderIcon(iconValue) {
        if (!iconValue) return '';

        if (iconValue.startsWith('svg:')) {
            const iconName = iconValue.substring(4);
            const picker = new IconPicker();
            const svg = await picker.fetchSVG(iconName);
            // Wrap in span for color inheritance
            return '<span class="icon-svg-wrapper" style="display: inline-block; width: 1em; height: 1em;">' + svg + '</span>';
        } else if (iconValue.startsWith('emoji:')) {
            // Return emoji WITHOUT wrapper so color doesn't apply
            return iconValue.substring(6);
        }

        return iconValue;
    }
}

let iconPickerInstance = null;

async function initIconPicker() {
    if (!iconPickerInstance) {
        iconPickerInstance = new IconPicker();
        await iconPickerInstance.init();
    }
    return iconPickerInstance;
}

async function openIconPicker(statusId, currentIcon, callback) {
    const picker = await initIconPicker();
    picker.open(statusId, currentIcon, callback);
}
