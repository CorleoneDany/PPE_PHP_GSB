<?php

/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Saisir fiche de frais"
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
// affectation du mois courant pour la saisie des fiches de frais
$mois = sprintf("%04d%02d", date("Y"), date("m"));

if(isset($_POST) && $_POST && isset($_POST['vehicule'])) {
  $vehicule = $_POST['vehicule'];
} else {
  $vehicule = obtenirUtilisateur($idConnexion, obtenirIdUserConnecte())['type_vehicule'];
}

// verification de l'existence de la fiche de frais pour ce mois courant
$existeFicheFrais = existeFicheFrais($idConnexion, $mois, obtenirIdUserConnecte());
// si elle n'existe pas, on la cree avec les elets frais forfaitisés a 0
if (!$existeFicheFrais) {
  ajouterFicheFrais($idConnexion, $mois, obtenirIdUserConnecte());
}
// acquisition des donnees entrees
// acquisition de l'etape du traitement 
$etape = lireDonnee("etape", "demanderSaisie");
// acquisition des quantites des éléments forfaitisés 
$tabQteEltsForfait = lireDonneePost("txtEltsForfait", "");
// acquisition des donnees d'une nouvelle ligne hors forfait
$idLigneHF = lireDonnee("idLigneHF", "");
$dateHF = lireDonnee("txtDateHF", "");
$libelleHF = lireDonnee("txtLibelleHF", "");
$montantHF = lireDonnee("txtMontantHF", "");

