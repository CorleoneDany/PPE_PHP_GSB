<?php
/** 
 * Regroupe les fonctions de gestion d'une session utilisateur.
 * @package default
 * @todo  RAS
 */

/** 
 * Demarre ou poursuit une session.                     
 *
 * @return void
 */
function initSession() {
    session_start();
}

/** 
 * Fournit l'id du visiteur connecte.                     
 *
 * Retourne l'id du visiteur connecte, une chaîne vide si pas de visiteur connecte.
 * @return string id du visiteur connecte
 */
function obtenirIdUserConnecte() {
    $ident="";
    if ( isset($_SESSION["loginUser"]) ) {
        $ident = (isset($_SESSION["idUser"])) ? $_SESSION["idUser"] : '';   
    }  
    return $ident ;
}

/**
 * Conserve en variables session les informations du visiteur connecte
 * 
 * Conserve en variables session l'id $id et le login $login du visiteur connecte
 * @param string id du visiteur
 * @param string login du visiteur
 * @return void    
 */
function affecterInfosConnecte($id, $login) {
    $_SESSION["idUser"] = $id;
    $_SESSION["loginUser"] = $login;
}

/** 
 * Deconnecte le visiteur qui s'est identifie sur le site.                     
 *
 * @return void
 */
function deconnecterVisiteur() {
    unset($_SESSION["idUser"]);
    unset($_SESSION["loginUser"]);
}

/** 
 * Verifie si un visiteur s'est connecte sur le site.                     
 *
 * Retourne true si un visiteur s'est identifie sur le site, false sinon. 
 * @return boolean echec ou succes
 */
function estVisiteurConnecte() {
    // actuellement il n'y a que les visiteurs qui se connectent
    return isset($_SESSION["loginUser"]);
}
?>