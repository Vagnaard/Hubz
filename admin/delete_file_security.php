<?php session_start();

if ($_SESSION['connected'] != true) {
    die();
}

include('../config.php');
include('../includes/dbconnect.php');

$file_id = $_GET['file_id'];
$security_descriptor_id = $_GET['security_descriptor_id'];

$query = $database_object->prepare("DELETE FROM file_security WHERE file_id = ? AND security_descriptor_id = ?");
$query->bindParam(1, $file_id);
$query->bindParam(2, $security_descriptor_id);

try {
    $query->execute();
} catch (Exception $e) {

}

$_SESSION['changesDone'] = true;

?>
<script type="text/javascript">
    parent.document.location.reload(true);
</script>
