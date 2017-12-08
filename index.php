<?php session_start();

header('Content-type: text/html; charset=utf-8');

include('config.php');
include('includes/dbconnect.php');

if ($http_redirect == true) {

    $http_suffix = "s";

    if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
        header('Location: https://' . substr($_SERVER['HTTP_HOST'], 4) . $_SERVER['REQUEST_URI']);
        exit();
    }

    if ($_SERVER["HTTPS"] != "on") {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
        exit();
    }
} else {
    $http_suffix = "";
}

header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");

if ($files_url != "") {
    $files_url = "/" . $files_url;
}
$files_url = $root_url . $files_url . $_SESSION['dir'];
include('includes/functions.php');

$is_account_setting = htmlspecialchars($_GET['account-settings']);
$is_accounts = htmlspecialchars($_GET['accounts']);
$is_admin_settings = htmlspecialchars($_GET['admin_settings']);
$is_file_admin = htmlspecialchars($_GET['file_admin']);


if ($_SESSION['goToAccounts'] != false) {
    header('location: ' . dirname($_SERVER['SCRIPT_NAME']) . '?accounts=true');
    $_SESSION['goToAccounts'] = false;
}

$directory = htmlspecialchars($_GET['dir']);
$directory_for_theme_maybe = htmlspecialchars($_GET['dir']);
if ($directory !== "") {
    $directory = $directory . "/";
}

$directory_path = $files_url . "/" . $directory;

if (0 === strpos(realpath($directory_path), $files_url)) {
    $PHPprotectV24 = true;
} else {
    $PHPprotectV24 = false;
}

$theme = 2;

if (!empty($theme)) {
    $_SESSION['theme'] = $theme;
}

if (!$_SESSION['theme']) {
    $theme = 1;
    $_SESSION['theme'] = $theme;
}

if ($_SESSION['theme']) {
    $theme = $_SESSION['theme'];
}

$sort = htmlspecialchars($_GET['sort']);

if (!empty($sort)) {
    $_SESSION['sort'] = $sort;
}

if (!$_SESSION['sort']) {
    $sort = "date";
    $_SESSION['sort'] = $sort;
}

if ($_SESSION['sort']) {
    $sort = $_SESSION['sort'];
}

$rights_id_map = [];
$descriptor_list = [];

