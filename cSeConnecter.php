<?php

/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Se connecter"
 * @package default
 * @todo  RAS
 */
$repInclude = './include/';
require($repInclude . "_init.inc.php");

// est-on au 1er appel du programme ou non ?
$etape = (count($_POST) != 0) ? 'validerConnexion' : 'demanderConnexion';

if ($etape == 'validerConnexion') { // un client demande a s'authentifier
  // acquisition des donnees envoyees, ici login et mot de passe
  $login = lireDonneePost("txtLogin");
  $mdp = lireDonneePost("txtMdp");
  $lgUser = verifierInfosConnexion($idConnexion, $login, $mdp);
  // si l'id utilisateur a ete trouve, donc informations fournies sous forme de tableau
  if (is_array($lgUser)) {
    affecterInfosConnecte($lgUser["id"], $lgUser["login"]);
  } else {
    ajouterErreur($tabErreurs, "Pseudo et/ou mot de passe incorrects");
  }
}
if ($etape == "validerConnexion" && nbErreurs($tabErreurs) == 0) {
  header("Location:cAccueil.php");
}

require($repInclude . "_entete.inc.php");
require($repInclude . "_sommaire.inc.php");

?>
<!-- Division pour le contenu principal -->
<div id="contenu">
  <h2>Identification utilisateur</h2>
  <form id="frmConnexion" action="" method="post">
    <input type="hidden" name="etape" id="etape" value="validerConnexion" />
    <div class="form-group row">
      <p class="col-lg-2 col-md-3">
        <label for="txtLogin" accesskey="n">Login : </label>
      </p>
      <p class="col-lg-10 col-md-9">
        <input type="text" id="txtLogin" name="txtLogin" maxlength="20" size="15" value="" title="Entrez votre login" class="form-control" />
      </p>
    </div>
    <div class="form-group row">
      <p class="col-lg-2 col-md-3">
        <label for="txtMdp" accesskey="m">Mot de passe : </label>
      </p>
      <p class="col-lg-10 col-md-9">
        <input type="password" id="txtMdp" name="txtMdp" maxlength="8" size="15" value="" title="Entrez votre mot de passe" class="form-control" />
      </p>
    </div>
    <p class="btn-container">
      <button type="submit" class="btn btn-submit">Valider</button>
      <button type="reset" class="btn btn-reset">Effacer</button>
    </p>
    <?php
    if ($etape == "validerConnexion") {
      if (nbErreurs($tabErreurs) > 0) {
        echo '<div class="row">';
        echo '<div class="col-lg-2 col-md-3"></div>';
        echo toStringErreurs($tabErreurs);
        echo '</div>';
      }
    }
    ?>
  </form>
</div>
<?php
require($repInclude . "_pied.inc.html");
require($repInclude . "_fin.inc.php");
?>