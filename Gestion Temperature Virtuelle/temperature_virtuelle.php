<?
######################### Data structure #########################
class SensorsState {
	public $temperature_errors_badvalue=0;
	public $temperature_errors_tooold=0;
	public $temperature_ok=0;
	public $temperature_ok_sensorlist_cat1=array();
	public $temperature_ok_sensorlist_cat2=array();
	public $final_temperature=100;
	
	public $humidity_errors_badvalue=0;
	public $humidity_errors_tooold=0;
	public $humidity_ok=0;
	public $humidity_ok_sensorlist_cat1=array();
	public $humidity_ok_sensorlist_cat2=array();
	public $final_humidity=200;
	
	function f_temperature_errors_badvalue(){
		$this->temperature_errors_badvalue += 1;
	}
	
	function f_temperature_errors_tooold(){
		$this->temperature_errors_tooold += 1;
	}
	
	function f_temperature_ok($sensorname,$category,$value){
		$this->temperature_ok += 1;
		if(strcmp($category, "First_category") == 0) {
			$this->temperature_ok_sensorlist_cat1[$sensorname] = $value;
		} else {
			$this->temperature_ok_sensorlist_cat2[$sensorname] = $value;
		}
	}
	
	function f_humidity_errors_badvalue(){
		$this->humidity_errors_badvalue += 1;
	}
	
	function f_humidity_errors_tooold(){
		$this->humidity_errors_tooold += 1;
	}
	
	function f_humidity_ok($sensorname,$category,$value){
		$this->humidity_ok += 1;
		if(strcmp($category, "First_category") == 0) {
			$this->humidity_ok_sensorlist_cat1[$sensorname] = $value;
		} else {
			$this->humidity_ok_sensorlist_cat2[$sensorname] = $value;
		}
	}
}

######################### Core informations #########################
$data_base = array(
	"[Séjour][TH Virtuel]" => array(
		"SensorsState" => new SensorsState(),
		"Temperature_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Séjour][Capteur TH][Température]" => array(),
					"[Séjour][Capteur TH Hauteur][Température]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array(
					"[Séjour][DHT22][Température]" => array(),
					"[Séjour][DS18B20][Température]" => array()
				)
			)
		),
		"Humidity_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Séjour][Capteur TH][Humidité]" => array(),
					"[Séjour][Capteur TH Hauteur][Humidité]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array(
					"[Séjour][DHT22][Humidité]" => array()
				)
			)
		)
	),
	"[Bureau][TH Virtuel]" => array(
		"SensorsState" => new SensorsState(),
		"Temperature_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Bureau][Capteur TH][Température]" => array(),
					"[Bureau][Capteur TH Haut][Température]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array()
			)
		),
		"Humidity_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Bureau][Capteur TH][Humidité]" => array(),
					"[Bureau][Capteur TH Haut][Humidité]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array()
			)
		)
	),
	"[Chambre][TH Virtuel]" => array(
		"SensorsState" => new SensorsState(),
		"Temperature_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Chambre][Capteur TH][Température]" => array(),
					"[Chambre][Capteur TH Haut][Température]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array()
			)
		),
		"Humidity_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Chambre][Capteur TH][Humidité]" => array(),
					"[Chambre][Capteur TH Haut][Humidité]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array()
			)
		)
	)
);
######################### Jeedom Tooling #########################

function log_if_verbose($text){
	global $scenario;
	$LOG=0;
	if($LOG == 1){
		$scenario->setLog("$text");
    }
}

function set_value_commandname($name,$value){
	cmd::byString($name)->event($value);
}

function return_value_commandname($name){
	log_if_verbose("$name valeur :" . cmd::byString("$name")->execCmd());
	return cmd::byString("$name")->execCmd();
}

function return_date_commandname($name){
	log_if_verbose("$name valeur :" . cmd::byString("$name")->execCmd());
	return cmd::byString("$name")->getCollectDate();
}

