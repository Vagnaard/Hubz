<?php

session_start();

header('Content-type: text/html; charset=utf-8');


if ($_SESSION['connected'] != true) {
    die();
}

include('../config.php');
include('dbconnect.php');

$file_id = $_POST['file_id'];
$descriptorId = $_POST['descriptorId'];
$deletion_date = htmlspecialchars($_POST['deletion_date']);
$comment = htmlspecialchars($_POST['comment']);

if($comment){
    $query = $database_object->prepare("UPDATE file_manager SET comment = ? WHERE id = ?");
    $query->bindParam(1, $comment);
    $query->bindParam(2, $file_id);

    try {
        $query->execute();
    } catch (Exception $e) {

    }
}

if($deletion_date){
    $query = $database_object->prepare("UPDATE file_manager SET deletion_date_time = ? WHERE id = ?");
    $query->bindParam(1, $deletion_date);
    $query->bindParam(2, $file_id);

    try {
        $query->execute();
    } catch (Exception $e) {

    }
}

if($descriptorId){
    $query = $database_object->prepare("DELETE FROM file_security WHERE file_id = ?");
    $query->bindParam(1, $file_id);

    try {
        $query->execute();
    } catch (Exception $e) {

    }

    $query = $database_object->prepare("INSERT INTO file_security (file_id, security_descriptor_id) VALUES (?, ?)");
    $query->bindParam(1, $file_id);
    $query->bindParam(2, $descriptorId);

    try {
        $query->execute();
    } catch (Exception $e) {

    }
}

$_SESSION['changesDone'] = true;


?>
<script type="text/javascript">
    parent.document.location.reload(true);
</script>
