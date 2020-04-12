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

<!-- Division principale -->
<div id="contenu">
    <?php
    $lgVisiteur = obtenirDetailUtilisateur($idConnexion, $idVisiteur);
    $noMois = intval(substr($idMois, 4, 2));
    $annee = intval(substr($idMois, 0, 4));
    // Gestion des messages d'informations
    if ($etape == "mettreEnPaiementFicheFrais") {
        ?>
        <p class="info">La fiche de frais de <?php echo $lgVisiteur['nom'] . ' ' . $lgVisiteur['prenom']; ?> de <?php echo obtenirLibelleMois($noMois) . ' ' . $annee; ?> a bien été mise en paiement</p>        
        <?php
    }
    ?>
 <h1>Suivi des paiement des fiches de frais</h1>
    <?php
        $req = "SELECT visiteur.id, nom, prenom, ficheFrais.mois, SUM(lignefraisforfait.quantite * fraisForfait.montant) AS montantForfait,";
        $req .= " (ficheFrais.montantValide - SUM(lignefraisforfait.quantite * fraisForfait.montant)) AS montantHorsForfait, ficheFrais.montantValide AS totalFicheFrais";
        $req .= " FROM visiteur INNER JOIN ficheFrais ON visiteur.id=ficheFrais.idVisiteur";
        $req .= "                  INNER JOIN lignefraisforfait ON (ficheFrais.idVisiteur = lignefraisforfait.idVisiteur  AND ficheFrais.mois = lignefraisforfait.mois)";
        $req .= "                  INNER JOIN fraisForfait ON lignefraisforfait.idFraisForfait = fraisForfait.id";
        $req .= " WHERE ficheFrais.idEtat = 'VA'";
        $req .= " GROUP BY nom, prenom, ficheFrais.mois";
        $idJeuFicheAPayer = mysql_query($req, $idConnexion);
    ?>
        