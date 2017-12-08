<?php

header('Content-type: text/html; charset=utf-8');

session_start();
include('../config.php');
if ($files_url != "") {
    $files_url = "/" . $files_url;
}
$files_url = $root_url . $files_url . $_SESSION['dir'];
include('functions.php');
include('dbconnect.php');

function logout()
{
    $_SESSION['connected'] = false;
    $_SESSION['admin'] = false;
    $_SESSION['superadmin'] = false;
    $_SESSION['uploader'] = false;
    $_SESSION['dir'] = "";
    $_SESSION['email'] = "";
    $_SESSION['userId'] = "";
    $_SESSION['rights'] = [];
    $_SESSION['rights_id'] = [];
}


$current_email = htmlspecialchars($_POST['email']);
$current_password = htmlspecialchars($_POST['password']);
$PHPprotectV108 = htmlspecialchars($_GET['logout']);

if (!empty($PHPprotectV108)) {
    logout();
     
    ?>
    <script type="text/javascript">
        parent.document.location.reload(true);
    </script>
    <?php die();
}

if (empty($current_email) || empty($current_password)) {
     
    ?>
    <script type="text/javascript">
        parent.alert('L\'adresse courriel ou le mot de passe est manquant, veuillez remplir tout les champs.');
    </script>
    <?php logout();
    die();
}


$query = $database_object->query("SELECT * FROM users WHERE email = '" . $current_email . "'");
$current_user = $query->fetch();

if (empty($current_user)) {
    ?>
    <script type="text/javascript">
        parent.alert('L\'adresse courriel que vous avez saisi n\'est associé à aucun compte.', 'Adresse courriel incorrecte');
    </script>
    <?php logout();
    die();
} else {

    $user_id = $current_user['userId'];
    $PHPprotectV109 = $current_user['password'];
    $current_status = $current_user['status'];
    $directory = $current_user['dir'];
    $PHPprotectV110 = $current_user['firstTime'];
    $_SESSION['rights'] = [];
    $_SESSION['rights_id'] = [];

    $query = $database_object->query("SELECT security_descriptors.id as id, descriptor FROM user_security JOIN security_descriptors ON security_descriptors.id = user_security.security_descriptor_id WHERE user_id = " . $user_id . " ");
    $current_user_descriptors = $query->fetchAll();

    foreach($current_user_descriptors as $d){
        array_push($_SESSION['rights'], $d["descriptor"]);
        array_push($_SESSION['rights_id'], $d["id"]);
    }

    if ($current_password !== $PHPprotectV109) {
        ?>
        <script type="text/javascript">
            parent.alert('Le mot de passe que vous avez saisi est incorrect. Assurez vous que le verrouillage des majuscules est désactivé. <br/><br/><a target="uploadIframe" href="includes/forgot-password.php">J\'ai oublié mon mot de passe</a>', 'Mot de passe incorrect');
        </script>
        <?php
        $_SESSION['forgotPassword'] = $current_email;
        logout();
        die();
    } else {

        $_SESSION['connected'] = true;
        $_SESSION['dir'] = $directory;
        $_SESSION['email'] = $current_email;
        $_SESSION['userId'] = $user_id;

        if ($current_status == "admin") {
            $_SESSION['admin'] = true;
            $_SESSION['uploader'] = true;
        } else {
            $_SESSION['admin'] = false;
        }

        if ($current_status == "superadmin") {
            $_SESSION['superadmin'] = true;
            $_SESSION['admin'] = true;
            $_SESSION['uploader'] = true;
        } else {
            $_SESSION['superadmin'] = false;
        }

        if ($current_status == "uploader") {
            $_SESSION['uploader'] = true;
        }

        if ($current_status == "user") {
            $_SESSION['uploader'] = false;
        }

        if ($PHPprotectV110 == 1) {
            ?>
            <script type="text/javascript">

                parent.confirm("C'est votre première visite ici, désirez-vous changer votre mot de passe par défault?", function (callback) {
                    if (callback) {
                        parent.window.location = "?account-settings=true&userId=<?php echo $user_id; ?>";
                    } else {
                        parent.document.location.reload(true);
                    }
                }, 'Bienvenue à vous');

            </script>
            <?php

            $PHPprotectV68 = $database_object->prepare("UPDATE users SET firstTime = :firstTime WHERE userId = '" . $user_id . "'");
            $PHPprotectV68->execute(array(
                'firstTime' => 0
            ));

            die();
        }


        if ($PHPprotectV110 == 2) {
            ?>
            <script type="text/javascript">

                parent.confirm("Votre mot de passe a subi une réinitialisation. Veuillez le mettre à jour pour des raisons de sécurité.", function (callback) {
                    if (callback) {
                        parent.window.location = "?account-settings=true&userId=<?php echo $user_id; ?>";
                    } else {
                        parent.document.location.reload(true);
                    }
                }, 'Nouveau mot de passe');

            </script>
            <?php

            die();
        }

    }

}


?>

<script type="text/javascript">
    parent.document.location.reload(true);
</script>
