<?php

/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Consulter une fiche de frais"
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

// acquisition des donnees entrees, ici le numero de mois et l'etape du traitement
$moisSaisi = lireDonneePost("lstMois", "");
$etape = lireDonneePost("etape", "");

if ($etape != "demanderConsult" && $etape != "validerConsult") {
    // si autre valeur, on considere que c'est le debut du traitement
    $etape = "demanderConsult";
}
if ($etape == "validerConsult") { // l'utilisateur valide ses nouvelles donnees

    // verification de l'existence de la fiche de frais pour le mois demande
    $existeFicheFrais = existeFicheFrais($idConnexion, $moisSaisi, obtenirIdUserConnecte());
    // si elle n'existe pas, on la cree avec les elets frais forfaitises a 0
    if (!$existeFicheFrais) {
        ajouterErreur($tabErreurs, "Le mois demande est invalide");
    } else {
        // recuperation des donnees sur la fiche de frais demandee
        $tabFicheFrais = obtenirDetailFicheFrais($idConnexion, $moisSaisi, obtenirIdUserConnecte());
    }
}
?>
<!-- Division principale -->
<div id="contenu">
    <h2>Mes fiches de frais</h2>
    <h3>Mois à selectionner : </h3>
    <form action="" method="post">
        <div class="corpsForm">
            <input type="hidden" name="etape" value="validerConsult" />
            <div class="form-group row">
                <p class="col-lg-2 col-md-3">
                    <label for="lstMois">Mois : </label>
                </p>
                <p class="col-lg-10 col-md-9">
                    <select id="lstMois" name="lstMois" title="Selectionnez le mois souhaite pour la fiche de frais" class="form-control">
                        <?php
                        // on propose tous les mois pour lesquels l'utilisateur a une fiche de frais
                        $req = obtenirReqMoisFicheFrais();
                        $idJeuMois = $idConnexion->prepare($req);
                        $idJeuMois->execute([obtenirIdUserConnecte(), 'CL']);
                        $lgMois = $idJeuMois->fetch(PDO::FETCH_ASSOC);
                        while (is_array($lgMois)) {
                            $mois = $lgMois["mois"];
                            $noMois = intval(substr($mois, 4, 2));
                            $annee = intval(substr($mois, 0, 4));
                        ?>
                            <option value="<?php echo $mois; ?>" <?php if ($moisSaisi == $mois) { ?> selected="selected" <?php } ?>><?php echo obtenirLibelleMois($noMois) . " " . $annee; ?></option>
                        <?php
                            $lgMois = $idJeuMois->fetch(PDO::FETCH_ASSOC);
                        }
                        $idJeuMois->closeCursor();
                        ?>
                    </select>
                </p>
            </div>
        </div>
        <div class="piedForm">
            <p>
                <button id="ok" type="submit" title="Demandez a consulter cette fiche de frais" class="btn btn-submit">Valider</button>
                <button id="annuler" type="reset" class="btn btn-reset">Annuler</button>
            </p>
        </div>

    </form>
    <?php

    // demande et affichage des differents elements (forfaitises et non forfaitises)
    // de la fiche de frais demandee, uniquement si pas d'erreur detecte au contrôle
    if ($etape == "validerConsult") {
        if (nbErreurs($tabErreurs) > 0) {
            echo toStringErreurs($tabErreurs);
        } else {
    ?>
            <h3>Fiche de frais du mois de <?php echo obtenirLibelleMois(intval(substr($moisSaisi, 4, 2))) . " " . substr($moisSaisi, 0, 4); ?> :
                <em><?php echo $tabFicheFrais["libelleEtat"]; ?> </em>
                depuis le <em><?php echo $tabFicheFrais["dateModif"]; ?></em></h3>
            <div class="encadre">
                <p>Montant valide : <?php echo $tabFicheFrais["montantValide"];
                                    ?>
                </p>
                <?php
                // demande de la requete pour obtenir la liste des elements 
                // forfaitises de l'utilisateur connecte pour le mois demande
                $req = obtenirReqEltsForfaitFicheFrais();
                $idJeuEltsFraisForfait = $idConnexion->prepare($req);
                $idJeuEltsFraisForfait->execute([obtenirIdUserConnecte(), $moisSaisi]);
                $lgEltForfait = $idJeuEltsFraisForfait->fetch(PDO::FETCH_ASSOC);
                // parcours des frais forfaitises du utilisateur connecte
                // le stockage intermediaire dans un tableau est necessaire
                // car chacune des lignes du jeu d'enregistrements doit etre doit etre
                // affichee au sein d'une colonne du tableau HTML
                $tabEltsFraisForfait = array();
                while (is_array($lgEltForfait)) {
                    $tabEltsFraisForfait[$lgEltForfait["libelle"]] = $lgEltForfait["quantite"];
                    $lgEltForfait = $idJeuEltsFraisForfait->fetch(PDO::FETCH_ASSOC);
                }
                $idJeuEltsFraisForfait->closeCursor();
                ?>
                <div class="table-responsive-sm">
                    <table class="table table-hover">
                        <caption>Quantites des elements forfaitises</caption>
                        <thead class="thead-dark">
                            <tr>
                                <?php
                                // premier parcours du tableau des frais forfaitises de l'utilisateur connecte
                                // pour afficher la ligne des libelles des frais forfaitises
                                foreach ($tabEltsFraisForfait as $unLibelle => $uneQuantite) {
                                ?>
                                    <th><?php echo $unLibelle; ?></th>
                                <?php
                                }
                                ?>
                            </tr>
                        </thead>
                        <tr>
                            <?php
                            // second parcours du tableau des frais forfaitises de l'utilisateur connecte
                            // pour afficher la ligne des quantites des frais forfaitises
                            foreach ($tabEltsFraisForfait as $unLibelle => $uneQuantite) {
                            ?>
                                <td class="qteForfait"><?php echo $uneQuantite; ?></td>
                            <?php
                            }
                            ?>
                        </tr>
                    </table>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <caption>Descriptif des elements hors forfait - <?php echo $tabFicheFrais["nbJustificatifs"]; ?> justificatifs reçus -
                        </caption>
                        <thead class="thead-dark">
                            <tr>
                                <th class="date">Date</th>
                                <th class="libelle">Libelle</th>
                                <th class="montant">Montant</th>
                            </tr>
                        </thead>

                        <?php
                        // demande de la requete pour obtenir la liste des elements hors
                        // forfait de l'utilisateur connecte pour le mois demande
                        $req = obtenirReqEltsHorsForfaitFicheFrais();
                        $idJeuEltsHorsForfait = $idConnexion->prepare($req);
                        $idJeuEltsHorsForfait->execute([obtenirIdUserConnecte(), $moisSaisi]);
                        $lgEltHorsForfait = $idJeuEltsHorsForfait->fetch(PDO::FETCH_ASSOC);

                        // parcours des elements hors forfait 
                        while (is_array($lgEltHorsForfait)) {
                        ?>
                            <tr>
                                <td><?php echo $lgEltHorsForfait["date"]; ?></td>
                                <td><?php echo filtrerChainePourNavig($lgEltHorsForfait["libelle"]); ?></td>
                                <td><?php echo $lgEltHorsForfait["montant"]; ?></td>
                            </tr>
                        <?php
                            $lgEltHorsForfait = $idJeuEltsHorsForfait->fetch(PDO::FETCH_ASSOC);
                        }
                        $idJeuEltsHorsForfait->closeCursor();
                        ?>
                    </table>
                </div>
            </div>
    <?php
        }
    }
    ?>
</div>
<?php
require($repInclude . "_pied.inc.html");
require($repInclude . "_fin.inc.php");
?>