// structure de decision sur les differentes etapes du cas d'utilisation
if ($etape == "validerSaisie") {
  // l'utilisateur valide les éléments forfaitisés         
  // verification des quantites des éléments forfaitisés
  $ok = verifierEntiersPositifs($tabQteEltsForfait);
  if (!$ok) {
    ajouterErreur($tabErreurs, "Chaque quantite doit etre renseignee et numerique positive.");
  } else { // mise a jour des quantites des éléments forfaitisés
    modifierEltsForfait($idConnexion, $mois, obtenirIdUserConnecte(), $tabQteEltsForfait);
    majVehicule($idConnexion, obtenirIdUserConnecte(), $vehicule);
  }
} elseif ($etape == "validerSuppressionLigneHF") {
  supprimerLigneHF($idConnexion, $idLigneHF);
} elseif ($etape == "validerAjoutLigneHF") {
  verifierLigneFraisHF($dateHF, $libelleHF, $montantHF, $tabErreurs);
  if (nbErreurs($tabErreurs) == 0) {
    // la nouvelle ligne ligne doit etre ajoutee dans la base de donnees
    ajouterLigneHF($idConnexion, $mois, obtenirIdUserConnecte(), $dateHF, $libelleHF, $montantHF);
  }
} else { // on ne fait rien, etape non prevue 

}
?>
<!-- Division principale -->
<div id="contenu">
  <h2>Renseigner ma fiche de frais du mois de <?php echo obtenirLibelleMois(intval(substr($mois, 4, 2))) . " " . substr($mois, 0, 4); ?></h2>
  <?php
  if ($etape == "validerSaisie" || $etape == "validerAjoutLigneHF" || $etape == "validerSuppressionLigneHF") {
    if (nbErreurs($tabErreurs) > 0) {
      echo toStringErreurs($tabErreurs);
    } else {
  ?>
      <p class="info">Les modifications de la fiche de frais ont bien ete enregistrees</p>
  <?php
    }
  }
  ?>
  <form action="" method="post">
    <div class="corpsForm">
      <input type="hidden" name="etape" value="validerSaisie" />
      <fieldset>
        <legend>Éléments forfaitisés</legend>
        <?php
        // demande de la requete pour obtenir la liste des éléments 
        // forfaitisés de l'utilisateur connecte pour le mois demande
        $req = obtenirReqEltsForfaitFicheFrais();
        $idJeuEltsFraisForfait = $idConnexion->prepare($req);
        $idJeuEltsFraisForfait->execute([obtenirIdUserConnecte(), $mois]);
        $lgEltForfait = $idJeuEltsFraisForfait->fetch(PDO::FETCH_ASSOC);


        //récupération de tous les types de véhicules et leur montant associé
        $vehicules = obtenirAllTypesVehicules($idConnexion);

        //récupération du type de véhicule
        // $req = obtenirReqFraisKm();
        // $vehicule = $idConnexion->prepare($req);
        // $vehicule = $vehicule->execute();


        while (is_array($lgEltForfait)) {
          $idFraisForfait = $lgEltForfait["idFraisForfait"];
          $libelle = $lgEltForfait["libelle"];
          $quantite = $lgEltForfait["quantite"];
        ?>
          <div class="form-group row">
            <p class="col-lg-2 col-md-3">
              <label for="<?php echo $idFraisForfait ?>"><?php echo $libelle; ?> : </label>
            </p>
            <p class="col-lg-10 col-md-9">
              <input type="text" id="<?php echo $idFraisForfait ?>" class="form-control" name="txtEltsForfait[<?php echo $idFraisForfait ?>]" size="10" maxlength="5" title="Entrez la quantite de l'élément forfaitise" value="<?php echo $quantite; ?>" />
            </p>
          </div>

        <?php
          $lgEltForfait = $idJeuEltsFraisForfait->fetch(PDO::FETCH_ASSOC);
        }
        $idJeuEltsFraisForfait->closeCursor();
        ?>

        <div class="form-group row">
          <p class="col-lg-2 col-md-3">
            <label for="vehicule">Type de véhicule : </label>
          </p>
          <p class="col-lg-10 col-md-9">
            <select id="vehicule" class="form-control" name="vehicule" value="<?php echo $quantite; ?>">
            <option value="">-- Sélectionner un véhicule --</option>
              <?php foreach ($vehicules as $vehi) {
                echo '<option value="' . $vehi['id'] . '" ' . ($vehi['id'] == $vehicule ? 'selected' : '') . '>' . $vehi['libelle'] . '</option>';
              } ?>
            </select>
          </p>
        </div>

      </fieldset>
    </div>
    <div class="piedForm">
      <p>
        <button type="submit" title="Enregistrer les nouvelles valeurs des éléments forfaitisés" class="btn btn-submit">Valider</button>
        <button id="annuler" type="reset" class="btn btn-reset">Effacer</button>
      </p>
    </div>

  </form>
  <?php
  // demande de la requete pour obtenir la liste des éléments hors
  // forfait de l'utilisateur connecte pour le mois demande
  $req = obtenirReqEltsHorsForfaitFicheFrais();
  $idJeuEltsHorsForfait = $idConnexion->prepare($req);
  $idJeuEltsHorsForfait->execute([obtenirIdUserConnecte(), $mois]);
  $lgEltHorsForfait = $idJeuEltsHorsForfait->fetch(PDO::FETCH_ASSOC);

  // On affiche le tableau que s'il y a des données à l'intérieur.
  if ($lgEltHorsForfait) {
  ?>
    <div class="table-responsive">
      <table class="table table-hover">
        <caption>Descriptif des éléments hors forfait</caption>
        <thead class="thead-dark">
          <tr>
            <th class="date">Date</th>
            <th class="libelle">Libelle</th>
            <th class="montant">Montant</th>
            <th class="action">&nbsp;</th>
          </tr>
        </thead>
        <?php
        // parcours des frais hors forfait de l'utilisateur connecte
        while (is_array($lgEltHorsForfait)) {
        ?>
          <tr>
            <td><?php echo $lgEltHorsForfait["date"]; ?></td>
            <td><?php echo filtrerChainePourNavig($lgEltHorsForfait["libelle"]); ?></td>
            <td><?php echo $lgEltHorsForfait["montant"]; ?></td>
            <td><a href="?etape=validerSuppressionLigneHF&amp;idLigneHF=<?php echo $lgEltHorsForfait["id"]; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cette ligne de frais hors forfait ?');" title="Supprimer la ligne de frais hors forfait">Supprimer</a></td>
          </tr>
        <?php
          $lgEltHorsForfait = $idJeuEltsHorsForfait->fetch(PDO::FETCH_ASSOC);
        }
        $idJeuEltsHorsForfait->closeCursor();
        ?>
      </table>
    </div>
  <?php } ?>
  <form action="" method="post">
    <div class="corpsForm">
      <input type="hidden" name="etape" value="validerAjoutLigneHF" />
      <fieldset>
        <legend>Nouvel élément hors forfait
        </legend>
        <div class="form-group row">
          <p class="col-lg-2 col-md-3">
            <label for="txtDateHF">Date : </label>
          </p>
          <p class="col-lg-10 col-md-9">
            <input type="text" id="txtDateHF" name="txtDateHF" class="form-control" maxlength="10" title="Entrez la date d'engagement des frais au format JJ/MM/AAAA" value="<?php echo $dateHF; ?>" />
          </p>
        </div>
        <div class="form-group row">
          <p class="col-lg-2 col-md-3">
            <label for="txtLibelleHF">Libelle : </label>
          </p>
          <p class="col-lg-10 col-md-9">
            <input type="text" id="txtLibelleHF" name="txtLibelleHF" class="form-control" maxlength="100" title="Entrez un bref descriptif des frais" value="<?php echo filtrerChainePourNavig($libelleHF); ?>" />
          </p>
        </div>

        <div class="form-group row">
          <p class="col-lg-2 col-md-3">
            <label for="txtMontantHF">Montant : </label>
          </p>
          <p class="col-lg-10 col-md-9">
            <input type="text" id="txtMontantHF" name="txtMontantHF" class="form-control" maxlength="10" title="Entrez le montant des frais (le point est le separateur decimal)" value="<?php echo $montantHF; ?>" />
          </p>
        </div>
      </fieldset>
    </div>
    <div class="piedForm">
      <p>
        <button id="ajouter" type="submit" class="btn btn-submit" title="Ajouter la nouvelle ligne hors forfait">Valider</button>
        <button id="effacer" type="reset" class="btn btn-reset">Effacer</button>
      </p>
    </div>

  </form>
</div>
<?php
require($repInclude . "_pied.inc.html");
require($repInclude . "_fin.inc.php");
?>