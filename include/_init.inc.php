<?php
/** 
 * Initialise les ressources necessaires au fonctionnement de l'application
 * @package default
 * @todo  RAS
 */
  require("_bdGestionDonnees.lib.php");
  require("_gestionSession.lib.php");
  require("_utilitairesEtGestionErreurs.lib.php");
  // demarrage ou reprise de la session
  initSession();
  // initialement, aucune erreur ...
  $tabErreurs = array();
    
  // Demande-t-on une deconnexion ?
  $demandeDeconnexion = lireDonneeUrl("cmdDeconnecter");
  if ( $demandeDeconnexion == "on") {
      deconnecterUtilisateur();
      header("Location: cAccueil.php");
  }
    
  // etablissement d'une connexion avec le serveur de donnees 
  // puis selection de la BD qui contient les donnees des utilisateurs et de leurs frais
  $idConnexion=connecterServeurBD();
  
?>