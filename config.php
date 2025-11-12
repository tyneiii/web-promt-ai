<?php
     $servername = "localhost";   
        $username = "root";          
        $password = "";              
        $database = "prompt_database";       

        // Kết nối đến MySQL
        $conn = new mysqli($servername, $username, $password, $database);
    if($conn->error){
        die('Error');
    }
?>