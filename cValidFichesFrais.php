<?php

    /*
     * Script de contrôle et d'affichage du cas d'utilisation "Valider fiche de frais"
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

    // affectation du mois précédent pour la validation des fiches de frais
    $mois = sprintf("%04d%02d", date("Y"), date("m"));
    // Cloture des fiches de frais antérieur au mois courant et au besoin, création des fiches pour le mois courant
    cloturerFichesFrais($idConnexion, $mois);

    // Récupération des données entrées, id visiteur, mois et l'étape du traitement
    $visiteurChoisi = lireDonnee("lstVisiteur", "");
    $moisChoisi = lireDonnee("lstMois", "");
    $etape = lireDonnee("etape", "");
    // acquisition des quantités des éléments forfaitisés 
    $tabQteEltsForfait = lireDonneePost("txtEltsForfait", "");
    // acquisition des informations des éléments hors forfait
    $tabEltsHorsForfait = lireDonneePost("txtEltsHorsForfait", "");
    $nbJustificatifs = lireDonneePost("nbJustificatifs", "");
