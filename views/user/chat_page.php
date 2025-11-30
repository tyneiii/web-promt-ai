<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Chat View (Messenger style)</title>
    <link rel="stylesheet" href="../../public/css/user/chat_page.css">
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<?php include_once __DIR__ . '/../../helpers/chat_page_logic.php' ?>

<body class="zalo-style">
    <div class="main-container">

        <div class="chat-sidebar">
            <div class="profile-header">
                <?php
                $user_fullname = htmlspecialchars($username ?? 'Người Dùng');
                $user_avatar = htmlspecialchars($user_avatar ?? '');
                $homepage_url = '/';
                $initial = strtoupper(substr($user_fullname, 0, 1));
                ?>
                <a href="profile.php" class="user-detail-link">
                    <div class="user-info-section">
                        <div class="avatar">
                            <?php if (!empty($user_avatar)): ?>
                                <img src="<?= $user_avatar ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <span class="initial-placeholder"><?= $initial ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="user-name" title="<?= $user_fullname ?>"><?= $user_fullname ?></span>
                    </div>
                </a>
                <a href="home.php" class="homepage-link" title="Quay về trang chủ">
                    Prompt AI
                </a>
            </div>
            <form class="search-bar" method="GET" action="chat_page.php">
                <input type="text" name="username" placeholder="Tìm kiếm hội thoại..." />
                <button type="submit" style="display: none;">Tìm kiếm</button>
            </form>

            <div class="conversation-list" id="conversationList">
                <div class="conversation-list" id="conversationList">
                    <?php
                    if (empty($chatList)):
                    ?>
                        <div style="text-align: center; padding: 20px; color: #8e8e8e; font-style: italic;">
                            Không có đoạn chat nào.
                        </div>
                        <?php
                    else:
                        foreach ($chatList as $chat):
                            $partnerName = htmlspecialchars($chat['username']);
                            $partnerAvatar = htmlspecialchars($chat['partner_avatar'] ?? '');
                            $lastMessage = htmlspecialchars($chat['last_message'] ?? 'Chưa có tin nhắn');
                            $previewText = strlen($lastMessage) > 30 ? substr($lastMessage, 0, 30) . '...' : $lastMessage;
                            $initial = strtoupper(substr($partnerName, 0, 1));
                        ?>
                            <a href="chat_page.php?chat_id=<?= htmlspecialchars($chat['chat_id']) ?>" class="convo-link">
                                <div class="convo-item<?= ($chat_id == $chat['chat_id']) ? ' active' : '' ?>" data-chat-id="<?= htmlspecialchars($chat['chat_id']) ?>">
                                    <div class="avatar">
                                        <?php if (!empty($partnerAvatar)): ?>
                                            <img src="<?= $partnerAvatar ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                        <?php else: ?>
                                            <?= $initial ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="convo-info-wrapper">
                                        <div class="convo-details">
                                            <div class="title"><?= $partnerName ?></div>
                                            <div class="preview"><?= htmlspecialchars($previewText) ?></div>
                                        </div>
                                        <span class="last-time"><?= formatLastTime($chat['last_time']) ?></span>
                                    </div>
                                </div>
                            </a>
                    <?php endforeach;
                    endif;
                    ?>
                </div>
            </div>
        </div>

        <div class="chat-wrapper" role="application" aria-label="Chat view">
            <header class="chat-header">
                <?php if (!empty($currentPartner)): ?>
                    <div class="avatar">
                        <?php
                        $partnerAvatar = htmlspecialchars($currentPartner['avatar'] ?? '');
                        $partnerName = htmlspecialchars($currentPartner['username']);
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

            <?php if (!empty($currentPartner)): ?>
                <main class="messages" id="messages">
                    <div id="loadMoreContainer" style="text-align: center; padding: 10px;"></div>
                    <?php renderMessages($mess, $account_id); ?>
                </main>
                <form class="composer" id="composer" onsubmit="return false;">
                    <div class="input">
                        <emoji-picker></emoji-picker>
                        <button type="button" id="emoji-btn" title="Chọn biểu tượng cảm xúc">
                            <i class="fa-regular fa-face-grin-squint-tears" style="color:orange;"></i>
                        </button>
                        <input id="messageInput" placeholder="Nhập tin nhắn ..." autocomplete="off" />
                    </div>
                    <button class="btn-send" id="sendBtn" aria-label="Gửi">Gửi</button>
                    <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" id="accountId" value="<?php echo htmlspecialchars($account_id); ?>">
                    <input type="hidden" id="activeChatId" value="<?= htmlspecialchars($chat_id ?? '') ?>">
                </form>
            <?php else: ?>
                <div style="text-align:center; color: #8e8e8e; padding-top: 50px; font-style: italic;">Không có tin nhắn!</div>
            <?php endif ?>
        </div>
    </div>
    <script src="../../public/js/chat_page.js"></script>
    <!-- <script>
        document.addEventListener('DOMContentLoaded', () => {
            initEmojiPicker('emoji-btn', 'messageInput');
        });
    </script> -->
</body>

</html>