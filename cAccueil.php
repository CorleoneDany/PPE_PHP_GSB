<?php

/** 
 * Page d'accueil de l'application web AppliFrais
 * @package default
 * @todo  RAS
 */
$repInclude = './include/';
require($repInclude . "_init.inc.php");

// page inaccessible si utilisateur non connecte
if (!estUtilisateurConnecte()) {
  header("Location: cSeConnecter.php");
}
require($repInclude . "_entete.inc.php");
require($repInclude . "_sommaire.inc.php");
?>
<!-- Division principale -->
<div id="contenu">
  <div class="row">
    <div class="col-lg-3 col-md-5 col-sm-7 col-10 mx-auto my-5">
    <img src="./images/logo.svg" alt="Laboratoire Galaxy-Swiss Bourdin" title="Laboratoire Galaxy-Swiss Bourdin" class="img-fluid">
    </div>
  </div>
  <h2 class="text-center">Bienvenue sur votre intranet GSB</h2>
  <p class="text-center">Veuillez choisir une fonctionnalité dans le menu.</p>
</div>
<?php
require($repInclude . "_pied.inc.html");
require($repInclude . "_fin.inc.php");
?>