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

function main(){
  global $scenario;
  $duree_prevue_running=2; #h
  $topic="global";
  $condition_list=array();
  $materiel_list=array();
  $bad_message_list=array();

// 2. Pluie
  $intensite_precipitation=get_value("#[Toulouse][Météo Appartement Dark Sky][Intensité de Précipitation]#")*100;
  $next_rain_delay=get_value("#[Toulouse][Pluie][Delai avant prochaine pluie]#");
  $probabilite_precipitation=get_value("#[Toulouse][Météo Appartement Dark Sky][Probabilité de Précipitation]#")*100;
  
  # Conditions
  array_push($condition_list,"Intensité de Précipitation : ".$intensite_precipitation."%");
  array_push($condition_list,"Probabilité de Précipitation : ".$probabilite_precipitation."%");
  if($next_rain_delay != ""){
	array_push($condition_list,"Delai avant prochaine pluie : ".$next_rain_delay);
  }
  
  if( $next_rain_delay != "" || $probabilite_precipitation > 0.1 || $intensite_precipitation > 0.1 ){
	 # Matériel
	 array_push($materiel_list,"K-way");
	 # Message
	 array_push($bad_message_list,"Mauvaises conditions météo.");
  }
  $meteo_running = get_value("#[Toulouse][Météo Appartement Dark Sky][Condition prochaines heures]#");

// 3. Température
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
   
   $remaining_time_sec = $coucher_soleil_time - $current_time;
   
   $scenario->setLog("remaining_time_sec : ".$remaining_time_sec);
   array_push($condition_list,"Coucher du Soleil : ".$coucher_soleil_time_string);
   if($remaining_time_sec < $duree_prevue_running*3600){
	   array_push($bad_message_list,"Le soleil va se coucher avant ton retour, dans " . convertDuration($remaining_time_sec) . ".");
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
>