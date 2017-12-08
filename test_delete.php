<?php

session_start();

header('Content-type: text/html; charset=utf-8');

include('config.php');
include('includes/dbconnect.php');


$query = $database_object->query("SELECT * FROM file_manager WHERE deletion_date_time <= '" . date("Y-m-d") . "' ");
$to_delete = $query->fetchAll();

foreach ($to_delete as $deadFile) {
    echo 'Dead file is ';
    echo $deadFile['file_path'];
    echo '     ';
    //recursiveRemoveDirectory(realpath($deadFile['file_path']));
    unlink(realpath($deadFile['file_path']));

    $query = $database_object->prepare("DELETE FROM file_manager WHERE id = ?");
    $query->bindParam(1, $deadFile['id']);

    try {
        $query->execute();
    } catch (Exception $e) {

    }

    $query = $database_object->prepare("DELETE FROM file_security WHERE security_descriptor_id = ?");
    $query->bindParam(1, $deadFile['id']);

    try {
        $query->execute();
    } catch (Exception $e) {

    }
}