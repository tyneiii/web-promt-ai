<?php
    $clientID = "957982290942-ps5dku2el8nick62eh49h2e5pd7v1bl6.apps.googleusercontent.com";
    $clientSecret = "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";
    $redirectUri = "http://localhost:8080/baiTap/web-promt-ai/controller/authentification/GoogleController.php";
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
?>