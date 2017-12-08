<?php

session_start();

header('Content-type: text/html; charset=utf-8');


if ($_SESSION['connected'] != true) {
    die();
}

include('../config.php');
include('dbconnect.php');

$userId = $_POST['userId'];
$descriptorId = $_POST['descriptorId'];

$query = $database_object->prepare("INSERT INTO user_security (user_id, security_descriptor_id) VALUES (?, ?)");
$query->bindParam(1, $userId);
$query->bindParam(2, $descriptorId);

try {
    $query->execute();
} catch (Exception $e) {

}

$_SESSION['changesDone'] = true;


?>
<script type="text/javascript">
    parent.document.location.reload(true);
</script>
