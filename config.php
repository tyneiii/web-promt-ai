<?php
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $root_dir = str_replace('\\', '/', __DIR__);
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $folder_name = str_replace($doc_root, '', $root_dir);
    $base_url = $protocol . "://" . $host . $folder_name;
    $redirectUri = $base_url . "/controller/authentification/GoogleController.php";
    // $redirectUri = "http://localhost:8080/baiTap/web-promt-ai/controller/authentification/GoogleController.php";
    $clientID = "957982290942-ps5dku2el8nick62eh49h2e5pd7v1bl6.apps.googleusercontent.com";
    $clientSecret = "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";

    $servername = "localhost";   
    $username = "root";          
    $password = "";              
    $database = "prompt_database";       
    $conn = new mysqli($servername, $username, $password, $database);
    if($conn->error){
        die('Error');
    }
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page_url = $protocol . "://" . $host . $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['current_url'])) {
    $_SESSION['current_url'] = $current_page_url;
    $_SESSION['previous_url'] = ''; 
}
if ($_SESSION['current_url'] !== $current_page_url) {
    $_SESSION['previous_url'] = $_SESSION['current_url'];
    $_SESSION['current_url'] = $current_page_url;
}
$redirect_url ="";
if (isset($_SESSION['previous_url']) && !empty($_SESSION['previous_url'])) {
    $redirect_url = $_SESSION['previous_url'];
} else {
    $redirect_url = "home.php"; 
}
?>