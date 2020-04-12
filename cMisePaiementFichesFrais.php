<?php

/**
 * Script de contrôle et d'affichage du cas d'utilisation "Suivre le paiement fiche de frais"
 * @package default
 * @todo  RAS
 */


$repInclude = './include/';
require($repInclude . "_init.inc.php");

// page inaccessible si utilisateur non connecté
if (!estUtilisateurConnecte()) {
    header("Location: cSeConnecter.php");
}
require($repInclude . "_entete.inc.html");
require($repInclude . "_sommaire.inc.php");

// acquisition des données entrées, ici l'id de visiteur, le mois et l'étape du traitement
$idVisiteur = lireDonnee("lstVisiteur", "");
$idMois = lireDonnee("lstMois", "");
$etape = lireDonnee("etape", "");

// structure de décision sur les différentes étapes du cas d'utilisation
if ($etape == "mettreEnPaiementFicheFrais") {
    modifierEtatFicheFrais($idConnexion, $idMois, $idVisiteur, 'MP');
}
?>