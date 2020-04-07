<?php
/** 
 * Regroupe les fonctions utilitaires et de gestion des erreurs.
 * @package default
 * @todo  fonction estMoisValide a definir completement ou a supprimer
 */

/** 
 * Fournit le libelle en français correspondant a un numero de mois.                     
 *
 * Fournit le libelle français du mois de numero $unNoMois.
 * Retourne une chaîne vide si le numero n'est pas compris dans l'intervalle [1,12].
 * @param int numero de mois
 * @return string identifiant de connexion
 */
function obtenirLibelleMois($unNoMois) {
    $tabLibelles = array(1=>"Janvier", 
                            "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet",
                            "Août", "Septembre", "Octobre", "Novembre", "Decembre");
    $libelle="";
    if ( $unNoMois >=1 && $unNoMois <= 12 ) {
        $libelle = $tabLibelles[$unNoMois];
    }
    return $libelle;
}

/** 
 * Verifie si une chaîne fournie est bien une date valide, au format JJ/MM/AAAA.                     
 * 
 * Retrourne true si la chaîne $date est une date valide, au format JJ/MM/AAAA, false sinon.
 * @param string date a verifier
 * @return boolean succes ou echec
 */ 
function estDate($date) {
	$tabDate = explode('/',$date);
	if (count($tabDate) != 3) {
	    $dateOK = false;
    }
    elseif (!verifierEntiersPositifs($tabDate)) {
        $dateOK = false;
    }
    elseif (!checkdate($tabDate[1], $tabDate[0], $tabDate[2])) {
        $dateOK = false;
    }
    else {
        $dateOK = true;
    }
	return $dateOK;
}

/**
 * Transforme une date au format français jj/mm/aaaa vers le format anglais aaaa-mm-jj
 * @param $date au format  jj/mm/aaaa
 * @return string la date au format anglais aaaa-mm-jj
*/
function convertirDateFrancaisVersAnglais($date){
	@list($jour,$mois,$annee) = explode('/',$date);
	return date("Y-m-d", mktime(0, 0, 0, $mois, $jour, $annee));
}

/**
 * Transforme une date au format format anglais aaaa-mm-jj vers le format 
 * français jj/mm/aaaa 
 * @param $date au format  aaaa-mm-jj
 * @return string la date au format format français jj/mm/aaaa
*/
function convertirDateAnglaisVersFrancais($date){
    @list($annee,$mois,$jour) = explode('-',$date);
	return date("d/m/Y", mktime(0, 0, 0, $mois, $jour, $annee));
}

/**
 * Indique si une date est incluse ou non dans l'annee ecoulee.
 * 
 * Retourne true si la date $date est comprise entre la date du jour moins un an et la 
 * la date du jour. False sinon.   
 * @param $date date au format jj/mm/aaaa
 * @return boolean succes ou echec
*/
function estDansAnneeEcoulee($date) {
	$dateAnglais = convertirDateFrancaisVersAnglais($date);
	$dateDuJourAnglais = date("Y-m-d");
	$dateDuJourMoinsUnAnAnglais = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1));
	return ($dateAnglais >= $dateDuJourMoinsUnAnAnglais) && ($dateAnglais <= $dateDuJourAnglais);
}

/** 
 * Verifie si une chaîne fournie est bien numerique entiere positive.                     
 * 
 * Retrourne true si la valeur transmise $valeur ne contient pas d'autres 
 * caracteres que des chiffres, false sinon.
 * @param string chaîne a verifier
 * @return boolean succes ou echec
 */ 
function estEntierPositif($valeur) {
    return preg_match("/[^0-9]/", $valeur) == 0;
}

/** 
 * Verifie que chaque valeur est bien renseignee et numerique entiere positive.
 *  
 * Renvoie la valeur booleenne true si toutes les valeurs sont bien renseignees et
 * numeriques entieres positives. False si l'une d'elles ne l'est pas.
 * @param array $lesValeurs tableau des valeurs
 * @return booleen succes ou echec
 */ 
function verifierEntiersPositifs($lesValeurs){
    $ok = true;     
    foreach ( $lesValeurs as $val ) {
        if ($val=="" || ! estEntierPositif($val) ) {
            $ok = false;
        }
    }
    return $ok; 
}

/** 
 * Fournit la valeur d'une donnee transmise par la methode get (url).                    
 * 
 * Retourne la valeur de la donnee portant le nom $nomDonnee reçue dans l'url, 
 * $valDefaut si aucune donnee de nom $nomDonnee dans l'url 
 * @param string nom de la donnee
 * @param string valeur par defaut 
 * @return string valeur de la donnee
 */ 
function lireDonneeUrl($nomDonnee, $valDefaut="") {
    if ( isset($_GET[$nomDonnee]) ) {
        $val = $_GET[$nomDonnee];
    }
    else {
        $val = $valDefaut;
    }
    return $val;
}

