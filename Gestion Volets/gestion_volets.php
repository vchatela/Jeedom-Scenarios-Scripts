<?


$red_color="#ff0000";
$orange_color="#ffa200";
$green_color="#00ff00";

function get_id_from_commandname($name){
	return cmd::byString('#'.$name.'#')->getId();
}

function get_id_from_scenarioname($name){
	return scenario::byString('#'.$name.'#')->getId();
}

function return_value_commandname($name){
	global $scenario;
	$scenario->setLog("$name valeur :" . cmd::byString("$name")->execCmd());
	return cmd::byString("$name")->execCmd();
}

function return_value_id($id){
	global $scenario;
	$scenario->setLog("$id valeur :" . cmd::byId("$id")->execCmd());
	return cmd::byId("$id")->execCmd();
}

function send_notification_via_scenario($message,$topic,$color){
  	global $scenario;
 	$id_notif_scenario=8;
    $notif_scenario=scenario::byId($id_notif_scenario);
  	$titre=$scenario->getName();# scenario name

    #Récupérer les tags dans un scenario
    $tags = $notif_scenario->getTags();
    #Ajouter des tags
    $tags['#titre#'] = "Scenario : ".$titre;
    $tags['#message#'] = $message;
    $tags['#topic#'] = $topic;
	$tags['#couleur']=$color;
  	
    $scenario->setLog("Notification envoyée : ".$message);
  
    #Passer les tags à un sous-scenario et le lancer
    $notif_scenario->setTags($tags);
    $notif_scenario->launch(); 
}

function set_position_wait_status($cible_position_volet,$id_position_volet, $id_etat_volet, $id_rafraichir_volet){
	global $scenario;
	# 3. Envoi commande
	$options = array('slider' => "$cible_position_volet");
	cmd::byId($id_position_volet)->execCmd($options);

	# 4. Wait de la position finale
	$condition = '#'.$id_etat_volet.'# == '.$cible_position_volet;
	$scenario->setLog('Attente de '.$condition);
	if (scenarioExpression::wait($condition,60)) {
	  $scenario->setLog('[OK] la condition a été remplie');
	}
	else {
		$scenario->setLog('[NOK] Timeout dépassé !!!');
	}

	cmd::byId($id_rafraichir_volet)->execCmd();
	$final_pos = return_value_id($id_etat_volet);
	$scenario->setLog('Etat Volet Bureau :'.$final_pos);
	return $final_pos;
}

// récup tags
$tags = $scenario->getTags();

# récupération tag(commande)
if(array_key_exists('#commande#',$tags)){
	$commande=str_replace('"','',$tags['#commande#']);
	$scenario->setLog("Commande : ".$commande);
} else {
	# error no tag commande ! 
	# TODO : jolie sortie du script
	return -1;
}

# récupération tag(volet)
if(array_key_exists('#volet#',$tags)){
	$volet=str_replace('"','',$tags['#volet#']);
	$scenario->setLog("Volet : ".$volet);
} else {
	# error no tag volet ! 
	# TODO : jolie sortie du script
	return -1;
}

if(strcmp($volet, "bureau") == 0){
	$cmd_scenario_arret = "[Volets][Bureau][Arrêter Volet Bureau]";
	$cmd_etat_volet = "[Bureau][8FGR-223 Volet roulant][Etat]";
	$cmd_position_volet = "[Bureau][8FGR-223 Volet roulant][Positionnement]";
	$cmd_rafraichir_volet = "[Bureau][8FGR-223 Volet roulant][Rafraichir]";
} else if(strcmp($volet, "chambre") == 0){
	$cmd_scenario_arret = "[Volets][Chambre][Arrêter Volet Chambre]";
	$cmd_etat_volet = "[Chambre][11FGR-223 Volet roulant][Etat]";
	$cmd_position_volet = "[Chambre][11FGR-223 Volet roulant][Positionnement]";
	$cmd_rafraichir_volet = "[Chambre][11FGR-223 Volet roulant][Rafraichir]";
} else if(strcmp($volet, "tv") == 0){
	$cmd_scenario_arret = "[Volets][Séjour][Arrêter Volet TV]";
	$cmd_etat_volet = "[Séjour][TV 10FGR-223 Volet roulant ][Etat]";
	$cmd_position_volet = "[Séjour][TV 10FGR-223 Volet roulant ][Positionnement]";
	$cmd_rafraichir_volet = "[Séjour][TV 10FGR-223 Volet roulant ][Rafraichir]";
} else if(strcmp($volet, "baie-vitree") == 0){
	$cmd_scenario_arret = "[Volets][Séjour][Arrêter Volet Baie Vitrée]";
	$cmd_etat_volet = "[Séjour][Baie Vitrée 9FGR-223 Volet roulant][Etat]";
	$cmd_position_volet = "[Séjour][Baie Vitrée 9FGR-223 Volet roulant][Positionnement]";
	$cmd_rafraichir_volet = "[Séjour][Baie Vitrée 9FGR-223 Volet roulant][Rafraichir]";
} else {
	$scenario->setLog('Erreur sur le choix du volet : '.$volet);
}

