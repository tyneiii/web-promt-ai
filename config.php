<?php
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