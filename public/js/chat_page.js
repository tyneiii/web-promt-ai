const messagesEl = document.getElementById('messages');
const input = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const csrfToken = document.getElementById('csrfToken').value;
const loadMoreContainer = document.getElementById('loadMoreContainer');
let isLoading = false;
const accountId = document.getElementById('accountId').value;
const activeChatIdEl = document.getElementById('activeChatId'); 
let currentChatId = activeChatIdEl ? activeChatIdEl.value : null; 

// Biến trạng thái cuộn cho AJAX Polling
let isScrolledToBottom = true; 

// --- CÁC HÀM TIỆN ÍCH ---
// ==============================================

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

function appendMessage(text, who = 'mine', prepend = false, messageId = null, timestamp = new Date().toISOString()) {
    const el = document.createElement('div');
    el.className = 'bubble ' + (who === 'mine' ? 'mine' : 'other');
    if (messageId !== null) {
        el.dataset.id = messageId;
    }
    const sentTime = new Date(timestamp).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });
    el.innerHTML = text.replace(/\n/g, '<br>') +
        '<span class="meta" data-sent-at="' + timestamp + '">' +
        sentTime + '</span>';
        
    if (prepend) {
        // Chèn trước loadMoreContainer.nextSibling (tức là sau loadMoreContainer)
        messagesEl.insertBefore(el, loadMoreContainer.nextSibling); 
    } else {
        messagesEl.appendChild(el);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    // Trả về element mới được tạo
    return el; 
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

// Hàm để lấy ID tin nhắn cuối cùng đang hiển thị trên DOM
function getLastMessageId() {
    const messages = messagesEl.querySelectorAll('.bubble[data-id]');
    if (messages.length === 0) {
        return 0; // Trả về 0 nếu chưa có tin nhắn nào
    }
    // Lấy tin nhắn cuối cùng và trả về data-id
    const lastMessage = messages[messages.length - 1];
    return parseInt(lastMessage.dataset.id) || 0;
}


// --- XỬ LÝ SỰ KIỆN GỬI TIN NHẮN VÀ INPUT ---
// ==============================================

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
    
    // Tạo tin nhắn tạm thời trên UI
    appendMessage(escapedV, 'mine');
    input.value = '';
    
    const payload = {
        message: escapedV,
        chat_id: chatIdToSend,
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
                // Nếu gửi thất bại, bạn có thể cân nhắc xóa tin nhắn tạm khỏi UI
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


// --- XỬ LÝ TẢI THÊM TIN NHẮN CŨ (LOAD MORE) ---
// ==============================================

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
    const firstBubble = messagesEl.querySelector('.bubble[data-id]');
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
                if (lastLoadedMessageDate && oldestTimestampOnScreen) {
                    if (getDisplayDate(lastLoadedMessageDate) === getDisplayDate(previousMessageDate)) {
                        if (existingDateSep && existingDateSep.classList.contains('date-sep')) {
                             existingDateSep.remove();
                        }
                    } else if (existingDateSep && existingDateSep.classList.contains('date-sep')) {
                    }
                }
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

if (messagesEl) {
    messagesEl.scrollTop = messagesEl.scrollHeight;
    messagesEl.addEventListener('scroll', () => {
        isScrolledToBottom = messagesEl.scrollHeight - messagesEl.clientHeight <= messagesEl.scrollTop + 1;
    });
}

function startPolling() {
    if (!currentChatId) {
        setTimeout(startPolling, 3000); 
        return;
    }
    const lastId = getLastMessageId();
    fetch(`../../public/ajax/new_messages.php?chat_id=${currentChatId}&last_id=${lastId}`)
        .then(response => {
             if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.messages.length > 0) {
                const wasScrolledToBottom = isScrolledToBottom; 
                data.messages.forEach(msg => {
                    if (!document.querySelector(`.bubble[data-id="${msg.chat_detail_id}"]`)) {
                        appendMessage(
                            msg.message, 
                            msg.sender_id == accountId ? 'mine' : 'other', 
                            false, 
                            msg.chat_detail_id, // Truyền ID tin nhắn
                            msg.sent_at // Truyền timestamp
                        );
                    }
                });
                if (wasScrolledToBottom) {
                    messagesEl.scrollTop = messagesEl.scrollHeight;
                }
            }
        })
        .catch(error => {
            console.error('Lỗi khi fetch tin nhắn mới:', error);
        })
        .finally(() => {
            setTimeout(startPolling, 3000);
        });
}

window.addEventListener('load', () => {
    if (messagesEl) {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    
    const totalMessages = messagesEl.querySelectorAll('.bubble').length;
    if (totalMessages >= 2) { 
        updateLoadMoreButton(true);
    } else {
        updateLoadMoreButton(false);
    }
    
    // BẮT ĐẦU POLLING
    if (currentChatId) {
        startPolling();
    }
});