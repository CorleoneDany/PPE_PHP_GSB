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
    
    // structure de décision sur les différentes étapes du cas d'utilisation
    if ($etape == "choixVisiteur") {
        // Selection d'un visiteur par l'utilisateur
    } elseif ($etape == "choixMois") {
        // Selection d'un mois par l'utilisateur 
    } elseif ($etape == "actualiserFraisForfait") {
        // Actualisation des informations des frais forfaitisés par l'utilisateur
        // Validation des éléments forfaitisés par l'utilisateur         
        // vérification des quantités des éléments forfaitisés
        $ok = verifierEntiersPositifs($tabQteEltsForfait);
        if (!$ok) {
            ajouterErreur($tabErreurs, "Chaque quantité doit être renseignée et numérique positive.");
        } else { 
            // mise à jour des quantités des éléments forfaitisés
            modifierEltsForfait($idConnexion, $moisChoisi, $visiteurChoisi, $tabQteEltsForfait);
        }
    } elseif ($etape == "actualiserFraisHorsForfait") {
        // Actualisation des infromations des frais hors forfait
        // Validation des éléments non forfaitisés par l'utilisateur      
        // Une suppression est donc considérée comme une actualisation puisque c'est 
        // le libellé qui est mis à jour   
    foreach ($tabEltsHorsForfait as $cle => $val) {
        switch ($cle) {
            case 'libelle':
                $libelleFraisHorsForfait = $val;
                break;
            case 'date':
                $dateFraisHorsForfait = $val;
                break;
            case 'montant':
                $montantFraisHorsForfait = $val;
                break;
        }
    }
    // vérification de la validité des données d'une ligne de frais hors forfait
    verifierLigneFraisHF($dateFraisHorsForfait, $libelleFraisHorsForfait, $montantFraisHorsForfait, $tabErreurs);
    if (nbErreurs($tabErreurs) == 0) {
        // mise à jour des quantités des éléments non forfaitisés
        modifierEltsHorsForfait($idConnexion, $tabEltsHorsForfait);
    }
    } elseif ($etape == "reporterLigneFraisHF") {
        // L'utilisateur demande le report d'une ligne hors forfait dont les justificatifs ne sont pas arrivés à temps
        reporterLigneHorsForfait($idConnexion, $tabEltsHorsForfait['id']);
    } elseif ($etape == "actualiserNbJustificatifs") {
        // Actualisation du nombre de justificatifs par l'utilisateur
        $ok = estEntierPositif($nbJustificatifs);
        if (!$ok) {
            ajouterErreur($tabErreurs, "Le nombre de justificatifs doit être renseigné et numérique positif.");
        } else {
            // mise à jour du nombre de justificatifs
            modifierNbJustificatifsFicheFrais($idConnexion, $moisChoisi, $visiteurChoisi, $nbJustificatifs);
        }
    } elseif ($etape == "validerFiche") {
        // Validation de la fiche de frais par l'utilisateur
        modifierEtatFicheFrais($idConnexion, $moisChoisi, $visiteurChoisi, 'VA');
    } 
?>    
