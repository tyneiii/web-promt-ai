// public/js/user_comments.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded!');  // ← Debug: Xem JS có chạy không

    const toggle = document.querySelector('.comment-toggle');
    console.log('Toggle found:', toggle);  // ← Debug: Selector có match không? Nếu null → sai class

    if (!toggle) {
        console.error('No .comment-toggle found! Check HTML class.');
        return;
    }

    const dropdown = document.getElementById('commentDropdown');
    console.log('Dropdown found:', dropdown);  // ← Debug

    if (!dropdown) {
        console.error('No #commentDropdown found!');
        return;
    }

    // Click toggle
    toggle.addEventListener('click', function(e) {
        console.log('Toggle clicked!');  // ← Debug: Click có trigger không
        e.stopPropagation();
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            console.log('Opening dropdown...');
            loadUserComments();
            dropdown.style.display = 'block';
        } else {
            console.log('Closing dropdown...');
            closeComments();
        }
    });

    // Đóng khi click ngoài
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.comment-toggle') && !e.target.closest('#commentDropdown')) {
            closeComments();
        }
    });
});

function closeComments() {
    const dropdown = document.getElementById('commentDropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
        console.log('Dropdown closed.');  // ← Debug
    }
}

function loadUserComments() {
    console.log('Loading comments...');  // ← Debug
    const list = document.getElementById('commentList');
    if (!list) {
        console.error('No #commentList!');
        return;
    }
    list.innerHTML = '<p style="text-align: center; color: #999;">Đang tải...</p>';

    fetch('../../controller/user/get_comments.php')
        .then(response => {
            console.log('Fetch response:', response.status);  // ← Debug: 200 OK?
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(data => {
            console.log('API data:', data);  // ← Debug: Xem data là gì (array, error, etc.)
            if (data.error) {
                list.innerHTML = '<p style="color: red; text-align: center;">' + data.error + '</p>';
                return;
            }
            if (data.length === 0) {
                list.innerHTML = '<p style="text-align: center; color: #999;">Bạn chưa có bình luận nào.</p>';
                return;
            }

            let html = '';
            data.forEach((comment, index) => {  // Thêm index để debug
                console.log('Rendering comment', index, comment);  // ← Debug
                const date = new Date(comment.created_at).toLocaleDateString('vi-VN', { 
                    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' 
                });
                const truncatedContent = comment.content.length > 100 ? comment.content.substring(0, 100) + '...' : comment.content;
                html += `
                    <div class="comment-item" style="border-bottom: 1px solid #eee; padding: 10px; margin-bottom: 10px; position: relative;">
                        <div style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">${escapeHtml(comment.username)}</div>
                        <div style="font-size: 14px; margin-bottom: 5px; color: #333; font-weight: 500;">${escapeHtml(comment.title)}</div>
                        <div style="font-size: 12px; margin-bottom: 5px; color: #666; white-space: pre-wrap;">${escapeHtml(truncatedContent)}</div>
                        <div style="font-size: 11px; color: #999; margin-bottom: 5px;">${date}</div>
                        
                        <!-- Menu 3 chấm -->
                        <details style="position: absolute; top: 5px; right: 5px;">
                            <summary style="cursor: pointer; list-style: none; padding: 2px 6px; border-radius: 50%; background: #f0f0f0; font-size: 16px;">⋮</summary>
                            <ul style="list-style: none; padding: 0; margin: 0; background: white; border: 1px solid #ccc; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); min-width: 150px;">
                                <li style="padding: 8px 12px; cursor: pointer;" onclick="viewDetail(${comment.prompt_id}); event.stopPropagation();">Xem chi tiết bài đăng</li>
                                <li style="padding: 8px 12px; cursor: pointer; color: #dc3545; border-top: 1px solid #eee;" onclick="deleteComment(${comment.comment_id}, ${comment.prompt_id}); event.stopPropagation();">Xóa bình luận</li>
                            </ul>
                        </details>
                    </div>
                `;
            });

            // Thêm link "Xem tất cả bình luận" ở đầu nếu có data
            if (data.length > 0) {
                html = '<a href="my_comments.php" style="display: block; text-align: center; padding: 10px; background: #f0f0f0; text-decoration: none; color: #333; border-radius: 4px; margin-bottom: 10px;">Xem tất cả bình luận &gt;&gt;</a>' + html;
            }

            list.innerHTML = html;
        })
        .catch(error => {
            console.error('Fetch error:', error);  // ← Debug
            list.innerHTML = '<p style="color: red; text-align: center;">Lỗi tải dữ liệu: ' + error.message + '</p>';
        });
}

// Helper để escape HTML (tránh XSS)
function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Functions cho menu (giữ nguyên)
function viewDetail(promptId) {
    window.location.href = `detail_post.php?id=${promptId}`;
    closeComments();
}

function deleteComment(commentId, promptId) {
    if (!confirm('Bạn chắc chắn muốn xóa bình luận này?')) return;
    
    fetch('../../controller/user/process_comment.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=delete&prompt_id=${promptId}&comment_id=${commentId}`
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    })
    .then(data => {
        console.log('Delete response:', data);  // ← Debug
        if (data.success) {
            alert('Xóa thành công!');
            loadUserComments();
        } else {
            alert('Lỗi xóa: ' + (data.message || 'Thử lại sau'));
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Lỗi xóa: ' + error.message);
    });
}