<?php
include_once __DIR__ . "/controller/account.php";
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$root_dir = str_replace('\\', '/', __DIR__);
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$folder_name = str_replace($doc_root, '', $root_dir);
$base_url = $protocol . "://" . $host . $folder_name;
$redirectUri = $base_url . "/controller/authentification/GoogleController.php";
$clientID = "957982290942-ps5dku2el8nick62eh49h2e5pd7v1bl6.apps.googleusercontent.com";
$clientSecret = "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";

// $servername = "ntu307.vpsttt.vn";
// $username = "ntu307_promt_admin";
// $password = "admin$$2025";
// $database = "ntu307_promt_database";
$servername = "localhost";
$username = "root";
$password = "";
$database = "prompt_database";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->error) {
    die('Error');
}

$conn->set_charset("utf8mb4"); 


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page_url = $protocol . "://" . $host . $_SERVER['REQUEST_URI'];
if (strpos($current_page_url, 'public/ajax') !== false) {
} else {
    if (!isset($_SESSION['current_url'])) {
        $_SESSION['current_url'] = $current_page_url;
        $_SESSION['previous_url'] = '';
    }

    if ($_SESSION['current_url'] !== $current_page_url) {
        $_SESSION['previous_url'] = $_SESSION['current_url'];
        $_SESSION['current_url'] = $current_page_url;
    }
}

$redirect_url = "";
if (isset($_SESSION['previous_url']) && !empty($_SESSION['previous_url'])) {
    $redirect_url = $_SESSION['previous_url'];
} else {
    $redirect_url = "home.php";
}
if (isset($_SESSION['account_id'])) {
    $account = getInfoAccount($conn, $_SESSION['account_id']);
    $_SESSION["loggedin"] = true;
    $_SESSION["account_id"] = $account['account_id'];
    $_SESSION["name_user"] = $account['username'];
    $_SESSION["avatar"] = $account['avatar'];
    $_SESSION["bg_avatar"] = $account['bg_avatar'];
    $_SESSION["role"] = $account['role_id'];
}