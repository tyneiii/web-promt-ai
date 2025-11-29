<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Chat View (Messenger style)</title>
    <link rel="stylesheet" href="../../public/css/user/chat_page.css">
</head>
<?php include_once __DIR__ . '/../../Controller/user/chat_page.php' ?>

<body class="zalo-style">
    <div class="main-container">

        <div class="chat-sidebar">
            <div class="search-bar">
                <input type="text" placeholder="Tìm kiếm hội thoại..." />
            </div>

            <div class="conversation-list" id="conversationList">
                <?php
                foreach ($chatList as $chat):
                    $partnerName = htmlspecialchars($chat['partner_fullname'] ?? $chat['username']);
                    $partnerAvatar = htmlspecialchars($chat['partner_avatar'] ?? '');
                    $lastMessage = htmlspecialchars($chat['last_message'] ?? 'Chưa có tin nhắn');
                    $previewText = strlen($lastMessage) > 30 ? substr($lastMessage, 0, 30) . '...' : $lastMessage;
                ?>
                    <a href="chat_page.php?chat_id=<?= htmlspecialchars($chat['chat_id']) ?>" class="convo-link">
                        <div class="convo-item active" data-chat-id="<?= htmlspecialchars($chat['chat_id']) ?>">
                            <div class="avatar">
                                <?php if (!empty($partnerAvatar)): ?>
                                    <img src="<?= $partnerAvatar ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <?= $initial ?>
                                <?php endif; ?>
                            </div>
                            <div class="convo-details">
                                <div class="title"><?= $partnerName ?></div>
                                <div class="preview"><?= htmlspecialchars($previewText) ?></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chat-wrapper" role="application" aria-label="Chat view">
            <header class="chat-header">
                <?php if (!empty($currentPartner)): ?>
                    <div class="avatar">
                        <?php
                            $partnerAvatar = htmlspecialchars($currentPartner['avatar'] ?? '');
                            $partnerName = htmlspecialchars($currentPartner['fullname'] ?? $currentPartner['username']);
                            $initial = strtoupper(substr($partnerName, 0, 1));
                        ?>
                        <?php if (!empty($partnerAvatar)): ?>
                            <img src="<?= $partnerAvatar ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <?= $initial ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="title"><?= $partnerName ?></div>
                    </div>
                <?php endif; ?>
            </header>

            <?php if(!empty($currentPartner)): ?>
                <main class="messages" id="messages">
                <div id="loadMoreContainer" style="text-align: center; padding: 10px;"></div>
                <?php renderMessages($mess, $account_id); ?>
            </main>

            <form class="composer" id="composer" onsubmit="return false;">
                <div class="input">
                    <span style="opacity:.6;margin-left:4px">😊</span>
                    <input id="messageInput" placeholder="Nhập tin nhắn ..." autocomplete="off" />
                </div>
                <button class="btn-send" id="sendBtn" aria-label="Gửi">Gửi</button>
                <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" id="accountId" value="<?php echo htmlspecialchars($account_id); ?>">
                <input type="hidden" id="activeChatId" value="<?= htmlspecialchars($chat_id ?? '') ?>">
            </form>
            <?php else: ?>
                <div style="text-align:center; color: #8e8e8e; padding-top: 50px;">Không có tin nhắn!</div>
            <?php endif ?>
        </div>
    </div>
    <script src="../../public/js/chat_page.js"></script>
</body>

</html>