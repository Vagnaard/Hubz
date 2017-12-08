<?php if ($_SESSION['connected'] == true) {

    $user_id = htmlspecialchars($_GET['userId']);

    if ($_SESSION['admin'] != true && $user_id != $_SESSION['userId']) {
        $user_id = $_SESSION['userId'];
    }

    include('includes/dbconnect.php');
    $query = $database_object->query("SELECT * FROM users WHERE userId = '" . $user_id . "'");
    $current_user = $query->fetch();

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
        $current_status = "Superadmin";
    }

    $database_object->exec("SET NAMES `utf8`");
    $query = $database_object->query("SELECT users.email AS email, security_descriptors.descriptor AS descriptor, user_security.user_id AS user_id, user_security.security_descriptor_id AS security_descriptor_id FROM user_security JOIN users ON users.userId = user_security.user_id JOIN security_descriptors ON security_descriptors.id = user_security.security_descriptor_id WHERE user_security.user_id = ".$user_id." ORDER BY id DESC");
    $user_securities = $query->fetchAll();

    $query = $database_object->query("SELECT id, descriptor FROM security_descriptors");
    $descriptors = $query->fetchAll();
    $file_list =[];
    if(!$_SESSION['superadmin'] && $_SESSION['admin'] && $_SESSION['rights_id']) {
        $rights_id = implode(",", $_SESSION['rights_id']);
        $query = $database_object->query("SELECT * FROM file_manager JOIN file_security ON file_manager.id = file_security.file_id WHERE security_descriptor_id IN (" . $rights_id . ") ");
        $file_list = $query->fetchAll();
    }
    ?>


    <form id="settingsForm" class="pageForm" action="includes/<?php if ($user_id != 0) {
        ?>account-settings-post.php<?php } else {
        ?>new-account-post.php<?php }
    ?>" target="uploadIframe" method="post">

        <?php if ($_SESSION['admin'] == true && $user_id != $_SESSION['userId'] && !empty($user_id)) {
            ?>

            <h1>Modifier le compte<br/><br/></h1>

            <div class="clear">
                <div class="file user alwaysSelected">
                    <label>
			  	<span class="fileName"><?php echo $current_user['email'];
                    ?></span>
                    </label>
                    <span class="fileInfos"><?php echo $current_status;
                        ?><br>
                        <?php echo $current_user['dir'];
                        ?>
			  </span>
                </div>

                <a target="uploadIframe" class="cancel btn"
                   style="width:260px; max-width:none; clear:both; margin-top:20px; margin-right:68px;"
                   href="includes/delete-account.php?userId=<?php echo $user_id;
                   ?>">
                    Supprimer le compte
                </a>
            </div>
            <br/><br/>
        <?php }

        if ($user_id == $_SESSION['userId']) {
            ?>
            <h1>Mon compte<br/><br/></h1>
        <?php }


        if ($_SESSION['admin'] == true && empty($user_id)) {
            ?>
            <h1>Nouveau compte<br/><br/></h1>
        <?php }
        ?>


        <?php if ($_SESSION['changesDone'] == true) {
            ?>
            <div class="win">
                <p>Tout les changements ont été apportés avec succès.</p>
            </div>
            <?php $_SESSION['changesDone'] = false;
        }
        ?>

        <label>
            Courriel:<br/>
            <input type="text" id="email" <?php if ($_SESSION['admin'] != true) {
                ?> readonly="true" <?php }
            ?> value="<?php echo $current_user['email'];
            ?>" name="email" placeholder="Courriel..."/>
        </label>
        <br/><br/>
        <label>
            Nom, Prenom:<br/>
            <input type="text" id="name" <?php if ($_SESSION['admin'] != true) {
                ?> readonly="true" <?php }
            ?> value="<?php echo $current_user['name'];
            ?>" name="name" placeholder="Nom..."/>
        </label>

        <?php if ($_SESSION['admin'] == true && $user_id != $_SESSION['userId']) {
            ?>

            <br/><br/>
            <label>Droits:<br/>
                <select name="status" id="status">
                    <option <?php if ($current_user['status'] == "user") {
                        ?> selected="true" <?php }
                    ?> value="user">Lecture
                    </option>
                    <option <?php if ($current_user['status'] == "uploader") {
                        ?> selected="true" <?php }
                    ?> value="uploader">Lecture / Écriture
                    </option>
                    <option <?php if ($current_user['status'] == "admin") {
                        ?> selected="true" <?php }
                    ?> value="admin">Administrateur
                    </option>
                    <option <?php if ($current_user['status'] == "superadmin") {
                        ?> selected="true" <?php }
                    ?> value="superadmin">Super Administrateur
                    </option>

                </select>
            </label>

            <?php if ($_SESSION['superadmin']) { ?>
                <br/><br/>
                <label>Groupe:<br/>
                    <br/>
                    <div>
                        <table class="permissions-table">
                            <tr>
                                <th>Groupe de Sécurité</th>
                                <th>Supprimer</th>
                            </tr>
                            <?php
                            foreach ($user_securities as $us) {
                                echo "<tr>";
                                echo "<td>";
                                echo $us["descriptor"];
                                echo "</td>";
                                echo "<td>";
                                echo "<a href='/hubz/admin/delete_user_security.php?user_id=" . $us["user_id"] . "&security_descriptor_id=" . $us["security_descriptor_id"] . "' target='uploadIframe'>Supprimer</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <select id="descriptorId" name="descriptorId">
                        <option value="">Aucun</option>
                        <?php
                        foreach ($descriptors as $d) {
                            echo "<option value='" . $d["id"] . "'>" .$d["descriptor"] . "</option>";
                        }
                        ?>
                    </select>
                    <br/>
                </label>
            <?php } ?>

            <div id="dirEditor" <?php if ($current_user['status'] == "admin" || $current_user['status'] == "superadmin") {
                ?> style='display:none;' <?php }
            ?> >
                <br/>
                <label>Répertoire:<br/>
                    <select name="dir" id="dir">
                        <option value="">Tout les dossiers</option>
                        <?php
                        $directory_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory_path), RecursiveIteratorIterator::SELF_FIRST);
                        asort($directory_iterator);
                        foreach ($directory_iterator as $iterated_directory => $PHPprotectV71) {

                            if (is_dir($iterated_directory)) {
                                $PHPprotectV16 = str_replace('/', "-", $files_url);
                                $iterated_directory = str_replace('/', "-", $iterated_directory);
                                $PHPprotectV16 = '/' . $PHPprotectV16 . '/';
                                $iterated_directory = preg_replace($PHPprotectV16, '', $iterated_directory, 1);
                                $iterated_directory = str_replace('-', "/", $iterated_directory);
                                if(substr_count($iterated_directory, "/." )>0){continue;}
                                if(!$_SESSION['superadmin'] && $_SESSION['admin']) {
                                    $is_matched = false;
                                    foreach ($file_list as $f) {
                                        $parsed_path = str_replace($files_url, "", $f['file_path']);
                                        if ($parsed_path == $iterated_directory) {
                                            $is_matched = true;
                                            break;
                                        }
                                    }
                                    if (!$is_matched) {
                                        continue;
                                    }
                                }

                                ?>

                                <option <?php if ($current_user['dir'] == $iterated_directory) {
                                    ?> selected="true" <?php }
                                ?> value="<?php echo $iterated_directory;
                                ?>"><?php echo $iterated_directory;
                                    ?></option>
                            <?php }

                        }

                        ?>
                    </select>
                </label>
            </div>


        <?php }
        ?>

        <?php if (empty($user_id) || ($_SESSION['admin'] == true && $user_id != $_SESSION['userId'])) {
            ?>
            <br/>
            <div id="generatePassword" class="cancel btn"
                 style="width:260px; float:none; max-width:none; clear:both; margin-top:20px; margin-right:68px;">
                ↻&nbsp;
                <?php if (!empty($user_id)) {
                    ?>
                    Réinitialiser le mot de passe
                <?php } else {
                    ?>
                    Générer un mot de passe
                <?php }
                ?>

            </div>
        <?php }
        ?>


        <?php if ($user_id == $_SESSION['userId']) {
            ?>
            <br/>
        <?php }
        ?>

        <?php if (!empty($user_id)){
        ?>
        <br/>
        <label>
            Nouveau mot de passe:
            <?php }else{
            ?>
            <br/>
            <label>
                Mot de passe:
                <?php }
                ?><br/>
                <input type="password" id="password" name="password" placeholder="Mot de passe..."/>
            </label>
            <br/><br/>
            <label>
                Confirmation du mot de passe:<br/>
                <input type="password" id="passwordConfirm" name="passwordConfirm" placeholder="Mot de passe..."/>
            </label>
            <br/><br/>

            <input type="hidden" id="userId" name="userId" value="<?php echo $user_id;
            ?>"/>

            <input type="reset" class="btn" value="Annuler"/>
            <input type="submit" class="btn" value="Enregistrer"/>


    </form>

<?php }
?>
