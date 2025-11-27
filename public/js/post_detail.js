function handleAction(action, promptId, comment = null) {
    console.log(`Đang xử lý: ${action}...`);
    const apiUrl = '../../Controller/user/prompt_detail.php'; 
    const postData = {
        action: action,
        prompt_id: promptId,
        comment: comment
    };
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(postData),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Lỗi mạng hoặc server không phản hồi.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Thành công: ' + data.message);
            window.history.back();
        } else {
            alert('Lỗi xử lý: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi Fetch/AJAX:', error);
        alert('Có lỗi xảy ra khi gửi yêu cầu: ' + error.message);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof promptStatus === 'undefined' || typeof currentPromptId === 'undefined') {
        console.error("Lỗi: Các biến JavaScript cần thiết (promptStatus, currentPromptId) chưa được định nghĩa.");
        return;
    }
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("img01");
    const postImage = document.querySelector(".post-image");
    const span = document.getElementsByClassName("close")[0];
    if (postImage) {
        postImage.onclick = function () {
            modal.style.display = "flex";
            modalImg.src = this.src;
        }
    }
    if (span) {
        span.onclick = function () {
            modal.style.display = "none";
        }
    }
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    const runBtn = document.querySelector('.run-btn');
    const runResult = document.getElementById('run-result');
    if (runBtn) {
        runBtn.addEventListener('click', () => {
            runResult.innerHTML = ''; 
            if (promptStatus === 'waiting') {
                const textarea = document.createElement('textarea');
                textarea.placeholder = 'Nhập nhận xét/test prompt...';
                textarea.style.width = '100%';
                textarea.style.minHeight = '100px';
                textarea.style.marginBottom = '10px';
                const approveBtn = document.createElement('button');
                approveBtn.textContent = 'Duyệt bài đăng';
                approveBtn.className = 'action-btn run-btn';
                approveBtn.style.marginRight = '10px';
                const rejectBtn = document.createElement('button');
                rejectBtn.textContent = 'Từ chối bài đăng';
                rejectBtn.className = 'action-btn back-btn';
                runResult.appendChild(textarea);
                runResult.appendChild(approveBtn);
                runResult.appendChild(rejectBtn);
                approveBtn.addEventListener('click', function () {
                    if (confirm("Bạn có chắc chắn muốn DUYỆT bài đăng này không? Bài sẽ được công khai.")) {
                        const comment = textarea.value;
                        handleAction('approve', currentPromptId, comment);
                    }
                });
                rejectBtn.addEventListener('click', function () {
                    if (confirm("Bạn có chắc chắn muốn TỪ CHỐI bài đăng này không? Bài sẽ bị xóa hoặc đưa về nháp.")) {
                        const comment = textarea.value;
                        handleAction('reject', currentPromptId, comment);
                    }
                });
            } else if (promptStatus === 'report') {
                const clearReportBtn = document.createElement('button');
                clearReportBtn.textContent = 'Duyệt bài đăng';
                clearReportBtn.className = 'action-btn run-btn';
                const deleteBtn = document.createElement('button');
                deleteBtn.textContent = 'Xóa bài đăng';
                deleteBtn.className = 'action-btn back-btn';
                deleteBtn.style.marginLeft = '10px';
                runResult.appendChild(clearReportBtn);
                runResult.appendChild(deleteBtn);
                deleteBtn.addEventListener('click', function () {
                    if (confirm("CẢNH BÁO: Bạn có chắc chắn muốn XÓA VĨNH VIỄN bài đăng này không?")) {
                        handleAction('delete', currentPromptId);
                    }
                });
                clearReportBtn.addEventListener('click', function () {
                    if (confirm("Bạn có chắc chắn xác nhận bài đăng KHÔNG CÓ VẤN ĐỀ và đưa nó về trạng thái công khai (public)?")) {
                        handleAction('unreport', currentPromptId);
                    }
                });
            } else if (promptStatus === 'public') {
                const buttonContainer = document.createElement('div');
                buttonContainer.style.textAlign = 'center';
                const backToPrevBtn = document.createElement('button');
                backToPrevBtn.textContent = 'Trở về';
                backToPrevBtn.className = 'action-btn back-btn';
                backToPrevBtn.style.minWidth = '100px';
                backToPrevBtn.style.marginTop = '20px';
                backToPrevBtn.onclick = function () {
                    window.history.back();
                };
                buttonContainer.appendChild(backToPrevBtn);
                runResult.appendChild(buttonContainer);
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('.short-desc, .info-block textarea');
    textareas.forEach(textarea => {
        function autoGrow() {
            textarea.style.height = 'auto'; 
            const newHeight = textarea.scrollHeight;
            textarea.style.height = newHeight + 'px';
        }
        textarea.addEventListener('input', autoGrow);
        autoGrow();
    });
});