function send_notification($message){
  global $scenario;
  $queue_action = '[Notifications][Queue Electricité 20min][Ajouter]';
  
  // Ajout à la queue
  $cmd_option = array('title' => "", 'message' => $message);
  $scenario->setLog('Commande : '. $queue_action. '/ ' .json_encode($cmd_option));
  $cmd = cmd::byString('#'. $queue_action .'#');
  $cmd->execCmd($cmd_option);
}

function update_virtual($virtual, $SensorsState){
	global $scenario;
	set_value_commandname("#".$virtual."[temperature_errors_badvalue]#",$SensorsState->temperature_errors_badvalue);
	set_value_commandname("#".$virtual."[temperature_errors_tooold]#",$SensorsState->temperature_errors_tooold);
	set_value_commandname("#".$virtual."[temperature_ok]#",$SensorsState->temperature_ok);
	set_value_commandname("#".$virtual."[Température]#",$SensorsState->final_temperature);
	
	set_value_commandname("#".$virtual."[humidity_errors_badvalue]#",$SensorsState->humidity_errors_badvalue);
	set_value_commandname("#".$virtual."[humidity_errors_tooold]#",$SensorsState->humidity_errors_tooold);
	set_value_commandname("#".$virtual."[humidity_ok]#",$SensorsState->humidity_ok);
	set_value_commandname("#".$virtual."[Humidité]#",$SensorsState->final_humidity);
	
	set_value_commandname("#".$virtual."[last_update]#",strftime("%B %d %Y, %X %Z",strtotime("now")));
	
	if($SensorsState->final_temperature == 100){
		send_notification($virtual." Température finale : 100°C... donc pas capteurs dispo !");
	}
	if($SensorsState->final_humidity == 200){
		send_notification($virtual." Humidité finale : 200% ... donc pas capteurs dispo !");
	}
	$scenario->setLog("virtual : " . $virtual);
	$scenario->setLog("final_temperature : " . $SensorsState->final_temperature);
	$scenario->setLog("final_humidity : " . $SensorsState->final_humidity);
}

