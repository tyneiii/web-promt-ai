<?php
    $hostname='localhost';
    $username='root';
    $pass='';
    $db='prompt_database';
    $conn= new mysqli($hostname,$username,$pass,$db);
    if($conn->error){
        die('Error');
    }
?>