<?php if ($_SESSION['admin'] == true) {
    header('Content-type: text/html; charset=utf-8');

    include('includes/dbconnect.php');
    $database_object->exec("SET NAMES `utf8`");
    $query = $database_object->query("SELECT users.email AS email, security_descriptors.descriptor AS descriptor, user_security.user_id AS user_id, user_security.security_descriptor_id AS security_descriptor_id FROM user_security JOIN users ON users.userId = user_security.user_id JOIN security_descriptors ON security_descriptors.id = user_security.security_descriptor_id ORDER BY id DESC");
    $user_securities = $query->fetchAll();

    $query = $database_object->query("SELECT userId, email FROM users");
    $emails = $query->fetchAll();

    $query = $database_object->query("SELECT id, descriptor FROM security_descriptors");
    $descriptors = $query->fetchAll();
}
?>

<div>
    <form id="admin_settings" class="pageForm" action="includes/admin_settings_post.php" method="post" target="uploadIframe">
        <div>
            <table class="permissions-table">
                <tr>
                    <th>Email</th>
                    <th>Groupe de Sécurité</th>
                    <th>Supprimer</th>
                </tr>
                <?php
                foreach ($user_securities as $us) {
                    echo "<tr>";
                    echo "<td>";
                    echo $us["email"];
                    echo "</td>";
                    echo "<td>";
                    echo $us["descriptor"];
                    echo "</td>";
                    echo "<td>";
                    echo "<a href='admin/delete_user_security.php?user_id=" . $us["user_id"] . "&security_descriptor_id=" . $us["security_descriptor_id"] . "' target='uploadIframe'>Supprimer</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div>
            <h1>Ajouter un usager À un groupe<br/><br/></h1>
            <select id="userId" name="userId">
                <?php
                foreach ($emails as $e) {
                    echo "<option value='" . $e["userId"] . "'>" . $e["email"] . "</option>";
                }
                ?>
            </select>
            <select id="descriptorId" name="descriptorId">
                <option value="">Aucun</option>
                <?php
                foreach ($descriptors as $d) {
                    echo "<option value='" . $d["id"] . "'>" .$d["descriptor"] . "</option>";
                }
                ?>
            </select>
            <br/>
            <input type="submit" class="btn" value="Enregistrer"/>

        </div>
    </form>
</div>
