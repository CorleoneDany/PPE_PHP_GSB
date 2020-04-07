<?php
/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Saisir fiche de frais"
 * @package default
 * @todo  RAS
 */
  $repInclude = './include/';
  require($repInclude . "_init.inc.php");

  // page inaccessible si visiteur non connecte
  if (!estVisiteurConnecte()) {
      header("Location: cSeConnecter.php");  
  }
  require($repInclude . "_entete.inc.html");
  require($repInclude . "_sommaire.inc.php");
  // affectation du mois courant pour la saisie des fiches de frais
  $mois = sprintf("%04d%02d", date("Y"), date("m"));
  // verification de l'existence de la fiche de frais pour ce mois courant
  $existeFicheFrais = existeFicheFrais($idConnexion, $mois, obtenirIdUserConnecte());
  // si elle n'existe pas, on la cree avec les elets frais forfaitises a 0
  if ( !$existeFicheFrais ) {
      ajouterFicheFrais($idConnexion, $mois, obtenirIdUserConnecte());
  }
  // acquisition des donnees entrees
  // acquisition de l'etape du traitement 
  $etape=lireDonnee("etape","demanderSaisie");
  // acquisition des quantites des elements forfaitises 
  $tabQteEltsForfait=lireDonneePost("txtEltsForfait", "");
  // acquisition des donnees d'une nouvelle ligne hors forfait
  $idLigneHF = lireDonnee("idLigneHF", "");
  $dateHF = lireDonnee("txtDateHF", "");
  $libelleHF = lireDonnee("txtLibelleHF", "");
  $montantHF = lireDonnee("txtMontantHF", "");
 
  // structure de decision sur les differentes etapes du cas d'utilisation
  if ($etape == "validerSaisie") { 
      // l'utilisateur valide les elements forfaitises         
      // verification des quantites des elements forfaitises
      $ok = verifierEntiersPositifs($tabQteEltsForfait);      
      if (!$ok) {
          ajouterErreur($tabErreurs, "Chaque quantite doit etre renseignee et numerique positive.");
      }
      else { // mise a jour des quantites des elements forfaitises
          modifierEltsForfait($idConnexion, $mois, obtenirIdUserConnecte(),$tabQteEltsForfait);
      }
  }                                                       
  elseif ($etape == "validerSuppressionLigneHF") {
      supprimerLigneHF($idConnexion, $idLigneHF);
  }
  elseif ($etape == "validerAjoutLigneHF") {
      verifierLigneFraisHF($dateHF, $libelleHF, $montantHF, $tabErreurs);
      if ( nbErreurs($tabErreurs) == 0 ) {
          // la nouvelle ligne ligne doit etre ajoutee dans la base de donnees
          ajouterLigneHF($idConnexion, $mois, obtenirIdUserConnecte(), $dateHF, $libelleHF, $montantHF);
      }
  }
  else { // on ne fait rien, etape non prevue 
  
  }                                  
?>
  <!-- Division principale -->
  <div id="contenu">
      <h2>Renseigner ma fiche de frais du mois de <?php echo obtenirLibelleMois(intval(substr($mois,4,2))) . " " . substr($mois,0,4); ?></h2>
