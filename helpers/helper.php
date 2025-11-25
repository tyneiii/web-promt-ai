<?php
function handlePrgRedirect($result, $current_get_params)
{
    if (!empty($result) && is_array($result)) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['status_message'] = $result;
    }
    $filtered_params = array_filter($current_get_params, function ($value) {
        return $value !== null && $value !== '';
    });
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    if (!empty($filtered_params)) {
        $redirect_url .= '?' . http_build_query($filtered_params);
    }
    header("Location: " . $redirect_url);
    exit();
}

function getMess()
{
    $result = [];
    if (isset($_SESSION['status_message'])) {
        $result = $_SESSION['status_message']; 
        unset($_SESSION['status_message']);
    }
    return $result;
}

function printMess($result){
    if (!is_array($result) || empty($result['message'])) {
        return;
    }
    $mess = $result['message'];
    $class = $result['success'] ? 'alert-success' : 'alert-error';
    echo "<div class='" . $class . "'>"
         . htmlspecialchars($mess) . 
         "</div>";
}
