<?
function send_notification($message,$topic,$is_important){
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
  	if($is_important && $GLOBALS['alert_sms']==1){
    	$tags['#importance#'] = 10;
      	$GLOBALS['alert_sms']=0;
    }

  	
    $scenario->setLog("Notification envoyée : ".$message);
  
    #Passer les tags à un sous-scenario et le lancer
    $notif_scenario->setTags($tags);
    $notif_scenario->launch(); 
}


function event_exists($calendar,$event_name){
  	global $scenario;
  	if(isset($calendar)) {
      	$found=0;
		$events=$calendar->getEvents();
      	foreach ($events as $event) {
			if(strcasecmp($event->getName(),$event_name)==0){
              	$found=1;
              	$scenario->setLog("Event :".$event_name." found !");
              	break;
            }
		}
	} else {
     	# calendar n'existe pas !
      	send_notification("Le calendrier ".$calendar_id." n'existe pas.. -- Erreur Calendrier Thermostat Séjour","erreur",1);
    }
  return $found;
}

function add_event_to_calendar($piece,$heure_debut,$heure_fin){
  global $scenario;
  $agenda_label = '[Appartement][Chauffage]';
  $calendar_id=115;
  $event_name="$piece $heure_debut-$heure_fin";
  $date=date('Y-m-d');
  $calendar=eqLogic::byId($calendar_id);
  $scenario->setLog("Calendrier :".$calendar_id);
  
  if(event_exists($calendar,$event_name)==0){
    	# event_name n'existe pas
    	send_notification("Evenement : *".$event_name."* n'a pas été trouvé dans le calendrier !", "erreur",1);
  }
  
  // Mise à jour de l'agenda
  $cmd_option = array('title' => $event_name, 'message' => $date);
  $scenario->setLog('Commande : '. $agenda_label. '[Ajouter une date] / ' .json_encode($cmd_option));
  $cmd = cmd::byString('#'. $agenda_label .'[Ajouter une date]#');
  $cmd->execCmd($cmd_option);
}

function convert_int_to_hour($int){
	return substr_replace($int, "h", -2, 0);
}

function is_saturday_or_sunday(){
  global $scenario;
  date_default_timezone_set('Europe/Paris');
  $date_format="l";
  if($GLOBALS['test_for_tomorrow']==0){
  	# Today day
  	$jour_semaine=date($date_format);
  } elseif($GLOBALS['test_for_tomorrow']==1){
    # Tomorrow day
    $tomorrow = strtotime('+1 day', strtotime('now'));
    $jour_semaine=gmdate($date_format, $tomorrow);
  }
  $scenario->setLog("Jour Semaine : $jour_semaine");
  if(strcasecmp($jour_semaine, 'Saturday') == 0 || strcasecmp($jour_semaine, 'Sunday') == 0){
   	return true; 
  } else {
    return false;
  }
}


# Horaire leve
function heure_leve($debut_travail_mathou,$mathou_work)
{
  	# Mathou
	if($mathou_work){
		if($debut_travail_mathou>1200){
			$mathou_leve=930;
		} elseif($debut_travail_mathou>1000){
			$mathou_leve=900;
		} elseif($debut_travail_mathou>=900){
			$mathou_leve=800;
		} elseif($debut_travail_mathou>730){
			$mathou_leve=700;
		} else{
			# avant 7h30 !
			 $mathou_leve=615;
		}
	} else {
		#repos mathou
		$mathou_leve=930;
	}
    
    # Valou
    if(is_saturday_or_sunday()){
      	$val_leve=930;
    } else {
     	 $val_leve=900;
    }
    
    return array($mathou_leve,$val_leve);
}
      
function heure_couche(){
  	if(is_saturday_or_sunday()){
      	$heure_couche=2330;
    } else {
     	 $heure_couche=2245;
    }
  return $heure_couche;
}
      
function heure_depart_boulot($debut_travail_mathou)
{
  	# Mathou
	if($debut_travail_mathou>1200){
      	$mathou_depart_boulot=1200;
    } elseif($debut_travail_mathou>1000){
      	$mathou_depart_boulot=1000;
	} elseif($debut_travail_mathou>=900){
      	$mathou_depart_boulot=830;
    } elseif($debut_travail_mathou>=700){
      	$mathou_depart_boulot=615;
    } else{
      	$mathou_depart_boulot=615;
    	send_notification("Heure_depart_boulot : problème sur l'heure de début -- trop tôt !","erreur",1);  
    }
    
    $val_depart_boulot=900;
    return array($mathou_depart_boulot,$val_depart_boulot);
}
		      