function check_sensor_valid($sensor_name, $type, &$list, &$sensorstate, $category){
	$now = strtotime("now");
	$acceptable_seconds_delay = 3600;
	
	$date=$list["date"];
	$value=$list["value"];
	
	$date_strtotime = strtotime($date);
	log_if_verbose("Date capture: " . $date);
	log_if_verbose("Diff between now and date capture : " . ($now - $date_strtotime));
	if(strcmp($type, "Temperature_list") == 0) {
		if(($now - $date_strtotime) < $acceptable_seconds_delay){
			if($value > 40){
				log_if_verbose("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $value . " refusé pour valeur de température éronnée!");
				$sensorstate->f_temperature_errors_badvalue();
				$list["valid"]=0;
			}else {
				log_if_verbose("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $value . " accepté !");
				$sensorstate->f_temperature_ok($sensor_name, $category,$value);
				$list["valid"]=1;
			}
		} else {
			$sensorstate->f_temperature_errors_tooold();
			log_if_verbose("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $value . " refusé pour date trop ancienne");
			$list["valid"]=0;
		}
	} else { // $type == "Humidity_list"
		if(($now - $date_strtotime) < $acceptable_seconds_delay){
			if($value < 5){
				log_if_verbose("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $value . " refusé pour valeur d'humidité éronnée!");
				$sensorstate->f_humidity_errors_badvalue();
				$list["valid"]=0;
			}else {
				log_if_verbose("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $value . " accepté !");
				$sensorstate->f_humidity_ok($sensor_name, $category,$value);
				$list["valid"]=1;
			}
		} else {
			$sensorstate->f_humidity_errors_tooold();
			log_if_verbose("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $value . " refusé pour date trop ancienne");
			$list["valid"]=0;
		}
	}
}

function main(&$data_base){
  global $scenario;
  
  ######################### Retrieve values and compute validity #########################
	foreach($data_base as $virtual => &$infos_zone){
		foreach(array("Temperature_list","Humidity_list") as $type){
			foreach(array("First_category","Second_category") as $category){
				log_if_verbose("category :" . $category);
				foreach($infos_zone[$type][$category]["sensors"] as $sensor => &$list){
					# Initilialisations
					$cmd_temp_name=cmd::byString("#".$sensor."#");
					$cmd_temp_value=$cmd_temp_name->execCmd();
					$cmd_temp_date=$cmd_temp_name->getCollectDate();
					$list += ["value" => "$cmd_temp_value"];
					$list += ["date" => "$cmd_temp_date"];
					# Check valid
					check_sensor_valid($sensor, $type, $list ,$infos_zone["SensorsState"],$category);
				}
			}
		}
	}
	#log_if_verbose("data_base :" . print_r($data_base, true));
	
	######################### Compute final values #########################
	foreach($data_base as $virtual => &$infos_zone){
		
		// Part Temperature
		$type="Temperature_list";
		$cat1_array = $infos_zone["SensorsState"]->temperature_ok_sensorlist_cat1;
		$cat2_array = $infos_zone["SensorsState"]->temperature_ok_sensorlist_cat2;
		log_if_verbose("cat1_array :" . print_r($cat1_array, true));
		if(sizeof($cat1_array) != 0){
			if(sizeof($cat1_array) == 1){
				send_notification($virtual."[First_category] Un seul capteur température haut utilisé : ". print_r(array_keys($cat1_array)[0],true)); 
			}
			$scenario->setLog("TH Cat1 nombre capteur utilisés : " . sizeof($cat1_array));
			$infos_zone["SensorsState"]->final_temperature = array_sum($cat1_array)/count($cat1_array);
		} else {
			if(sizeof($cat2_array) != 0){
				if(sizeof($cat2_array) == 1){
					send_notification($virtual."[Second_category] Un seul capteur température haut utilisé : ". print_r(array_keys($cat2_array)[0],true)); 
				}
				$scenario->setLog("TH Cat2 nombre capteur utilisés : " . sizeof($cat2_array));
				$infos_zone["SensorsState"]->final_temperature = array_sum($cat2_array)/count($cat2_array);
			} else{
				send_notification($virtual." : Aucun capteur température dispo !");
			}
		}
		// Part Humidity
		$type="Humidity_list";
		$cat1_array = $infos_zone["SensorsState"]->humidity_ok_sensorlist_cat1;
		$cat2_array = $infos_zone["SensorsState"]->humidity_ok_sensorlist_cat2;
		if(sizeof($cat1_array) != 0){
			if(sizeof($cat1_array) == 1){
				send_notification($virtual."[First_category] Un seul capteur humidité haut utilisé : ".print_r(array_keys($cat1_array)[0],true)); 
			}
			$scenario->setLog("Hum Cat1 nombre capteur utilisés : " . sizeof($cat1_array));
			$infos_zone["SensorsState"]->final_humidity = array_sum($cat1_array)/count($cat1_array);
		} else {
			if(sizeof($cat2_array) != 0){
				if(sizeof($cat2_array) == 1){
					send_notification($virtual."[Second_category] Un seul capteur humidité haut utilisé : ".print_r(array_keys($cat2_array)[0],true)); 
				}
				$scenario->setLog("Hum Cat2 nombre capteur utilisés : " . sizeof($cat2_array));
				$infos_zone["SensorsState"]->final_humidity = array_sum($cat2_array)/count($cat2_array);
			} else{
				send_notification($virtual." : Aucun capteur humidité dispo !");
			}
		}
		// Update Virtual
		update_virtual($virtual,$infos_zone["SensorsState"]);
	}
	#log_if_verbose("data_base :" . print_r($data_base, true));
}

main($data_base);