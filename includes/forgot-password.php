<?php

session_start();

if (empty($_SESSION['forgotPassword'])) {
    die();
}

include('../config.php');
include('functions.php');
include('dbconnect.php');

if ($http_redirect == true) {
    $http_suffix = "s";
} else {
    $http_suffix = "";
}

$generated_password = generatePassword();

$PHPprotectV68 = $database_object->prepare("UPDATE users SET password = :password, firstTime = :firstTime WHERE email = '" . $_SESSION['forgotPassword'] . "'");
$PHPprotectV68->execute(array(
    'password' => $generated_password,
    'firstTime' => 2
));

$email_list = $_SESSION['forgotPassword'];

$header = "From: " . $_SERVER['HTTP_HOST'] . " <noreply@" . $_SERVER['HTTP_HOST'] . ">\n";
$header .= "X-Mailer: Our Php\n";

$boundary_x = "==String_Boundary_x" . md5(time()) . "x";
$boundary_y = "==String_Boundary2_y" . md5(time()) . "y";


$header .= "MIME-Version: 1.0\n";
$header .= "Content-Type: multipart/related;\n";
$header .= " type=\"multipart/alternative\";\n";
$header .= " boundary=\"$boundary_x\";\n\n";


$password_line = "Votre nouveau mot de passe: " . $generated_password . " \n\n";


$document_body .= "--$boundary_x\n";
$document_body .= "Content-Type: multipart/alternative;\n";
$document_body .= " boundary=\"$boundary_y\";\n\n";


$document_body .= "--$boundary_y\n";
$document_body .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
$document_body .= "Content-Transfer-Encoding: 7bit\n\n";

$document_body .= "--$boundary_y\n";
$document_body .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
$document_body .= "Content-Transfer-Encoding: 7bit\n\n";


$document_body .= stripslashes(utf8_decode("<html bgcolor=\"ffffff\"><head><title>Réinitialisation du mot de passe</title><meta name = \"format-detection\" content = \"telephone=no\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body bgcolor=\"ffffff\">
<font face=\"arial\" color=\"ffffff\">
<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
  <tr>
    <td align=\"center\" valign=\"middle\">    
    <table width=\"498\" style=\"background:#eee; border:solid #ccc 1px; font-family:Arial, Helvetica, sans-serif; color:#666; font-size: 12px;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
    <tr>
    <td style=\"padding:30px 40px 30px 40px;\" width=\"420\" align=\"left\" valign=\"top\">
    
    <div style=\"text-transform: uppercase; font-size: 14px;\">
    <img style=\"margin-bottom:10px;\" src=\"cid:image1\" align=\"middle\" width=\"74\" height=\"35\" alt=\"HUBZ / " . $header_label . "\"\> <strong> / " . $header_label . "</strong>
    </div>
    <br/>
     Une demande de réinitialisation du mot de passe a été effectuée, voici le nouveau code à utiliser pour vous connecter:<br/><br/> <span style='font-size:24px; font-family: \"Courier New\", Courier, monospace;'>" . $generated_password . "</span><br/><br/> <strong>Prenez-le en note et assurez-vous de le garder dans un endroit sûr!</strong>
    </td>
  </tr>
  <tr>
    <td style=\"padding:20px 40px 20px 20px; background:#eee; border-top: solid #ccc 1px;\" align=\"left\" valign=\"top\"><h6 style=\"color:#666 !important; font-weight:normal; margin:0px; padding:0px; font-size:12px;\";>© " . date("Y") . " | <a style=\"color:#0076C0; text-transform:uppercase; font-weight:bold;\" href=\"http" . $http_suffix . "://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])) . "\">" . $header_label . "</a></h6></td>
  </tr>
</table>
    
    
    </td>
  </tr>
</table>
</font>
</body>
</html>\n"));

$document_body .= "--$boundary_y--\n";


$document_body .= "--$boundary_x\n";
$document_body .= "Content-ID: <image1>\n";
$document_body .= "Content-Type: image/png\n";
$document_body .= "Content-Transfer-Encoding: base64\n\n";


$PHPprotectV82 = "../img/logo.png";
$file_name = fopen($PHPprotectV82, 'rb');
$PHPprotectV83 = fread($file_name, filesize($PHPprotectV82));
fclose($file_name);


$PHPprotectV83 = chunk_split(base64_encode($PHPprotectV83));
$document_body .= "$PHPprotectV83\n\n";


$document_body .= "--$boundary_x--\n";


$title_line = utf8_decode("Réinitialisation du mot de passe");


if (@mail($email_list, $title_line, $document_body, $header)) {


    $_SESSION['forgotPassword'] = 1;


    ?>
    <script type="text/javascript">
        parent.document.location.reload(true);
    </script>

    <?php


} else {


}


?>
