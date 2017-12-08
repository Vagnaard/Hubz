<?php

session_start();

if ($_SESSION['uploader'] != true) {
    die();
}

include('../config.php');
if ($files_url != "") {
    $files_url = "/" . $files_url;
}
$files_url = $root_url . $files_url . $_SESSION['dir'];
include('../includes/functions.php');
include('../includes/dbconnect.php');


$directory = htmlspecialchars($_GET['dir']);

if ($directory !== "") {
    $directory = $directory . "/";
}

$directory_path = $files_url . "/" . $directory;

if (0 === strpos(realpath($directory_path), $files_url)) {
} else {
    die();
}

$new_folder_path = $directory_path . 'Nouveau_dossier';

$final_path = paginateFolder($new_folder_path);

$query = $database_object->prepare("INSERT INTO file_manager (file_path) VALUES (?)");
$query->bindParam(1, $final_path);

try {
    $query->execute();
} catch (Exception $e) {

}


$_SESSION['newFolder'] = true;


?>
<script type="text/javascript">
    parent.document.location.reload(true);
</script>
