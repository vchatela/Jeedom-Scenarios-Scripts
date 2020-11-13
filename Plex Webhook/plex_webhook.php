<?
function startsWith( $haystack, $needle ) {
     $length = strlen( $needle );
     return substr( $haystack, 0, $length ) === $needle;
}

function send_notification($message){
  	global $scenario;
 	$id_notif_scenario=8;
    $notif_scenario=scenario::byId($id_notif_scenario);
  	$titre=$scenario->getName();# scenario name

    #Récupérer les tags dans un scenaraio
    $tags = $notif_scenario->getTags();
    #Ajouter des tags
    $tags['#titre#'] = "Scenario : ".$titre;
    $tags['#message#'] = $message;
    $tags['#topic#'] = "plex";

  	
    $scenario->setLog("Notification envoyée : ".$message);
  
    #Passer les tags à un sous-scenario et le lancer
    $notif_scenario->setTags($tags);
    $notif_scenario->launch(); 
}


function send_to_queue($message){
  global $scenario;
  $queue_action = '[Equipements][Queue Plex][Ajouter]';
  
  // Ajout à la queue
  $cmd_option = array('title' => "", 'message' => $message);
  $scenario->setLog('Commande : '. $queue_action. '/ ' .json_encode($cmd_option));
  $cmd = cmd::byString('#'. $queue_action .'#');
  $cmd->execCmd($cmd_option);
}


$known_values = array();
$known_values["mibox_toulouse.uuid"] = "106e1da57d2ea0ff-com-plexapp-android";

// On récupère le payload
$cmd = cmd::byString("#[Equipements][Plex Webhook][payload]#");
$postdata = $cmd->execCmd();

// On le décode
$postdata = utf8_encode($postdata);
$scenario->setLog('PostData :'.$postdata);
$results = json_decode($postdata);

// On récupère l'événement déclencheur: media.play, media.pause, media.stop, ...
$payload = array();
$payload["event"] = $results->event;
$payload["Player.uuid"] = $results->Player->uuid;
$payload["Account.title"] = $results->Account->title;
$payload["Account.id"] = $results->Account->id;
$payload["Server.title"] = $results->Server->title;
$payload["Server.uuid"] = $results->Server->uuid;
$payload["Metadata.title"] = $results->Metadata->title;
$payload["Metadata.type"] = $results->Metadata->type;
$payload["Metadata.key"] = $results->Metadata->key;
$payload["Metadata.guid"] = $results->Metadata->guid;

$message_stop="[OK] Plex disponible.";
$message_start="[Occupé] Valentin regarde : ".$payload["Metadata.title"];

if(strcmp($payload["Server.title"],"synology_vc") == 0 || strcmp($payload["Server.uuid"],"a626911308c33cf28a2bc4f7465d41360e105b83") == 0){
  #$scenario->setLog('Running on Plex NAS..');
  if($payload["Account.id"] == 1 || strcmp($payload["Account.title"],"chatelard.valentin")==0){
    #$scenario->setLog('Valentin is playing ..');
  	if(startsWith($payload["Metadata.guid"],"tv.plex.xmltv") || startsWith($payload["Metadata.key"],"/livetv/")){
   		switch ($payload["event"]){
         	case "media.stop":
        		send_notification($message_stop);
				send_to_queue($message_stop);
            	$scenario->setData('plex_running',0);
            	$scenario->setLog('event : media.stop raised');
        		break;
           	case "media.play":
        		send_notification($message_start);
				send_to_queue($message_start);
            	$scenario->setData('plex_running',1);
            	$scenario->setLog('event : media.play raised');
            	$scenario->setLog($payload["Metadata.title"] . "is being played");
        		break;
            case "media.pause":
        		# que faire de pause ? rien ?
				if($scenario->getData('plex_running') == 1){
            		$scenario->setData('plex_running',0);
                  	send_notification($message_stop);
					send_to_queue($message_stop);
                }
            	$scenario->setLog('event : media.pause raised');
        		break;
            case "media.resume":
        		# que faire de resume ? rien ?
            	if($scenario->getData('plex_running') == 0){
            		$scenario->setData('plex_running',1);
                  	send_notification($message_start);
					send_to_queue($message_start);
                }
            	$scenario->setLog('event : media.resume raised');
        		break;
            case "media.scrobble":
        		# que faire de resume ? rien ?
            	if($scenario->getData('plex_running') == 0){
            		$scenario->setData('plex_running',1);
                  	send_notification($message_start);
					send_to_queue($message_start);
                }
            	$scenario->setLog('event : media.scrobble raised');
        		break;
          default:
            	$scenario->setLog('event not known... : event='. $payload["event"]);
            	break;
        }
  	}
  }
}