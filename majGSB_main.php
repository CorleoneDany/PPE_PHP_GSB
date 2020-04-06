 Programme d'actualisation des lignes des tables,  
 cette mise à jour peut prendre plusieurs minutes...
<?php
include("include/fct.inc.php");

/* Modification des paramètres de connexion */

$serveur='mysql:host=localhost';
$bdd='dbname=gsb_frais';   		
$user='userGsb' ;    		
$mdp='secret' ;	

/* fin paramètres*/

try {
	$pdo = new PDO($serveur.';'.$bdd, $user, $mdp);
	$pdo->query("SET CHARACTER SET utf8"); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Échec lors de la connexion : ' . $e->getMessage();
}

set_time_limit(0);
creationFichesFrais($pdo);
creationFraisForfait($pdo);
creationFraisHorsForfait($pdo);
majFicheFrais($pdo);

?>