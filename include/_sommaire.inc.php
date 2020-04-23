<?php

/** 
 * Contient la division pour le sommaire, sujet à des variations suivant la 
 * connexion ou non d'un utilisateur, et dans l'avenir, suivant le type de cet utilisateur 
 * @todo  RAS
 */

?>
<!-- Division pour le sommaire -->
<?php
if (estUtilisateurConnecte()) {
  $idUser = obtenirIdUserConnecte();
  $lgUser = obtenirDetailUtilisateur($idConnexion, $idUser);
  $nom = $lgUser['nom'];
  $prenom = $lgUser['prenom'];
  $libelleType = $lgUser['TypeUser'];
} else {
  $idUser      = $lgUser = $prenom = null;
  $nom         = 'Menu';
  $libelleType = 'Invité';
}

if (estUtilisateurConnecte()) {

  // affichage des éventuelles erreurs déjà détectées
  if (nbErreurs($tabErreurs) > 0) {
    echo toStringErreurs($tabErreurs);
  }
}
?>
<?php if (estUtilisateurConnecte()) { ?>

  <nav id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <h3><?= "$nom $prenom<br><span>$libelleType</span>"; ?></h3>
    </div>

    <!-- Sidebar Links -->
    <ul class="list-unstyled components">
      <!-- Link with dropdown items -->
      <ul class="list-unstyled" id="homeSubmenu">
        <li>
          <a href="cAccueil.php" title="Page d'accueil">Accueil</a>
        </li>

        <?php
        if ($libelleType == "Visiteur médical") {
        ?>
          <li>
            <a href="cSaisieFicheFrais.php" title="Saisie fiche de frais du mois courant">Saisie fiche de frais</a>
          </li>
          <li>
            <a href="cConsultFichesFrais.php" title="Consultation de mes fiches de frais">Mes fiches de frais</a>
          </li>
        <?php
        }
        if ($libelleType == "Comptable") {
        ?>
          <li>
            <a href="cValidFichesFrais.php" title="Validation des fiches de frais du mois précédent">Validation des fiches de frais</a>
          </li>
          <li>
            <a href="cMisePaiementFichesFrais.php" title="Suivre le paiment des fiches de frais du mois précédent">Suivre le paiement des fiches de frais</a>
          </li>
        <?php
        }
        ?>

        <li>
          <a href="cSeDeconnecter.php" title="Se déconnecter">Se déconnecter</a>
        </li>
      </ul>
  </nav>



  <button type="button" id="sidebarCollapse" class="navbar-btn">
    <span></span>
    <span></span>
    <span></span>
  </button>

<?php } ?>

<div id="content" class="container">