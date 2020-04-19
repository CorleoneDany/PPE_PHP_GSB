<?php  
/** 
 * Script de contr�le et d'affichage du cas d'utilisation "Se d�connecter"
 * @package default
 * @todo  RAS
 */
  $repInclude = './include/';
  require($repInclude . "_init.inc.php");
  
  deconnecterUtilisateur() ;  
  header("Location:cSeConnecter.php");
  
?>