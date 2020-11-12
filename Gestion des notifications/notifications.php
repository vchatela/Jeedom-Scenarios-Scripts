<?
function send_slack($channel, $message){
	$cmd_option = array('message' => $message);
	$cmd = cmd::byString($channel);
	$cmd->execCmd($cmd_option);
}

$tags=$scenario->getTags();
$scenario->setLog(print_r($tags,true));

# topics
$topic_list = array(
	"global" => "[Notifications][Slack Jeedom][Message jeedomtoulouse]",
	"reporting" => "[Notifications][Slack Jeedom][Message jeedomtoulouse_reporting]",
	"electricite" => "[Notifications][Slack Jeedom][Message jeedomtoulouse_electricite]",
	"alerte" => "[Notifications][Slack Jeedom][Message jeedomtoulouse_alerte]",
	"evenement" => "[Notifications][Slack Jeedom][Message jeedomtoulouse_evenement]",
	"alarme" => "[Notifications][Slack Jeedom][Message jeedomtoulouse_alarme]",
	"plex" => "[Notifications][Slack Jeedom][Message jeedomtoulouse_plex]"
);

if(array_key_exists('#topic#',$tags)){
	$topic=str_replace('"','',$tags['#topic#']);
} else {
	$topic="global";
}

$channel="#".$topic_list[$topic]."#";

# Gestion titre
if(array_key_exists("#titre#",$tags)){
	$titre=str_replace('"','',$tags["#titre#"]);
	send_slack($channel,"*".$titre."*");
}
# Gestion message
if(array_key_exists("#message#",$tags)){
	$message=str_replace('"','',$tags['#message#']);
} else {
	# error !!!
	$scenario->setLog("No message received..");
}
send_slack($channel,"[".ucwords($topic)."] ".$message);