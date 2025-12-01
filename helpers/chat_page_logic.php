<?php
include_once __DIR__ . "/../Controller/user/chat.php";
include_once __DIR__ . "/../config.php";
define('MESSAGE_LIMIT', 5);
$role = (int)$_SESSION['role'];
if ($role == 1) {
    header("Location: home.php");
    exit();
}
$account_id = (int)$_SESSION['account_id'];
$username = $_SESSION['name_user']; 
$user_avatar= $_SESSION['avatar'];
$searchName= $_GET['username'] ?? '';
$chatList = getChatList($conn, $account_id, $role, $searchName);
$chat_id=null;
if ($role === 2) {
    $chat_id = (int)getIDChat($conn, $account_id);
    $chatList = getChatList($conn, $account_id, $role, $searchName);
} elseif( $role === 3) {
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
        echo '<div style="text-align:center; color: #8e8e8e; padding-top: 50px; font-style: italic;">Chưa có tin nhắn nào trong đoạn chat này.</div>';
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
function formatLastTime($timestamp) {
    if (empty($timestamp)) {
        return '';
    }

    $now = new DateTime();
    $lastTime = new DateTime($timestamp);
    $diff = $now->diff($lastTime);
    
    if ($lastTime->format('Y-m-d') === $now->format('Y-m-d')) {
        return $lastTime->format('H:i'); 
    }
    $yesterday = new DateTime('yesterday');
    if ($lastTime->format('Y-m-d') === $yesterday->format('Y-m-d')) {
        return 'Hôm qua ' . $lastTime->format('H:i');
    }
    return $lastTime->format('d/m/Y'); 
}