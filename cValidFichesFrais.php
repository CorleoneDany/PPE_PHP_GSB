<?php
/**
 * Script de contrôle et d'affichage du cas d'utilisation "Valider fiche de frais"
 * @package default
 * @todo  RAS
 */

$repInclude = './include/';
require($repInclude . "_init.inc.php");

$_SESSION["idUser"] = 'a131';

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

// Récupération des données entrées, id utilisateur, mois et l'étape du traitement
$utilisateurChoisi = lireDonnee("lstUtilisateur", "");
$moisChoisi = lireDonnee("lstMois", "");
$etape = lireDonnee("etape", "");
// acquisition des quantités des éléments forfaitisés 
$tabQteEltsForfait = lireDonneePost("txtEltsForfait", "");
// acquisition des informations des éléments hors forfait
$tabEltsHorsForfait = lireDonneePost("txtEltsHorsForfait", "");
$nbJustificatifs = lireDonneePost("nbJustificatifs", "");

// structure de décision sur les différentes étapes du cas d'utilisation
if ($etape == "choixUtilisateur") {
    // Selection d'un utilisateur par l'utilisateur
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
        modifierEltsForfait($idConnexion, $moisChoisi, $utilisateurChoisi, $tabQteEltsForfait);
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
        modifierNbJustificatifsFicheFrais($idConnexion, $moisChoisi, $utilisateurChoisi, $nbJustificatifs);
    }
} elseif ($etape == "validerFiche") {
    // Validation de la fiche de frais par l'utilisateur
    modifierEtatFicheFrais($idConnexion, $moisChoisi, $utilisateurChoisi, 'VA');
}
?>
<!-- Affichage utilisateur validation fiche de frais -->

<div id="contenu">
    <h1>Validation des frais par l'utilisateur </h1>
    <?php
    // Gestion des messages d'informations
    if ($etape == "actualiserFraisForfait") {
        if (nbErreurs($tabErreurs) > 0) {
            echo toStringErreurs($tabErreurs);
        } else {
    ?>
            <p class="info">L'actualisation des quantités au forfait a bien été enregistrée</p>
        <?php
        }
    }
    if ($etape == "actualiserFraisHorsForfait") {
        if (nbErreurs($tabErreurs) > 0) {
            echo toStringErreurs($tabErreurs);
        } else {
        ?>
            <p class="info">L'actualisation de la ligne de frais hors forfait a bien été enregistrée</p>
        <?php
        }
    }
    if ($etape == "reporterLigneFraisHF") {
        ?>
        <p class="info">La ligne de frais hors forfait a bien été reportée</p>
        <?php
    }
    if ($etape == "actualiserNbJustificatifs") {
        if (nbErreurs($tabErreurs) > 0) {
            echo toStringErreurs($tabErreurs);
        } else {
        ?>
            <p class="info">L'actualisation du nombre de justificatifs a bien été enregistré</p>
        <?php
        }
    }
    if ($etape == "validerFiche") {
        $lgUtilisateur = obtenirDetailUtilisateur($idConnexion, $utilisateurChoisi);
        ?>
        <p class="info">La fiche de frais du visiteur <?php echo $lgUtilisateur['prenom'] . " " . $lgUtilisateur['nom']; ?> pour <?php echo obtenirLibelleMois(intval(substr($moisChoisi, 4, 2))) . " " . intval(substr($moisChoisi, 0, 4)); ?> a bien été enregistrée</p>
    <?php
        // On réinitialise le mois choisi pour forcer la disparition du bas de page, la réactualisation des mois et le choix d'un nouveau mois
        $moisChoisi = "";
    }
    ?>
    <form id="formChoixUtilisateur" method="post" action="">
        <p>
            <input type="hidden" name="etape" value="choixUtilisateur" />
            <label class="titre">Choisir l'utilisateur :</label>
            <select name="lstUtilisateur" id="idLstUtilisateur" class="zone" onchange="changerVisiteur(this.options[this.selectedIndex].value);">
                <?php
                // Si aucun utilisateur n'a encore été choisi, on place en premier une invitation au choix
                if ($utilisateurChoisi == "") {
                ?>
                    <option value="-1">=== Choisir un visiteur médical ===</option>
                <?php
                }
                // On propose tous les utilisateurs qui sont des visteurs médicaux
                $req = obtenirReqListeVisiteurs();
                $idJeuUtilisateur = mysqli_query($idConnexion, $req);
                while ($lgUtilisateur = mysqli_fetch_array($idJeuUtilisateur)) {
                ?>
                    <option value="<?php echo $lgUtilisateur['id']; ?>" <?php if ($utilisateurChoisi == $lgUtilisateur['id']) { ?> selected="selected" <?php } ?>><?php echo $lgUtilisateur['nom'] . " " . $lgUtilisateur['prenom']; ?></option>
                <?php
                }
                mysqli_free_result($idJeuUtilisateur);
                ?>
            </select>
        </p>
    </form>
