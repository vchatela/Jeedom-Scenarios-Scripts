<?
// function send_notification_via_scenario($message,$topic,$is_important){
  	// global $scenario;
 	// $id_notif_scenario=8;
    // $notif_scenario=scenario::byId($id_notif_scenario);
  	// $titre=$scenario->getName();# scenario name

    // #Récupérer les tags dans un scenario
    // $tags = $notif_scenario->getTags();
    // #Ajouter des tags
    // #$tags['#titre#'] = "Scenario : ".$titre;
    // $tags['#message#'] = $message;
    // $tags['#topic#'] = $topic;
  	// if($is_important && $GLOBALS['alert_sms']==1){
    	// $tags['#importance#'] = 10;
      	// $GLOBALS['alert_sms']=0;
    // }

  	
    // $scenario->setLog("Notification envoyée : ".$message);
  
    // #Passer les tags à un sous-scenario et le lancer
    // $notif_scenario->setTags($tags);
    // $notif_scenario->launch(); 
// }

function send_notification_1j($message){
  global $scenario;
  $queue_action = '[Notifications][Queue Erreurs 1xjour][Ajouter]';
  
  // Ajout à la queue
  $cmd_option = array('title' => "", 'message' => $message);
  $scenario->setLog('Commande : '. $queue_action. '/ ' .json_encode($cmd_option));
  $cmd = cmd::byString('#'. $queue_action .'#');
  $cmd->execCmd($cmd_option);
}

function send_notification($message){
  global $scenario;
  $queue_action = '[Notifications][Queue Erreurs journée][Ajouter]';
  
  // Ajout à la queue
  $cmd_option = array('title' => "", 'message' => $message);
  $scenario->setLog('Commande : '. $queue_action. '/ ' .json_encode($cmd_option));
  $cmd = cmd::byString('#'. $queue_action .'#');
  $cmd->execCmd($cmd_option);
}

$msg="";

$tags=$scenario->getTags();

# useless_errors : une liste de texte et si un log contient ce texte il sera droppé
# msg_to_reduce_array : une liste de texte et si un log contient ce texte il sera réduit à 100 caractères
# useless_plugins_errors : une liste de nom de plugin pour lesquels les logs sont droppés

$useless_errors=array("Attention le thermostat est suspendu","This parser can only read from strings or streams","La somme des sous-équipements est supérieure");
$msg_to_reduce_array=array("[Appartement][Attestations Covid]");
$useless_plugins_errors=array("vigilancemeteo","netatmoPublicData");
$once_day_plugins_errors=array("ics");

$plug=$tags['#plug#'];
$msg_source=$tags['#erreur#'];
  
$useless=false;

$listMessage = message::all();
$nbMessage = message::nbMessage();
$found_msg=null;

foreach ($listMessage as $message){  
  $message_text=$message->getMessage();
  #$scenario->setLog("message_text : ".$message_text." - msg_source : ".$msg_source);
  	if (strcmp('"'.$message_text.'"', $msg_source)==0) {
      // found the real message in list
 		$found_msg=$message;
        $scenario->setLog("Message trouvé : ".$message_text);
      break;
    }
}

if($found_msg != null){
    $plugin=$found_msg->getPlugin();
    $message_text=$found_msg->getMessage();
  	$date=$found_msg->getDate();
  	
  	// Remove some specific messages OR full plugins
    if(! in_array($plugin,$useless_plugins_errors)){
      // Remove some specific messages
      foreach ($useless_errors as $error_msg) {
          if (stristr($message_text, $error_msg)) {
              	// Useless message found -- then remove it
              	$useless=true;
              	$message->remove();
            	$scenario->setLog("DEBUG : message supprimé :".$message_text);
            	break;
          }
      }
      
      if(!$useless){
        $scenario->setLog("Not useless");
        // Append messages
        $msg .= "[".$date."]";
        $msg .= " (".$plugin.")";
        ($message->getAction() != "") ? $msg .= " (Action : ".$message->getAction().")" : null;
        foreach ($msg_to_reduce_array as $msg_to_reduce) {
          if (stristr($message_text, $msg_to_reduce)) {
              // Short the message
            	$scenario->setLog("Reduce message_text");
              $message_text=substr($message_text, 0, 100)." [...]";
          }
      	}
        $msg .= " ".$message_text."\n";
      }
      
      if($msg != ""){
		if(in_array($plugin,$once_day_plugins_errors)){
			send_notification_1j($msg);
		} else {
			send_notification($msg);
		}
    } else {
     	 $scenario->setLog("ERROR msg empty.. not sent so.");
    }
     } else {
     	$message->remove(); 
    }
} else {
 	$scenario->setLog("Message not found in error list : ".$msg); 
}

