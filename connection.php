<?php

    $servername='localhost';
    $db_username='root';
    $password='';
    $dbName='kidswallet';
    
    $conn=new mysqli($servername,$db_username,$password,$dbName);
    
    if($conn->connect_error){
        die("Connection failed:".$conn->connect_error);
    }

?>