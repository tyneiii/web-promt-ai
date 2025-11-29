const messagesEl = document.getElementById('messages');
const input = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const csrfToken = document.getElementById('csrfToken').value;
const loadMoreContainer = document.getElementById('loadMoreContainer');
let isLoading = false;
const accountId = document.getElementById('accountId').value;
const activeChatIdEl = document.getElementById('activeChatId'); 
let currentChatId = activeChatIdEl ? activeChatIdEl.value : null; 

function getDisplayDate(timestamp) {
    const messageDate = new Date(timestamp);
    const today = new Date();
    const todayNormalized = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
    const messageDateNormalized = new Date(messageDate.getFullYear(), messageDate.getMonth(), messageDate.getDate()).getTime();
    const oneDay = 24 * 60 * 60 * 1000; 
    const yesterdayNormalized = todayNormalized - oneDay;
    if (messageDateNormalized === todayNormalized) {
        return 'Hôm nay';
    } else if (messageDateNormalized === yesterdayNormalized) {
        return 'Hôm qua';
    } else {
        const day = messageDate.getDate().toString().padStart(2, '0');
        const month = (messageDate.getMonth() + 1).toString().padStart(2, '0');
        const year = messageDate.getFullYear();
        return `${day}/${month}/${year}`;
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
    const chatIdToSend = activeChatEl ? activeChatEl.dataset.chatId : currentChatId;
    if (!chatIdToSend) {
        alert("Vui lòng chọn một đoạn chat trước khi gửi tin nhắn.");
        return;
    }
    const escapedV = escapeHtml(v);
    appendMessage(escapedV, 'mine');
    input.value = '';
    const payload = {
        message: escapedV,
        chat_id: chatIdToSend, // GỬI CHAT ID
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
            if (!data.success) {
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
        button.style.cssText = 'padding: 8px 15px; border: none; border-radius: 20px; background-color: #333; color: #fff; cursor: pointer;';
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
    if (!currentChatId || !oldestId) {
        updateLoadMoreButton(false);
        isLoading = false;
        return;
    }
    const firstBubbleBeforeLoad = messagesEl.querySelector('.bubble');
    const existingDateSep = firstBubbleBeforeLoad ? firstBubbleBeforeLoad.previousElementSibling : null;
    const oldestTimestampOnScreen = firstBubbleBeforeLoad ? firstBubbleBeforeLoad.querySelector('.meta').dataset.sentAt : null;
    const currentScrollTop = messagesEl.scrollTop;
    loadMoreContainer.innerHTML = '<span style="color: #b71c1c;">Đang tải...</span>';
    
    const payload = {
        oldest_id: oldestId,
        chat_id: currentChatId, 
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
                data.messages.reverse();
                let previousMessageDate = oldestTimestampOnScreen ? 
                    new Date(oldestTimestampOnScreen) : new Date(0); 
                
                let lastLoadedMessageDate = null;

                data.messages.forEach((msg, index) => {
                    const currentMessageDate = new Date(msg.sent_at);
                    let shouldInsertDateSep = false;

                    // Chỉ kiểm tra dấu ngày giữa các tin nhắn trong LÔ MỚI TẢI
                    if (index > 0) {
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

                    // Chèn bubble
                    const el = document.createElement('div');
                    el.className = 'bubble ' + (msg.sender_id == accountId ? 'mine' : 'other');
                    el.dataset.id = msg.chat_detail_id;
                    const sentTime = currentMessageDate.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    el.innerHTML = msg.message.replace(/\n/g, '<br>') + // Đảm bảo nl2br được áp dụng
                        '<span class="meta" data-sent-at="' + msg.sent_at + '">' +
                        sentTime +
                        '</span>';
                    messagesEl.insertBefore(el, loadMoreContainer.nextSibling);
                    
                    if (index === data.messages.length - 1) {
                         lastLoadedMessageDate = currentMessageDate;
                    }
                });

                // 2. XỬ LÝ MỐC NỐI & DỌN DẸP DẤU NGÀY THỪA

                // Kiểm tra nếu tin nhắn mới nhất vừa được chèn (lastLoadedMessageDate) 
                // và tin nhắn cũ nhất đang có (previousMessageDate) CÙNG MỘT NGÀY.
                if (lastLoadedMessageDate && oldestTimestampOnScreen) {
                    if (getDisplayDate(lastLoadedMessageDate) === getDisplayDate(previousMessageDate)) {
                        // Nếu cùng ngày VÀ có dấu ngày đang nằm sai vị trí (trước firstBubbleBeforeLoad)
                        // thì xóa dấu ngày đó đi.
                        if (existingDateSep && existingDateSep.classList.contains('date-sep')) {
                             existingDateSep.remove();
                        }
                    } else if (existingDateSep && existingDateSep.classList.contains('date-sep')) {
                        // Nếu KHÁC NGÀY, thì dấu ngày cũ phải được giữ lại 
                        // nhưng phải đảm bảo rằng nó không có nội dung trùng với tin nhắn vừa load.
                        // (Trong trường hợp này, chúng ta giả định logic PHP ban đầu đã render đúng dấu ngày).
                    }
                }
                
                // 3. Điều chỉnh cuộn và nút tải thêm
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
            // Đặt lại nút tải để người dùng có thể thử lại
            updateLoadMoreButton(true); 
        });
}

window.addEventListener('load', () => {
    messagesEl.scrollTop = messagesEl.scrollHeight;
    const totalMessages = messagesEl.querySelectorAll('.bubble').length;
    if (totalMessages >= 2) {
        updateLoadMoreButton(true);
    } else {
        updateLoadMoreButton(false);
    }
});