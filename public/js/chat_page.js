const messagesEl = document.getElementById('messages');
const input = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const csrfToken = document.getElementById('csrfToken').value;
const loadMoreContainer = document.getElementById('loadMoreContainer');
let isLoading = false;
const accountId = document.getElementById('accountId').value;

function getDisplayDate(date) {
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);

    const formatDate = (d) => d.toISOString().split('T')[0];

    const dateStr = formatDate(date);
    const todayStr = formatDate(today);
    const yesterdayStr = formatDate(yesterday);

    if (dateStr === todayStr) {
        return 'Hôm nay';
    } else if (dateStr === yesterdayStr) {
        return 'Hôm qua';
    } else {
        return date.toLocaleDateString('vi-VN');
    }
}

function appendMessage(text, who = 'mine', prepend = false) {
    const el = document.createElement('div');
    el.className = 'bubble ' + (who === 'mine' ? 'mine' : 'other');

    el.innerHTML = text.replace(/\n/g, '<br>') +
        '<span class="meta" data-sent-at="' + new Date().toISOString() + '">' +
        new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        }) + '</span>';

    if (prepend) {
        messagesEl.insertBefore(el, loadMoreContainer.nextSibling);
    } else {
        messagesEl.appendChild(el);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
}

function escapeHtml(str) {
    return str.replace(/[&<>"']/g, function (m) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": "&#39;"
        }[m];
    });
}

sendBtn.addEventListener('click', () => {
    const v = input.value.trim();
    if (!v) return;
    const activeChatEl = document.querySelector('.convo-item.active');
    const chatId = activeChatEl ? activeChatEl.dataset.chatId : null;
    if (!chatId) {
        alert("Vui lòng chọn một đoạn chat trước khi gửi tin nhắn.");
        return;
    }
    const escapedV = escapeHtml(v);
    appendMessage(escapedV, 'mine');
    input.value = '';
    const payload = {
        message: escapedV,
        chat_id: chatId, // Thêm chat_id vào payload
        csrf_token: csrfToken
    };
    fetch('../../public/ajax/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            } else {
                console.error("Lỗi khi lưu tin nhắn:", data.error, data.details);
                alert("Lỗi: Không thể gửi tin nhắn. Vui lòng thử lại. Chi tiết: " + (data.details || data.error));
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            alert("Lỗi kết nối: Không thể gửi tin nhắn.");
        });
});

input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendBtn.click();
    }
});

function updateLoadMoreButton(hasMore) {
    loadMoreContainer.innerHTML = '';
    if (hasMore) {
        const button = document.createElement('button');
        button.id = 'loadMoreBtn';
        button.textContent = 'Tải thêm tin nhắn cũ';
        button.style.cssText = 'padding: 8px 15px; border: none; border-radius: 20px; background-color: #333; color: #fff; cursor: pointer;'; // Style tối ưu cho dark mode
        button.addEventListener('click', loadMoreMessages);
        loadMoreContainer.appendChild(button);
    } else {
        loadMoreContainer.innerHTML = '<span style="color: var(--muted);">Hết tin nhắn cũ</span>';
    }
}

function getOldestId() {
    const firstBubble = messagesEl.querySelector('.bubble');
    if (firstBubble) {
        const oldestId = firstBubble.dataset.id;
        if (oldestId && !isNaN(parseInt(oldestId))) {
            return parseInt(oldestId);
        }
    }
    return null;
}

function loadMoreMessages() {
    if (isLoading) return;
    isLoading = true;

    const oldestId = getOldestId();
    if (!oldestId) {
        updateLoadMoreButton(false);
        isLoading = false;
        return;
    }

    const currentScrollTop = messagesEl.scrollTop;

    loadMoreContainer.innerHTML = '<span style="color: #b71c1c;">Đang tải...</span>';

    const payload = {
        oldest_id: oldestId,
        csrf_token: csrfToken
    };

    fetch('../../public/ajax/get_old_messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
        .then(response => {
            if (response.status === 403) {
                throw new Error('Lỗi bảo mật (403 Forbidden). Vui lòng tải lại trang.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.messages.length > 0) {

                const oldHeight = messagesEl.scrollHeight;

                // Lấy ngày của tin nhắn CŨ NHẤT hiện đang có trên màn hình
                const firstBubble = messagesEl.querySelector('.bubble');
                let oldestTimestampOnScreen = firstBubble ?
                    firstBubble.querySelector('.meta').dataset.sentAt : '1970-01-01T00:00:00Z';

                let previousMessageDate = new Date(oldestTimestampOnScreen);

                // Duyệt qua các tin nhắn được tải về
                data.messages.forEach((msg, index) => {
                    const currentMessageDate = new Date(msg.sent_at);

                    let shouldInsertDateSep = false;

                    if (index === 0) {
                        // Trường hợp 1: Tin nhắn tải về đầu tiên (cũ nhất)
                        if (getDisplayDate(currentMessageDate) !== getDisplayDate(previousMessageDate)) {
                            shouldInsertDateSep = true;
                        }
                    } else {
                        // Trường hợp 2: Các tin nhắn tải về tiếp theo (index > 0)
                        const dateOfPreviousLoadedMessage = new Date(data.messages[index - 1].sent_at);
                        if (getDisplayDate(currentMessageDate) !== getDisplayDate(dateOfPreviousLoadedMessage)) {
                            shouldInsertDateSep = true;
                        }
                    }

                    if (shouldInsertDateSep) {
                        const dateSep = document.createElement('div');
                        dateSep.className = 'date-sep';
                        dateSep.textContent = getDisplayDate(currentMessageDate);
                        messagesEl.insertBefore(dateSep, loadMoreContainer.nextSibling);
                    }

                    // --- CHÈN BUBBLE TIN NHẮN ---
                    const el = document.createElement('div');
                    el.className = 'bubble ' + (msg.sender_id == accountId ? 'mine' : 'other');
                    el.dataset.id = msg.chat_detail_id;

                    const sentTime = currentMessageDate.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    el.innerHTML = msg.message +
                        '<span class="meta" data-sent-at="' + msg.sent_at + '">' +
                        sentTime +
                        '</span>';

                    messagesEl.insertBefore(el, loadMoreContainer.nextSibling);
                });

                const newHeight = messagesEl.scrollHeight;
                messagesEl.scrollTop = newHeight - oldHeight + currentScrollTop;

                if (data.messages.length < 2) {
                    updateLoadMoreButton(false);
                } else {
                    updateLoadMoreButton(true);
                }

            } else {
                updateLoadMoreButton(false);
            }
            isLoading = false;
        })
        .catch(error => {
            console.error('Lỗi tải tin nhắn cũ:', error);
            alert('Lỗi tải tin nhắn cũ: ' + error.message);
            isLoading = false;
            updateLoadMoreButton(true);
        });
}


window.addEventListener('load', () => {
    messagesEl.scrollTop = messagesEl.scrollHeight;

    const totalMessages = messagesEl.querySelectorAll('.bubble').length;
    if (totalMessages >= 2) { // MESSAGE_LIMIT là 2
        updateLoadMoreButton(true);
    } else {
        updateLoadMoreButton(false);
    }
});