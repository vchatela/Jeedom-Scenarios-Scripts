<?
$cmd_alarme_etat = "[Sécurité][Alarme V2][Actif]";
$id_alarme_etat = cmd::byString('#'.$cmd_alarme_etat.'#')->getId();

$data_json='{
  	"absent":0,
  	"partiel":{"chauffe":0,"off":0},
  	"present":{"chauffe":0,"off":0}
}';

$data = json_decode($data_json, true);

#$begin = new DateTime('2021-07-01 00:00');
#$end = new DateTime('today midnight');

$begin = new DateTime('2020-07-01 00:00');
$end = new DateTime('2021-06-31 00:00');

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);

foreach ($period as $dt) {
  	$date_debut = $dt->format("Y-m-d H:i:s");
    $date_fin = $dt->modify('+1 day')->format("Y-m-d H:i:s");
  
  	$cmd_actif_th_sejour = "[Séjour][Thermostat Séjour][Actif]";
  	$id_actif_th_sejour = cmd::byString('#'.$cmd_actif_th_sejour.'#')->getId();
  	$duration_actif = scenarioExpression::durationbetween($id_actif_th_sejour,1,$date_debut,$date_fin);
  	#$scenario->setLog("Chauffé :".." min");
  
  	if($duration_actif > 30){
      	$etat = "chauffe";
    } else {
      	$etat = "off";
    }
  
  	# duration alarme
	$duration_alarme = scenarioExpression::durationbetween($id_alarme_etat,1,$date_debut,$date_fin);
  	if($duration_alarme > 900){
  		$data["absent"]++;
      	#$scenario->setLog("ABSENT - ".$date_debut."-".$date_fin." - Alarme=".$duration_alarme." min - Chauffe=".$duration_actif." min");
    } else if($duration_alarme > 240){
      	$data["partiel"][$etat]++;
      	#$scenario->setLog("PARTIEL ".$etat."- ".$date_debut."-".$date_fin." - Alarme=".$duration_alarme." min - Chauffe=".$duration_actif." min");
    } else{
      	$data["present"][$etat]++;
      	#$scenario->setLog("PRESENT ".$etat."-".$date_debut."-".$date_fin." - Alarme=".$duration_alarme." min - Chauffe=".$duration_actif." min");
    }
}
#$scenario->setLog("PRESENT - ".$date_present_total." - PARTIEL=".$date_present_partiel." - ABSENT=".$date_absent);
$scenario->setLog("Du : ".$begin->format("Y-m-d")." au ".$end->format("Y-m-d"));
$scenario->setLog(json_encode($data,JSON_PRETTY_PRINT ));


