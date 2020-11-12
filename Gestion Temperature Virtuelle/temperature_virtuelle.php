<?
$temperature_errors_badvalue=0;
$temperature_errors_tooold=0;
$temperature_ok=0;
$humidity_errors_badvalue=0;
$humidity_errors_tooold=0;
$humidity_ok=0;

function check_sensor_states(){
	global $temperature_errors_badvalue;
  	global $temperature_errors_tooold;
  	global $temperature_ok;
    global $humidity_errors_badvalue;
  	global $humidity_errors_tooold;
  	global $humidity_ok;
	
	cmd::byString("#[Séjour][TH Virtuel][temperature_errors_badvalue]#")->event(($temperature_errors_badvalue));
	cmd::byString("#[Séjour][TH Virtuel][temperature_errors_tooold]#")->event(($temperature_errors_tooold));
	cmd::byString("#[Séjour][TH Virtuel][temperature_ok]#")->event(($temperature_ok));
	cmd::byString("#[Séjour][TH Virtuel][humidity_errors_badvalue]#")->event(($humidity_errors_badvalue));
	cmd::byString("#[Séjour][TH Virtuel][humidity_errors_tooold]#")->event(($humidity_errors_tooold));
	cmd::byString("#[Séjour][TH Virtuel][humidity_ok]#")->event(($humidity_ok));
}

