<?php
/** 
 * Regroupe les fonctions d'acces aux donnees.
 * @package default
 * @author Arthur Martin
 * @todo Fonctions retournant plusieurs lignes sont e reecrire.
 */

/** 
 * Se connecte au serveur de donnees MySql.                      
 * Se connecte au serveur de donnees MySql e partir de valeurs
 * predefinies de connexion (hete, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succes obtenu, le booleen false 
 * si probleme de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() {
    $hote = "localhost";
    $login = "userGsb";
    $mdp = "Admin111";
    return mysql_connect($hote, $login, $mdp);
}

/**
 * Selectionne (rend active) la base de donnees.
 * Selectionne (rend active) la BD predefinie gsb_frais sur la connexion
 * identifiee par $idCnx. Retourne true si succes, false sinon.
 * @param resource $idCnx identifiant de connexion
 * @return boolean succes ou echec de selection BD 
 */
function activerBD($idCnx) {
    $bd = "gsb_valide";
    $query = "SET CHARACTER SET utf8";
    // Modification du jeu de caracteres de la connexion
    $res = mysql_query($query, $idCnx); 
    $ok = mysql_select_db($bd, $idCnx);
    return $ok;
}

/** 
 * Ferme la connexion au serveur de donnees.
 * Ferme la connexion au serveur de donnees identifiee par l'identifiant de 
 * connexion $idCnx.
 * @param resource $idCnx identifiant de connexion
 * @return void  
 */
function deconnecterServeurBD($idCnx) {
    mysql_close($idCnx);
}

/**
 * Echappe les caracteres speciaux d'une chaene.
 * Envoie la chaene $str echappee, ced avec les caracteres consideres speciaux
 * par MySql (tq la quote simple) precedes d'un \, ce qui annule leur effet special
 * @param string $str chaene e echapper
 * @return string chaene echappee 
 */    
function filtrerChainePourBD($str) {
    if ( ! get_magic_quotes_gpc() ) { 
        // si la directive de configuration magic_quotes_gpc est activee dans php.ini,
        // toute chaene reeue par get, post ou cookie est deje echappee 
        // par consequent, il ne faut pas echapper la chaene une seconde fois                              
        $str = mysql_real_escape_string($str);
    }
    return $str;
}

/** 
 * Fournit les informations sur un visiteur demande. 
 * Retourne les informations du visiteur d'id $unId sous la forme d'un tableau
 * associatif dont les cles sont les noms des colonnes(id, nom, prenom).
 * @param resource $idCnx identifiant de connexion
 * @param string $unId id de l'utilisateur
 * @return array  tableau associatif du visiteur
 */
function obtenirDetailVisiteur($idCnx, $unId) {
    $id = filtrerChainePourBD($unId);
    $requete = "select id, nom, prenom from visiteur where id='" . $unId . "'";
    $idJeuRes = mysql_query($requete, $idCnx);  
    $ligne = false;     
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }
    return $ligne ;
}

/** 
 * Fournit les informations d'une fiche de frais. 
 * Retourne les informations de la fiche de frais du mois de $unMois (MMAAAA)
 * sous la forme d'un tableau associatif dont les cles sont les noms des colonnes
 * (nbJustitificatifs, idEtat, libelleEtat, dateModif, montantValide).
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return array tableau associatif de la fiche de frais
 */
function obtenirDetailFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $ligne = false;
    $requete="select IFNULL(nbJustificatifs,0) as nbJustificatifs, Etat.id as idEtat, libelle as libelleEtat, dateModif, montantValide 
    from FicheFrais inner join Etat on idEtat = Etat.id 
    where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    $idJeuRes = mysql_query($requete, $idCnx);  
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
    }        
    mysql_free_result($idJeuRes);
    
    return $ligne ;
}
              
/** 
 * Verifie si une fiche de frais existe ou non. 
 * Retourne true si la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur existe, false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return booleen existence ou non de la fiche de frais
 */
function existeFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idVisiteur from FicheFrais where idVisiteur='" . $unIdVisiteur . 
              "' and mois='" . $unMois . "'";
    $idJeuRes = mysql_query($requete, $idCnx);  
    $ligne = false ;
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }        
    
    // si $ligne est un tableau, la fiche de frais existe, sinon elle n'exsite pas
    return is_array($ligne) ;
}

/** 
 * Fournit le mois de la derniere fiche de frais d'un visiteur.
 * Retourne le mois de la derniere fiche de frais du visiteur d'id $unIdVisiteur.
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id visiteur  
 * @return string dernier mois sous la forme AAAAMM
 */
function obtenirDernierMoisSaisi($idCnx, $unIdVisiteur) {
	$requete = "select max(mois) as dernierMois from FicheFrais where idVisiteur='" .
            $unIdVisiteur . "'";
	$idJeuRes = mysql_query($requete, $idCnx);
    $dernierMois = false ;
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        $dernierMois = $ligne["dernierMois"];
        mysql_free_result($idJeuRes);
    }        
	return $dernierMois;
}

/** 
 * Ajoute une nouvelle fiche de frais et les elements forfaitises associes, 
 * Ajoute la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur, avec les elements forfaitises associes dont la quantite initiale
 * est affectee e 0. Clet eventuellement la fiche de frais precedente du visiteur. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return void
 */
function ajouterFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    // modification de la derniere fiche de frais du visiteur
    $dernierMois = obtenirDernierMoisSaisi($idCnx, $unIdVisiteur);
	$laDerniereFiche = obtenirDetailFicheFrais($idCnx, $dernierMois, $unIdVisiteur);
	if ( is_array($laDerniereFiche) && $laDerniereFiche['idEtat']=='CR'){
		modifierEtatFicheFrais($idCnx, $dernierMois, $unIdVisiteur, 'CL');
	}
    
    // ajout de la fiche de frais e l'etat Cree
    $requete = "insert into FicheFrais (idVisiteur, mois, nbJustificatifs, montantValide, idEtat, dateModif) values ('" 
              . $unIdVisiteur 
              . "','" . $unMois . "',0,NULL, 'CR', '" . date("Y-m-d") . "')";
    mysql_query($requete, $idCnx);
    
    // ajout des elements forfaitises
    $requete = "select id from FraisForfait";
    $idJeuRes = mysql_query($requete, $idCnx);
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        while ( is_array($ligne) ) {
            $idFraisForfait = $ligne["id"];
            // insertion d'une ligne frais forfait dans la base
            $requete = "insert into LigneFraisForfait (idVisiteur, mois, idFraisForfait, quantite)
                        values ('" . $unIdVisiteur . "','" . $unMois . "','" . $idFraisForfait . "',0)";
            mysql_query($requete, $idCnx);
            // passage au frais forfait suivant
            $ligne = mysql_fetch_assoc ($idJeuRes);
        }
        mysql_free_result($idJeuRes);       
    }        
}

/**
 * Retourne le texte de la requete select concernant les mois pour lesquels un 
 * visiteur a une fiche de frais. 
 * 
 * La requete de selection fournie permettra d'obtenir les mois (AAAAMM) pour 
 * lesquels le visiteur $unIdVisiteur a une fiche de frais. 
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requete select
 */                                                 
function obtenirReqMoisFicheFrais($unIdVisiteur) {
    $req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='"
            . $unIdVisiteur . "' order by fichefrais.mois desc ";
    return $req ;
}  
                  
/**
 * Retourne le texte de la requete select concernant les elements forfaitises 
 * d'un visiteur pour un mois donnes. 
 * 
 * La requete de selection fournie permettra d'obtenir l'id, le libelle et la
 * quantite des elements forfaitises de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demande (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requete select
 */                                                 
function obtenirReqEltsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idFraisForfait, libelle, quantite from LigneFraisForfait
              inner join FraisForfait on FraisForfait.id = LigneFraisForfait.idFraisForfait
              where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    return $requete;
}

