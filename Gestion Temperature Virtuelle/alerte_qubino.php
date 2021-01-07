<?
$data_base = array(
	"Séjour" => array(
		"Thermostat" => "[Séjour][Thermostat Séjour]",
		"Qubino" => "[Séjour][Gestionnaire dénergie QUBINO]"
		),
	"Bureau" => array(
		"Thermostat" => "[Bureau][Thermostat Bureau]",
		"Qubino" => "[Bureau][12ZMNHJD1 Fil Pilote]"
		),
	"Chambre" => array(
		"Thermostat" => "[Chambre][Thermostat Chambre]",
		"Qubino" => "[Chambre][5ZMNHJD1 Fil Pilote]"
		)
);

function send_notification($message){
  global $scenario;
  $queue_action = '[Notifications][Queue Alerte 20min][Ajouter]';
  
  // Ajout à la queue
  $cmd_option = array('title' => "", 'message' => $message);
  $scenario->setLog('Commande : '. $queue_action. '/ ' .json_encode($cmd_option));
  $cmd = cmd::byString('#'. $queue_action .'#');
  $cmd->execCmd($cmd_option);
}

function log_if_verbose($text){
	global $scenario;
	$LOG=1;
	if($LOG == 1){
		$scenario->setLog("$text");
    }
}

function notify_stop_success($piece,$status,$etat){
	$message="[".$piece."][Réussi]Tentative d'arrêt forcé réussie du QUBINO: 
Thermostat :  ".$status."
Gestionnaire Energie : ".$etat." (0 : OFF - 255 : ON)";
	send_notification($message);
}

function notify_stop_error($piece,$status,$etat){
	$message="[".$piece."][ERREUR]Tentative d'arrêt forcé échouée du QUBINO: 
Thermostat :  ".$status."
Gestionnaire Energie : ".$etat." (0 : OFF - 255 : ON)";
	send_notification($message);
}

function notify_start_success($piece,$status,$etat){
	$message="[".$piece."][Réussi]Tentative de démarrage forcé réussie du QUBINO: 
Thermostat :  ".$status."
Gestionnaire Energie : ".$etat." (0 : OFF - 255 : ON)";
	send_notification($message);
}

function notify_start_error($piece,$status,$etat){
	$message="[".$piece."][ERREUR]Tentative de démarrage forcé échouée du QUBINO: 
Thermostat :  ".$status."
Gestionnaire Energie : ".$etat." (0 : OFF - 255 : ON)";
	send_notification($message);
}

function start_qubino($qubino){
	run_command("#".$qubino."[Confort]#");	
}

function stop_qubino($qubino){
	run_command("#".$qubino."[Arret]#");	
}

function set_value_commandname($name,$value){
	cmd::byString($name)->event($value);
}

function return_value_commandname($name){
	log_if_verbose("$name valeur :" . cmd::byString("$name")->execCmd());
	return cmd::byString("$name")->execCmd();
}

function run_command($name){
	cmd::byString("$name")->execCmd();
}

function main(&$data_base){
  global $scenario;

	foreach($data_base as $piece => $sensor_list){
		$status = return_value_commandname("#".$sensor_list["Thermostat"]."[Statut]#");
		$etat = return_value_commandname("#".$sensor_list["Qubino"]."[Etat]#");
		log_if_verbose("status : ".$status." - qubino : ".$etat);
		if( strcmp($status, "Arrêté") == 0 && $etat == 255 ){
			log_if_verbose("[".$piece."] Incohérance ... Exctinction en cours");
			stop_qubino($sensor_list["Qubino"]);
			log_if_verbose("[".$piece."] Exctinction envoyée");
			sleep(5);
			$status = return_value_commandname("#".$sensor_list["Thermostat"]."[Statut]#");
			$etat = return_value_commandname("#".$sensor_list["Qubino"]."[Etat]#");
			log_if_verbose("Nouveau status : ".$status." - qubino : ".$etat);
			if( strcmp($status, "Arrêté") == 0 && $etat == 255 ){
				log_if_verbose("[".$piece."] Incohérance de nouveau ... erreur");
				notify_stop_error($piece,$status,$etat);
			} else {
				log_if_verbose("[".$piece."] Résolu");
				notify_stop_success($piece,$status,$etat);
			}
		} else {
			if( strcmp($status, "Chauffage") == 0 && $etat == 0 ){
				log_if_verbose("[".$piece."] Incohérance ... Allumage en cours");
				start_qubino($sensor_list["Qubino"]);
				log_if_verbose("[".$piece."] Allumage envoyée");
				sleep(5);
				$status = return_value_commandname("#".$sensor_list["Thermostat"]."[Statut]#");
				$etat = return_value_commandname("#".$sensor_list["Qubino"]."[Etat]#");
				log_if_verbose("Nouveau status : ".$status." - qubino : ".$etat);
				if( strcmp($status, "Chauffage") == 0 && $etat == 0 ){
					log_if_verbose("[".$piece."] Incohérance de nouveau ... erreur");
					notify_start_error($piece,$status,$etat);
				} else {
					log_if_verbose("[".$piece."] Résolu");
					notify_start_success($piece,$status,$etat);
				}	
			}
		}
	}
}

main($data_base);