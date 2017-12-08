<?php if ($_SESSION['admin'] == true) {
    header('Content-type: text/html; charset=utf-8');

    include('includes/dbconnect.php');
    include('../config.php');
    $database_object->exec("SET NAMES `utf8`");

    // Cleanup the view to make sure no one hard deleted the files out of the system
    $directory_path = $files_url . "/" ;
    $directory_tree = glob($directory_path . '*', GLOB_BRACE);


    if(!empty($imploded_directory_tree))
    {
        $query = $database_object->prepare('DELETE FROM file_manager WHERE file_path in (select id from (SELECT id FROM file_manager WHERE file_path NOT IN ("?") )as x )');
        $query->bindParam(1, $imploded_directory_tree);

        try {
            $query->execute();
        } catch (Exception $e) {

        }
    }

    $query = $database_object->query("SELECT file_manager.id AS id, file_manager.file_path, deletion_date_time, security_descriptors.descriptor AS descriptor, security_descriptors.id AS descriptor_id FROM file_manager JOIN file_security ON file_manager.id = file_security.file_id JOIN security_descriptors ON file_security.security_descriptor_id = security_descriptors.id ORDER BY id DESC");
    $file_securities = $query->fetchAll();

    $query = $database_object->query("SELECT id, file_path FROM file_manager");
    $files = $query->fetchAll();

    $query = $database_object->query("SELECT id, descriptor FROM security_descriptors");
    $descriptors = $query->fetchAll();

}
?>

<div>
    <form id="admin_settings" class="pageForm" action="includes/file_administration_post.php" method="post" target="uploadIframe">
        <div>
            <table class="permissions-table">
                <tr>
                    <th>Chemin du fichier</th>
                    <th>Date d'expiration</th>
                    <th>Groupe de Sécurité</th>
                    <th>Supprimer</th>
                </tr>
                <?php
                foreach ($file_securities as $fs) {
                    echo "<tr>";
                    echo "<td>";
                    echo str_replace ($files_url,"", $fs["file_path"]);
                    echo "</td>";
                    echo "<td>";
                    echo $fs["deletion_date_time"];
                    echo "</td>";
                    echo "<td>";
                    echo $fs["descriptor"];
                    echo "</td>";
                    echo "<td>";
                    echo "<a href='admin/delete_file_security.php?file_id=" . $fs["id"] . "&security_descriptor_id=" . $fs["descriptor_id"] . "' target='uploadIframe'>Supprimer</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div>
            <h1>Ajouter un groupe/temps À un fichier<br/><br/></h1>
            <label>Fichier : </label>
            <select id="file_id" name="file_id">
                <?php
                foreach ($files as $e) {
                    echo "<option value='" . $e["id"] . "'>" . $e["file_path"] . "</option>";
                }
                ?>
            </select>
            <br/>
            <label>Groupe : </label>
            <select id="descriptorId" name="descriptorId">
                <option value="">Aucun</option>
                <?php
                foreach ($descriptors as $d) {
                    echo "<option value='" . $d["id"] . "'>" .$d["descriptor"] . "</option>";
                }
                ?>
            </select>
            <br/>
            <label>Date d'expiration: </label>
            <input name="deletion_date" type="date"/>
            <br/>
            <input type="submit" class="btn" value="Enregistrer"/>

        </div>
    </form>
</div>