function heure_retour_boulot($debut_travail_mathou)
{
  	# Mathou
	if($debut_travail_mathou>1200){
      	$mathou_retour_boulot=2200;
    } elseif($debut_travail_mathou>1000){
      	$mathou_retour_boulot=2000;
	} elseif($debut_travail_mathou>=900){
      	$mathou_retour_boulot=1800;
    } elseif($debut_travail_mathou>730){
      	$mathou_retour_boulot=1730;
    } else{
		$mathou_retour_boulot=1630;
	}
    
    $val_retour_boulot=1730;
    return array($mathou_retour_boulot,$val_retour_boulot);
}

function search_event_exists($events_of_the_day,$event_name){
  $events_list = explode(";", $events_of_the_day);
  foreach ($events_list as $event) {
      if (strpos(strtolower($event), strtolower($event_name)) !== false) {
          return $event;
      }
  }
  return false;
}

function extract_start_hour($event){
	# Event="A 10H30 : Mathou"
    preg_match_all('/A ([^:]+):/',$event,$match);
    return strval(str_replace("H","",$match[1][0]));
}

function main(){
  global $scenario;
  $soulard_mathou_work="Mathou";
  $soulard_val_TT="TT";
  $abs_pattern="[Abs]";
  $present_pattern="[Présent]";
  
  $test_for_tomorrow=$GLOBALS['test_for_tomorrow'];
  $scenario->setLog("test_for_tomorrow : $test_for_tomorrow");
  
  $cmd_today = cmd::byString("#[Toulouse][Calendrier Soulard][Today]#");
  $event_today=$cmd_today->execCmd();
  $cmd_tomorrow = cmd::byString("#[Toulouse][Calendrier Soulard][Tomorrow]#");
  $event_tomorrow=$cmd_tomorrow->execCmd();
  
  if($GLOBALS['test_for_tomorrow']==0){
	$events_of_the_day=$event_today;
  } elseif ($GLOBALS['test_for_tomorrow']==1){
	$events_of_the_day=$event_tomorrow;
  }
  
  # Determine [Présent] today
  $present_today=search_event_exists($events_of_the_day,$present_pattern);
  
  # Mathou event work
  $mathou_work=search_event_exists($events_of_the_day,$soulard_mathou_work);
  # mathou_work contains either : false OR "A XXHXX : Mathou"
  if($mathou_work != false){
	$debut_travail_mathou=extract_start_hour($mathou_work);
	$scenario->setLog("debut_travail_mathou: $debut_travail_mathou");
  }
  # Val homework
  $val_TT=search_event_exists($events_of_the_day,$soulard_val_TT);
    
  ## Gérer cas absence
  
  ## Déterminer si 1 ou 2 blocs séjour
  if($mathou_work==false || $val_TT!=false || is_saturday_or_sunday() || $present_today!=false){
   	 $journee_complete=1;
  } else {
   	$journee_complete=0; 
  }
  
    $leve_array=heure_leve($debut_travail_mathou,$mathou_work);
  	#$scenario->setLog("Leve array : " . join(', ', $leve_array));
	$mathou_leve=$leve_array[0];
  	$val_leve=$leve_array[1];
	$scenario->setLog("mathou_leve: $mathou_leve");
	$scenario->setLog("val_leve: $val_leve");
  	$heure_couche=heure_couche();
	$scenario->setLog("heure_couche: $heure_couche");
	
  if($journee_complete==1){
    ### Si mathou repos (RAS dans agenda Mathou) OU val télétravail OU [Présent] OU Samedi OU Dimanche 
    $scenario->setLog("Journée complète.");
    $heure_fin_chambre=convert_int_to_hour(max($mathou_leve,$val_leve));
	$heure_debut_jour_sejour=convert_int_to_hour(min($mathou_leve,$val_leve));
	$heure_fin_jour_sejour=convert_int_to_hour($heure_couche);
	$heure_debut_chambre=convert_int_to_hour($heure_couche);
	$scenario->setLog("heure_fin_chambre: $heure_fin_chambre");
	$scenario->setLog("heure_debut_jour_sejour: $heure_debut_jour_sejour");
	$scenario->setLog("heure_fin_jour_sejour: $heure_fin_jour_sejour");
	$scenario->setLog("heure_debut_chambre: $heure_debut_chambre");
    $scenario->setData("prog_today", "Chambre 1h05-6h15 + Chambre 6h15-$heure_fin_chambre + Sejour $heure_debut_jour_sejour-$heure_fin_jour_sejour + Chambre $heure_debut_chambre-1h00");
    if($GLOBALS['test_for_tomorrow']==0){
	  add_event_to_calendar("Chambre","1h05","6h15");
      add_event_to_calendar("Chambre","6h15",$heure_fin_chambre);
      add_event_to_calendar("Sejour",$heure_debut_jour_sejour,$heure_fin_jour_sejour);
      add_event_to_calendar("Chambre",$heure_debut_chambre,"1h00");
      $message_debut="[Thermostat] Programmation :";
    } else {
	  $scenario->setLog("Chambre 1h05-6h15");
      $scenario->setLog("Chambre 6h15-$heure_fin_chambre");
      $scenario->setLog("Sejour $heure_debut_jour_sejour-$heure_fin_jour_sejour");
      $scenario->setLog("Chambre $heure_debut_chambre-1h00");
      $message_debut="[Thermostat] Programmation prévue pour demain :";
    }
    send_notification($debut_message."Chambre 1h05-6h15 + Chambre 6h15-$heure_fin_chambre + Sejour $heure_debut_jour_sejour-$heure_fin_jour_sejour + Chambre $heure_debut_chambre-1h00","electricite",0);
  }
  else {
    $scenario->setLog("Deux blocs en journée.");
    $depart_array=heure_depart_boulot($debut_travail_mathou);
    $mathou_depart_boulot=$depart_array[0];
  	$val_depart_boulot=$depart_array[1];
    $retour_array=heure_retour_boulot($debut_travail_mathou);
    $mathou_retour_boulot=$retour_array[0];
  	$val_retour_boulot=$retour_array[1];
	$scenario->setLog("mathou_retour_boulot: $mathou_retour_boulot");
	$scenario->setLog("val_retour_boulot: $val_retour_boulot");
    ### les 2 travaillent (pas de TT) dans la journée
    $heure_fin_chambre=convert_int_to_hour(max($mathou_leve,$val_leve));
	$heure_debut_matin_sejour=convert_int_to_hour(min($mathou_leve,$val_leve));
    $heure_fin_matin_sejour=convert_int_to_hour(max($mathou_depart_boulot,$val_depart_boulot));
	$heure_debut_soir_sejour=convert_int_to_hour(min($mathou_retour_boulot,$val_retour_boulot));
	$heure_fin_soir_sejour=convert_int_to_hour($heure_couche);
	$heure_debut_chambre=convert_int_to_hour($heure_couche);
	$scenario->setLog("heure_fin_chambre: $heure_fin_chambre");
	$scenario->setLog("heure_debut_matin_sejour: $heure_debut_matin_sejour");
	$scenario->setLog("heure_fin_matin_sejour: $heure_fin_matin_sejour");
	$scenario->setLog("heure_debut_soir_sejour: $heure_debut_soir_sejour");
	$scenario->setLog("heure_fin_jour_sejour: $heure_fin_jour_sejour");
	$scenario->setLog("heure_debut_chambre: $heure_debut_chambre");
    if($GLOBALS['test_for_tomorrow']==0){
	  add_event_to_calendar("Chambre","1h05","6h15");
      add_event_to_calendar("Chambre","6h15",$heure_fin_chambre);
      add_event_to_calendar("Sejour",$heure_debut_matin_sejour,$heure_fin_matin_sejour);
      add_event_to_calendar("Sejour",$heure_debut_soir_sejour,$heure_fin_soir_sejour);
      add_event_to_calendar("Chambre",$heure_debut_chambre,"1h00");
      $message_debut="[Thermostat] Programmation :";
    } else {
	  $scenario->setLog("Chambre 1h05-6h15");
      $scenario->setLog("Chambre 6h15-$heure_fin_chambre");
      $scenario->setLog("Sejour $heure_debut_matin_sejour-$heure_fin_matin_sejour");
      $scenario->setLog("Sejour $heure_debut_soir_sejour-$heure_fin_soir_sejour");
      $scenario->setLog("Chambre $heure_debut_chambre-1h00");
      $message_debut="[Thermostat] Programmation prévue pour demain :";
    }
    send_notification($debut_message."Chambre 1h05-6h15 + Chambre 6h15-$heure_fin_chambre + Sejour $heure_debut_matin_sejour-$heure_fin_matin_sejour + Sejour $heure_debut_soir_sejour-$heure_fin_soir_sejour + Chambre $heure_debut_chambre-1h00","electricite",0);
  }
}

$GLOBALS['test_for_tomorrow']=$scenario->getData('test_for_tomorrow');
$GLOBALS['alert_sms']=1;
main();
$GLOBALS['alert_sms']=0;