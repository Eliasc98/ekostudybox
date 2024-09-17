<?php
    include('configuration.php');
    
    require_once('mail/class.smtp.php');
    require_once('mail/class.phpmailer.php');
    
    require_once('classes/Generic.php');
 
 

$getFromGeneric  = new Generic($pdo);
    
