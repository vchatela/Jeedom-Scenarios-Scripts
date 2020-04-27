<?
$msg = "";

# useless_errors : une liste de texte et si un log contient ce texte il sera droppé
# msg_to_reduce_array : une liste de texte et si un log contient ce texte il sera réduit à 100 caractères
# useless_plugins_errors : une liste de nom de plugin pour lesquels les logs sont droppés

$useless_errors=array("Attention le thermostat est suspendu","This parser can only read from strings or streams");
$msg_to_reduce_array=array("[Appartement][Attestations Covid]");
$useless_plugins_errors=array("vigilancemeteo");

$listMessage = message::all();
foreach ($listMessage as $message){
 	$useless=false;
  
  	$plugin=$message->getPlugin();
    $message_text=$message->getMessage();
  	$date=$message->getDate();
  
  	// Remove some specific messages OR full plugins
    if(! in_array($plugin,$useless_plugins_errors)){
      // Remove some specific messages
      foreach ($useless_errors as $error_msg) {
        	//$scenario->setLog("DEBUG : error_msg :".$error_msg);
          if (stristr($message_text, $error_msg)) {
              // Useless message found -- then remove it
              $useless=true;
              $message->remove();
            	$scenario->setLog("DEBUG : message supprimé :".$message_text);
          }
      }
      
      if(!$useless){
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
    } else {
     	$message->remove(); 
    }
}
$tags['#msg#'] = $msg;
$scenario->setTags($tags);
>