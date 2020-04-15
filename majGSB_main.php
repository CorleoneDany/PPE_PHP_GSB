 Programme d'actualisation des lignes des tables,  
 cette mise a jour peut prendre plusieurs minutes...
<?php
include("include/fct.inc.php");

/* Modification des parametres de connexion */

$serveur='mysql:host=localhost';
$bdd='dbname=gsb_frais';   		
$user='userGsb' ;    		
$mdp='Admin111' ;	

/* fin parametres*/

try {
	$pdo = new PDO($serveur.';'.$bdd, $user, $mdp);
	$pdo->query("SET CHARACTER SET utf8"); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'echec lors de la connexion : ' . $e->getMessage();
}

set_time_limit(0);
creationFichesFrais($pdo);
creationFraisForfait($pdo);
creationFraisHorsForfait($pdo);
majFicheFrais($pdo);

?>