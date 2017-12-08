<form id="loginForm" class="pageForm" action="includes/login-post.php" target="uploadIframe" method="post">

    <h1>Bienvenue,<br/> <span>veuillez vous identifier pour poursuivre.</span><br/><br/></h1>

    <?php if ($_SESSION['forgotPassword'] == 1) {
        ?>
        <div class="win">
            <p><strong>La demande de réinitialisation du mot de passe a été effectuée avec succès.</strong><br/><br/> Un
                courriel vous a été envoyé avec le nouveau code à utiliser pour vous connecter.</p>
        </div>
        <?php $_SESSION['forgotPassword'] = "";
    }
    ?>

    <label>
        Courriel:<br/>
        <input type="text" id="email" name="email" placeholder="Courriel..."/>
    </label>
    <br/><br/>
    <label>
        Mot de passe:<br/>
        <input type="password" id="password" name="password" placeholder="Mot de passe..."/>
    </label>
    <br/><br/>


    <input type="reset" class="btn" value="Annuler"/>
    <input type="submit" class="btn" value="Connexion"/>

</form>
