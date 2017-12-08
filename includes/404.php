<form id="404Form" class="pageForm" action="includes/login-post.php" target="uploadIframe" method="post">

    <h1>Le dossier que vous tentez d'accèder est introuvable,
        <span>il a peut être été renommé, déplacé ou supprimé.</span><br/><br/></h1>

    <?php

    /*
    <label>
Recherche:<br/>
    <input type="text" id="search" name="search" placeholder="Rechercher..." />
</label>
<br/>

<input type="submit" class="btn" value="Rechercher" />
*/


    ?>

    <div class="clear" style="clear:both;">
        <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']);
        ?>">« Retour à l'accueil</a>
    </div>

</form>
