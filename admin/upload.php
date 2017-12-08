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
include('../includes/sendMailAdmin.php');


if ($_SESSION['connected'] != true) {
    ?>
    <script type="text/javascript">
        parent.document.location.reload(true);
    </script>
    <?php die();
}

$directory = htmlspecialchars($_POST['dir']);

if (0 === strpos(realpath($files_url . "/" . $directory), $files_url)) {
} else {
    die();
}


if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > (maxUploadSize() * 1000000)) {
    ?>
    <script type="text/javascript">
        parent.alert('Le fichier que vous tentez de télécharger est supérieur à <strong><?php echo maxUploadSize(); ?> Mb</strong>. Taille du fichier: <strong><?php echo $_SERVER['CONTENT_LENGTH'] / 1000000; ?> Mb</strong>.');
        parent.cancelUpload();
    </script>
    <?php die();
}

if ($_FILES['file']['error']) {
    ?>
    <script type="text/javascript">
        parent.alert('Une erreur est survenu lors du téléchargement.');
        parent.cancelUpload();
    </script>
    <?php die();
}

$generated_url = $_FILES["file"]["name"];
$destination_path_infos = pathinfo($generated_url, PATHINFO_EXTENSION);
$generated_url = basename($generated_url, '.' . $destination_path_infos);
$generated_url = GenerateUrl($generated_url);


if ($generated_url === "") {
    $generated_url = "Sans_titre";
}

$paginated_file = paginateFile($files_url, $directory, $generated_url, strtolower($destination_path_infos));

move_uploaded_file($_FILES["file"]["tmp_name"], $paginated_file);

chmod($paginated_file, 0777);

$query = $database_object->prepare("INSERT INTO file_manager (file_path) VALUES ( ?)");
$query->bindParam(1, $paginated_file);

try {
    $query->execute();
} catch (Exception $e) {

}

$_SESSION['newFile'] = true;
// Commented until the client understand what the hell he wants
/*if ($_SESSION['admin'] != true) {
    include('../includes/sendMail.php');
} else {

    $query = $database_object->query('SELECT email FROM users WHERE dir ="'."/" . rtrim($directory,"/") . '" ');
    $file_users = $query->fetchAll();
    $email_list = "";
    $first_time = true;
    foreach ($file_users as $u) {
        if ($first_time) {
            $first_time = false;
        }
        $email_list .= $u['email'] . ",";
    }
    $email_list = rtrim($email_list, ", ");

    // This is not made for this so i'm gonna just do it in a quick and dirty maner because I have 20 minutes of budget left

    // GET ALL THE FILES
    $query = $database_object->query('SELECT * FROM file_manager');
    $comment_list= $query->fetchAll();

    $comment = "";
    $root_directory = explode("/", $directory, 2)[0];

    //Let's find the correct comment booh ya!
    foreach($comment_list as $c){
        // Let's strip all the shite
        $parsed_path = str_replace($files_url, "", $c['file_path']);
        if(ltrim($parsed_path,'/') == $root_directory){
            $comment = $c['comment'];
            break;
        }
    }

    sendMailAdmin($email_list, $directory, $paginated_file, $header_label, $comment);
}*/

?>
<script type="text/javascript">
    parent.document.location.reload(true);
</script>
