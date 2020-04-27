<?
$cmd = cmd::byString("#[Toulouse][Calendrier Google][Today]#");
$events_today = $cmd->execCmd();
$birthday_today_list = array();
$events_list = explode(";", $events_today);
foreach ($events_list as $event) {
	if (strpos(strtolower($event), 'anniversaire') !== false) {
		#event anniversaire
		$event = str_replace("Toute la journée :", "", $event); 
		array_push($birthday_today_list,$event);
		#$scenario->setData("event_anniversaire", $event);
	}
}

if(!empty($birthday_today_list)){
	$scenario->setData("event_anniversaire", join('-', $birthday_today_list));
        #$scenario->setLog(join('-', $birthday_today_list));
}
>