if ($_SESSION['userId']) {
    $_SESSION['rights'] = [];
    $database_object->exec("SET NAMES `utf8`");
    $query = $database_object->query("SELECT descriptor, security_descriptors.id as descriptor_id FROM user_security JOIN security_descriptors ON security_descriptors.id = user_security.security_descriptor_id WHERE user_id = " . $_SESSION['userId'] . " ");
    $current_user_descriptors = $query->fetchAll();

    foreach ($current_user_descriptors as $d) {
        array_push($_SESSION['rights'], $d["descriptor"]);
        $rights_id_map[$d["descriptor"]] = $d["descriptor_id"];
    }

    $query = $database_object->query("SELECT descriptor, id as descriptor_id FROM security_descriptors");
    $descriptor_list = $query->fetchAll();

    // Lets also destroy files that should be dead while we are at it.

    $query = $database_object->query("SELECT * FROM file_manager WHERE deletion_date_time <= '" . date("Y-m-d") . "' ");
    $to_delete = $query->fetchAll();

    foreach ($to_delete as $deadFile) {
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
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <meta http-equiv="X-UA-Compatible" content="IE=7"/>
    <meta name="viewport" content="initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">

    <title>HUBZ / <?php echo $header_label; ?></title>

    <link href="img/favicon.png" rel="shortcut icon"/>

    <script src="http<?php echo $http_suffix; ?>://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"
            type="text/javascript"></script>
    <script src="scripts/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
    <script src="scripts/jquery.placeholder.min.js" type="text/javascript"></script>
    <script src="scripts/jquery.touchpunch.js" type="text/javascript"></script>
    <script src="scripts/jquery.doubletap.js" type="text/javascript"></script>


    <link rel="stylesheet" media="screen" href="css/style.css" type="text/css"/>
    <link rel="stylesheet" media="screen and (max-width: 860px)" href="css/tablet.css" type="text/css"/>

    <script type="text/javascript">

        $(function () {


            $("input:text, textarea").placeholder();


            $(".unselectable").disableSelection();


            $("input:text, input:password, textarea").bind("focus mousedown", function () { // mousedown is for FF
                $(".unselectable").enableSelection();
                $(".file").draggable('disable');
            });

            $("input:text, input:password, textarea").blur(function () {
                $(".unselectable").disableSelection();
                $(".file").draggable('enable');
            });


            <?php if($_SESSION['pendingDownload'] != "" && $_SESSION['connected'] != false){
            ?>

            $(window).load(function () {
                confirm('Êtes-vous certain de vouloir télécharger le fichier <strong style="word-wrap:break-word;"><?php echo basename($_SESSION['pendingDownload']);
                    ?></strong> ?', function (callback) {
                    if (callback) {
                        $(location).attr('href', "file.php?file=<?php echo $_SESSION['pendingDownload'];
                            ?>&onSite=true");
                    }
                });
            });

            <?php

            $_SESSION['pendingDownload'] = "";

            }
            ?>



            <?php if($_SESSION['deniedAccess'] != false && $_SESSION['connected'] != false){
            ?>

            $(window).load(function () {
                alert('Le fichier que vous tentez d\'accèder est introuvable, il a peut être été renommé, déplacé ou supprimé.', 'Fichier introuvable');
            });

            <?php

            $_SESSION['deniedAccess'] = false;

            }
            ?>


            <?php if($_SESSION['admin'] == true){
            ?>


            $("#generatePassword").click(function () {

                var generatedPassword = wpiGenerateRandomNumber(6);

                alert("Voici le mot de passe que vous avez généré:<br/><br/> <span style='font-size:24px; font-family: \"Courier New\", Courier, monospace;'>" + generatedPassword + "</span><br/><br/> <strong>Prenez-le en note et assurez-vous de le garder dans un endroit sûr!</strong>", 'Mot de passe');

                $(".pageForm input[type=password]").val(generatedPassword);

            });

            $("#status").change(function () {

                if ($(this).children("option:selected").val() != "admin") {
                    $("#dirEditor").slideDown(250);
                } else {
                    $("#dirEditor").slideUp(250);
                    $("#dir").children("option:first-child").attr("selected", "true");
                }

            });


            $(".user").dblclick(function () {
                $(location).attr('href', $(this).attr('data-href'));
            });

            $(".user").doubletap(function () {
                $(location).attr('href', $(this).attr('data-href'));
            });

            <?php }
            ?>

            <?php if($_SESSION['uploader'] == true){
            ?>

            $(".edit").click(function () {

                var oldName = $(this).parent().find(".fileName").text();

                $(this).parent().find('.editField').show().focus().val(oldName);
                $(this).parent().find(".fileName").hide();

                $(".file").removeClass("focused");
                $(this).parent().addClass("focused");

                $(this).parent().find('.descriptor').show();
                $(this).parent().find(".descriptor_name").hide();

                $(this).parent().find('.expiry').show();
                $(this).parent().find(".expiry_date_label").hide();

                //$(this).parent().find('.email').show();

                return false;
            });

            $(".descriptorId").on('change', function() {
                var descriptorId = $(this).val();
                var fileId = $(this).parent().parent().find("#file_idz").val();

                if(descriptorId){
                    $.post("includes/file_administration_post.php", {
                        file_id: fileId,
                        descriptorId: descriptorId
                    }, function() {parent.document.location.reload(true);})
                }

                $(this).parent().parent().find('.descriptor').hide();
                $(this).parent().parent().find(".descriptor_name").show();
            });

            $(".expiry_date").on('change',function() {
                var date = $(this).val();
                var fileId = $(this).parent().parent().find("#file_idz").val();

                if(date){
                    $.post("includes/file_administration_post.php", {
                        file_id: fileId,
                        deletion_date: date
                    }, function() {parent.document.location.reload(true);})
                }

                $(this).parent().parent().find('.expiry').hide();
                $(this).parent().parent().find(".expiry_date_label").show();
            });

            $(".email_text").on('change',function() {
                var comment = $(this).val();
                var fileId = $(this).parent().parent().find("#file_idz").val();

                if(comment){
                    $.post("includes/file_administration_post.php", {
                        file_id: fileId,
                        comment: comment
                    }, function() {parent.document.location.reload(true);})
                }

                $(this).parent().parent().find('.email').hide();
            });

            $(".editField").blur(function () {
                elem = this;
                $(this).parent().parent().removeClass("focused");
                $(this).val(jQuery.trim($(this).val()));
                newName = $(this).val();
                fieldValue = newName;
                if (getParameterByName($(this).parent().parent().attr("data-href"), "dir")) {
                    oldName = getParameterByName($(this).parent().parent().attr("data-href"), "dir");
                    dir = oldName.substring(0, oldName.lastIndexOf("/"));
                    if (dir != "") {
                        newName = dir + "/" + newName
                    }
                    fileType = "folder"
                }
                if (getParameterByName($(this).parent().parent().attr("data-href"), "file")) {
                    oldName = getParameterByName($(this).parent().parent().attr("data-href"), "file");
                    dir = oldName.substring(0, oldName.lastIndexOf("/"));
                    extension = oldName.substring(oldName.lastIndexOf("."));
                    if (dir == "") {
                        newName = newName + extension
                    } else {
                        newName = dir + "/" + newName + extension
                    }
                    fileType = "file"
                }
                if ($(this).val() != "" && $(this).parent().parent().attr("data-href").substring($(this).parent().parent().attr("data-href").lastIndexOf("=")) != "=" + newName) {
                    $.post("admin/renamefile.php", {
                        sourceFile: oldName,
                        destination: newName,
                        newName: fieldValue
                    }, function (a) {
                        var c = getParameterByName(a, "newDestination");
                        var b = getParameterByName(a, "fileName");
                        if (a.substring(0, a.indexOf(" ")) == "!erreur!") {
                            alert(a.substr(a.indexOf(" ") + 1));
                            $(elem).val("")
                        } else {
                            $(elem).parent().find(".fileName").text(b);
                            if (fileType == "file") {
                                $(elem).parent().parent().attr("data-href", "file.php?file=" + c);
                                $(elem).parent().parent().find(".delete").attr("data-href", "admin/deletefile.php?file=" + c);
                                $(elem).parent().parent().find(".delete").attr("data-basename", "?file=" + b + extension);
                                $(elem).parent().parent().find(".download").attr("data-href", c)
                            }
                            if (fileType == "folder") {
                                $(elem).parent().parent().attr("data-href", "?dir=" + c);
                                $(elem).parent().parent().find(".delete").attr("data-href", "admin/deletefile.php?file=" + c);
                                $(elem).parent().parent().find(".delete").attr("data-basename", "?dir=" + b)
                            }
                        }
                    })
                }
                $(elem).hide();
                $(elem).parent().find(".fileName").show();
            });

            $(".file").draggable({
                helper: "clone",
                revert: true,
                revertDuration: 250,
                zIndex: 444,
                scroll: true,
                containment: "document",
                cancel: ".parentFolder, .editField, .edit, .download, .delete, .user",
                opacity: 0.9,
                start: function (a, b) {
                    $(".ui-draggable-dragging").width($(this).width());
                    $(this).addClass("dragged");
                    $(".file").removeClass("selected");
                    $(this).addClass("selected");
                    $(".editField:focus").blur()
                },
                stop: function (a, b) {
                    $(".file").draggable("option", "revert", true);
                    $(".file").removeClass("dragged")
                }
            });

            function getParameterByName(c, b) {
                var a = RegExp("[?&]" + b + "=([^&]*)").exec(c);
                return a && decodeURIComponent(a[1].replace(/\+/g, " "))
            }

            $(".folder").droppable({
                hoverClass: "dropAble", drop: function (c, d) {
                    $(".file").draggable("option", "revert", false);
                    $(".file").addClass("noShake").removeClass("shake");
                    elem = this;
                    var b = getParameterByName($(d.draggable).attr("data-href"), "file");
                    var a = getParameterByName($(this).attr("data-href"), "dir");
                    if ($(d.draggable).hasClass("folder")) {
                        var b = getParameterByName($(d.draggable).attr("data-href"), "dir")
                    }
                    if (b.substring(0, b.lastIndexOf("/")) == "") {
                        a = a + "/" + b.substring(b.lastIndexOf("/"))
                    } else {
                        a = a + b.substring(b.lastIndexOf("/"))
                    }
                    $.post("admin/renamefile.php", {sourceFile: b, destination: a}, function (f) {
                        var e = getParameterByName(f, "size");
                        if (isNaN($(elem).find(".fileNumber span").text())) {
                            fileNumber = 0
                        } else {
                            fileNumber = parseInt($(elem).find(".fileNumber span").text())
                        }
                        $(elem).find(".fileNumber span").text(fileNumber + 1);
                        if (f.substring(0, f.indexOf(" ")) == "!erreur!") {
                            alert(f.substr(f.indexOf(" ") + 1));
                            return false
                        } else {
                            $(elem).find(".fileInfos .size").html(e)
                        }
                        if (fileNumber + 1 > 1) {
                            $(elem).find(".fileNumber").html("<span>" + (fileNumber + 1) + "</span> éléments")
                        }
                        $(d.draggable).animate({opacity: 0}, 250, function () {
                            $(d.draggable).remove()
                        });
                        $(".ui-draggable-dragging").remove()
                    })
                }
            });


            $(".delete").click(function () {
                deleteFileName = getParameterByName($(this).attr("data-basename"), "file");
                deleteFolderName = getParameterByName($(this).attr("data-basename"), "dir");

                elem = this;

                if (deleteFileName != null) {
                    confirm('Êtes-vous certain de vouloir supprimer le fichier <strong style="word-wrap:break-word;">' + deleteFileName + '</strong> ?', function (callback) {
                        if (callback) {
                            $("#uploadIframe").attr('src', $(elem).attr('data-href'));
                        }
                    });
                }
                if (deleteFolderName != null) {
                    confirm('Êtes-vous certain de vouloir supprimer le dossier <strong style="word-wrap:break-word;">' + deleteFolderName + '</strong> ainsi que tout ses documents?', function (callback) {
                        if (callback) {
                            $("#uploadIframe").attr('src', $(elem).attr('data-href'));
                        }
                    });
                }

            });


            $("#newFolder").click(function () {
                $("#uploadIframe").attr("src", "admin/newfolder.php?dir=<?php echo $directory;
                    ?>");
                return false;
            });




            <?php

            if($_SESSION['newFolder'] == true){
            ?>

            var isiPad = navigator.userAgent.match(/iPad/i) != null;

            if (isiPad) {

            } else {
                $(".newFolder").addClass("selected").find(".edit").trigger("click");
                $(".newFolder").find("input[type=text]").select();
            }

            $(window).load(function () {
                $('html, body').animate({scrollTop: $(".newFolder").position().top - 160}, 1000);
            });

            <?php
            $_SESSION['newFolder'] = false;
            }


            ?>



            <?php

            if($_SESSION['newFile'] == true){
            ?>

            var isiPad = navigator.userAgent.match(/iPad/i) != null;

            if (isiPad) {

            } else {
                $(".newFile").addClass("selected").find(".edit").trigger("click");
                $(".newFile").find("input[type=text]").select();
            }

            $(window).load(function () {
                $('html, body').animate({scrollTop: $(".newFile").position().top - 160}, 1000);
            });
            <?php
            $_SESSION['newFile'] = false;
            }


            ?>



            $("#file").val("");
            $("#file").change(function () {
                $("#fileForm").submit();
                uploadfilename = $(this).val().split('\\').pop();
                $(".overlay strong").text(uploadfilename).parent().parent().show().animate({opacity: 0.9}, 150);


            });

            $("#cancelUpload").click(function () {
                cancelUpload();
            });

            $(".editField").click(function () {
                return false;
            });




            <?php }else{
            ?>


            $(".file").draggable({
                cancel: ".file"
            });


            <?php }
            ?>




            $("body").bind("click touchstart", function () {
                $(".file").removeClass("selected");
                $(".editField:focus").blur();

                $('.descriptor').hide();
                $(".descriptor_name").show();
                $('.expiry').hide();
                $(".expiry_date_label").show();
                $(".email").hide();
            });

            $(".file .editField").bind("touchstart", function (event) {
                event.stopPropagation();
            });

            $(".file .expiry_date").bind("click touchstart", function (event) {
                event.stopPropagation();
            });

            $(".file").bind("click touchstart", function (event) {
                $(".file").removeClass("selected");
                $(this).addClass("selected");
                $(".editField:focus").blur();

                return false;

            });


            $(".download").click(function () {

                alert("Voici le lien pour télécharger le fichier: <br/><br/><strong style='word-wrap:break-word;'>http<?php echo $http_suffix; ?>://<?php echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/file.php?file=" . $_SESSION['dir']; ?>/" + $(this).attr('data-href') + "</strong> <br/><br/>Seules les personnes ayant accès à vos documents seront en mesure de le télécharger.", "Partager le fichier");

                return false;
            });


            $(".folder").dblclick(function () {
                $(location).attr('href', $(this).attr('data-href'));
            });

            $(".folder").doubletap(function () {
                $(location).attr('href', $(this).attr('data-href'));
            });


            $(".file:not(.folder, .user)").dblclick(function () {
                if ($(this).find("input[type=text]").is(":focus")) {

                } else {

                    elem = this;

                    confirm('Êtes-vous certain de vouloir télécharger le fichier <strong style="word-wrap:break-word;">' + $(this).find(".fileName").text() + '.' + $(this).find(".extension").text() + '</strong> ?', function (callback) {
                        if (callback) {
                            $(location).attr('href', $(elem).attr('data-href') + "&onSite=true");
                        }
                    }, 'Téléchargement');
                }
            });

            $(".file:not(.folder, .user)").doubletap(function () {
                if ($(this).find("input[type=text]").is(":focus")) {

                } else {

                    elem = this;

                    confirm('Êtes-vous certain de vouloir télécharger le fichier <strong style="word-wrap:break-word;">' + $(this).find(".fileName").text() + '.' + $(this).find(".extension").text() + '</strong> ?', function (callback) {
                        if (callback) {
                            $(location).attr('href', $(elem).attr('data-href') + "&onSite=true");
                        }
                    }, 'Téléchargement');
                }
            });


            jQuery.expr[':'].contains = function (a, i, m) {
                return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
            };

            $("#searchInput").bind("keyup", function () {
                query = $(this).val();
                if (query != "") {
                    $(".file").hide();
                    $(".file .extension:contains('" + query + "')").parent().show();
                    $(".file .fileInfos .date:contains('" + query + "')").parent().parent().show();
                    $(".file label .fileName:contains('" + query + "')").parent().parent().show();
                } else {
                    $(".file").show();
                }
            });


            $(".pageForm").submit(function () {
                $(".win").remove();
            });


            $(".alert .confirmBtn").live("click", function () {

                $(this).parent().fadeOut(150).remove();

                if ($(".alert").length == 0) {
                    $(".alertOverlay").animate({opacity: 0}, 150, function () {
                        $(".alertOverlay").hide();
                    });
                }
                if ($(".alert").length > 0) {
                    $(".alert:first").show().css({
                        marginLeft: -$(".alert:first").width() / 2,
                        marginTop: -$(".alert:first").height() / 2
                    }).animate({opacity: 1}, 150).find(".confirmBtn:last").focus();
                }

                return false;

            });

            (function () {
                window.alert = function (a, b) {
                    if (!b) {
                        b = "Oups!"
                    }
                    $("body").prepend('<div class="alert"><h1>' + b + "</h1><p>" + a + '</p><a href="#" class="btn confirmBtn">Ok</a></div>');
                    $(".alert").css({opacity: 0}).hide();
                    $(".alert:first").show().css({
                        marginLeft: -$(".alert:first").width() / 2,
                        marginTop: -$(".alert:first").height() / 2
                    }).animate({opacity: 1}, 150).find(".confirmBtn:last").focus();
                    $(".alertOverlay").show().animate({opacity: 0.8}, 150)
                }
            })();
            var confirmId = 0;
            (function () {
                window.confirm = function (a, c, b) {
                    if (!b) {
                        b = "Confirmation"
                    }
                    confirmId++;
                    $("body").prepend('<div class="alert"><h1>' + b + "</h1><p>" + a + '</p><a href="#" id="cancel_' + confirmId + '" class="btn confirmBtn cancel">Non</a><a href="#" id="confirm_' + confirmId + '" class="btn confirmBtn">Oui</a></div>');
                    $(".alert").css({opacity: 0}).hide();
                    $(".alert:first").show().css({
                        marginLeft: -$(".alert:first").width() / 2,
                        marginTop: -$(".alert:first").height() / 2
                    }).animate({opacity: 1}, 150).find(".confirmBtn:last").focus();
                    $(".alertOverlay").show().animate({opacity: 0.8}, 150);
                    $("#confirm_" + confirmId).click(function () {
                        c(true)
                    });
                    $("#cancel_" + confirmId).click(function () {
                        c(false)
                    })
                }
            })();

        });


        <?php if($_SESSION['uploader'] == true){
        ?>

        function cancelUpload() {
            $("#uploadIframe").attr("src", "scripts/blank.html");
            $(".overlay").hide();
            $("#file").val("");
        }

        <?php }
        ?>

        <?php if($_SESSION['admin'] == true){
        ?>

        function wpiGenerateRandomNumber(limit) {

            limit = limit || 8;

            var password = '';

            var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

            var list = chars.split('');
            var len = list.length, i = 0;

            do {

                i++;

                var index = Math.floor(Math.random() * len);

                password += list[index];

            } while (i < limit);

            return password;


        }

        <?php }
        ?>


    </script>


    <style type="text/css">

        <?php if($_SESSION['uploader'] == true){
        ?>

        .selected .fileName, .selected .fileInfos {
            margin-right: 20px;
        }

        <?php }
        ?>

        <?php if($_SESSION['uploader'] != true){
        ?>

        .theme2 .download {
            right: 10px;
        }

        <?php }
        ?>


    </style>

</head>

<body>

<div class="alertOverlay"></div>

<div class="overlay">
    Veuillez patienter pendant le téléchargement...<br/><br/>
    <span>Fichier: <strong>Aucun</strong></span><br/><br/>
    <img src="img/ajax-loader.gif" alt="ajax-loader" width="128" height="15"/>
    <div id="cancelUpload" class="btn">Annuler</div>
</div>

<div class="leftCol">
    <a id="logoHolder" href="<?php echo dirname($_SERVER['SCRIPT_NAME']);
    ?>"><img id="mainLogo" src="img/logo.png" alt="logo" width="" height=""/>
        <div class="espace-client">ESPACE CLIENT</div>
    </a>
    <div class="leftColContent">

        <?php if ($_SESSION['connected'] == true) {
            ?>

            Bienvenue,<br/> <a style="word-wrap: break-word; white-space: pre-wrap; "
                               href="?account-settings=true&userId=<?php echo $_SESSION['userId'];
                               ?>"><?php echo $_SESSION['email'];
                ?></a>
            <ul class="menu">
                <li><a href="<?php echo dirname($_SERVER['SCRIPT_NAME']);
                    ?>">Mes documents</a></li>
                <?php if ($_SESSION['uploader'] == true && $is_account_setting == false && $is_accounts != true && $PHPprotectV24 == true && !$is_admin_settings && !$is_file_admin) {
                    ?>
                    <li><a id="newFolder" href="#">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>✚</strong>&nbsp;&nbsp;
                            Nouveau dossier</a></li>
                    <li class="fileFormHolder">
                        <form id="fileForm" target="uploadIframe" action="admin/upload.php" method="post"
                              enctype="multipart/form-data">
                            <a href="#">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>✚</strong>&nbsp;&nbsp; Ajouter un
                                fichier</a> <span> <strong><?php echo maxUploadSize();
                                    ?> Mb</strong> max.</span>
                            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo maxUploadSize();
                            ?>000000"/>
                            <input type="file" name="file" id="file">
                            <input type="hidden" name="dir" id="dir" value="<?php echo $directory;
                            ?>"/>
                        </form>
                    </li>
                <?php }
                ?>
                <?php if ($_SESSION['admin'] == true) {
                    ?>
                    <li><a href="?accounts=true">Gestion des comptes</a></li>
                    <li><a href="?account-settings=true&userId=0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>✚</strong>&nbsp;&nbsp;
                            Ajouter un compte</a></li>
                <?php }
                ?>

            </ul>
            <ul class="menu">
                <li><a href="?account-settings=true&userId=<?php echo $_SESSION['userId'];
                    ?>">✓&nbsp;&nbsp; Mon compte</a></li>
                <li><a target="uploadIframe" href="includes/login-post.php?logout=true">✖&nbsp;&nbsp; Déconnexion</a>
                </li>
            </ul>

        <?php }
        ?>

        <div id="questions">Pour toute question, n'hésitez pas à <a target="_blank" href="<?php echo $contact_url;
            ?>">nous contacter</a>.
        </div>
    </div>
</div>

<div id="dirHeader">
    <input type="text" id="searchInput" size="22" placeholder="Rechercher..."/>


    <div id="sort">

        <div class="btnGroup">
            <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']);
            ?>">/ Mes documents</a><?php

            $PHPprotectV27 = explode("/", $directory_for_theme_maybe);
            $PHPprotectV28 = "";
            $PHPprotectV29 = "";
            foreach ($PHPprotectV27 as $PHPprotectV30) {
                $PHPprotectV29 = $PHPprotectV29 . $PHPprotectV30 . "/";
                $PHPprotectV31 = substr($PHPprotectV29, 0, -1);

                $PHPprotectV28 = $PHPprotectV28 . '<a href="?dir=' . $PHPprotectV31 . '">/ ' . $PHPprotectV30 . '</a>';
            }

            if ($directory !== "") {
                echo $PHPprotectV28;
            }


            ?></div>
    </div>


</div>

<?php

if ($_SESSION['connected'] == true && $is_account_setting != true && $is_accounts != true && !$is_admin_settings && !$is_file_admin) {
    ?>

    <div class="files unselectable <?php if ($theme == 2) {
        echo 'theme2';
    }


    ?>">


        <?php

        if ($PHPprotectV24 == true) {


            ?>

            <div class="pageForm">
                <h1>
                    <?php
                    if (empty($directory)) {
                        echo 'Mes documents';
                    } else {
                        echo basename($directory);
                    }
                    ?>
                    <br/><br/></h1>
            </div>

            <?php

            if (dirname($directory) != "") {

                if (dirname($directory) != ".") {
                    $extracted_directory = dirname($directory);
                } else {
                    $extracted_directory = "";
                }

                echo '<div data-href="?dir=' . $extracted_directory . $PHPprotectV33 . '" class="file folder parentFolder">
						  <label><span class="fileName">↵ Dossier parent</span></label>
						  </div>';

            }


            date_default_timezone_set('America/Montreal');
            setlocale(LC_TIME, 'fr_FR');

            $directory_tree = glob($directory_path . '*', GLOB_BRACE);

            usort($directory_tree, create_function('$first_file,$second_file', 'return filemtime($second_file) - filemtime($first_file);'));
            $PHPprotectV37 = basename($directory_tree[0]);

            $directory_tree = glob($directory_path . '*', GLOB_BRACE);

            if ($sort == "date" || $sort == "") {

                usort($directory_tree, create_function('$first_file,$second_file', 'return filemtime($second_file) - filemtime($first_file);'));
            }

            if ($sort == "date_") {

                usort($directory_tree, create_function('$first_file,$second_file', 'return filemtime($first_file) - filemtime($second_file);'));
            }

            if ($sort == "taille") {
                usort($directory_tree, create_function('$first_file,$second_file', 'return filesize_r($second_file) - filesize_r($first_file);'));
            }

            if ($sort == "taille_") {
                usort($directory_tree, create_function('$first_file,$second_file', 'return filesize_r($first_file) - filesize_r($second_file);'));
            }

            if ($sort == "type") {
                usort($directory_tree, create_function('$second_file,$first_file', '
	return	is_dir ($first_file)
		? (is_dir ($second_file) ? strnatcasecmp ($first_file, $second_file) : -1)
		: (is_dir ($second_file) ? 1 : (
			strcasecmp (pathinfo ($first_file, PATHINFO_EXTENSION), pathinfo ($second_file, PATHINFO_EXTENSION)) == 0
			? strnatcasecmp ($first_file, $second_file)
			: strcasecmp (pathinfo ($first_file, PATHINFO_EXTENSION), pathinfo ($second_file, PATHINFO_EXTENSION))
		))
	;
	'));
            }

            if ($sort == "type_") {
                usort($directory_tree, create_function('$first_file,$second_file', '
	return	is_dir ($first_file)
		? (is_dir ($second_file) ? strnatcasecmp ($first_file, $second_file) : -1)
		: (is_dir ($second_file) ? 1 : (
			strcasecmp (pathinfo ($first_file, PATHINFO_EXTENSION), pathinfo ($second_file, PATHINFO_EXTENSION)) == 0
			? strnatcasecmp ($first_file, $second_file)
			: strcasecmp (pathinfo ($first_file, PATHINFO_EXTENSION), pathinfo ($second_file, PATHINFO_EXTENSION))
		))
	;
	'));
            }

            if ($sort == "nom_") {

                $directory_tree = array_reverse(glob($directory_path . '*', GLOB_BRACE));
            }

            $i = 0;

            // Major change here for theme 2. We want to sort these by area but only if we are at the base.
            if (empty($directory) && ($_SESSION['superadmin'] == true || $_SESSION['admin'] == true)) {

                $query = $database_object->query("SELECT id, descriptor FROM security_descriptors");
                $descriptors = $query->fetchAll();

                if ($_SESSION['superadmin'] == true) {
                    $query = $database_object->query("SELECT comment, file_manager.id AS id, file_path, CASE WHEN security_descriptor_id IS NULL THEN 99 ELSE security_descriptor_id END AS security_descriptor_id, descriptor, DATE_FORMAT(deletion_date_time,'%d-%m-%Y') as deletion_date_time FROM file_manager LEFT JOIN file_security ON file_security.file_id = file_manager.id LEFT JOIN security_descriptors ON file_security.security_descriptor_id = security_descriptors.id ORDER BY security_descriptor_id ASC");
                    $rights_id_map = [];
                    foreach ($descriptor_list as $d) {
                        $rights_id_map[$d["descriptor"]] = $d["descriptor_id"];
                    }

                } else if ($_SESSION['admin'] == true) {
                    $area_ids = implode(",", $rights_id_map);
                    if (!empty($area_ids)) {
                        $query = $database_object->query("SELECT comment, file_manager.id AS id, file_path, CASE WHEN security_descriptor_id IS NULL THEN 99 ELSE security_descriptor_id END AS security_descriptor_id, descriptor, DATE_FORMAT(deletion_date_time,'%d-%m-%Y') as deletion_date_time FROM file_manager LEFT JOIN file_security ON file_security.file_id = file_manager.id LEFT JOIN security_descriptors ON file_security.security_descriptor_id = security_descriptors.id WHERE security_descriptor_id IN (" . $area_ids . ") ORDER BY security_descriptor_id DESC");
                    }
                }

                $files = $query->fetchAll();
                $group_separators = [];

                foreach ($rights_id_map as $id) {
                    $group_separators[$id] = false;
                }
                // generic one because why not ...
                $group_separators[99] = false;


                foreach ($files as $file) {
                    $file_name = $file['file_path'];
                    $current_file_id = $file['id'];
                    $current_descriptor = $file['descriptor'];
                    $current_expiry_date = $file['deletion_date_time'];
                    $current_comment = $file['comment'];

                    // So tired ... this sucks but it's gonna work ... kinda ... unless someone put a / in his filename ... i hate my life
                    if (substr_count(str_replace($files_url, "", $file_name), '/') > 1) {
                        continue;
                    }

                    if (!$group_separators[$file['security_descriptor_id']]) {
                        $group_separators[$file['security_descriptor_id']] = true;
                        if ($file['security_descriptor_id'] == 99) {
                            echo '<div>Hors Catégorie</div>';
                        } else {
                            echo '<div>' . array_search($file['security_descriptor_id'], $rights_id_map) . '</div>';
                        }
                    }

                    $i++;

                    $exploded_file = pathinfo($file_name);
                    $PHPprotectV39 = stripslashes(basename($file_name, "." . $exploded_file['extension']));
                    $generated_url = stripslashes(basename($file_name));
                    $PHPprotectV41 = htmlentities(strftime("%e %B %Y", filemtime($file_name)));
                    $PHPprotectV42 = formatSizeUnits(filesize_r($file_name));

                    // We also need to know all users that can access a file
                    $query = $database_object->query('SELECT DISTINCT name FROM users WHERE dir ="'."/" . $PHPprotectV39 . '" ');
                    $file_users = $query->fetchAll();
                    $imploded_file_users = "Aucun";
                    $first_time = true;
                    foreach ($file_users as $u) {
                        if ($first_time) {
                            $imploded_file_users = "";
                            $first_time = false;
                        }
                        $imploded_file_users .= $u['name'] . " , ";
                    }
                    $imploded_file_users = rtrim($imploded_file_users, ", ");

                    if (is_file($file_name) && $exploded_file['extension'] == "zip") {

                        $file_name = str_replace($files_url . "/", "", $file_name);

                        if ($PHPprotectV37 == $PHPprotectV39 . "." . $exploded_file['extension']) {
                            $PHPprotectV43 = 'newFile';
                        } else {
                            $PHPprotectV43 = '';
                        }

                        if ($_SESSION['uploader'] == true) {
                            $PHPprotectV44 = '
			  <div class="edit"></div>
			  <div data-href="admin/deletefile.php?file=' . $file_name . '" data-basename="?file=' . basename($file_name) . '" class="delete"></div>
    		';
                        } else {
                            $PHPprotectV44 = "";
                        }

                        echo '<div data-href="file.php?file=' . $file_name . '" class="noShake file zip  ' . $PHPprotectV43 . '">
			  <label>
			  <input type="text" maxlength="255" value="' . $PHPprotectV39 . '" class="editField" />
			  <span class="fileName">' . $PHPprotectV39 . '</span></label>
			  

			  <span class="fileInfos">';
                        if ($_SESSION['admin'] == true) {
                            echo '<br><span class="">Utilisateurs : ' . $imploded_file_users . '</span><br>';
                        }
                        echo '<span class="size">' . $PHPprotectV42 . '</span><br/><span class="date">' . $PHPprotectV41 . '</span></span>
			  
			  <div data-href="' . $file_name . '" class="download"></div>
			 
			 ' . $PHPprotectV44 . '
			  </div>';

                    } else if (is_file($file_name)) {

                        $file_name = str_replace($files_url . "/", "", $file_name);

                        if ($PHPprotectV37 == $PHPprotectV39 . "." . $exploded_file['extension']) {
                            $PHPprotectV43 = 'newFile';
                        } else {
                            $PHPprotectV43 = '';
                        }


                        if ($_SESSION['uploader'] == true) {
                            $PHPprotectV44 = '
    						  <div class="edit"></div>
			  <div data-href="admin/deletefile.php?file=' . $file_name . '" data-basename="?file=' . basename($file_name) . '" class="delete"></div>
    		';
                        } else {
                            $PHPprotectV44 = "";
                        }

                        echo '<div data-href="file.php?file=' . $file_name . '" class="noShake file ' . $PHPprotectV43 . '">
			  <label>
			  <input type="text" maxlength="255" value="' . $PHPprotectV39 . '" class="editField" />
			  
			  <span class="fileName">' . $PHPprotectV39 . '</span></label>
			  
			  <span class="fileInfos">';
                        if ($_SESSION['admin'] == true) {
                            echo '<br><span class="">Utilisateurs : ' . $imploded_file_users . '</span><br>';
                        }

                        echo '<span class="extension">' . $exploded_file['extension'] . '</span>

			  <span class="size">' . $PHPprotectV42 . '</span><br/><span class="date">' . $PHPprotectV41 . '</span>  </span>
			  <div data-href="' . $file_name . '" class="download"></div>
			  			 
			 ' . $PHPprotectV44 . '
			  </div>';

                    } else if (is_dir($file_name)) {

                        $file_name = str_replace($files_url . "/", "", $file_name);

                        $PHPprotectV45 = 0;
                        foreach (glob($files_url . "/" . $file_name . "/" . '*', GLOB_BRACE) as $PHPprotectV46) {
                            $PHPprotectV45++;
                        }

                        if ($PHPprotectV45 == 0) {
                            $PHPprotectV45 = "Aucun";
                        }

                        if ($_SESSION['uploader'] == true) {
                            $PHPprotectV44 = '
    			<div class="edit"></div>
			  <div data-href="admin/deletefile.php?file=' . $file_name . '" data-basename="?dir=' . basename($file_name) . '" class="delete"></div>
    		';
                        } else {
                            $PHPprotectV44 = "";
                        }

                        if ($PHPprotectV37 == $PHPprotectV39) {
                            $PHPprotectV47 = 'newFolder';
                        } else {
                            $PHPprotectV47 = '';
                        }

                        echo '<div data-href="?dir=' . $file_name . $PHPprotectV33 . '" class="noShake file folder ' . $PHPprotectV47 . '">
			  <label>
			  <input type="text" maxlength="255" value="' . $PHPprotectV39 . '" class="editField" />
			  <span class="fileName">' . $PHPprotectV39 . '</span></label>
			  <label>Groupe: 
			  <span class="descriptor" style="display: none;">
			        <select id="descriptorId" name="descriptorId" class="descriptorId">
                        <option value="">Aucun</option>';

                        foreach ($descriptors as $d) {
                            $selected = $current_descriptor == $d["descriptor"] ? "selected" : "";
                            echo "<option value='" . $d["id"] . "' ".$selected .">" .$d["descriptor"] . "</option>";
                        }

                    echo '</select>
              </span>
              <span class="descriptor_name">'.$current_descriptor.'</span>
              <input type="hidden" id="file_idz" value="'.$current_file_id.'" />
              </label>
              <label style="display: none;" class="email">
                Text explicatif pour email:<br/>
                <span >
                    <textarea rows="4" cols="80" class="email_text">'.$current_comment.'</textarea>
                </span>
                <input type="hidden" id="file_idz" value="'.$current_file_id.'" />
              </label>
			  <span class="fileInfos">';
                        if ($_SESSION['admin'] == true) {
                            echo '<br><span class="">Utilisateurs : ' . $imploded_file_users . '</span><br>';
                        }
                        echo '<span class="size">' . $PHPprotectV42 . '</span> - <span class="fileNumber"><span>' . $PHPprotectV45 . '</span> élément' . plural($PHPprotectV45) . '</span><br/><span class="date">' . $PHPprotectV41 . '</span></span>
			  ' . $PHPprotectV44 . '

			  </div>
			  ';

                    }
                }

            } else {
                // This whole section sucks so much. TODO : USE FIRE
                foreach ($directory_tree as $file_name) {

                    $i++;

                    $exploded_file = pathinfo($file_name);
                    $PHPprotectV39 = stripslashes(basename($file_name, "." . $exploded_file['extension']));
                    $generated_url = stripslashes(basename($file_name));
                    $PHPprotectV41 = htmlentities(strftime("%e %B %Y", filemtime($file_name)));
                    $PHPprotectV42 = formatSizeUnits(filesize_r($file_name));

                    $query = $database_object->query("SELECT comment, file_manager.id AS id, file_path, CASE WHEN security_descriptor_id IS NULL THEN 99 ELSE security_descriptor_id END AS security_descriptor_id, descriptor, DATE_FORMAT(deletion_date_time,'%d-%m-%Y') as deletion_date_time FROM file_manager LEFT JOIN file_security ON file_security.file_id = file_manager.id LEFT JOIN security_descriptors ON file_security.security_descriptor_id = security_descriptors.id WHERE file_path = '".$file_name."'");
                    $file_from_db = $query->fetch();
                    $current_expiry_date = $file_from_db['deletion_date_time'];
                    $current_file_id = $file_from_db['id'];

                    if (is_file($file_name) && $exploded_file['extension'] == "zip") {

                        $file_name = str_replace($files_url . "/", "", $file_name);

                        if ($PHPprotectV37 == $PHPprotectV39 . "." . $exploded_file['extension']) {
                            $PHPprotectV43 = 'newFile';
                        } else {
                            $PHPprotectV43 = '';
                        }

                        if ($_SESSION['uploader'] == true) {
                            $PHPprotectV44 = '
			  <div class="edit"></div>
			  <div data-href="admin/deletefile.php?file=' . $file_name . '" data-basename="?file=' . basename($file_name) . '" class="delete"></div>
    		';
                        } else {
                            $PHPprotectV44 = "";
                        }

                        echo '<div data-href="file.php?file=' . $file_name . '" class="noShake file zip  ' . $PHPprotectV43 . '">
			  <label>
			  <input type="text" maxlength="255" value="' . $PHPprotectV39 . '" class="editField" />
			  <span class="fileName">' . $PHPprotectV39 . '</span></label>			  
			  <span class="fileInfos">
			  <span class="size">' . $PHPprotectV42 . '</span><br/><span class="date">' . $PHPprotectV41 . '</span></span>
			  
			  <div data-href="' . $file_name . '" class="download"></div>
			 
			 ' . $PHPprotectV44 . '
			  </div>';

                    } else if (is_file($file_name)) {

                        $file_name = str_replace($files_url . "/", "", $file_name);

                        if ($PHPprotectV37 == $PHPprotectV39 . "." . $exploded_file['extension']) {
                            $PHPprotectV43 = 'newFile';
                        } else {
                            $PHPprotectV43 = '';
                        }


                        if ($_SESSION['uploader'] == true) {
                            $PHPprotectV44 = '
    						  <div class="edit"></div>
			  <div data-href="admin/deletefile.php?file=' . $file_name . '" data-basename="?file=' . basename($file_name) . '" class="delete"></div>
    		';
                        } else {
                            $PHPprotectV44 = "";
                        }

                        echo '<div data-href="file.php?file=' . $file_name . '" class="noShake file ' . $PHPprotectV43 . '">
			  <label>
			  <input type="text" maxlength="255" value="' . $PHPprotectV39 . '" class="editField" />
			  
			  <span class="fileName">' . $PHPprotectV39 . '</span></label>
			  <label>Date d\'expiration:
			    <span class="expiry" style="display: none;">
                    <input name="deletion_date" type="date" class="expiry_date" value="'.$current_expiry_date.'"/>
                </span>
                <span class="expiry_date_label">'.$current_expiry_date.'</span>
                <input type="hidden" id="file_idz" value="'.$current_file_id.'" />
              </label>
			  <span class="fileInfos"><span class="extension">' . $exploded_file['extension'] . '</span>
			  <span class="size">' . $PHPprotectV42 . '</span><br/><span class="date">' . $PHPprotectV41 . '</span>  </span>
			  <div data-href="' . $file_name . '" class="download"></div>
			  			 
			 ' . $PHPprotectV44 . '
			  </div>';

                    } else if (is_dir($file_name)) {

                        $file_name = str_replace($files_url . "/", "", $file_name);

                        $PHPprotectV45 = 0;
                        foreach (glob($files_url . "/" . $file_name . "/" . '*', GLOB_BRACE) as $PHPprotectV46) {
                            $PHPprotectV45++;
                        }

                        if ($PHPprotectV45 == 0) {
                            $PHPprotectV45 = "Aucun";
                        }

                        if ($_SESSION['uploader'] == true) {
                            $PHPprotectV44 = '
    			<div class="edit"></div>
			  <div data-href="admin/deletefile.php?file=' . $file_name . '" data-basename="?dir=' . basename($file_name) . '" class="delete"></div>
    		';
                        } else {
                            $PHPprotectV44 = "";
                        }

                        if ($PHPprotectV37 == $PHPprotectV39) {
                            $PHPprotectV47 = 'newFolder';
                        } else {
                            $PHPprotectV47 = '';
                        }

                        echo '<div data-href="?dir=' . $file_name . $PHPprotectV33 . '" class="noShake file folder ' . $PHPprotectV47 . '">
			  <label>
			  <input type="text" maxlength="255" value="' . $PHPprotectV39 . '" class="editField" />
			  <span class="fileName">' . $PHPprotectV39 . '</span></label>
			  
			  <span class="fileInfos"><span class="size">' . $PHPprotectV42 . '</span> - <span class="fileNumber"><span>' . $PHPprotectV45 . '</span> élément' . plural($PHPprotectV45) . '</span><br/><span class="date">' . $PHPprotectV41 . '</span></span>
			  ' . $PHPprotectV44 . '
			  </div>';

                    }

                }
            }


            if (empty($i)) {
                ?>
                <div class="pageForm" style="font-weight:normal; font-size: 12px;"><br/><br/>ⓘ&nbsp;&nbsp; Ce dossier
                    contient actuellement aucun document.
                </div>
            <?php }

        } else {
            include("includes/404.php");
        }


        ?>
    </div><!-- End of files -->
    <?php

}

if ($_SESSION['admin'] == true && $is_accounts == true) {

    ?>
    <div class="files unselectable"><?php
    include("includes/accounts.php");
    ?></div><?php
}

if ($_SESSION['connected'] == true && $is_account_setting == true && $_SESSION['admin'] == true) {

    ?>
    <div class="files"><?php
    include("includes/account-settings.php");

    ?></div><?php
}

if ($_SESSION['connected'] == true && $is_admin_settings == true && $_SESSION['superadmin'] == true) {

    ?>
    <div class="files"><?php
    include("includes/admin_settings.php");

    ?></div><?php
}

if ($_SESSION['connected'] == true && $is_file_admin == true && $_SESSION['admin'] == true) {

    ?>
    <div class="files"><?php
    include("includes/file_administration.php");

    ?></div><?php
}

if ($_SESSION['connected'] == false) {

    ?>
    <div class="files"><?php
    include("includes/login.php");

    ?></div><?php
}


?>


<iframe width="425" height="350" id="uploadIframe" name="uploadIframe" frameborder="0" scrolling="no" marginheight="0"
        marginwidth="0" src="scripts/blank.html"></iframe>


</body>
</html>
