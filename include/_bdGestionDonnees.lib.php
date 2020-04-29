<?php
/** 
 * Regroupe les fonctions d'acces aux donnees.
 * @package default
 * @author Arthur Martin
 * @todo Fonctions retournant plusieurs lignes sont à reecrire.
 */

/** 
 * Se connecte au serveur de donnees MySql.                      
 * Se connecte au serveur de donnees MySql à partir de valeurs
 * predefinies de connexion (hete, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succes obtenu, le booleen false 
 * si probleme de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() {
    $hote = "localhost";
    $login = "userGsb";
    $mdp = "Admin111";
    $bd = "gsb_frais";

    try {
        $connexion = new PDO("mysql:host=$hote;dbname=$bd;charset=utf8", $login, $mdp);
    } catch(PDOException $e) {
        $connexion = false;
        echo $e->getMessage();
    }
    return $connexion;
}

/** 
 * Ferme la connexion au serveur de donnees.
 * Ferme la connexion au serveur de donnees identifiee par l'identifiant de 
 * connexion $idCnx.
 * @param resource $idCnx identifiant de connexion
 * @return void  
 */
function deconnecterServeurBD($idCnx) {
    $idCnx = null;
}

/**
 * Echappe les caracteres speciaux d'une chaene.
 * Envoie la chaene $str echappee, ced avec les caracteres consideres speciaux
 * par MySql (tq la quote simple) precedes d'un \, ce qui annule leur effet special
 * @param string $str chaene à echapper
 * @return string chaene echappee 
 */    
 

/** 
 * Fournit les informations sur un utilisateur demande. 
 * Retourne les informations de l'utilisateur d'id $unId sous la forme d'un tableau
 * associatif dont les cles sont les noms des colonnes(id, nom, prenom).
 * @param resource $idCnx identifiant de connexion
 * @param string $unId id de l'utilisateur
 * @return array  tableau associatif de l'utilisateur
 */
