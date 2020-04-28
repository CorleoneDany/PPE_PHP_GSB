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
require($repInclude . "_entete.inc.php");
require($repInclude . "_sommaire.inc.php");

// acquisition des données entrées, ici l'id de l'utilisateur, le mois et l'étape du traitement
$idUtilisateur = lireDonnee("lstUtilisateur", "");
$idMois = lireDonnee("lstMois", "");
$etape = lireDonnee("etape", "");

// structure de décision sur les différentes étapes du cas d'utilisation
if ($etape == "mettreEnPaiementFicheFrais") {
    modifierEtatFicheFrais($idConnexion, $idMois, $idUtilisateur, 'MP');
}
?>

<!-- Division principale -->
<div id="contenu">
    <?php
    $lgUtilisateur = obtenirDetailUtilisateur($idConnexion, $idUtilisateur);
    $noMois = intval(substr($idMois, 4, 2));
    $annee = intval(substr($idMois, 0, 4));
    // Gestion des messages d'informations
    if ($etape == "mettreEnPaiementFicheFrais") {
    ?>
        <p class="info">La fiche de frais de <?php echo $lgUtilisateur['nom'] . ' ' . $lgUtilisateur['prenom']; ?> de <?php echo obtenirLibelleMois($noMois) . ' ' . $annee; ?> a bien été mise en paiement</p>
    <?php
    }
    ?>
    <h2>Suivi des paiement des fiches de frais</h2>
    <?php
    $req = "SELECT Utilisateur.id, nom, prenom, ficheFrais.mois, SUM(lignefraisforfait.quantite * fraisForfait.montant) AS montantForfait,";
    $req .= " (ficheFrais.montantValide - SUM(lignefraisforfait.quantite * fraisForfait.montant)) AS montantHorsForfait, ficheFrais.montantValide AS totalFicheFrais";
    $req .= " FROM Utilisateur INNER JOIN ficheFrais ON Utilisateur.id=ficheFrais.idUtilisateur";
    $req .= "                  INNER JOIN lignefraisforfait ON (ficheFrais.idUtilisateur = lignefraisforfait.idUtilisateur  AND ficheFrais.mois = lignefraisforfait.mois)";
    $req .= "                  INNER JOIN fraisForfait ON lignefraisforfait.idFraisForfait = fraisForfait.id";
    $req .= " WHERE ficheFrais.idEtat = 'VA'";
    $req .= " GROUP BY nom, prenom, ficheFrais.mois";
    $idJeuFicheAPayer = $idConnexion->prepare($req);
    $idJeuFicheAPayer->execute([]);
    ?>
    <form id="formChoixFichesAPayer" method="post" action="">
        <p>
            <input type="hidden" id="etape" name="etape" value="mettreEnPaiementFicheFrais" />
            <input type="hidden" id="lstUtilisateur" name="lstUtilisateur" value="" />
            <input type="hidden" id="lstMois" name="lstMois" value="" />
        </p>
        <div style="clear:left;">
            <h2>Fiches de frais validées</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th rowspan="2" style="vertical-align:middle;">Visiteur&nbsp;médical</th>
                        <th rowspan="2" style="vertical-align:middle;">Mois</th>
                        <th colspan="3">Fiches de frais</th>
                        <th rowspan="2" style="vertical-align:middle;">Actions</th>
                    </tr>
                    <tr>
                        <th>Forfait</th>
                        <th>Hors forfait</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <?php
                while ($lgFicheAPayer = $idJeuFicheAPayer->fetch(PDO::FETCH_ASSOC)) {
                    $mois = $lgFicheAPayer['mois'];
                    $noMois = intval(substr($mois, 4, 2));
                    $annee = intval(substr($mois, 0, 4));
                ?>
                    <tr>
                        <td><?php echo $lgFicheAPayer['nom'] . ' ' . $lgFicheAPayer['prenom']; ?></td>
                        <td><?php echo obtenirLibelleMois($noMois) . ' ' . $annee; ?></td>
                        <td><?php echo $lgFicheAPayer['montantForfait']; ?></td>
                        <td><?php echo $lgFicheAPayer['montantHorsForfait']; ?></td>
                        <td><?php echo $lgFicheAPayer['totalFicheFrais']; ?></td>
                        <td class="td-link">
                            <div id="actionsFicheFrais" class="actions">
                                <a class="actionsCritiques" onclick="mettreEnPaiementFicheFrais('<?php echo $lgFicheAPayer['id']; ?>',<?php echo $lgFicheAPayer['mois']; ?>);" title="Mettre en paiement la fiche de frais">&nbsp;<img src="images/mettreEnPaiementIcon.svg" class="icon" alt="icone Mettre en paiment" />&nbsp;Mettre en paiement&nbsp;</a>
                            </div>
                        </td>
                    </tr>

                <?php
                }
                ?>
            </table>
        </div>
    </form>
</div>
<script type="text/javascript">
    function mettreEnPaiementFicheFrais(idUtilisateur, idMois) {
        document.getElementById('lstUtilisateur').value = idUtilisateur;
        document.getElementById('lstMois').value = idMois;
        document.getElementById('formChoixFichesAPayer').submit();
    }
</script>
<?php
require($repInclude . "_pied.inc.html");
require($repInclude . "_fin.inc.php");
?>