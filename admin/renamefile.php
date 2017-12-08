<?php
session_start();

header('Content-type: text/html; charset=utf-8');

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

$padded_files_url = $files_url . "/";

$source_file = htmlspecialchars($_POST['sourceFile']);
$destination = htmlspecialchars($_POST['destination']);
$destination_path_infos = pathinfo($destination, PATHINFO_EXTENSION);
$new_name = htmlspecialchars($_POST['newName']);


if (0 === strpos(realpath($padded_files_url . $source_file), $files_url)) {
} else {
    die();
}

if ($source_file === "") {
    die();
}

if ($new_name !== "") {
    $generated_url = GenerateUrl($new_name);

    if ($generated_url === "") {
        $generated_url = "Sans_titre";
    }

    if (is_dir($padded_files_url . $source_file)) {
        $PHPprotectV55 = preg_replace('~(.*)' . preg_quote($new_name, '~') . '~', '$1' . "", $destination, 1);
        $destination = $PHPprotectV55 . $generated_url;

    } else {
        $PHPprotectV55 = preg_replace('~(.*)' . preg_quote($new_name . "." . $destination_path_infos, '~') . '~', '$1' . "", $destination, 1);
        $destination = $PHPprotectV55 . $generated_url . "." . $destination_path_infos;

    }

} else {
    die();
}

if (is_dir($padded_files_url . $destination)) {
    echo '!erreur! Un dossier portant le nom <strong style="word-wrap:break-word;">' . basename($destination) . '</strong> existe déjà. <br/><br/>

Veuillez le renommer et essayer de nouveau.';
    die();
}

rename($padded_files_url . $source_file, $padded_files_url . $destination);


if (0 === strpos(realpath($padded_files_url . $destination), $files_url)) {
} else {
    rename($padded_files_url . $destination, $padded_files_url . $source_file);
    die();
}


$destination_path_info = pathinfo($destination);

$s = $padded_files_url . $source_file;
$d = $padded_files_url . $destination;

$query = $database_object->prepare("UPDATE file_manager SET file_path = ? WHERE file_path = ?");
$query->bindParam(1, $d);
$query->bindParam(2, $s);

try {
    $query->execute();
} catch (Exception $e) {

}

echo "?size=" . formatSizeUnits(filesize_r($padded_files_url . $destination_path_info['dirname'])) . "&newDestination=" . $destination . "&fileName=" . $generated_url;


?>
