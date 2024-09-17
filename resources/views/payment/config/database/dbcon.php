<?php
    $servername = "mysql:host=localhost; dbname=alsist";
    $username = "root";
    $password = "";
   
    
    try{

        $pdo = new PDO($servername, $username, $password);

    }catch(PDOException $e){

        echo 'Connection error'. $e->getMessage();
        
    }
?>