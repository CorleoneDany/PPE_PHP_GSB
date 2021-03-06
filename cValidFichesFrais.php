<?php

/**
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
require($repInclude . "_entete.inc.php");
require($repInclude . "_sommaire.inc.php");

// affectation du mois précédent pour la validation des fiches de frais
$mois = sprintf("%04d%02d", date("Y"), date("m"));
// Cloture des fiches de frais antérieur au mois courant et au besoin, création des fiches pour le mois courant
cloturerFichesFrais($idConnexion, $mois);

// acquisition des données entrées, ici l'id de visiteur, le mois et l'étape du traitement
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
    // L'utilisateur a choisi un visiteur
} elseif ($etape == "choixMois") {
    // L'utilisateur a choisi un mois
} elseif ($etape == "actualiserFraisForfait") {
    // L'utilisateur actualise les informations des frais forfaitisés
    // l'utilisateur valide les éléments forfaitisés         
    // vérification des quantités des éléments forfaitisés
    $ok = verifierEntiersPositifs($tabQteEltsForfait);
    if (!$ok) {
        ajouterErreur($tabErreurs, "Chaque quantité doit être renseignée et numérique positive.");
    } else { // mise à jour des quantités des éléments forfaitisés
        modifierEltsForfait($idConnexion, $moisChoisi, $visiteurChoisi, $tabQteEltsForfait);
    }
} elseif ($etape == "actualiserFraisHorsForfait") {
    // L'utilisateur actualise les informations des frais hors forfait
    // l'utilisateur valide les éléments non forfaitisés      
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
    // le visiteur demande le report d'une ligne hors forfait dont les justificatifs ne sont pas arrivés à temps
    reporterLigneHorsForfait($idConnexion, $tabEltsHorsForfait['id']);
} elseif ($etape == "actualiserNbJustificatifs") {
    // L'utilisateur actualise le nombre de justificatifs
    $ok = estEntierPositif($nbJustificatifs);
    if (!$ok) {
        ajouterErreur($tabErreurs, "Le nombre de justificatifs doit être renseigné et numérique positif.");
    } else {
        // mise à jour du nombre de justificatifs
        modifierNbJustificatifsFicheFrais($idConnexion, $moisChoisi, $visiteurChoisi, $nbJustificatifs);
    }
} elseif ($etape == "validerFiche") {
    // L'utilisateur valide la fiche
    modifierEtatFicheFrais($idConnexion, $moisChoisi, $visiteurChoisi, 'VA');
}
?>

<!-- Division principale -->
<div id="contenu">
    <h2>Validation des frais par visiteur </h2>
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
        $lgVisiteur = obtenirDetailUtilisateur($idConnexion, $visiteurChoisi);
        ?>
        <p class="info">La fiche de frais du visiteur <?php echo $lgVisiteur['prenom'] . " " . $lgVisiteur['nom']; ?> pour <?php echo obtenirLibelleMois(intval(substr($moisChoisi, 4, 2))) . " " . intval(substr($moisChoisi, 0, 4)); ?> a bien été enregistrée</p>
    <?php
        // On réinitialise le mois choisi pour forcer la disparition du bas de page, la réactualisation des mois et le choix d'un nouveau mois
        $moisChoisi = "";
    }
    ?>
    <form id="formChoixVisiteur" method="post" action="">
        <input type="hidden" name="etape" value="choixVisiteur" />
        <div class="form-group row">
            <p class="col-lg-2 col-md-3">
                <label class="titre">Choisir le visiteur :</label>
            </p>
            <p class="col-lg-10 col-md-9">
                <select name="lstVisiteur" id="idLstVisiteur" class="zone form-control" onchange="changerVisiteur(this.options[this.selectedIndex].value);">
                    <?php
                    // Si aucun visiteur n'a encore été choisi, on place en premier une invitation au choix
                    if ($visiteurChoisi == "") {
                    ?>
                        <option value="-1">=== Choisir un visiteur médical ===</option>
                    <?php
                    }
                    // On propose tous les utilisateurs qui sont des visteurs médicaux
                    $req = obtenirReqListeVisiteurs();
                    $idJeuVisiteurs = $idConnexion->prepare($req);
                    $idJeuVisiteurs->execute([]);
                    while ($lgVisiteur = $idJeuVisiteurs->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <option value="<?php echo $lgVisiteur['id']; ?>" <?php if ($visiteurChoisi == $lgVisiteur['id']) { ?> selected="selected" <?php } ?>><?php echo $lgVisiteur['nom'] . " " . $lgVisiteur['prenom']; ?></option>
                    <?php
                    }
                    $idJeuVisiteurs->closeCursor();
                    ?>
                </select>
            </p>

        </div>
    </form>
    <?php
    // Si aucun visiteur n'a encore été choisi on n'affiche pas le form de choix de mois
    if ($visiteurChoisi != "") {
    ?>
        <form id="formChoixMois" method="post" action="">
            <input type="hidden" name="etape" value="choixMois" />
            <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
            <?php
            // On propose tous les mois pour lesquels le visiteur dispose d'une fiche de frais cloturée
            $req = obtenirReqMoisFicheFrais();
            $idJeuMois = $idConnexion->prepare($req);
            $idJeuMois->execute([$visiteurChoisi, 'CL']);
            $lgMois = $idJeuMois->fetch(PDO::FETCH_ASSOC);
            // 4-a Aucune fiche de frais n'existe le système affiche "Pas de fiche de frais pour ce visiteur ce mois". Retour au 2
            if (empty($lgMois)) {
                ajouterErreur($tabErreurs, "Pas de fiche de frais à valider pour ce visiteur, veuillez choisir un autre visiteur");
                echo toStringErreurs($tabErreurs);
            } else {
            ?>
                <div class="form-group row">
                    <p class="col-lg-2 col-md-3">
                        <label class="titre">Mois :</label>
                    </p>
                    <p class="col-lg-10 col-md-9">
                        <select name="lstMois" id="idDateValid" class="zone form-control" onchange="this.form.submit();">
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
                                $lgMois = $idJeuMois->fetch(PDO::FETCH_ASSOC);
                            }
                            $idJeuMois->closeCursor();
                        }
                        ?>
                        </select>
                    </p>
                </div>
        </form>
    <?php
    }
    // On n'affiche le form de Gestion de Frais que s'il y a un mois qui a été sélectionné
    if ($visiteurChoisi != "" && $moisChoisi != "") {
        // Traitement des frais si un visiteur et un mois ont été choisis
        $req = obtenirReqEltsForfaitFicheFrais();
        $idJeuEltsForfait = $idConnexion->prepare($req);
        $idJeuEltsForfait->execute([$visiteurChoisi, $moisChoisi]);
        $lgEltsForfait = $idJeuEltsForfait->fetch(PDO::FETCH_ASSOC);
        while (is_array($lgEltsForfait)) {
            // On place la bonne valeur en fonction de l'identifiant de forfait
            switch ($lgEltsForfait['idFraisForfait']) {
                case "ETP":
                    $etp = $lgEltsForfait['quantite'];
                    break;
                case "NUI":
                    $nui = $lgEltsForfait['quantite'];
                    break;
                case "REP":
                    $rep = $lgEltsForfait['quantite'];
                    break;
            }
            $lgEltsForfait = $idJeuEltsForfait->fetch(PDO::FETCH_ASSOC);
        }
        $km = obtenirFraisKmUtilisateur($idConnexion, $visiteurChoisi);
        $idJeuEltsForfait->closeCursor();
    ?>
        <form id="formFraisForfait" method="post" action="">
            <p>
                <input type="hidden" name="etape" value="actualiserFraisForfait" />
                <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
            </p>
            <div style="clear:left;">
                <h3>Frais au forfait</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Repas midi</th>
                            <th>Nuitée </th>
                            <th>Etape</th>
                            <th style="width: 15%">Km </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tr>
                        <td><input type="text" id="idREP" class="form-control" name="txtEltsForfait[REP]" value="<?php echo $rep; ?>" style="text-align:right;" onchange="afficheMsgInfosForfaitAActualisees();" /></td>
                        <td><input type="text" id="idNUI" class="form-control" name="txtEltsForfait[NUI]" value="<?php echo $nui; ?>" style="text-align:right;" onchange="afficheMsgInfosForfaitAActualisees();" /></td>
                        <td><input type="text" id="idETP" class="form-control" name="txtEltsForfait[ETP]" value="<?php echo $etp; ?>" style="text-align:right;" onchange="afficheMsgInfosForfaitAActualisees();" /></td>
                        <td><input type="text" id="idKM" class="form-control" name="txtEltsForfait[KM]" value="<?php echo $km; ?>" style="text-align:right;" onchange="afficheMsgInfosForfaitAActualisees();" /></td>
                        <td>
                            <div id="actionsFraisForfait" class="actions">
                                <span><img src="images/actualiserIcon.svg" class="icon" alt="icone Actualiser" /><a class="actions" id="lkActualiserLigneFraisForfait" onclick="actualiserLigneFraisForfait(<?php echo $rep; ?>,<?php echo $nui; ?>,<?php echo $etp; ?>,<?php echo $km; ?>);" title="Actualiser la ligne de frais forfaitisé">Actualiser</a></span>
                                <span><img src="images/reinitialiserIcon.svg" class="icon" alt="icone Réinitialiser" /><a class="actions" id="lkReinitialiserLigneFraisForfait" onclick="reinitialiserLigneFraisForfait();" title="Réinitialiser la ligne de frais forfaitisé">Réinitialiser</a></span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <div id="msgFraisForfait" class="infosNonActualisees">Attention, les modifications doivent être actualisées pour être réellement prises en compte...</div>
        <p class="titre"></p>
        <div style="clear:left;">
            <h3>Hors forfait</h3>
        </div>
        <?php
        // On récupère les lignes hors forfaits
        $req = obtenirReqEltsHorsForfaitFicheFrais();
        $idJeuEltsHorsForfait = $idConnexion->prepare($req);
        $idJeuEltsHorsForfait->execute([$visiteurChoisi, $moisChoisi]);
        $lgEltsHorsForfait = $idJeuEltsHorsForfait->fetch(PDO::FETCH_ASSOC);
        while (is_array($lgEltsHorsForfait)) {
        ?>
            <form id="formFraisHorsForfait<?php echo $lgEltsHorsForfait['id']; ?>" method="post" action="">
                <p>
                    <input type="hidden" id="idEtape<?php echo $lgEltsHorsForfait['id']; ?>" name="etape" value="actualiserFraisHorsForfait" />
                    <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                    <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
                    <input type="hidden" name="txtEltsHorsForfait[id]" value="<?php echo $lgEltsHorsForfait['id']; ?>" />
                </p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 15%">Date</th>
                                <th>Libellé </th>
                                <th style="width: 15%">Montant</th>
                                <th style="width: 15%">Actions</th>
                            </tr>
                        </thead>
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
                            <td><input type="text" id="idDate<?php echo $lgEltsHorsForfait['id']; ?>" class="form-control" name="txtEltsHorsForfait[date]" value="<?php echo convertirDateAnglaisVersFrancais($lgEltsHorsForfait['date']); ?>" onchange="afficheMsgInfosHorsForfaitAActualisees(<?php echo $lgEltsHorsForfait['id']; ?>);" /></td>
                            <td><input type="text" id="idLibelle<?php echo $lgEltsHorsForfait['id']; ?>" class="form-control" name="txtEltsHorsForfait[libelle]" value="<?php echo filtrerChainePourNavig($lgEltsHorsForfait['libelle']); ?>" onchange="afficheMsgInfosHorsForfaitAActualisees(<?php echo $lgEltsHorsForfait['id']; ?>);" /></td>
                            <td><input type="text" id="idMontant<?php echo $lgEltsHorsForfait['id']; ?>" class="form-control" name="txtEltsHorsForfait[montant]" value="<?php echo $lgEltsHorsForfait['montant']; ?>" style="text-align:right;" onchange="afficheMsgInfosHorsForfaitAActualisees(<?php echo $lgEltsHorsForfait['id']; ?>);" /></td>
                            <td>
                                <div id="actionsFraisHorsForfait<?php echo $lgEltsHorsForfait['id']; ?>" class="actions">
                                    <span><img src="images/actualiserIcon.svg" class="icon" alt="icone Actualiser" /><a class="actions" id="lkActualiserLigneFraisHF<?php echo $lgEltsHorsForfait['id']; ?>" onclick="actualiserLigneFraisHF(<?php echo $lgEltsHorsForfait['id']; ?>,'<?php echo convertirDateAnglaisVersFrancais($lgEltsHorsForfait['date']); ?>','<?php echo $lgEltsHorsForfait['libelle']; ?>',<?php echo $lgEltsHorsForfait['montant']; ?>);" title="Actualiser la ligne de frais hors forfait">Actualiser</a></span>
                                    <span><img src="images/reinitialiserIcon.svg" class="icon" alt="icone Réinitialiser" /><a class="actions" id="lkReinitialiserLigneFraisHF<?php echo $lgEltsHorsForfait['id']; ?>" onclick="reinitialiserLigneFraisHorsForfait(<?php echo $lgEltsHorsForfait['id']; ?>);" title="Réinitialiser la ligne de frais hors forfait">Réinitialiser</a></span>
                                    <?php
                                    // L'option "Supprimer" n'est proposée que si les frais n'ont pas déjà été refusés
                                    if (strpos($lgEltsHorsForfait['libelle'], 'REFUSÉ : ') === false) {
                                    ?>
                                        <span><a class="actionsCritiques" onclick="reporterLigneFraisHF(<?php echo $lgEltsHorsForfait['id']; ?>);" title="Reporter la ligne de frais hors forfait"><img src="images/reporterIcon.svg" class="icon" alt="icone Reporter" />Reporter</a></span>
                                        <span><a class="actionsCritiques" onclick="refuseLigneFraisHF(<?php echo $lgEltsHorsForfait['id']; ?>);" title="Supprimer la ligne de frais hors forfait"><img src="images/refuserIcon.svg" class="icon" alt="icone Supprimer" />Supprimer</a></span>
                                    <?php
                                    } else {
                                    ?>
                                        <span><a class="actionsCritiques" onclick="reintegrerLigneFraisHF(<?php echo $lgEltsHorsForfait['id']; ?>);" title="Réintégrer la ligne de frais hors forfait"><img src="images/reintegrerIcon.png" class="icon" alt="icone Réintégrer" />Réintégrer</a></span>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </td>
                            </tr>
                    </table>
                </div>
            </form>
            <div id="msgFraisHorsForfait<?php echo $lgEltsHorsForfait['id']; ?>" class="infosNonActualisees">Attention, les modifications doivent être actualisées pour être réellement prises en compte...</div>
        <?php
            $lgEltsHorsForfait = $idJeuEltsHorsForfait->fetch(PDO::FETCH_ASSOC);
        }
        ?>
        <form id="formNbJustificatifs" method="post" action="">
            <p>
                <input type="hidden" name="etape" value="actualiserNbJustificatifs" />
                <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
            </p>
            <div class="row">
                <p class="col-lg-3 col-md-4">
                    <label for="idNbJustificatifs">Nombre de justificatifs :</label>
                </p>
                <?php
                $laFicheFrais = obtenirDetailFicheFrais($idConnexion, $moisChoisi, $visiteurChoisi);
                ?>
                <p class="col-lg-9 col-md-4">
                    <input type="text" class="zone form-control" id="idNbJustificatifs" name="nbJustificatifs" value="<?php echo $laFicheFrais['nbJustificatifs']; ?>" onchange="afficheMsgNbJustificatifs();" />
                </p>
                <div id="actionsNbJustificatifs" class="actions flex-row my-4">
                    <span><img src="images/actualiserIcon.svg" class="icon" alt="icone Actualiser" /><a class="actions" id="lkActualiserNbJustificatifs" onclick="actualiserNbJustificatifs(<?php echo $laFicheFrais['nbJustificatifs']; ?>);" title="Actualiser le nombre de justificatifs">Actualiser</a></span>
                    <span><img src="images/reinitialiserIcon.svg" class="icon" alt="icone Réinitialiser" /><a class="actions" id="lkReinitialiserNbJustificatifs" onclick="reinitialiserNbJustificatifs();" title="Réinitialiser le nombre de justificatifs">Réinitialiser</a></span>
                </div>
            </div>
        </form>
        <div id="msgNbJustificatifs" class="infosNonActualisees">Attention, le nombre de justificatifs doit être actualisé pour être réellement pris en compte...</div>

        <form id="formValidFiche" method="post" action="">
            <p>
                <input type="hidden" name="etape" value="validerFiche" />
                <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
                <p class="btn-container-2">
                    <button class="zone btn btn-reset" type="button" onclick="changerVisiteur();">Changer de visiteur</button>
                    <button class="zone btn btn-submit" type="button" onclick="validerFiche();">Valider cette fiche</button>
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