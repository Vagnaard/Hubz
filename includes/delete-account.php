<?php 
	
	session_start();
	
	if ($_SESSION['admin'] != true) {
		die();	
	}
	
	$user_id = htmlspecialchars($_GET['userId']);
	$is_confirmed = htmlspecialchars($_GET['confirm']);
	
	
	if($is_confirmed != true){
?>
		
		<script type="text/javascript">
								
								parent.confirm("Êtes-vous certain de vouloir supprimer le compte suivant?", function (callback) {
										if(callback){
											window.location = "includes/delete-account.php?userId=<?php echo $user_id; ?>&confirm=true";
										}
						    	});	
						    	
								</script>
		
	<?php die(); }
	
	
	include('../config.php');
	include('functions.php');
	include('dbconnect.php');
	
	if($user_id == $_SESSION['userId']){
		die();
	}
	
	$query = $database_object->query("DELETE FROM users WHERE userId = '".$user_id."'");
	
	
	$_SESSION['goToAccounts'] = true;
	

?>
<script type="text/javascript">
	parent.document.location.reload(true);
</script>
