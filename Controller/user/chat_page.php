<?php
include_once __DIR__ . "/../../Controller/user/chat.php";
include_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['id_user']) || (int)$_SESSION['id_user'] === 0) {
    die("Lỗi: Người dùng chưa đăng nhập.");
}
define('MESSAGE_LIMIT', 2);
$role = (int)$_SESSION['role'];
$account_id = (int)$_SESSION['id_user'];
$chatList = getChatList($conn, $account_id, $role);
$chat_id=null;
if ($role === 2) {
    $chat_id = (int)getIDChat($conn, $account_id);
} else {
    if (isset($_GET['chat_id']) && is_numeric($_GET['chat_id'])) {
        $chat_id = (int)$_GET['chat_id'];
    } elseif (!empty($chatList)) {
        $chat_id = (int)$chatList[0]['chat_id'];
    }
}

if(!empty($chat_id)) {
    $currentPartner = getInfoUserFromChat($conn, $chat_id, $role);
    $mess = getMessages($conn, $chat_id, MESSAGE_LIMIT, null);
}

if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
$csrf_token = generateCsrfToken();

function printBubble(array $message, string $bubble_class): void
{
    $time_format = date('H:i', strtotime($message['sent_at']));
    $safe_message = nl2br(htmlspecialchars($message['message']));
    $sent_at_data = $message['sent_at'];
    $message_id = $message['chat_detail_id'];
    echo <<<HTML
    <div class="bubble $bubble_class" data-id="$message_id"> 
        $safe_message
        <span class="meta" data-sent-at="$sent_at_data">
            $time_format
        </span>
    </div>
    HTML;
}

function renderMessages(array $messages, string $current_user_id): void
{
    if (empty($messages)) {
        echo '<div style="text-align:center; color: #8e8e8e; padding-top: 50px;">Chưa có tin nhắn nào trong đoạn chat này.</div>';
        return;
    }
    $current_date = '';
    $today = date('d/m/Y');
    $yesterday = date('d/m/Y', strtotime('-1 day'));
    foreach ($messages as $message) {
        $message_date = date('d/m/Y', strtotime($message['sent_at']));
        if ($message_date != $current_date) {
            $display_date = $message_date;
            if ($display_date == $today) {
                $display_date = 'Hôm nay';
            } elseif ($display_date == $yesterday) {
                $display_date = 'Hôm qua';
            }
            echo '<div class="date-sep">' . $display_date . '</div>';
            $current_date = $message_date;
        }
        $is_mine = ($message['sender_id'] == $current_user_id);
        $bubble_class = $is_mine ? 'mine' : 'other';
        printBubble($message, $bubble_class);
    }
}
