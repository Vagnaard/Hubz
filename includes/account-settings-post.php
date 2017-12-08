<?php

session_start();

header('Content-type: text/html; charset=utf-8');


if ($_SESSION['connected'] != true) {
    die();
}

$user_id = htmlspecialchars($_POST['userId']);

if ($_SESSION['admin'] != true && $user_id != $_SESSION['userId']) {
    $user_id = $_SESSION['userId'];
}

include('../config.php');
if ($files_url != "") {
    $files_url = "/" . $files_url;
}
$files_url = $root_url . $files_url;
include('functions.php');
include('dbconnect.php');

$query = $database_object->query("SELECT * FROM users WHERE userId = '" . $user_id . "'");
$current_user = $query->fetch();

$current_email = htmlspecialchars($_POST['email']);
$current_name = htmlspecialchars($_POST['name']);
$current_password = htmlspecialchars($_POST['password']);
$current_password_confirm = htmlspecialchars($_POST['passwordConfirm']);
$current_status = htmlspecialchars($_POST['status']);
$directory = htmlspecialchars($_POST['dir']);
$descriptorId = $_POST['descriptorId'];

if ($current_status == "admin" || current_status == "superadmin" ) {
    $directory = "";
}

if (!empty($current_password) || !empty($current_password_confirm)) {

    if ($current_password != $current_password_confirm) {
         
        ?>
        <script type="text/javascript">
            parent.alert('La confirmation du mot de passe est incorrecte. Aucun changement n\'a été apporté.', 'Mot de passe inccorect');
        </script>
        <?php die();
    }


    if (strlen($current_password) < 6) {
         
        ?>
        <script type="text/javascript">
            parent.alert('Le mot de passe choisi est trop court. Il doit comporter un minimum de <strong>6 caractères</strong>.', 'Mot de passe<br/> trop court');
        </script>
        <?php die();
    }

}

if ($_SESSION['admin'] == true && $current_email != $current_user['email']) {

    if (!check_email_address($current_email)) {
         
        ?>
        <script type="text/javascript">
            parent.alert('L\'adresse courriel que vous avez saisi n\'est pas valide. Aucun changement n\'a été apporté.', 'Courriel invalide');
        </script>
        <?php die();
    }

    $email_query = $database_object->query("SELECT * FROM users WHERE email = '" . $current_email . "'");
    $PHPprotectV67 = $email_query->fetch();

    if (!empty($PHPprotectV67)) {
        ?>
        <script type="text/javascript">
            parent.alert('L\'adresse courriel que vous avez saisi est déjà utilisé pour un autre compte. Aucun changement n\'a été apporté.', 'Courriel invalide');
        </script>
        <?php die();
    }
}


if ($_SESSION['admin'] == true && $user_id != $_SESSION['userId']) {

    if ($current_status == "user" || $current_status == "uploader" || $current_status == "admin" || $current_status == "superadmin") {

    } else {
        die();
    }

    if (0 === strpos(realpath($files_url . $directory), $files_url)) {
    } else {
        die();
    }

}


if ($_SESSION['admin'] == true && $user_id != $_SESSION['userId']) {

    $PHPprotectV68 = $database_object->prepare("UPDATE users SET status = :status WHERE userId = '" . $user_id . "'");
    $PHPprotectV68->execute(array(
        'status' => $current_status
    ));

    $_SESSION['changesDone'] = true;
}


if (!empty($current_password) && !empty($current_password_confirm)) {

    $PHPprotectV68 = $database_object->prepare("UPDATE users SET password = :password, firstTime = :firstTime WHERE userId = '" . $user_id . "'");
    $PHPprotectV68->execute(array(
        'password' => $current_password,
        'firstTime' => 0
    ));

    $_SESSION['changesDone'] = true;

}

if ($_SESSION['admin'] == true && $current_email != $current_user['email']) {

    $PHPprotectV68 = $database_object->prepare("UPDATE users SET email = :email WHERE userId = '" . $user_id . "'");
    $PHPprotectV68->execute(array(
        'email' => $current_email
    ));

    $_SESSION['changesDone'] = true;
}

if ($_SESSION['admin'] == true && $current_name != $current_user['name']) {

    $PHPprotectV68 = $database_object->prepare("UPDATE users SET name = :name WHERE userId = '" . $user_id . "'");
    $PHPprotectV68->execute(array(
        'name' => $current_name
    ));

    $_SESSION['changesDone'] = true;
}

if ($_SESSION['admin'] == true && $user_id != $_SESSION['userId'] && $directory != $current_user['dir']) {

    $PHPprotectV68 = $database_object->prepare("UPDATE users SET dir = :dir WHERE userId = '" . $user_id . "'");
    $PHPprotectV68->execute(array(
        'dir' => $directory
    ));

    $_SESSION['changesDone'] = true;
}

if($_SESSION['superadmin'] == true && !empty($descriptorId)) {
    $query = $database_object->prepare("INSERT INTO user_security (user_id, security_descriptor_id) VALUES (?, ?)");
    $query->bindParam(1, $user_id);
    $query->bindParam(2, $descriptorId);

    try {
        $query->execute();
    } catch (Exception $e) {

    }

}

?>

<script type="text/javascript">
    parent.document.location.reload(true);
</script>