<?php
  if ($etape == "validerSaisie" || $etape == "validerAjoutLigneHF" || $etape == "validerSuppressionLigneHF") {
      if (nbErreurs($tabErreurs) > 0) {
          echo toStringErreurs($tabErreurs);
      } 
      else {
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
            <legend>Elements forfaitises
            </legend>
      <?php          
            // demande de la requete pour obtenir la liste des elements 
            // forfaitises du visiteur connecte pour le mois demande
            $req = obtenirReqEltsForfaitFicheFrais($mois, obtenirIdUserConnecte());
            $idJeuEltsFraisForfait = mysql_query($req, $idConnexion);
            echo mysql_error($idConnexion);
            $lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
            while ( is_array($lgEltForfait) ) {
                $idFraisForfait = $lgEltForfait["idFraisForfait"];
                $libelle = $lgEltForfait["libelle"];
                $quantite = $lgEltForfait["quantite"];
            ?>
            <p>
              <label for="<?php echo $idFraisForfait ?>">* <?php echo $libelle; ?> : </label>
              <input type="text" id="<?php echo $idFraisForfait ?>" 
                    name="txtEltsForfait[<?php echo $idFraisForfait ?>]" 
                    size="10" maxlength="5"
                    title="Entrez la quantite de l'element forfaitise" 
                    value="<?php echo $quantite; ?>" />
            </p>
            <?php        
                $lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);   
            }
            mysql_free_result($idJeuEltsFraisForfait);
            ?>
          </fieldset>
      </div>
      <div class="piedForm">
      <p>
        <input id="ok" type="submit" value="Valider" size="20" 
               title="Enregistrer les nouvelles valeurs des elements forfaitises" />
        <input id="annuler" type="reset" value="Effacer" size="20" />
      </p> 
      </div>
        
      </form>
  	<table class="listeLegere">
  	   <caption>Descriptif des elements hors forfait
       </caption>
             <tr>
                <th class="date">Date</th>
                <th class="libelle">Libelle</th>
                <th class="montant">Montant</th>  
                <th class="action">&nbsp;</th>              
             </tr>
<?php          
          // demande de la requete pour obtenir la liste des elements hors
          // forfait du visiteur connecte pour le mois demande
          $req = obtenirReqEltsHorsForfaitFicheFrais($mois, obtenirIdUserConnecte());
          $idJeuEltsHorsForfait = mysql_query($req, $idConnexion);
          $lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
          
          // parcours des frais hors forfait du visiteur connecte
          while ( is_array($lgEltHorsForfait) ) {
          ?>
              <tr>
                <td><?php echo $lgEltHorsForfait["date"] ; ?></td>
                <td><?php echo filtrerChainePourNavig($lgEltHorsForfait["libelle"]) ; ?></td>
                <td><?php echo $lgEltHorsForfait["montant"] ; ?></td>
                <td><a href="?etape=validerSuppressionLigneHF&amp;idLigneHF=<?php echo $lgEltHorsForfait["id"]; ?>"
                       onclick="return confirm('Voulez-vous vraiment supprimer cette ligne de frais hors forfait ?');"
                       title="Supprimer la ligne de frais hors forfait">Supprimer</a></td>
              </tr>
          <?php
              $lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
          }
          mysql_free_result($idJeuEltsHorsForfait);
?>
    </table>
      <form action="" method="post">
      <div class="corpsForm">
          <input type="hidden" name="etape" value="validerAjoutLigneHF" />
          <fieldset>
            <legend>Nouvel element hors forfait
            </legend>
            <p>
              <label for="txtDateHF">* Date : </label>
              <input type="text" id="txtDateHF" name="txtDateHF" size="12" maxlength="10" 
                     title="Entrez la date d'engagement des frais au format JJ/MM/AAAA" 
                     value="<?php echo $dateHF; ?>" />
            </p>
            <p>
              <label for="txtLibelleHF">* Libelle : </label>
              <input type="text" id="txtLibelleHF" name="txtLibelleHF" size="70" maxlength="100" 
                    title="Entrez un bref descriptif des frais" 
                    value="<?php echo filtrerChainePourNavig($libelleHF); ?>" />
            </p>
            <p>
              <label for="txtMontantHF">* Montant : </label>
              <input type="text" id="txtMontantHF" name="txtMontantHF" size="12" maxlength="10" 
                     title="Entrez le montant des frais (le point est le separateur decimal)" value="<?php echo $montantHF; ?>" />
            </p>
          </fieldset>
      </div>
      <div class="piedForm">
      <p>
        <input id="ajouter" type="submit" value="Ajouter" size="20" 
               title="Ajouter la nouvelle ligne hors forfait" />
        <input id="effacer" type="reset" value="Effacer" size="20" />
      </p> 
      </div>
        
      </form>
  </div>
<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?> 