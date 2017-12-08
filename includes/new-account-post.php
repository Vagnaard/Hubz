<?php

header('Content-type: text/html; charset=utf-8');

session_start();

if ($_SESSION['admin'] != true) {
    die();
}

include('../config.php');
if ($files_url != "") {
    $files_url = "/" . $files_url;
}
$files_url = $root_url . $files_url;
include('functions.php');
include('sendMailAccountCreation.php');
include('dbconnect.php');

$current_email = htmlspecialchars($_POST['email']);
$current_password = htmlspecialchars($_POST['password']);
$current_password_confirm = htmlspecialchars($_POST['passwordConfirm']);
$current_status = htmlspecialchars($_POST['status']);
$directory = htmlspecialchars($_POST['dir']);
$descriptorId = $_POST['descriptorId'];

if ($current_status == "admin") {
    $directory = "";
}


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

if (empty($current_password_confirm) || empty($current_password)) {
     
    ?>
    <script type="text/javascript">
        parent.alert('Veuillez entrer un mot de passe. Il doit comporter un minimum de <strong>6 caractères</strong>.', 'Mot de passe<br/> incorrect');
    </script>
    <?php die();
}


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


if ($current_status == "user" || $current_status == "uploader" || $current_status == "admin") {

} else {
    die();
}

if (0 === strpos(realpath($files_url . $directory), $files_url)) {
} else {
    die();
}

$PHPprotectV68 = $database_object->prepare('INSERT INTO users (email, password, status, dir) VALUES(?, ?, ?, ?)');
$PHPprotectV68->execute(array($current_email, $current_password, $current_status, $directory));

$query = $database_object->query("SELECT * FROM users WHERE email = '" . $current_email . "'");
$current_user = $query->fetch();

if($_SESSION['superadmin'] == true && !empty($descriptorId)) {
$query = $database_object->prepare("INSERT INTO user_security (user_id, security_descriptor_id) VALUES (?, ?)");
$query->bindParam(1, $current_user['userId']);
$query->bindParam(2, $descriptorId);

try {
    $query->execute();
} catch (Exception $e) {

}

}

$_SESSION['goToAccounts'] = true;

sendMailAccountCreation("$current_email", $current_password, $header_label);


?>
<script type="text/javascript">
    parent.document.location.reload(true);
</script>