function extract_valid_values($temperature_list, $humidity_list){
  	global $temperature_errors_badvalue;
  	global $temperature_errors_tooold;
  	global $temperature_ok;
    global $humidity_errors_badvalue;
  	global $humidity_errors_tooold;
  	global $humidity_ok;
	$now = strtotime("now");
	global $scenario;
	#$scenario->setLog("Now date : " . $now);
	$valid_temperature_list = array();
	$valid_humidity_list = array();
	$acceptable_seconds_delay = 3600;
	# Valid if under 
	foreach($temperature_list as $sensor_name => $sensor){
		foreach($sensor as $date => $temperature){
			#$scenario->setLog("Foreach Date : " . $date);
			#$scenario->setLog("Foreach Temperature : " . $temperature);
			$date_strtotime = strtotime($date);
			#$scenario->setLog("Foreach date strtotime: " . $date);
			#$scenario->setLog("Diff : " . ($now - $date_strtotime));
			if(($now - $date_strtotime) < $acceptable_seconds_delay){
              	if($temperature > 40){
                  	$scenario->setLog("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $temperature . " refusé pour valeur de température éronnée!");
                  $temperature_errors_badvalue = $temperature_errors_badvalue + 1;
                }else {
					#$scenario->setLog("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $temperature . " accepté !");
					$valid_temperature_list[$sensor_name] = $temperature;
                  	$temperature_ok = $temperature_ok + 1;
                }
			} else {
				$temperature_errors_tooold = $temperature_errors_tooold + 1;
			}
		}
	}
	foreach($humidity_list as $sensor_name => $sensor){
		foreach($sensor as $date => $humidity){
			#$scenario->setLog("Foreach Date : " . $date);
			#$scenario->setLog("Foreach Humidity : " . $humidity);
			$date_strtotime = strtotime($date);
			#$scenario->setLog("Foreach date strtotime: " . $date);
			#$scenario->setLog("Diff : " . ($now - $date_strtotime));
			if(($now - $date_strtotime) < $acceptable_seconds_delay){
              	if($humidity < 5){
                  $scenario->setLog("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $temperature . " refusé pour valeur d'humidité éronnée !");
                  $humidity_errors_badvalue = $humidity_errors_badvalue + 1;
                } else {
					#$scenario->setLog("Sensor : " . $sensor_name . " - Date : " . $date . " - Valeur : " . $humidity . " accepté !");
					$valid_humidity_list[$sensor_name] = $humidity;
					$humidity_ok = $humidity_ok +1;
                }
			} else {
				$humidity_errors_tooold = $humidity_errors_tooold + 1;
			}
		}
	}
	
	#Prepare list of values
	#$printable_temperature_dates = "";
	#foreach($temperature_date_list as $date) {
	#	$printable_temperature_dates .= $date;
	#}
	#$printable_himidity_dates = "";
	#foreach($humidity_date_list as $date) {
	#	$printable_himidity_dates .= $date;
	#}
	# Check if no valid value
	#if(sizeof($valid_temperature_list) == 0){
	#	send_notifications("[Gestion TH Virtuel] Aucune température n'est valide .. \n " . $printable_temperature_dates);
	#}
	#if(sizeof($valid_humidity_list) == 0){
	#	send_notifications("[Gestion TH Virtuel] Aucune humidité n'est valide .. \n " . $printable_himidity_dates);
	#}
	#$scenario->setLog("Valid_temperature_list : " . print_r($valid_temperature_list, true));
	#$scenario->setLog("Valid_humidity_list : " . print_r($valid_humidity_list, true));
	
	return array($valid_temperature_list, $valid_humidity_list);
}

function send_notifications($message){
  global $scenario;
  $id_scenario_notification = 8;
  $scenario = scenario::byId($id_scenario_notification);
  $scenario->launch();
  $scenario->setLog($message);
}

function compute_final_values($valid_temperature_list, $valid_humidity_list){
	global $scenario;
	# Set default values 
	$final_temperature = 100;
	$final_humidity = 200;
	
	# Compute average of valid list preferably on Xiaomi otherwise on all
	
	#$scenario->setLog("valid_temperature_list : " . print_r($valid_temperature_list, true) . " - Sizeof : " . sizeof($valid_temperature_list));
	#$scenario->setLog("valid_humidity_list : " . print_r($valid_humidity_list, true) . " - Sizeof : " . sizeof($valid_humidity_list));
	
	$haut_th_sensors_list = array_intersect_key($valid_temperature_list, array_flip(array("Xiaomi TH","Xiaomi TH Haut")));
	$haut_hum_sensors_list = array_intersect_key($valid_humidity_list, array_flip(array("Xiaomi TH","Xiaomi TH Haut")));
	
	#$scenario->setLog("haut_th_sensors_list : " . print_r($haut_th_sensors_list, true) . " - Sizeof : " . sizeof($haut_th_sensors_list));
	#$scenario->setLog("haut_hum_sensors_list : " . print_r($haut_hum_sensors_list, true) . " - Sizeof : " . sizeof($haut_hum_sensors_list));
	
	
	
	if(sizeof($haut_th_sensors_list) != 0){
		$final_temperature = array_sum($haut_th_sensors_list)/count($haut_th_sensors_list);
		if(sizeof($haut_th_sensors_list) == 1){
			send_notifications("[Gestion TH Virtuel] Un seul capteur température haut utilisé : ". print_r($haut_th_sensors_list, true));
		}
		#$scenario->setLog("final_temperature via      haut_th_sensors_list");
	}
	else {
		if(sizeof($valid_temperature_list) != 0){
			$final_temperature = array_sum($valid_temperature_list)/count($valid_temperature_list);
			send_notifications("[Gestion TH Virtuel]  Seuls les capteur rpi sont utilisés pour température :" . print_r($valid_temperature_list, true));
			#$scenario->setLog("final_temperature via      valid_temperature_list");
		} else {
			send_notifications("[Gestion TH Virtuel] Aucun capteur température dispo !");
		}
	}
	
	if(sizeof($haut_hum_sensors_list) != 0){
		$final_humidity = array_sum($haut_hum_sensors_list)/count($haut_hum_sensors_list);
		if(sizeof($haut_hum_sensors_list) == 1){
			send_notifications("[Gestion TH Virtuel] Un seul capteur humidité haut utilisé : ". print_r($haut_hum_sensors_list, true));
		}
		#$scenario->setLog("final_humidity via      haut_hum_sensors_list   -- final_humidity : " . $final_humidity);
	}
	else {
		if(sizeof($valid_humidity_list) != 0){
			$final_humidity = array_sum($valid_humidity_list)/count($valid_humidity_list);
			send_notifications("[Gestion TH Virtuel] Seuls les capteur rpi sont utilisés pour humidité :" . print_r($valid_humidity_list, true));
			#$scenario->setLog("final_humidity via      valid_humidity_list   -- final_humidity : " . $final_humidity);
		}else {
			send_notifications("[Gestion TH Virtuel] Aucun capteur humidité dispo !");
		}
	}
	
	# Checks 
	if($final_temperature == 100){
		send_notifications("[Gestion TH Virtuel] Température finale : 100°C... donc pas capteurs dispo !");
	}
	if($final_humidity == 200){
		send_notifications("[Gestion TH Virtuel] Humidité finale : 200% ... donc pas capteurs dispo !");
	}
	
	#$scenario->setLog("final_temperature : " . $final_temperature);
	#$scenario->setLog("final_humidity : " . $final_humidity);
	
	return array($final_temperature, $final_humidity);
}

function main(){
  global $scenario;
  
  # Température
  $cmd_temp_dht22_name = cmd::byString("#[Séjour][DHT22][Température]#");
  $cmd_temp_dht22 = $cmd_temp_dht22_name->execCmd();
  $cmd_temp_dht22_date = $cmd_temp_dht22_name->getCollectDate(); 
  #$scenario->setLog("[Séjour][DHT22][Température] valeur :" . $cmd_temp_dht22);
  #$scenario->setLog("[Séjour][DHT22][Température] date :" . $cmd_temp_dht22_date);
  
  $cmd_temp_DS18B20_name = cmd::byString("#[Séjour][DS18B20][Température]#");
  $cmd_temp_DS18B20 = $cmd_temp_DS18B20_name->execCmd();
  $cmd_temp_DS18B20_date = $cmd_temp_DS18B20_name->getCollectDate(); 
  #$scenario->setLog("[Séjour][DS18B20][Température] valeur :" . $cmd_temp_DS18B20);
  #$scenario->setLog("[Séjour][DS18B20][Température] date :" . $cmd_temp_DS18B20_date);
  
  $cmd_temp_TH_name = cmd::byString("#[Séjour][Capteur TH][Température]#");
  $cmd_temp_TH = $cmd_temp_TH_name->execCmd();
  $cmd_temp_TH_date = $cmd_temp_TH_name->getCollectDate(); 
  #$scenario->setLog("[Séjour][Capteur TH][Température] valeur :" . $cmd_temp_TH);
  #$scenario->setLog("[Séjour][Capteur TH][Température] date :" . $cmd_temp_TH_date);
  
  $cmd_temp_TH_haut_name = cmd::byString("#[Séjour][Capteur TH Hauteur][Température]#");
  $cmd_temp_TH_haut = $cmd_temp_TH_haut_name->execCmd();
  $cmd_temp_TH_haut_date = $cmd_temp_TH_haut_name->getCollectDate(); 
  #$scenario->setLog("[Séjour][Capteur TH Hauteur][Température] valeur :" . $cmd_temp_TH_haut);
  #$scenario->setLog("[Séjour][Capteur TH Hauteur][Température] date :" . $cmd_temp_TH_haut_date);   
  
  $temperature_list = array(
		"DHT22" => array($cmd_temp_dht22_date => $cmd_temp_dht22),
		"DS18B20" => array($cmd_temp_DS18B20_date => $cmd_temp_DS18B20),
		"Xiaomi TH" => array($cmd_temp_TH_date => $cmd_temp_TH),
		"Xiaomi TH Haut" => array($cmd_temp_TH_haut_date =>$cmd_temp_TH_haut)
	);
  
  #$scenario->setLog("Temperature_list : " . print_r($temperature_list,true));
  
  # Humidité
  $cmd_hum_dht22_name = cmd::byString("#[Séjour][DHT22][Humidité]#");
  $cmd_hum_dht22 = $cmd_hum_dht22_name ->execCmd();
  $cmd_hum_dht22_date = $cmd_hum_dht22_name->getCollectDate(); 
  #$scenario->setLog("[Séjour][DHT22][Humidité] valeur :" . $cmd_hum_dht22);
  #$scenario->setLog("[Séjour][DHT22][Humidité] date :" . $cmd_hum_dht22_date);   
  
  $cmd_hum_TH_name = cmd::byString("#[Séjour][Capteur TH][Humidité]#");
  $cmd_hum_TH = $cmd_hum_TH_name->execCmd();
  $cmd_hum_TH_date = $cmd_hum_TH_name->getCollectDate(); 
  #$scenario->setLog("[Séjour][Capteur TH][Humidité] valeur :" . $cmd_hum_TH);
  #$scenario->setLog("[Séjour][Capteur TH][Humidité] date :" . $cmd_hum_TH_date);  
  
  
  $cmd_hum_TH_haut_name = cmd::byString("#[Séjour][Capteur TH Hauteur][Humidité]#");
  $cmd_hum_TH_haut = $cmd_hum_TH_haut_name->execCmd();
  $cmd_hum_TH_haut_date = $cmd_hum_TH_haut_name->getCollectDate(); 
  #$scenario->setLog("[Séjour][Capteur TH Hauteur][Humidité] valeur :" . $cmd_hum_TH_haut);
  #$scenario->setLog("[Séjour][Capteur TH Hauteur][Humidité] date :" . $cmd_hum_TH_haut_date);  
  
  $humidity_list = array(
		"DHT22" => array($cmd_hum_dht22_date => $cmd_hum_dht22),
		"Xiaomi TH" => array($cmd_hum_TH_date => $cmd_hum_TH),
		"Xiaomi TH Haut" => array($cmd_hum_TH_haut_date =>$cmd_hum_TH_haut)
	);
  
 
  #$scenario->setLog("Humidity_list : " . print_r($humidity_list,true));
  
  # Extract all valid value (within the range of validity)
  list($valid_temperature_list, $valid_humidity_list) = extract_valid_values($temperature_list, $humidity_list);
  # Define final values
  list($final_temperature, $final_humidity) = compute_final_values($valid_temperature_list, $valid_humidity_list);
  # Inform risks on sensors 
  check_sensor_states();
  
  # Set final values
  $final_temperature = round($final_temperature,2);
  $scenario->setLog("Température finale : " . $final_temperature);
  cmd::byString("#[Séjour][TH Virtuel][Température]#")->event(($final_temperature));
  
  $final_humidity = round($final_humidity,2);
  $scenario->setLog("Humidité finale : " . $final_humidity);
  cmd::byString("#[Séjour][TH Virtuel][Humidité]#")->event(($final_humidity));
}

main();