/**
 * Retourne le texte de la requete select concernant les elements hors forfait 
 * d'un visiteur pour un mois donnes. 
 * 
 * La requete de selection fournie permettra d'obtenir l'id, la date, le libelle 
 * et le montant des elements hors forfait de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demande (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requete select
 */                                                 
function obtenirReqEltsHorsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select id, date, libelle, montant from LigneFraisHorsForfait
              where idVisiteur='" . $unIdVisiteur 
              . "' and mois='" . $unMois . "'";
    return $requete;
}

/**
 * Supprime une ligne hors forfait.
 * Supprime dans la BD la ligne hors forfait d'id $unIdLigneHF
 * @param resource $idCnx identifiant de connexion
 * @param string $idLigneHF id de la ligne hors forfait
 * @return void
 */
function supprimerLigneHF($idCnx, $unIdLigneHF) {
    $requete = "delete from LigneFraisHorsForfait where id = " . $unIdLigneHF;
    mysql_query($requete, $idCnx);
}

/**
 * Ajoute une nouvelle ligne hors forfait.
 * Insere dans la BD la ligne hors forfait de libelle $unLibelleHF du montant 
 * $unMontantHF ayant eu lieu e la date $uneDateHF pour la fiche de frais du mois
 * $unMois du visiteur d'id $unIdVisiteur
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (AAMMMM)
 * @param string $unIdVisiteur id du visiteur
 * @param string $uneDateHF date du frais hors forfait
 * @param string $unLibelleHF libelle du frais hors forfait 
 * @param double $unMontantHF montant du frais hors forfait
 * @return void
 */
function ajouterLigneHF($idCnx, $unMois, $unIdVisiteur, $uneDateHF, $unLibelleHF, $unMontantHF) {
    $unLibelleHF = filtrerChainePourBD($unLibelleHF);
    $uneDateHF = filtrerChainePourBD(convertirDateFrancaisVersAnglais($uneDateHF));
    $unMois = filtrerChainePourBD($unMois);
    $requete = "insert into LigneFraisHorsForfait(idVisiteur, mois, date, libelle, montant) 
                values ('" . $unIdVisiteur . "','" . $unMois . "','" . $uneDateHF . "','" . $unLibelleHF . "'," . $unMontantHF .")";
    mysql_query($requete, $idCnx);
}

/**
 * Modifie les quantites des elements forfaitises d'une fiche de frais. 
 * Met e jour les elements forfaitises contenus  
 * dans $desEltsForfaits pour le visiteur $unIdVisiteur et
 * le mois $unMois dans la table LigneFraisForfait, apres avoir filtre 
 * (annule l'effet de certains caracteres consideres comme speciaux par 
 *  MySql) chaque donnee   
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (MMAAAA) 
 * @param string $unIdVisiteur  id visiteur
 * @param array $desEltsForfait tableau des quantites des elements hors forfait
 * avec pour cles les identifiants des frais forfaitises 
 * @return void  
 */
function modifierEltsForfait($idCnx, $unMois, $unIdVisiteur, $desEltsForfait) {
    $unMois=filtrerChainePourBD($unMois);
    $unIdVisiteur=filtrerChainePourBD($unIdVisiteur);
    foreach ($desEltsForfait as $idFraisForfait => $quantite) {
        $requete = "update LigneFraisForfait set quantite = " . $quantite 
                    . " where idVisiteur = '" . $unIdVisiteur . "' and mois = '"
                    . $unMois . "' and idFraisForfait='" . $idFraisForfait . "'";
      mysql_query($requete, $idCnx);
    }
}

/**
 * Contrele les informations de connexionn d'un utilisateur.
 * Verifie si les informations de connexion $unLogin, $unMdp sont ou non valides.
 * Retourne les informations de l'utilisateur sous forme de tableau associatif 
 * dont les cles sont les noms des colonnes (id, nom, prenom, login, mdp)
 * si login et mot de passe existent, le booleen false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unLogin login 
 * @param string $unMdp mot de passe 
 * @return array tableau associatif ou booleen false 
 */
