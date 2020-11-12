<?
function startsWith( $haystack, $needle ) {
     $length = strlen( $needle );
     return substr( $haystack, 0, $length ) === $needle;
}

$known_values = array();
$known_values["mibox"] = "UUUUUUUUU-com-plexapp-android";

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


if(strcmp($payload["Server.title"],"YYYYYYYYYYYYYYYYYY") == 0 || strcmp($payload["Server.uuid"],"XXXXXXXXXXXXXXXX") == 0){
  #$scenario->setLog('Running on Plex NAS..');
  if($payload["Account.id"] == 1 || strcmp($payload["Account.title"],"ZZZZZZZZZ")==0){
    #$scenario->setLog('ZZZZZZZZZZ is playing ..');
  	if(startsWith($payload["Metadata.guid"],"tv.plex.xmltv") || startsWith($payload["Metadata.key"],"/livetv/")){
   		switch ($payload["event"]){
         	case "media.stop":
        		# que faire
            	$scenario->setLog('event : media.stop raised');
        		break;
           	case "media.play":
        		# que faire
            	$scenario->setLog('event : media.play raised');
            	$scenario->setLog($payload["Metadata.title"] . "is being played");
        		break;
            case "media.pause":
        		# que faire de pause ? rien ?
            	$scenario->setLog('event : media.pause raised');
        		break;
            case "media.resume":
        		# que faire de resume ? rien ?
            	$scenario->setLog('event : media.resume raised');
        		break;
          default:
            	$scenario->setLog('default : event='. $payload["event"]);
            	break;
        }
  	}
  }
}