/** 
 * Fournit la valeur d'une donnee transmise par la methode post 
 *  (corps de la requete HTTP).                    
 * 
 * Retourne la valeur de la donnee portant le nom $nomDonnee reçue dans le corps de la requete http, 
 * $valDefaut si aucune donnee de nom $nomDonnee dans le corps de requete
 * @param string nom de la donnee
 * @param string valeur par defaut 
 * @return string valeur de la donnee
 */ 
function lireDonneePost($nomDonnee, $valDefaut="") {
    if ( isset($_POST[$nomDonnee]) ) {
        $val = $_POST[$nomDonnee];
    }
    else {
        $val = $valDefaut;
    }
    return $val;
}

/** 
 * Fournit la valeur d'une donnee transmise par la methode get (url) ou post 
 *  (corps de la requete HTTP).                    
 * 
 * Retourne la valeur de la donnee portant le nom $nomDonnee
 * reçue dans l'url ou corps de requete, 
 * $valDefaut si aucune donnee de nom $nomDonnee ni dans l'url, ni dans corps.
 * Si le meme nom a ete transmis a la fois dans l'url et le corps de la requete,
 * c'est la valeur transmise par l'url qui est retournee.  
 * @param string nom de la donnee
 * @param string valeur par defaut 
 * @return string valeur de la donnee
 */ 
function lireDonnee($nomDonnee, $valDefaut="") {
    if ( isset($_GET[$nomDonnee]) ) {
        $val = $_GET[$nomDonnee];
    }
    elseif ( isset($_POST[$nomDonnee]) ) {
        $val = $_POST[$nomDonnee];
    }
    else {
        $val = $valDefaut;
    }
    return $val;
}

/** 
 * Ajoute un message dans le tableau des messages d'erreurs.                    
 * 
 * Ajoute le message $msg en fin de tableau $tabErr. Ce tableau est passe par 
 * reference afin que les modifications sur ce tableau soient visibles de l'appelant.  
 * @param array $tabErr  
 * @param string message
 * @return void
 */ 
function ajouterErreur(&$tabErr,$msg) {
    $tabErr[count($tabErr)]=$msg;
}

/** 
 * Retourne le nombre de messages d'erreurs enregistres.                    
 * 
 * Retourne le nombre de messages d'erreurs enregistres dans le tableau $tabErr. 
 * @param array $tabErr tableau des messages d'erreurs  
 * @return int nombre de messages d'erreurs
 */ 
function nbErreurs($tabErr) {
    return count($tabErr);
}
 
/** 
 * Fournit les messages d'erreurs sous forme d'une liste a puces HTML.                    
 * 
 * Retourne le source HTML, division contenant une liste a puces, d'apres les
 * messages d'erreurs contenus dans le tableau des messages d'erreurs $tabErr. 
 * @param array $tabErr tableau des messages d'erreurs  
 * @return string source html
 */ 
function toStringErreurs($tabErr) {
    $str = '<div class="erreur">';
    $str .= '<ul>';
    foreach($tabErr as $erreur){
        $str .= '<li>' . $erreur . '</li>';
	}
    $str .= '</ul>';
    $str .= '</div>';
    return $str;
} 

/** 
 * Echappe les caracteres consideres speciaux en HTML par les entites HTML correspondantes.
 *  
 * Renvoie une copie de la chaîne $str a laquelle les caracteres consideres speciaux
 * en HTML (tq la quote simple, le guillemet double, les chevrons), auront ete
 * remplaces par les entites HTML correspondantes. 
 * @param string $str chaîne a echapper
 * @return string chaîne echappee 
 */ 
function filtrerChainePourNavig($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/** 
 * Verifie la validite des donnees d'une ligne de frais hors forfait.
 *  
 * Renseigne le tableau des messages d'erreurs d'apres les erreurs rencontrees
 * sur chaque donnee d'une ligne de frais hors forfait : verifie que chaque 
 * donnee est bien renseignee, le montant est numerique positif, la date valide
 * et dans l'annee ecoulee.  
 * @param array $date date d'engagement de la ligne de frais HF
 * @param array $libelle libelle de la ligne de frais HF
 * @param array $montant montant de la ligne de frais HF
 * @param array $tabErrs tableau des messages d'erreurs passe par reference
 * @return void
 */ 
function verifierLigneFraisHF($date, $libelle, $montant, &$tabErrs) {
    // verification du libelle 
    if ($libelle == "") {
		ajouterErreur($tabErrs, "Le libelle doit etre renseigne.");
	}
	// verification du montant
	if ($montant == "") {
		ajouterErreur($tabErrs, "Le montant doit etre renseigne.");
	}
	elseif ( !is_numeric($montant) || $montant < 0 ) {
        ajouterErreur($tabErrs, "Le montant doit etre numerique positif.");
    }
    // verification de la date d'engagement
	if ($date == "") {
		ajouterErreur($tabErrs, "La date d'engagement doit etre renseignee.");
	}
	elseif (!estDate($date)) {
		ajouterErreur($tabErrs, "La date d'engagement doit etre valide au format JJ/MM/AAAA");
	}	
	elseif (!estDansAnneeEcoulee($date)) {
	    ajouterErreur($tabErrs,"La date d'engagement doit se situer dans l'annee ecoulee");
    }
}
?>