$id_scenario_arret = get_id_from_scenarioname($cmd_scenario_arret);
$id_etat_volet = get_id_from_commandname($cmd_etat_volet);
$id_position_volet = get_id_from_commandname($cmd_position_volet);
$id_rafraichir_volet = get_id_from_commandname($cmd_rafraichir_volet);


## 1. On attend que plus rien ne tourne, et on récupère l'état du volet 

$condition = 'scenario(#'.$id_scenario_arret.'#) == 0';
$condition_log = 'scenario(#'.$cmd_scenario_arret.'#) == 0';
$scenario->setLog('Attente de '.$condition_log);
if (scenarioExpression::wait($condition,10)) {
  $scenario->setLog('[OK] la condition a été remplie');
}
else {
	$scenario->setLog('[NOK] Timeout dépassé !!!');
}

$etat_volet_debut = return_value_id($id_etat_volet);
$scenario->setLog('Etat Volet Bureau debut :'.$etat_volet_debut);

## 2. Gestion ouvrir/fermer
if(strcmp($commande, "ouvrir") == 0){
	$cible_position_volet=99;
} else if(strcmp($commande, "fermer") == 0){
	$cible_position_volet=0;
} else if(strcmp($commande, "fermer-redboule") == 0){
	$cible_position_volet=50;
} else {
	send_notification_via_scenario("[Volet][".$volet."][".$commande."] Commande en erreur : commande=tag(commande,'') n'existe pas",'alerte',$red_color);
}

# 3. Envoi position + wait + retourne position finale
$etat_volet_step1 = set_position_wait_status($cible_position_volet,$id_position_volet, $id_etat_volet, $id_rafraichir_volet);

# 5. Vérification des différences
if($etat_volet_step1 == $cible_position_volet){
	# 5.1 ok
} else if($etat_volet_step1 == $etat_volet_debut){
	# 5.2 rien n'a bougé
	
	send_notification_via_scenario("[Volet][".$volet."][".$commande."] Erreur
Nouvelle tentative...",'alerte',$orange_color);

	# on relance
	$etat_volet_step2 = set_position_wait_status($cible_position_volet,$id_position_volet, $id_etat_volet, $id_rafraichir_volet);
	
	# check final
	if($etat_volet_step2 == $cible_position_volet){
		send_notification_via_scenario("[Volet][".$volet."][".$commande."] ... réussi ! 
Rappel : 99 ouvert -- 50 mi ouvert redboule -- 0 fermé
Statut : ".$etat_volet_step2,'alerte',$green_color);

	} else {
		send_notification_via_scenario("[Volet][".$volet."][".$commande."] Erreur
Rappel : 99 ouvert -- 50 mi ouvert redboule -- 0 fermé
Etat des volets :
- Bureau : ".return_value_commandname("#[Bureau][8FGR-223 Volet roulant][Etat]#")."
- TV : ".return_value_commandname("#[Séjour][TV 10FGR-223 Volet roulant ][Etat]#")."
- Baie Vitrée : ".return_value_commandname("#[Séjour][Baie Vitrée 9FGR-223 Volet roulant][Etat]#")."
- Chambre : ".return_value_commandname("#[Chambre][11FGR-223 Volet roulant][Etat]#"),'alerte',$red_color);
	}
	
} else {
	# 5.3 Nouvel état 
	send_notification_via_scenario("[Volet][".$volet."][".$commande."] Erreur
Rappel : 99 ouvert -- 50 mi ouvert redboule -- 0 fermé
Etat demandé : ".$commande." -- valeur : ".$cible_position_volet."
Au début du scénario : ".$etat_volet_debut."
Actuellement : ".$etat_volet_step1,'alerte',$orange_color);
}