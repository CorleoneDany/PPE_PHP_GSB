//<![CDATA[
function afficheMsgInfosForfaitAActualisees() {
    document.getElementById('msgFraisForfait').style.display = "block";
    document.getElementById('actionsFraisForfait').style.display = "inline";
    document.getElementById('lkActualiserLigneFraisForfait').style.display = "inline";
    document.getElementById('lkReinitialiserLigneFraisForfait').style.display = "inline";
}
function afficheMsgInfosHorsForfaitAActualisees(idAMontrer) {
    document.getElementById('msgFraisHorsForfait' + idAMontrer).style.display = "block";
    document.getElementById('lkActualiserLigneFraisHF' + idAMontrer).style.display = "inline";
    document.getElementById('lkReinitialiserLigneFraisHF' + idAMontrer).style.display = "inline";
}
function afficheMsgNbJustificatifs() {
    document.getElementById('msgNbJustificatifs').style.display = "block";
    document.getElementById('lkActualiserNbJustificatifs').style.display = "inline";
    document.getElementById('lkReinitialiserNbJustificatifs').style.display = "inline";
}
function reinitialiserLigneFraisForfait() {
    document.getElementById('formFraisForfait').reset();
    document.getElementById('msgFraisForfait').style.display = "none";
    document.getElementById('actionsFraisForfait').style.display = "none";        
}
function reinitialiserLigneFraisHorsForfait(idElementHF) {
    document.getElementById('formFraisHorsForfait' + idElementHF).reset();
    document.getElementById('msgFraisHorsForfait' + idElementHF).style.display = "none"; 
    document.getElementById('lkActualiserLigneFraisHF' + idElementHF).style.display = "none";
    document.getElementById('lkReinitialiserLigneFraisHF' + idElementHF).style.display = "none";
}
function reinitialiserNbJustificatifs() {
    document.getElementById('formNbJustificatifs').reset();
    document.getElementById('msgNbJustificatifs').style.display = "none"; 
    document.getElementById('lkActualiserNbJustificatifs').style.display = "none";
    document.getElementById('lkReinitialiserNbJustificatifs').style.display = "none";
}
function changerVisiteur(idUtilisateur) {
    if(getModifsEnCours()) {
        if(confirm('Attention, des modifications n\'ont pas été actualisées. Souhaitez-vous vraiment changer de visiteur et perdre toutes les modifications non actualisées ?')) {
            if(!idUtilisateur) {
                // C'est le bouton "Changer de visiteur" qui a été utilisé
                // On recharge la page comme si on avait cliqué dans le sommaire
                window.location = "./cValidFichesFrais.php";
            } else {
                // On change de visiteur avec le visiteur choisi
                document.getElementById('formChoixVisiteur').submit();
            }
        }
    } else {
        if(!idUtilisateur) {
            // C'est le bouton "Changer de visiteur" qui a été utilisé
            // On recharge la page comme si on avait cliqué dans le sommaire
            window.location = "./cValidFichesFrais.php";
        } else {
            // On change de visiteur avec le visiteur choisi
            document.getElementById('formChoixVisiteur').submit();
        }
    }
}
function getModifsEnCours() {
    var modif = false;
    // Si cet élément existe, c'est que l'on a bien dépassé le stade du choix de visiteur
    if(document.getElementById('msgFraisForfait')) {
        // Modification en cours sur les frais forfaitisés ?
        if(document.getElementById('msgFraisForfait').style.display == "block") {
            modif = true;
            return modif;
        }
        // Modification en cours sur les frais hors forfaits ?
        var forms = document.getElementsByTagName('form');
        for (var cpt = 0; cpt < forms.length; cpt++) {
            var unForm = forms[cpt];
            if (unForm.id) {
                if(unForm.id.search('formFraisHorsForfait') != -1) {
                    if(document.getElementById('msgFraisHorsForfait' + unForm.id.replace('formFraisHorsForfait',"")).style.display == "block") {
                        modif = true;
                        return modif;
                    }
                }
            }   
        }
        // Modification en cours sur le nombre de justificatifs ?
        if(document.getElementById('msgNbJustificatifs').style.display == "block") {
            modif = true;
            return modif;
        }
    }
    return modif
}
function actualiserLigneFraisForfait(rep,nui,etp,km) {
    // Trouver quelles sont les mises à jour à réaliser
    var modif = false;
    var txtModifs = '';
    if (rep != document.getElementById('idREP').value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne quantité de repas : ' + rep + ' \n \'--> Nouvelle quantité : ' + document.getElementById('idREP').value;
    }
    if (nui != document.getElementById('idNUI').value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne quantité de nuitées : ' + nui + ' \n \'--> Nouvelle quantité : ' + document.getElementById('idNUI').value;
    }
    if (etp != document.getElementById('idETP').value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne quantité d\'étapes : ' + etp + ' \n \'--> Nouvelle quantité : ' + document.getElementById('idETP').value;
    }
    if (km != document.getElementById('idKM').value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne quantité de kilomètres : ' + km + ' \n \'--> Nouvelle quantité : ' + document.getElementById('idKM').value;
    }
    if (modif) {
        var question = 'Souhaitez-vous vraiment effectuer la ou les modifications suivantes cette ligne de frais forfaitisés ?' + txtModifs;
        if (confirm(question)) {
            document.getElementById('formFraisForfait').submit();
        }
    } else {
        alert('Aucune modification à actualiser...');
        reinitialiserLigneFraisForfait();
    }
}
function actualiserLigneFraisHF(idElementHF,dateElementHF,libelleElementHF,montantElementHF) {
    // Trouver quelles sont les mises à jour à réaliser
    var modif = false;
    var txtModifs = '';
    if (dateElementHF != document.getElementById('idDate' + idElementHF).value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne date : "' + dateElementHF + '" \n \'--> Nouvelle date : "' + document.getElementById('idDate' + idElementHF).value + '"';
    }
    if (libelleElementHF != document.getElementById('idLibelle' + idElementHF).value) {
        // Modification portant sur le libellé
        modif = true;
        txtModifs += '\n\nAncien libellé : "' + libelleElementHF + '" \n \'--> Nouveau libellé : ' + document.getElementById('idLibelle' + idElementHF).value + '"';
    }
    if (montantElementHF != document.getElementById('idMontant' + idElementHF).value) {
        // Modification portant sur le montant
        modif = true;
        txtModifs += '\n\nAncien montant : ' + montantElementHF + '\u20AC \n \'--> Nouveau montant : ' + document.getElementById('idMontant' + idElementHF).value + '\u20AC';
    }
    // Demande de confirmation s'il y a des modifications à réellement actualiser
    if (modif) {
        var question = 'Souhaitez-vous vraiment effectuer la ou les modifications suivantes cette ligne de frais hors forfait ?' + txtModifs;
        if (confirm(question)) {
            document.getElementById('formFraisHorsForfait' + idElementHF).submit();
        }
    } else {
        alert('Aucune modification à actualiser...');
        reinitialiserLigneFraisHorsForfait(idElementHF);
    }
}
function actualiserNbJustificatifs(nbJustificatifs) {
    if (confirm('Souhaitez-vous vraiment passer le nombre de justificatifs de ' + nbJustificatifs + ' à ' + document.getElementById('idNbJustificatifs').value + ' ?')) {
        document.getElementById('formNbJustificatifs').submit();
    }
}
function reporterLigneFraisHF(idElementHF) {
    var question = 'Souhaitez-vous vraiment reporter la ligne de frais hors forfait du ' + document.getElementById('idDate' + idElementHF).value ;
    question += ' portant le libellé "' + document.getElementById('idLibelle' + idElementHF).value + '"';
    question += ' pour un montant de ' + document.getElementById('idMontant' + idElementHF).value + '\u20AC ?';
    if (confirm(question)) {
        // On passe par l'étape "reporterLigneFraisHF"
        document.getElementById('idEtape' + idElementHF).value = 'reporterLigneFraisHF';
        document.getElementById('formFraisHorsForfait' + idElementHF).submit();
    }
}
function refuseLigneFraisHF(idElementHF) {
    var question = 'Souhaitez-vous vraiment supprimer la ligne de frais hors forfait du ' + document.getElementById('idDate' + idElementHF).value ;
    question += ' portant le libellé "' + document.getElementById('idLibelle' + idElementHF).value + '"';
    question += ' pour un montant de ' + document.getElementById('idMontant' + idElementHF).value + '\u20AC ?';
    if (confirm(question)) {
        // On ajoute en début de libelle le texte "REFUSÉ : "
        document.getElementById('idLibelle' + idElementHF).value = 'REFUSÉ : ' + document.getElementById('idLibelle' + idElementHF).value;
        document.getElementById('formFraisHorsForfait' + idElementHF).submit();
    }
}
function reintegrerLigneFraisHF(idElementHF) {
    var question = 'Souhaitez-vous vraiment réintégrer la ligne de frais hors forfait du ' + document.getElementById('idDate' + idElementHF).value ;
    question += ' portant le libellé "' + document.getElementById('idLibelle' + idElementHF).value.replace('REFUSÉ : ',"") + '"';
    question += ' pour un montant de ' + document.getElementById('idMontant' + idElementHF).value + '\u20AC ?';
    if (confirm(question)) {
        // On retire en début de libelle le texte "REFUSÉ : "
        document.getElementById('idLibelle' + idElementHF).value = document.getElementById('idLibelle' + idElementHF).value.replace('REFUSÉ : ',"");
        document.getElementById('formFraisHorsForfait' + idElementHF).submit();
    }
}
function validerFiche() {
    var nbRefus = 0;
    var nbValid = 0;
    var forms = document.getElementsByTagName('form');
    for (var cpt = 0; cpt < forms.length; cpt++) {
        var unForm = forms[cpt];
        if (unForm.id) {
            if(unForm.id.search('formFraisHorsForfait') != -1) {
                if(document.getElementById('idLibelle'+ unForm.id.replace('formFraisHorsForfait',"")).value.search('REFUSÉ : ') != -1) {
                    nbRefus++;
                } else {            
                    nbValid++;
                }
            }
        }   
    }
    // Vérification supplémentaire sur le nombre de justificatifs, qui au minimum doit au moins être égal au nombre de ligne de frais validées
    if ((nbValid) > document.getElementById('idNbJustificatifs').value) {
        alert('Attention, le nombre de justificatifs devrait être au minimum égal au nombre de ligne validées...');
    }
    else {
        var synthese = '\n\n Détails de la validation :';
        synthese += '\n - Refus : ' + nbRefus;
        synthese += '\n - Validation : ' + nbValid;
        if(getModifsEnCours()) {
            if(confirm('Attention, des modifications n\'ont pas été actualisées. Souhaitez-vous vraiment valider cette fiche et perdre toutes les modifications non actualisées ?')) {
                if(confirm('Une fois validée, cette fiche n\'apparaîtra plus dans les fiches à valider et vous ne pourrez plus la modifier. Souhaitez-vous valider tout de même cette fiche ?' + synthese)) {
                    document.getElementById('formValidFiche').submit();
                }
            }
        } else {
            if(confirm('Une fois validée, cette fiche n\'apparaîtra plus dans les fiches à valider et vous ne pourrez plus la modifier. Souhaitez-vous valider tout de même cette fiche ?' + synthese)) {
                document.getElementById('formValidFiche').submit();
            }
        }
    }
}
//]]>