function obtenirDetailUtilisateur($idCnx, $unId) {
    $requete = "select utilisateur.id, nom, prenom, TypeUser from utilisateur inner join type_user on utilisateur.idType = type_user.id_type where utilisateur.id = ?";
    $idJeuRes = $idCnx->prepare($requete);  
    $idJeuRes->execute([$unId]);
    $ligne = false;     
    if ($idJeuRes) {
        $ligne = $idJeuRes->fetch(PDO::FETCH_ASSOC);
        $idJeuRes->closeCursor();
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
 * @param string $unIdUtilisateur id utilisateur  
 * @return array tableau associatif de la fiche de frais
 */
function obtenirDetailFicheFrais($idCnx, $unMois, $unIdUtilisateur) {
    $unMois = $unMois;
    $ligne = false;
    $requete="select IFNULL(nbJustificatifs,0) as nbJustificatifs, Etat.id as idEtat, libelle as libelleEtat, dateModif, montantValide 
    from FicheFrais inner join Etat on idEtat = Etat.id 
    where idUtilisateur = ? and mois = ?";
    $idJeuRes = $idCnx->prepare($requete);
    $idJeuRes->execute([$unIdUtilisateur, $unMois]);  
    if ( $idJeuRes ) {
        $ligne = $idJeuRes->fetch(PDO::FETCH_ASSOC);
    }        
    $idJeuRes->closeCursor();
    
    return $ligne ;
}
              
/** 
 * Verifie si une fiche de frais existe ou non. 
 * Retourne true si la fiche de frais du mois de $unMois (MMAAAA) de l'utilisateur 
 * $idUtilisateur existe, false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (MMAAAA)
 * @param string $unIdUtilisateur id utilisateur  
 * @return booleen existence ou non de la fiche de frais
 */
function existeFicheFrais($idCnx, $unMois, $unIdUtilisateur) {
    $requete = "select idUtilisateur from FicheFrais where idUtilisateur='" . $unIdUtilisateur . 
              "' and mois = ?";
    $idJeuRes = $idCnx->prepare($requete);  
    $idJeuRes->execute([$unMois]);
    $ligne = false ;
    if ( $idJeuRes ) {
        $ligne = $idJeuRes->fetch(PDO::FETCH_ASSOC);
        $idJeuRes->closeCursor();
    }        
    
    // si $ligne est un tableau, la fiche de frais existe, sinon elle n'exsite pas
    return is_array($ligne) ;
}

/** 
 * Fournit le mois de la derniere fiche de frais d'un utilisateur.
 * Retourne le mois de la derniere fiche de frais de l'utilisateur d'id $unIdUtilisateur.
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdUtilisateur id utilisateur  
 * @return string dernier mois sous la forme AAAAMM
 */
function obtenirDernierMoisSaisi($idCnx, $unIdUtilisateur) {
	$requete = "select max(mois) as dernierMois from FicheFrais where idUtilisateur=?";
    $idJeuRes = $idCnx->prepare($requete);
    $idJeuRes->execute([$unIdUtilisateur]);
    $dernierMois = false ;
    if ( $idJeuRes ) {
        $ligne = $idJeuRes->fetch(PDO::FETCH_ASSOC);
        $dernierMois = $ligne["dernierMois"];
        $idJeuRes->closeCursor();
    }        
	return $dernierMois;
}

/** 
 * Ajoute une nouvelle fiche de frais et les elements forfaitises associes, 
 * Ajoute la fiche de frais du mois de $unMois (MMAAAA) de l'utilisateur 
 * $idUtilisateur, avec les elements forfaitises associes dont la quantite initiale
 * est affectee à 0. Clet eventuellement la fiche de frais precedente de l'utilisateur. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (MMAAAA)
 * @param string $unIdUtilisateur id utilisateur  
 * @return void
 */
function ajouterFicheFrais($idCnx, $unMois, $unIdUtilisateur) {
    $unMois = $unMois;
    // modification de la derniere fiche de frais de l'utilisateur
    $dernierMois = obtenirDernierMoisSaisi($idCnx, $unIdUtilisateur);
	$laDerniereFiche = obtenirDetailFicheFrais($idCnx, $dernierMois, $unIdUtilisateur);
	if ( is_array($laDerniereFiche) && $laDerniereFiche['idEtat']=='CR'){
		modifierEtatFicheFrais($idCnx, $dernierMois, $unIdUtilisateur, 'CL');
	}
    
    // ajout de la fiche de frais à l'etat Cree
    $requete = "insert into FicheFrais (idUtilisateur, mois, nbJustificatifs, montantValide, idEtat, dateModif) values (:unIdUtilisateur, :unMois,0,NULL, 'CR', " . date("Y-m-d") . ")";
    $insert = $idCnx->prepare($requete);
    $params = [
        ':unIdUtilisateur' => $unIdUtilisateur,
        ':unMois'          => $unMois
    ];
    $insert->execute($params);
    
    
    // ajout des elements forfaitises
    $requete = "select id from FraisForfait";
    $idJeuRes = $idCnx->prepare($requete);
    $idJeuRes->execute([]);
    if ( $idJeuRes ) {
        $ligne = $idJeuRes->fetch(PDO::FETCH_ASSOC);
        while ( is_array($ligne) ) {
            $idFraisForfait = $ligne["id"];
            // insertion d'une ligne frais forfait dans la base
            $requete = "insert into LigneFraisForfait (idUtilisateur, mois, idFraisForfait, quantite)
                        values (:unIdUtilisateur, :unMois, :idFraisForfait, 0)";
            $insert = $idCnx->prepare($requete);
            $params = [
                ':unIdUtilisateur' => $unIdUtilisateur,
                ':unMois'          => $unMois,
                ':idFraisForfait'  => $idFraisForfait
            ];
            $insert->execute($params);
            // passage au frais forfait suivant
            $ligne = $idJeuRes->fetch(PDO::FETCH_ASSOC);
        }
        $idJeuRes->closeCursor();       
    }        
}

/**
 * Retourne le texte de la requete select concernant les mois pour lesquels un 
 * Utilisateur a une fiche de frais. 
 * 
 * La requete de selection fournie permettra d'obtenir les mois (AAAAMM) pour 
 * lesquels l'utilisateur $unIdUtilisateur a une fiche de frais. 
 * @return string texte de la requete select
 */                                                 
function obtenirReqMoisFicheFrais() {
    $req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idUtilisateur = ? and idEtat = ? order by fichefrais.mois desc";
    return $req ;
}  
                  
/**
 * Retourne le texte de la requete select concernant les elements forfaitises 
 * d'un utilisateur pour un mois donnes. 
 * 
 * La requete de selection fournie permettra d'obtenir l'id, le libelle et la
 * quantite des elements forfaitises de la fiche de frais de l'utilisateur
 * d'id $idUtilisateur pour le mois $mois    

 */                                                 
function obtenirReqEltsForfaitFicheFrais() {
    $requete = "select idFraisForfait, libelle, quantite from LigneFraisForfait
              inner join FraisForfait on FraisForfait.id = LigneFraisForfait.idFraisForfait 
              where idUtilisateur= ? and mois= ?";
    return $requete;
}


/**
 * Retourne la requête qui permet de récupérer tous les types de véhicules.
 */                                                 
function obtenirAllTypesVehicules($idConnexion) {
    $req = "select * from `fraiskm`";
    $listeVehicules = $idConnexion->query($req);
    $vehicules = [];
    while($row = $listeVehicules->fetch(PDO::FETCH_ASSOC)) {
      $vehicules[] = $row;
    }
    return $vehicules;
}

/**
 * Retourne forfait utilisateur
 * @param PDO $idConnexion
 * @param string $idUtilisateur
 */
function obtenirFraisKmUtilisateur($idConnexion, $idUtilisateur) {
    $requete = "select montant from fraiskm fkm 
                inner join utilisateur u on fkm.id = u.type_vehicule 
                inner join lignefraisforfait lff on u.id = lff.idUtilisateur 
                where u.id = ?
                group by fkm.id";
    $utilisateur = $idConnexion->prepare($requete);
    $utilisateur->execute([$idUtilisateur]);

    $km = $utilisateur->fetch(PDO::FETCH_COLUMN);
    
    return $km;
}

/**
 * Retourne la requête qui permet de récupérer le montant d'un frais kilométrique.
 */                                                 
function obtenirReqFraisKm() {
    $requete = "select `montant` from `fraiskm` where `id` = ?";
    return $requete;
}


/**
 * Retourne le texte de la requete select concernant les elements hors forfait 
 * d'un utilisateur pour un mois donnes. 
 * 
 * La requete de selection fournie permettra d'obtenir l'id, la date, le libelle 
 * et le montant des elements hors forfait de la fiche de frais de l'utilisateur
 * d'id $idUtilisateur pour le mois $mois    
 * @param string $unMois mois demande (MMAAAA)
 * @param string $unIdUtilisateur id utilisateur  
 * @return string texte de la requete select
 */                                                 
function obtenirReqEltsHorsForfaitFicheFrais() {
    $requete = "select id, date, libelle, montant from LigneFraisHorsForfait where idUtilisateur= ?and mois= ?";
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
    $requete = "delete from LigneFraisHorsForfait where id = ?";
    $delete = $idCnx->prepare($requete);
    $delete->execute([$unIdLigneHF]);
}

/**
 * Ajoute une nouvelle ligne hors forfait.
 * Insere dans la BD la ligne hors forfait de libelle $unLibelleHF du montant 
 * $unMontantHF ayant eu lieu à la date $uneDateHF pour la fiche de frais du mois
 * $unMois de l'utilisateur d'id $unIdUtilisateur
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (AAMMMM)
 * @param string $unIdUtilisateur id de l'utilisateur
 * @param string $uneDateHF date du frais hors forfait
 * @param string $unLibelleHF libelle du frais hors forfait 
 * @param double $unMontantHF montant du frais hors forfait
 * @return void
 */
function ajouterLigneHF($idCnx, $unMois, $unIdUtilisateur, $uneDateHF, $unLibelleHF, $unMontantHF) {
    $uneDateHF = convertirDateFrancaisVersAnglais($uneDateHF);
    $unMois = $unMois;
    $requete = "insert into LigneFraisHorsForfait(idUtilisateur, mois, date, libelle, montant) 
                values (:unIdUtilisateur, :unMois, :uneDateHF, :unLibelleHF, :unMontantHF)";
    $insert = $idCnx->prepare($requete);
    $params = [
        ':unIdUtilisateur' => $unIdUtilisateur,
        ':unMois'          => $unMois,
        ':uneDateHF'       => $uneDateHF,
        ':unLibelleHF'     => $unLibelleHF,
        ':unMontantHF'     => $unMontantHF
    ];
    $insert->execute($params);
}

/**
 * Modifie les quantites des elements forfaitises d'une fiche de frais. 
 * Met à jour les elements forfaitises contenus  
 * dans $desEltsForfaits pour l'utilisateur $unIdUtilisateur et
 * le mois $unMois dans la table LigneFraisForfait, apres avoir filtre 
 * (annule l'effet de certains caracteres consideres comme speciaux par 
 *  MySql) chaque donnee   
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demande (MMAAAA) 
 * @param string $unIdUtilisateur  id utilisateur
 * @param array $desEltsForfait tableau des quantites des elements hors forfait
 * avec pour cles les identifiants des frais forfaitises 
 * @return void  
 */
function modifierEltsForfait($idCnx, $unMois, $unIdUtilisateur, $desEltsForfait) {
    foreach ($desEltsForfait as $idFraisForfait => $quantite) {
        $requete = "update LigneFraisForfait set quantite = :quantite where idUtilisateur = :unIdUtilisateur and mois = :unMois and idFraisForfait= :idFraisForfait";
        $update = $idCnx->prepare($requete);
        $params = [
            ':quantite'        => $quantite,
            ':unIdUtilisateur' => $unIdUtilisateur,
            ':unMois'          => $unMois,
            ':idFraisForfait'  => $idFraisForfait
        ];
        $update->execute($params);
    }
}

/**
 * Met à jour le véhicule d'un utilisateur
 * @param PDO $idConnexion
 * @param string $idUtilisateur
 * @param string $vehiculeSelectionne
 */
function majVehicule($idConnexion, $idUtilisateur, $vehiculeSelectionne) {
    $requete = "update utilisateur set type_vehicule = ? where id = ?";
    $update  = $idConnexion->prepare($requete);
    $params  = [$vehiculeSelectionne, $idUtilisateur];
    $update->execute($params);
}

/**
 * Permet d'obtenir un utilisateur et ses données.
 * @param PDO $idConnexion
 * @param string $idUtilisateur
 */
function obtenirUtilisateur($idConnexion, $idUtilisateur) {
    $requete = "select * from utilisateur where id = ?";
    $utilisateur = $idConnexion->prepare($requete);
    $utilisateur->execute([$idUtilisateur]);
    $utilisateur = $utilisateur->fetch(PDO::FETCH_ASSOC);
    return $utilisateur;
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
    $req = 'SELECT * FROM `utilisateur` WHERE `login` = ?';
    $utilisateur = $idCnx->prepare($req);
    $utilisateur->execute([$unLogin]);
    $mdpBdd = false;
    if ($utilisateur) {
        $ligne = $utilisateur->fetch(PDO::FETCH_ASSOC);
        if(!password_verify($unMdp, $ligne['mdp'])) {
            $ligne = false;
        }
        $utilisateur->closeCursor();
    }
    return $ligne;
}

/**
 * Modifie l'etat et la date de modification d'une fiche de frais
 
 * Met à jour l'etat de la fiche de frais de l'utilisateur $unIdUtilisateur pour
 * le mois $unMois à la nouvelle valeur $unEtat et passe la date de modif à 
 * la date d'aujourd'hui
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdUtilisateur 
 * @param string $unMois mois sous la forme aaaamm
 * @return void 
 */
function modifierEtatFicheFrais($idCnx, $unMois, $unIdUtilisateur, $unEtat) {
    $requete = "update FicheFrais set idEtat =:unEtat, dateModif = now() where idUtilisateur :unIdUtilisateur and mois = :unMois";
    $update = $idCnx->prepare($requete);
    $params = [
        ':unEtat'          => $unEtat,
        ':unIdUtilisateur' => $unIdUtilisateur,
        ':unMois'          => $unMois
    ];
    $update->execute($params);
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
    $requete = "update LigneFraisHorsForfait set libelle = :libelleFraisHorsForfait, date = :dateFraisHorsForfait, montant = :montantFraisHorsForfait where id = :idFraisHorsForfait";
    $update = $idCnx->prepare($requete);
    $params = [
        ':libelleFraisHorsForfait' => $libelleFraisHorsForfait,
        ':dateFraisHorsForfait'    => convertirDateFrancaisVersAnglais($dateFraisHorsForfait),
        ':montantFraisHorsForfait' => $montantFraisHorsForfait,
        ':idFraisHorsForfait'      => $idFraisHorsForfait
    ];
    $update->execute($params);
}

/**
 * Modifie le nombre de justificatifs d'une fiche de frais
 *
 * Met à jour le nombre de justificatifs de la fiche de frais de l'utilisateur $unIdUtilisateur pour
 * le mois $unMois à la nouvelle valeur $nbJustificatifs
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdUtilisateur 
 * @param string $unMois mois sous la forme aaaamm
 * @param integer $nbJustificatifs
 * @return void 
 */
function modifierNbJustificatifsFicheFrais($idCnx, $unMois, $unIdUtilisateur, $nbJustificatifs) {
    $requete = "update FicheFrais set nbJustificatifs = :nbJustificatifs where idUtilisateur = :unIdUtilisateur and mois = :unMois";
    $update = $idCnx->prepare($requete);
    $params = [
        ':nbJustificatifs' => $nbJustificatifs,
        ':unIdUtilisateur' => $unIdUtilisateur,
        ':unMois'          => $unMois,
    ];
    $update->execute($params);
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
    $query = $idCnx->prepare("CALL reporterLigneFraisHF(?);");
    $query->execute([$unIdLigneHF]);
}

/**
 * Cloture les fiches de frais antérieur au mois $unMois
 *
 * Cloture les fiches de frais antérieur au mois $unMois
 * et au besoin, créer une nouvelle de fiche de frais pour le mois courant
 * @param resource $idCnx identifiant de connexion
  * @param string $unMois mois sous la forme aaaamm
  * @return void 
 */
function cloturerFichesFrais($idCnx, $unMois) {
    $req = "SELECT idUtilisateur, mois FROM ficheFrais WHERE idEtat = 'CR' AND CAST(mois AS unsigned) < ? ";
    $idJeuFichesFrais = $idCnx->prepare($req);
    $idJeuFichesFrais->execute([$unMois]);
    while ($lgFicheFrais = $idJeuFichesFrais->fetch(PDO::FETCH_ASSOC)) {
        modifierEtatFicheFrais($idCnx, $lgFicheFrais['mois'], $lgFicheFrais['idUtilisateur'], 'CL');
        // Vérification de l'existence de la fiche de frais pour le mois courant
        $existeFicheFrais = existeFicheFrais($idCnx, $unMois, $lgFicheFrais['idUtilisateur']);
        // si elle n'existe pas, on la crée avec les éléments de frais forfaitisés à 0
        if (!$existeFicheFrais) {
            ajouterFicheFrais($idCnx, $unMois, $lgFicheFrais['idUtilisateur']);
        }
    }
}

?>