<?php if ($_SESSION['admin'] == true) {

    include('includes/dbconnect.php');
    $query = "";
    if($_SESSION['superadmin'] == true ){
        $query = $database_object->query("SELECT * FROM users ORDER BY email ASC");
    } else {
        $rights_id = implode(",", $_SESSION['rights_id']);
        $query = $database_object->query("SELECT * FROM users JOIN user_security ON users.userid = user_security.user_id WHERE security_descriptor_id IN (".$rights_id.") ORDER BY email ASC");
    }

    ?>
    <div class="pageForm">
        <h1>Gestion des comptes<br/><br/></h1>
    </div>

    <?php while ($current_user = $query->fetch()) {

        if ($current_user['status'] == 'user') {
            $current_status = "Lecture";
        }
        if ($current_user['status'] == 'uploader') {
            $current_status = "Lecture / Écriture";
        }
        if ($current_user['status'] == 'admin') {
            $current_status = "Administrateur";
        }

        if ($current_user['status'] == 'superadmin') {
            $current_status = "Super Administrateur";
        }

        ?>

        <div data-href="?account-settings=true&userId=<?php echo $current_user['userId'];
        ?>" class="file user">
            <label>
			  	<span class="fileName"><?php echo $current_user['email'];
                    ?></span>
            </label>
            <span class="fileInfos"><?php echo $current_status;
                ?><br/>
			  <span class="date"><?php echo $current_user['dir'];
                  ?></span>
			  </span>
        </div>

    <?php }
    ?>

<?php }
?>
