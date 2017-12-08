<?php 
	
	$PHPprotectV72[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    $database_object = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_user, $db_password, $PHPprotectV72);
	

?>
