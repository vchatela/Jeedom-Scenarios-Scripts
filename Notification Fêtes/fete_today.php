<?
	$cmd = cmd::byString("#[Notifications][Domogeek news][Saint du jour]#");
$events_today = $cmd->execCmd();
$person_list = $scenario->getData("fete_a_souhaiter");
$fete_today_list = array();
$person_array = explode(";", $person_list);
foreach ($person_array as $person) {
	if (strpos(strtolower($events_today), strtolower($person)) !== false) {
      $scenario->setLog("P : $person");
		#event anniversaire
      	array_push($fete_today_list,$person);
	}
}
if(!empty($fete_today_list)){
	$scenario->setData("event_fete_today", join(', ', $fete_today_list));
}
>