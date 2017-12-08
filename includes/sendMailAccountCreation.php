<?php
function sendMailAccountCreation($email, $pass, $header_label)
{

    $email_list = $email;

    $header = "From: " . $_SERVER['HTTP_HOST'] . " <noreply@" . $_SERVER['HTTP_HOST'] . ">\n";
    $header .= "X-Mailer: Our Php\n";

    $boundary_x = "==String_Boundary_x" . md5(time()) . "x";
    $boundary_y = "==String_Boundary2_y" . md5(time()) . "y";


    $header .= "MIME-Version: 1.0\n";
    $header .= "Content-Type: multipart/related;\n";
    $header .= " type=\"multipart/alternative\";\n";
    $header .= " boundary=\"$boundary_x\";\n\n";

    $document_body = "";
    $document_body .= "--$boundary_x\n";
    $document_body .= "Content-Type: multipart/alternative;\n";
    $document_body .= " boundary=\"$boundary_y\";\n\n";

    $document_body .= "--$boundary_y\n";
    $document_body .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
    $document_body .= "Content-Transfer-Encoding: 7bit\n\n";

    $document_body .= "--$boundary_y\n";
    $document_body .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
    $document_body .= "Content-Transfer-Encoding: 7bit\n\n";


    $document_body .= stripslashes(utf8_decode("<html bgcolor=\"ffffff\"><head><title>Création d'un compte</title><meta name = \"format-detection\" content = \"telephone=no\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body bgcolor=\"ffffff\">
<font face=\"arial\" color=\"ffffff\">
<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
  <tr>
    <td align=\"center\" valign=\"middle\">    
    <table width=\"498\" style=\"background:#eee; border:solid #ccc 1px; font-family:Arial, Helvetica, sans-serif; color:#666; font-size: 12px;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
    <tr>
    <td style=\"padding:30px 40px 30px 40px;\" width=\"420\" align=\"left\" valign=\"top\">
    
    <div style=\"text-transform: uppercase; font-size: 14px;\">
    <img style=\"margin-bottom:10px;\" src=\"cid:image1\" align=\"middle\" width=\"148\" height=\"28\" alt=\"HUBZ / " . $header_label . "\"\> <strong> / " . $header_label . "</strong>
    </div>
    <P>
Bonjour,
<br><br>
Nous avons créé votre profil dans notre espace client Consumaj. Nous pourrons se partager quelques documents en cours de mandat.
<br><br>
Pour accéder à votre espace, veuillez-vous connecter en suivant ces accès
<br><br>
Nom d'utilisateur : ".$email."<br>
Mot de pass : ".$pass."<br>
  <br/> <a href='https://consumaj.com/hubz/'>Cliquez ici pour accéder à votre espace client.</a>
<br><br>
Merci de nous faire confiance !<br>
Équipe Consumaj
</P>

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
    $PHPprotectv101 = fopen($PHPprotectV82, 'rb');
    $PHPprotectV83 = fread($PHPprotectv101, filesize($PHPprotectV82));
    fclose($PHPprotectv101);


    $PHPprotectV83 = chunk_split(base64_encode($PHPprotectV83));
    $document_body .= "$PHPprotectV83\n\n";


    $document_body .= "--$boundary_x--\n";


    $title_line = utf8_decode("Création d'un compte");


    if (@mail($email_list, $title_line, $document_body, $header)) {


    } else {


    }

}


?>
