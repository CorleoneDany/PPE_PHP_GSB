<?php

$repInclude = './include/';
require($repInclude . "_init.inc.php");

/* Modification des parametres de connexion */

$serveur = 'mysql:host=localhost';
$bdd     = 'dbname=gsb_frais'; 
$utf8    = 'charset=utf8';	
$user    = 'userGsb' ;    		
$mdp     = 'Admin111' ;

/* fin parametres*/

try {
	$pdo = new PDO($serveur.';'.$bdd.';'.$utf8, $user, $mdp);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'echec lors de la connexion : ' . $e->getMessage();
}

$recupererUtilisateurs = $pdo->query('SELECT * FROM utilisateur');
$listeUtilisateurs     = [];
while($row = $recupererUtilisateurs->fetch(PDO::FETCH_ASSOC)) {
    $listeUtilisateurs[] = $row;
}


foreach($listeUtilisateurs as $utilisateur) {
    $pdo->query('UPDATE `utilisateur` set `mdp` = "' . password_hash($utilisateur['mdp'], PASSWORD_DEFAULT) . '" WHERE `id` = "' . $utilisateur['id'] . '"');
}


?>