function verifierInfosConnexion($idCnx, $unLogin, $unMdp) {
    $unLogin = filtrerChainePourBD($unLogin);
    $unMdp = filtrerChainePourBD($unMdp);
    // le mot de passe est crypte dans la base avec la fonction de hachage md5
    $req = "select id, nom, prenom, login, mdp from Visiteur where login='".$unLogin."' and mdp='" . $unMdp . "'";
    $idJeuRes = mysql_query($req, $idCnx);
    $ligne = false;
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }
    return $ligne;
}

/**
 * Modifie l'etat et la date de modification d'une fiche de frais
 
 * Met e jour l'etat de la fiche de frais du visiteur $unIdVisiteur pour
 * le mois $unMois e la nouvelle valeur $unEtat et passe la date de modif e 
 * la date d'aujourd'hui
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @return void 
 */
function modifierEtatFicheFrais($idCnx, $unMois, $unIdVisiteur, $unEtat) {
    $requete = "update FicheFrais set idEtat = '" . $unEtat . 
               "', dateModif = now() where idVisiteur ='" .
               $unIdVisiteur . "' and mois = '". $unMois . "'";
    mysql_query($requete, $idCnx);
}

/**
 * Retourne la requete d'obtention de la liste des visiteurs médicaux
 *
 * Retourne la requête d'obtention de la liste des visiteurs médicaux (id, nom et prenom)
 * @return string $requete
 */
function obtenirReqListeVisiteurs() {
    $requete = "select id, nom, prenom from utilisateur where idType='V' order by nom";
    return $requete;
}

/**
 * Modifie les quantités des éléments non forfaitisés d'une fiche de frais. 
 * Met à jour les éléments non forfaitisés contenus  
 * dans $desEltsHorsForfaits
 * @param resource $idCnx identifiant de connexion
 * @param array $desEltsHorsForfait tableau des éléments hors forfait
 * avec pour clés les identifiants des frais hors forfait
 * @return void  
 */
function modifierEltsHorsForfait($idCnx, $desEltsHorsForfait) {
    foreach ($desEltsHorsForfait as $cle => $val) {
        switch ($cle) {
            case 'id':
                $idFraisHorsForfait = $val;
                break;
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
    $requete = "update LigneFraisHorsForfait"
            . " set libelle = '" . filtrerChainePourBD($libelleFraisHorsForfait) . "',"
            . " date = '" . convertirDateFrancaisVersAnglais($dateFraisHorsForfait) . "',"
            . " montant = " . $montantFraisHorsForfait
            . " where id = " . $idFraisHorsForfait;
    mysql_query($requete, $idCnx);
}

/**
 * Modifie le nombre de justificatifs d'une fiche de frais
 *
 * Met à jour le nombre de justificatifs de la fiche de frais du visiteur $unIdVisiteur pour
 * le mois $unMois à la nouvelle valeur $nbJustificatifs
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @param integer $nbJustificatifs
 * @return void 
 */
function modifierNbJustificatifsFicheFrais($idCnx, $unMois, $unIdVisiteur, $nbJustificatifs) {
    $requete = "update FicheFrais set nbJustificatifs = " . $nbJustificatifs .
            " where idVisiteur ='" . $unIdVisiteur . "' and mois = '" . $unMois . "'";
    mysql_query($requete, $idCnx);
}

/**
 * Reporte d'un mois une ligne de frais hors forfait
 * 
 * 
 * @param resource $idCnx identifiant de connexion
 * @param int $unIdLigneHF identifiant de ligne hors forfait
 * @return void
 */
function reporterLigneHorsForfait($idCnx, $unIdLigneHF) {
    mysql_query('CALL reporterLigneFraisHF(' . $unIdLigneHF . ');', $idCnx);
}
?>