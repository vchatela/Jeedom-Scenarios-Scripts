<?
$cmd = cmd::byString("#[Toulouse][Calendrier Google][Tomorrow]#");
$events_tomorrow = $cmd->execCmd();
#$scenario->setLog("$events_tomorrow");
$birthday_tomorrow_list = array();
$events_list = explode(";", $events_tomorrow);
foreach ($events_list as $event) {
	if (strpos(strtolower($event), 'anniversaire') !== false) {
		#event anniversaire
		$event = str_replace("Toute la journée :", "", $event);
		array_push($birthday_tomorrow_list,$event);
		#$scenario->setData("event_tomorrow_anniversaire", $event);
	}
}

if(!empty($birthday_tomorrow_list)){
	$scenario->setData("event_tomorrow_anniversaire", join('-', $birthday_tomorrow_list));
        #$scenario->setLog(join('-', $birthday_tomorrow_list));
}
>