<?php session_start();

if ($_SESSION['uploader'] != true) {
    die();
}
die();
include('../config.php');
if ($files_url != "") {
    $files_url = "/" . $files_url;
}
$files_url = $root_url . $files_url . $_SESSION['dir'];
include('../includes/functions.php');
include('../includes/dbconnect.php');

$padded_files_url = $files_url . "/";

$file_name = htmlspecialchars($_GET['file']);
$directory = htmlspecialchars($_GET['dir']);

$complete_file_path = realpath($padded_files_url . $file_name);

if (0 === strpos($complete_file_path, $files_url)) {
} else {
    die();
}

if (is_readable($complete_file_path) && is_dir($complete_file_path)) {
    recursiveRemoveDirectory($complete_file_path);
}

if (is_readable($complete_file_path) && is_file($complete_file_path)) {
    unlink($complete_file_path);
}

$query = $database_object->prepare("DELETE FROM file_manager WHERE file_path = ?");
$query->bindParam(1, $complete_file_path);

try {
    $query->execute();
} catch (Exception $e) {

}

?>
<script type="text/javascript">
    parent.document.location.reload(true);
</script>
