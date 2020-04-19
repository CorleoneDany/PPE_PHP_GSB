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
 * Fournit l'id de l'utilisateur connecte.                     
 *
 * Retourne l'id de l'utilisateur connecte, une chaîne vide si pas d'utilisateur connecte.
 * @return string id de l'utilisateur connecte
 */
function obtenirIdUserConnecte() {
    $ident="";
    if ( isset($_SESSION["loginUser"]) ) {
        $ident = (isset($_SESSION["idUser"])) ? $_SESSION["idUser"] : '';   
    }  
    return $ident ;
}

/**
 * Conserve en variables session les informations de l'utilisateur connecte
 * 
 * Conserve en variables session l'id $id et le login $login de l'utilisateur connecte
 * @param string id de l'utilisateur
 * @param string login de l'utilisateur
 * @return void    
 */
function affecterInfosConnecte($id, $login) {
    $_SESSION["idUser"] = $id;
    $_SESSION["loginUser"] = $login;
}

/** 
 * Deconnecte l'utilisateur qui s'est identifie sur le site.                     
 *
 * @return void
 */
function deconnecterUtilisateur() {
    unset($_SESSION["idUser"]);
    unset($_SESSION["loginUser"]);
}

/** 
 * Verifie si un utilisateur s'est connecte sur le site.                     
 *
 * Retourne true si un utilisateur s'est identifie sur le site, false sinon. 
 * @return boolean echec ou succes
 */
function estUtilisateurConnecte() {
    // actuellement il n'y a que les utilisateurs qui se connectent
    return isset($_SESSION["loginUser"]);
}
?>