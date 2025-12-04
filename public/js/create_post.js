// Create Post Page JavaScript

// Redirect URL (should be set from PHP)
const redirectUrl = document.querySelector('[data-redirect-url]')?.dataset.redirectUrl || 'home.php';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeCollapsibleGroups();
    initializeFileUpload();
    initializeTopicSelector();
});

/**
 * Initialize collapsible groups
 */
function initializeCollapsibleGroups() {
    const collapsibleGroups = document.querySelectorAll('.collapsible-group');

    collapsibleGroups.forEach(group => {
        const header = group.querySelector('.collapsible-header');
        header.addEventListener('click', () => {
            const wasActive = group.classList.contains('active');

            collapsibleGroups.forEach(otherGroup => {
                otherGroup.classList.remove('active');
            });

            if (!wasActive) {
                group.classList.add('active');
            }
        });
    });
}

/**
 * Initialize file upload with preview
 */
function initializeFileUpload() {
    const fileInput = document.getElementById('fileInput');
    const imagePreview = document.getElementById('image-preview');
    const uploadSection = document.getElementById('upload-section');

    if (!fileInput) return;

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.innerHTML = `<img src="${e.target.result}" alt="Image preview"/>`;
                imagePreview.style.display = 'block';
                
                // Auto-open upload section
                if (!uploadSection.classList.contains('active')) {
                    uploadSection.querySelector('.collapsible-header').click();
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

/**
 * Initialize topic selector (multi-select)
 */
function initializeTopicSelector() {
    const topicInput = document.querySelector('.topic-input');
    const topicDropdown = document.querySelector('.topic-dropdown');
    const selectedTopics = document.querySelector('.selected-topics');

    if (!topicInput || !topicDropdown || !selectedTopics) return;

    const topics = [
        { "tag_id": 1, "tag_name": "Công việc" },
        { "tag_id": 2, "tag_name": "Công nghệ" },
        { "tag_id": 3, "tag_name": "Học tập" },
        { "tag_id": 4, "tag_name": "Sáng tạo nội dung" },
        { "tag_id": 5, "tag_name": "Giải trí" },
        { "tag_id": 6, "tag_name": "Phát triển bản thân" },
        { "tag_id": 7, "tag_name": "Cuộc sống" },
        { "tag_id": 8, "tag_name": "Kinh doanh" },
        { "tag_id": 9, "tag_name": "Công cụ" },
        { "tag_id": 10, "tag_name": "Khác" }
    ];

    let chosen = [];

    /**
     * Render dropdown options
     */
    function renderDropdown(filter = '') {
        topicDropdown.innerHTML = '';
        topics
            .filter(t => t.tag_name.toLowerCase().includes(filter.toLowerCase()) &&
                !chosen.some(c => c.tag_id === t.tag_id))
            .forEach(t => {
                const div = document.createElement('div');
                div.textContent = t.tag_name;
                div.onclick = () => selectTopic(t);
                topicDropdown.appendChild(div);
            });
    }

    /**
     * Select a topic
     */
    function selectTopic(topic) {
        if (chosen.length >= 3) {
            alert('Chỉ được chọn tối đa 3 chủ đề!');
            return;
        }
        chosen.push(topic);
        renderSelected();
        renderDropdown(topicInput.value);
        updateTagsHidden();
    }

    /**
     * Remove a topic
     */
    function removeTopic(tag_id) {
        tag_id = Number(tag_id);
        chosen = chosen.filter(t => Number(t.tag_id) !== tag_id);
        renderSelected();
        renderDropdown(topicInput.value);
        updateTagsHidden();
    }

    /**
     * Render selected topics
     */
    function renderSelected() {
        selectedTopics.innerHTML = '';
        chosen.forEach(t => {
            const tag = document.createElement('div');
            tag.className = 'tag';
            tag.innerHTML = `#${t.tag_name} 
                <button type="button" onclick="event.stopPropagation(); removeTopic(${t.tag_id});">×</button>`;
            selectedTopics.appendChild(tag);
        });
    }

    /**
     * Update hidden tags input
     */
    function updateTagsHidden() {
        const hiddenInput = document.getElementById('tags-hidden');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(chosen.map(t => t.tag_id));
        }
    }

    /**
     * Expose removeTopic to global scope for onclick handlers
     */
    window.removeTopic = removeTopic;

    // Event listeners
    topicInput.addEventListener('focus', () => {
        renderDropdown();
        topicDropdown.classList.add('show');
    });

    topicInput.addEventListener('blur', () => {
        setTimeout(() => topicDropdown.classList.remove('show'), 150);
    });

    topicInput.addEventListener('input', () => renderDropdown(topicInput.value));
}

/**
 * Confirm cancel
 */
function confirmCancel() {
    if (confirm('Bạn có chắc chắn muốn hủy bài viết này không?')) {
        window.location.href = redirectUrl;
    }
}

/**
 * Handle form submit
 */
function handleSubmit() {
    const tagsInput = document.getElementById('tags-hidden');
    const tags = JSON.parse(tagsInput.value || '[]');
    
    if (tags.length === 0) {
        alert('Vui lòng chọn ít nhất 1 chủ đề trước khi đăng bài!');
        return false;
    }
    return true;
}

/**
 * Close modal and redirect to home
 */
function closeModalAndRedirect() {
    document.body.style.overflow = 'auto';
    window.location.href = 'home.php';
}

/**
 * Close modal and stay to create new post
 */
function closeModalAndStay() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.remove();
    }
    document.body.style.overflow = 'auto';

    // Reset form
    const form = document.querySelector('.form-card');
    if (form) {
        form.reset();
    }

    const imagePreview = document.getElementById('image-preview');
    if (imagePreview) {
        imagePreview.innerHTML = '';
    }

    // Reset topics
    const tagsInput = document.getElementById('tags-hidden');
    if (tagsInput) {
        tagsInput.value = '';
    }

    // Close collapsible groups
    document.querySelectorAll('.collapsible-group').forEach(g => {
        g.classList.remove('active');
    });
}

/**
 * Expose global functions for inline event handlers
 */
window.confirmCancel = confirmCancel;
window.handleSubmit = handleSubmit;
window.closeModalAndRedirect = closeModalAndRedirect;
window.closeModalAndStay = closeModalAndStay;
