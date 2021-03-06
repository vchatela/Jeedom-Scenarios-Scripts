<?
function send_notification($message,$topic){
  	global $scenario;
 	$id_notif_scenario=8;
    $notif_scenario=scenario::byId($id_notif_scenario);
  	$titre=$scenario->getName();# scenario name

    #Récupérer les tags dans un scenaraio
    $tags = $notif_scenario->getTags();
    #Ajouter des tags
    $tags['#titre#'] = "Scenario : ".$titre;
    $tags['#message#'] = $message;
    $tags['#topic#'] = $topic;

    $scenario->setLog("Notification envoyée : ".$message);
  
    #Passer les tags à un sous-scenario et le lancer
    $notif_scenario->setTags($tags);
    $notif_scenario->launch(); 
}

function get_value($command){
	return cmd::byString("$command")->execCmd();
}

function change_category(&$condition_list, $category){
	array_push($condition_list,"---- *".$category."* ----");
}

function main(){
  global $scenario;
  $duree_prevue_running=2; #h
  $topic="global";
  $condition_list=array();
  $materiel_list=array();
  $bad_message_list=array();

// 1. Vent
  change_category($condition_list,"Vent");
  $wind_speed=array();
  $wind_speedest=array();
  array_push($wind_speed,get_value("#[Toulouse][Netatmo 70:ee:50:3d:13:7c][Vitesse du vent]#"));
  array_push($wind_speedest,get_value("#[Toulouse][Netatmo 70:ee:50:3d:13:7c][Vitesse des rafales]#"));
  array_push($wind_speed,get_value("#[Toulouse][Météo Appartement Dark Sky][Vitesse du Vent]#"));
  array_push($wind_speedest,get_value("#[Toulouse][Météo Appartement Dark Sky][Vitesse de Rafale]#"));
  $wind_direction=get_value("#[Toulouse][Météo Appartement Dark Sky][Direction du Vent]#");
  
  # Conditions
  $wind_speed_value = array_sum($wind_speed) / count($wind_speed);
  $wind_speedest_value = array_sum($wind_speedest) / count($wind_speedest);
  array_push($condition_list,"Vitesse Vent : ".$wind_speed_value."km/h");
  array_push($condition_list,"Vitesse Rafales : ".$wind_speedest_value."km/h");
  array_push($condition_list,"Direction du vent : ".$wind_direction."(Nord = 0 ; Sud = 180 ; Est = 90 ; Ouest = 270)");

// 2. Pluie
  change_category($condition_list,"Pluie");
  $intensite_precipitation=get_value("#[Toulouse][Météo Appartement Dark Sky][Intensité de Précipitation]#")*100;
  $next_rain_delay=get_value("#[Toulouse][Pluie][Delai avant prochaine pluie]#");
  $rain_estimation_text=get_value("#[Toulouse][Pluie][Previsions Textuelles]#");
  $will_rain=get_value("#[Toulouse][Pluie][Pluie prévue dans l heure]#");
  $probabilite_precipitation=get_value("#[Toulouse][Météo Appartement Dark Sky][Probabilité de Précipitation]#")*100;
  $conditions_day=get_value("#[Toulouse][Météo Appartement Dark Sky][Condition prochaines heures]#");
  $rain_type=get_value("#[Toulouse][Météo Appartement Dark Sky][Type de Précipitation]#");
  
  # Conditions
  array_push($condition_list,"Intensité de Précipitation : ".$intensite_precipitation."%");
  array_push($condition_list,"Probabilité de Précipitation : ".$probabilite_precipitation."%");
  if($next_rain_delay != ""){
	array_push($condition_list,"Delai avant prochaine pluie : ".$next_rain_delay);
  }
  if($will_rain == 1){
	  array_push($condition_list,"Pluie prévue dans l'heure.");
  }
  array_push($condition_list,"Prévisions : ".$rain_estimation_text);
  array_push($condition_list,"Conditions du jour : ".$conditions_day);
  
  if( $next_rain_delay != "" || $probabilite_precipitation > 0.1 || $intensite_precipitation > 0.1 || $will_rain == 1){
	 # Matériel
	 array_push($materiel_list,"K-way");
	 # Message
	 array_push($condition_list,"Type de pluie à venir : ".$rain_type);
	 array_push($bad_message_list,"Mauvaises conditions météo.");
  }
  $meteo_running = get_value("#[Toulouse][Météo Appartement Dark Sky][Condition prochaines heures]#");

// 3. Température
  change_category($condition_list,"Température");
  $temperature=get_value("#[Toulouse][Météo Appartement Dark Sky][Température]#");
  # Conditions
  array_push($condition_list,"Température Extérieure : ".$temperature ."°C");
  
  if($temperature > 27){
		array_push($bad_message_list,"Il fait chaud.");
		array_push($materiel_list,"Beaucoup d'eau");
  } elseif($temperature > 15){
	  ; #
  } elseif($temperature > 9){
		array_push($bad_message_list,"Il fait frais.");
		array_push($materiel_list,"Lycra + tour de cou");
  } elseif($temperature > 0){
		array_push($bad_message_list,"Il fait froid.");
		array_push($materiel_list,"Lycra + Bonnet + gants + tour de cou");
  } else{
		array_push($bad_message_list,"Il fait très froid.");
		array_push($materiel_list,"Lycra + Bonnet + gants + tour de cou");
  }
  
// 4. Qualité de l'air
  change_category($condition_list,"Qualité de l'air");
  $couleur_indice_qualite_air=get_value("#[Toulouse][Qualité Air][Couleur Indice]#");
  # Conditions
  array_push($condition_list,"Couleur Indice Qualité Air : ".$couleur_indice_qualite_air);
  
  if( $couleur_indice_qualite_air != "green" ){
	array_push($bad_message_list,"Qualité de l'air est mauvaise.");
  }

// 5. Nuit

   $coucher_soleil_time_string=date('H:i',strtotime(get_value("#[Toulouse][Météo Appartement Dark Sky][Coucher du Soleil]#")));
   #$scenario->setLog("coucher_soleil_time_string : ".$coucher_soleil_time_string);
   
   $current_time_string=date('H:i');
   #$scenario->setLog("current_time_string : ".$current_time_string);
   
   $coucher_soleil_time=strtotime($coucher_soleil_time_string);
   #$scenario->setLog("coucher_soleil_time : ".$coucher_soleil_time);
   
   $current_time=strtotime($current_time_string);
   #$scenario->setLog("current_time : ".$current_time);
   
   if($coucher_soleil_time > $current_time){
		$remaining_time_sec = $coucher_soleil_time - $current_time;
   } else {
	   $remaining_time_sec = 0;
   }
   $scenario->setLog("remaining_time_sec : ".$remaining_time_sec);
   array_push($condition_list,"Coucher du Soleil : ".$coucher_soleil_time_string);
   if($remaining_time_sec < $duree_prevue_running*3600){
	   if($remaining_time_sec == 0){
			array_push($bad_message_list,"Le soleil est déjà couché depuis ".$coucher_soleil_time_string.".");
	   } else {
			array_push($bad_message_list,"Le soleil va se coucher avant ton retour, dans " . convertDuration($remaining_time_sec) . ".");
	   }
	   array_push($materiel_list,"Lampe Torche");
   }

// 6. Notification
# concat : $message_list $condition_list $bad_message_list
  $final_message_list=array("*Prévision course à pied*");
  if(!empty($bad_message_list)){
	array_push($final_message_list,"*Risques* \n". implode("\n", $bad_message_list));
  }
  if(!empty($materiel_list)){
	array_push($final_message_list,"*Matériel* \n". implode("\n",$materiel_list));
  }
  if(!empty($condition_list)){
	array_push($final_message_list,"*Conditions du jour* \n". implode("\n",$condition_list));
  }
  send_notification(implode("\n",$final_message_list),$topic);
}

main();
