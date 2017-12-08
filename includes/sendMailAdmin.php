<?php

function sendMailAdmin($email_list, $directory, $paginated_file, $header_label, $comment){
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


    if ($directory == "") {
        $baseFolder = "Mes documents/";
    } else {
        $baseFolder = $directory;
    }

    $parsed_comment = $comment ? "<br/>Commentaire de l'administrateur: <br/>".$comment."<br/><br/>" : "";

    $document_body .= stripslashes(utf8_decode("<html bgcolor=\"ffffff\"><head><title>Ajout d'un nouveau fichier</title><meta name = \"format-detection\" content = \"telephone=no\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body bgcolor=\"ffffff\">
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
    <p>
    Bonjour, 
    <br><br>
    L'utilisateur ".$_SESSION['email'] .", a déposé un document dans votre espace client Consumaj.<br>
    <br>
    ".$parsed_comment."
    <br/> <a href='https://consumaj.com/hubz/'>Cliquez ici pour accéder à votre espace client.</a>
    Pour toute questions, n'hésitez pas à nous contacter ! <br>
    <br>
    Équipe Consumaj

    </p>
    <br/>
     Un fichier portant le nom: <strong>" . basename($paginated_file) . "</strong> a été ajouté dans le dossier suivant:<br/><br/> <span style='font-size:18px; font-family: \"Courier New\", Courier, monospace;'>" . $baseFolder . "</span><br/><br/> Ajouté par: <strong>" . $_SESSION['email'] . "</strong>
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
    $PHPprotectv101 = fopen($PHPprotectV82, 'rb');
    $PHPprotectV83 = fread($PHPprotectv101, filesize($PHPprotectV82));
    fclose($PHPprotectv101);


    $PHPprotectV83 = chunk_split(base64_encode($PHPprotectV83));
    $document_body .= "$PHPprotectV83\n\n";


    $document_body .= "--$boundary_x--\n";


    $title_line = utf8_decode("Ajout d'un nouveau fichier");


    if (@mail($email_list, $title_line, $document_body, $header)) {


    } else {


    }
}

?>