</div>

<?php
// Si aucun utilisateur n'a encore été choisi on n'affiche pas le form de choix de mois
if ($utilisateurChoisi != "") {
?>
    <form id="formChoixMois" method="post" action="">
        <p>
            <input type="hidden" name="etape" value="choixMois" />
            <input type="hidden" name="lstUtilisateur" value="<?php echo $utilisateurChoisi; ?>" />
            <?php
            // On propose tous les mois pour lesquels l'utilisateur dispose d'une fiche de frais cloturée
            $req = obtenirReqMoisFicheFrais($utilisateurChoisi, 'CL');
            $idJeuMois = mysqli_query($idConnexion, $req);
            $lgMois = mysqli_fetch_assoc($idJeuMois);
            // 4-a Aucune fiche de frais n'existe le système affiche "Pas de fiche de frais pour cet utilisateur ce mois". Retour au 2
            if (empty($lgMois)) {
                ajouterErreur($tabErreurs, "Pas de fiche de frais à valider pour cet utilisateur, veuillez choisir un autre utilisateur");
                echo toStringErreurs($tabErreurs);
            } else {
            ?>
                <label class="titre">Mois :</label>
                <select name="lstMois" id="idDateValid" class="zone" onchange="this.form.submit();">
                    <?php
                    // Si aucun mois n'a encore été choisi, on place en premier une invitation au choix
                    if ($moisChoisi == "") {
                    ?>
                        <option value="-1">=== Choisir un mois ===</option>
                    <?php
                    }
                    while (is_array($lgMois)) {
                        $mois = $lgMois["mois"];
                        $noMois = intval(substr($mois, 4, 2));
                        $annee = intval(substr($mois, 0, 4));
                    ?>
                        <option value="<?php echo $mois; ?>" <?php if ($moisChoisi == $mois) { ?> selected="selected" <?php } ?>><?php echo obtenirLibelleMois($noMois) . ' ' . $annee; ?></option>
                <?php
                        $lgMois = mysqli_fetch_assoc($idJeuMois);
                    }
                    mysqli_free_result($idJeuMois);
                }
                ?>
                </select>
        </p>
    </form>
<?php
}
// On n'affiche le form de Gestion de Frais que s'il y a un mois qui a été sélectionné
if ($utilisateurChoisi != "" && $moisChoisi != "") {
    // Traitement des frais si un utilisateur et un mois ont été choisis
    $req = obtenirReqEltsForfaitFicheFrais($moisChoisi, $utilisateurChoisi);
    $idJeuEltsForfait = mysqli_query($idConnexion,$req);
    $lgEltsForfait = mysqli_fetch_assoc($idJeuEltsForfait);
    while (is_array($lgEltsForfait)) {
        // On place la bonne valeur en fonction de l'identifiant de forfait
        switch ($lgEltsForfait['idFraisForfait']) {
            case "ETP":
                $etp = $lgEltsForfait['quantite'];
                break;
            case "KM":
                $km = $lgEltsForfait['quantite'];
                break;
            case "NUI":
                $nui = $lgEltsForfait['quantite'];
                break;
            case "REP":
                $rep = $lgEltsForfait['quantite'];
                break;
        }
        $lgEltsForfait = mysqli_fetch_assoc($idJeuEltsForfait);
    }
    mysqli_free_result($idJeuEltsForfait);
?>
    <form id="formFraisForfait" method="post" action="">
        <p>
            <input type="hidden" name="etape" value="actualiserFraisForfait" />
            <input type="hidden" name="lstUtilisateur" value="<?php echo $utilisateurChoisi; ?>" />
            <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
        </p>
        <div style="clear:left;">
            <h2>Frais au forfait</h2>
        </div>
        <table style="color:white;" border="1">
            <tr>
                <th>Repas midi</th>
                <th>Nuitée </th>
                <th>Etape</th>
                <th>Km </th>
                <th>Actions</th>
            </tr>
            <tr align="center">
                <td style="width:80px;"><input type="text" size="3" id="idREP" name="txtEltsForfait[REP]" value="<?php echo $rep; ?>" style="text-align:right;" onchange="afficheMsgInfosForfaitAActualisees();" /></td>
                <td style="width:80px;"><input type="text" size="3" id="idNUI" name="txtEltsForfait[NUI]" value="<?php echo $nui; ?>" style="text-align:right;" onchange="afficheMsgInfosForfaitAActualisees();" /></td>
                <td style="width:80px;"><input type="text" size="3" id="idETP" name="txtEltsForfait[ETP]" value="<?php echo $etp; ?>" style="text-align:right;" onchange="afficheMsgInfosForfaitAActualisees();" /></td>
                <td style="width:80px;"><input type="text" size="3" id="idKM" name="txtEltsForfait[KM]" value="<?php echo $km; ?>" style="text-align:right;" onchange="afficheMsgInfosForfaitAActualisees();" /></td>
                <td>
                    <div id="actionsFraisForfait" class="actions">
                        <a class="actions" id="lkActualiserLigneFraisForfait" onclick="actualiserLigneFraisForfait(<?php echo $rep; ?>,<?php echo $nui; ?>,<?php echo $etp; ?>,<?php echo $km; ?>);" title="Actualiser la ligne de frais forfaitisé">&nbsp;<img src="images/actualiserIcon.png" class="icon" alt="icone Actualiser" />&nbsp;Actualiser&nbsp;</a>
                        <a class="actions" id="lkReinitialiserLigneFraisForfait" onclick="reinitialiserLigneFraisForfait();" title="Réinitialiser la ligne de frais forfaitisé">&nbsp;<img src="images/reinitialiserIcon.png" class="icon" alt="icone Réinitialiser" />&nbsp;Réinitialiser&nbsp;</a>
                    </div>
                </td>
            </tr>
        </table>
    </form>
    <div id="msgFraisForfait" class="infosNonActualisees">Attention, les modifications doivent être actualisées pour être réellement prises en compte...</div>
    <p class="titre">&nbsp;</p>
    <div style="clear:left;">
        <h2>Hors forfait</h2>
    </div>
    <?php
    // On récupère les lignes hors forfaits
    $req = obtenirReqEltsHorsForfaitFicheFrais($moisChoisi, $utilisateurChoisi);
    $idJeuEltsHorsForfait = mysqli_query($idConnexion, $req);
    $lgEltsHorsForfait = mysqli_fetch_assoc($idJeuEltsHorsForfait);
    while (is_array($lgEltsHorsForfait)) {
    ?>
        <form id="formFraisHorsForfait<?php echo $lgEltsHorsForfait['id']; ?>" method="post" action="">
            <p>
                <input type="hidden" id="idEtape<?php echo $lgEltsHorsForfait['id']; ?>" name="etape" value="actualiserFraisHorsForfait" />
                <input type="hidden" name="lstUtilisateur" value="<?php echo $utilisateurChoisi; ?>" />
                <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
                <input type="hidden" name="txtEltsHorsForfait[id]" value="<?php echo $lgEltsHorsForfait['id']; ?>" />
            </p>
            <table style="color:white;" border="1">
                <tr>
                    <th>Date</th>
                    <th>Libellé </th>
                    <th>Montant</th>
                    <th>Actions</th>
                </tr>
                <?php
                // Si les frais n'ont pas déjà été refusés, on affiche normalement
                if (strpos($lgEltsHorsForfait['libelle'], 'REFUSÉ : ') === false) {
                ?>
                    <tr>
                    <?php
                }
                // Sinon on met la ligne en grisée
                else {
                    ?>
                    <tr style="background-color:gainsboro;">
                    <?php
                }
                    ?>
                    <td style="width:100px;"><input type="text" size="12" id="idDate<?php echo $lgEltsHorsForfait['id']; ?>" name="txtEltsHorsForfait[date]" value="<?php echo convertirDateAnglaisVersFrancais($lgEltsHorsForfait['date']); ?>" onchange="afficheMsgInfosHorsForfaitAActualisees(<?php echo $lgEltsHorsForfait['id']; ?>);" /></td>
                    <td style="width:220px;"><input type="text" size="30" id="idLibelle<?php echo $lgEltsHorsForfait['id']; ?>" name="txtEltsHorsForfait[libelle]" value="<?php echo filtrerChainePourNavig($lgEltsHorsForfait['libelle']); ?>" onchange="afficheMsgInfosHorsForfaitAActualisees(<?php echo $lgEltsHorsForfait['id']; ?>);" /></td>
                    <td style="width:90px;"><input type="text" size="10" id="idMontant<?php echo $lgEltsHorsForfait['id']; ?>" name="txtEltsHorsForfait[montant]" value="<?php echo $lgEltsHorsForfait['montant']; ?>" style="text-align:right;" onchange="afficheMsgInfosHorsForfaitAActualisees(<?php echo $lgEltsHorsForfait['id']; ?>);" /></td>
                    <td>
                        <div id="actionsFraisHorsForfait<?php echo $lgEltsHorsForfait['id']; ?>" class="actions">
                            <a class="actions" id="lkActualiserLigneFraisHF<?php echo $lgEltsHorsForfait['id']; ?>" onclick="actualiserLigneFraisHF(<?php echo $lgEltsHorsForfait['id']; ?>,'<?php echo convertirDateAnglaisVersFrancais($lgEltsHorsForfait['date']); ?>','<?php echo filtrerChainePourBD($lgEltsHorsForfait['libelle']); ?>',<?php echo $lgEltsHorsForfait['montant']; ?>);" title="Actualiser la ligne de frais hors forfait">&nbsp;<img src="images/actualiserIcon.png" class="icon" alt="icone Actualiser" />&nbsp;Actualiser&nbsp;</a>
                            <a class="actions" id="lkReinitialiserLigneFraisHF<?php echo $lgEltsHorsForfait['id']; ?>" onclick="reinitialiserLigneFraisHorsForfait(<?php echo $lgEltsHorsForfait['id']; ?>);" title="Réinitialiser la ligne de frais hors forfait">&nbsp;<img src="images/reinitialiserIcon.png" class="icon" alt="icone Réinitialiser" />&nbsp;Réinitialiser&nbsp;</a>
                            <?php
                            // L'option "Supprimer" n'est proposée que si les frais n'ont pas déjà été refusés
                            if (strpos($lgEltsHorsForfait['libelle'], 'REFUSÉ : ') === false) {
                            ?>
                                <a class="actionsCritiques" onclick="reporterLigneFraisHF(<?php echo $lgEltsHorsForfait['id']; ?>);" title="Reporter la ligne de frais hors forfait">&nbsp;<img src="images/reporterIcon.png" class="icon" alt="icone Reporter" />&nbsp;Reporter&nbsp;</a>
                                <a class="actionsCritiques" onclick="refuseLigneFraisHF(<?php echo $lgEltsHorsForfait['id']; ?>);" title="Supprimer la ligne de frais hors forfait">&nbsp;<img src="images/refuserIcon.png" class="icon" alt="icone Supprimer" />&nbsp;Supprimer&nbsp;</a>
                            <?php
                            } else {
                            ?>
                                <a class="actionsCritiques" onclick="reintegrerLigneFraisHF(<?php echo $lgEltsHorsForfait['id']; ?>);" title="Réintégrer la ligne de frais hors forfait">&nbsp;<img src="images/reintegrerIcon.png" class="icon" alt="icone Réintégrer" />&nbsp;Réintégrer&nbsp;</a>
                            <?php
                            }
                            ?>
                        </div>
                    </td>
                    </tr>
            </table>
        </form>
        <div id="msgFraisHorsForfait<?php echo $lgEltsHorsForfait['id']; ?>" class="infosNonActualisees">Attention, les modifications doivent être actualisées pour être réellement prises en compte...</div>
    <?php
        $lgEltsHorsForfait = mysqli_fetch_assoc($idJeuEltsHorsForfait);
    }
    ?>
    <form id="formNbJustificatifs" method="post" action="">
        <p>
            <input type="hidden" name="etape" value="actualiserNbJustificatifs" />
            <input type="hidden" name="lstUtilisateur" value="<?php echo $utilisateurChoisi; ?>" />
            <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
        </p>
        <div class="titre">Nombre de justificatifs :
            <?php
            $laFicheFrais = obtenirDetailFicheFrais($idConnexion, $moisChoisi, $utilisateurChoisi);
            ?>
            <input type="text" class="zone" size="4" id="idNbJustificatifs" name="nbJustificatifs" value="<?php echo $laFicheFrais['nbJustificatifs']; ?>" style="text-align:center;" onchange="afficheMsgNbJustificatifs();" />
            <div id="actionsNbJustificatifs" class="actions">
                <a class="actions" id="lkActualiserNbJustificatifs" onclick="actualiserNbJustificatifs(<?php echo $laFicheFrais['nbJustificatifs']; ?>);" title="Actualiser le nombre de justificatifs">&nbsp;<img src="images/actualiserIcon.png" class="icon" alt="icone Actualiser" />&nbsp;Actualiser&nbsp;</a>
                <a class="actions" id="lkReinitialiserNbJustificatifs" onclick="reinitialiserNbJustificatifs();" title="Réinitialiser le nombre de justificatifs">&nbsp;<img src="images/reinitialiserIcon.png" class="icon" alt="icone Réinitialiser" />&nbsp;Réinitialiser&nbsp;</a>
            </div>
        </div>
    </form>
    <div id="msgNbJustificatifs" class="infosNonActualisees">Attention, le nombre de justificatifs doit être actualisé pour être réellement pris en compte...</div>

    <form id="formValidFiche" method="post" action="">
        <p>
            <input type="hidden" name="etape" value="validerFiche" />
            <input type="hidden" name="lstUtilisateur" value="<?php echo $utilisateurChoisi; ?>" />
            <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
            <p>
                <input class="zone" type="button" onclick="changerVisiteur();" value="Changer de visiteur" />
                <input class="zone" type="button" onclick="validerFiche();" value="Valider cette fiche" />
            </p>
    </form>

<?php
}
?>
</div>

<script type="text/javascript">
    <?php
    require($repInclude . "_fonctionsValidFichesFrais.inc.js");
    ?>
</script>
<?php
require($repInclude . "_pied.inc.html");
require($repInclude . "_fin.inc.php");
?>