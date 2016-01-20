<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(__FILE__).'/phpMQTT/phpMQTT.php');
require_once(dirname(__FILE__).'/Database.php');
require_once(dirname(__FILE__).'/Rosenberg.php');
$sRaspberryHost = "192.168.1.10";
$oDB = new Database($sRaspberryHost);

$mqtt = new phpMQTT($sRaspberryHost, 1883, "iws daemon");
if(!$mqtt->connect()){
	exit(1);
}
$topics['/iws/+/soil_moisture'] = array("qos"=>0, "function"=>"processMoisture");
$mqtt->subscribe($topics,0);
$oRosenberg = new Rosenberg($oDB);
$mqtt->subscribe($oRosenberg->getTopics());

while($mqtt->proc()){
	
}


$mqtt->close();

function processMoisture($topic,$msg) {
    print "processing $topic:$msg\n";
    global $oDB;
    $pTopic = "/\/iws\/(?P<sensor_id>MOIST_[0-9A-F]{8})\/soil_moisture/";
    if(!preg_match($pTopic, $topic, $aMatches)) {
        die("Couldn't match topic in $topic\n");
    }
    $data = array(
                array('tags' => array('sensor_id' => $aMatches['sensor_id']),
                    'fields' => array('millivolts' => (int)$msg),
                    'time' => date("c")));
    print "Inserting moisture $topic -> $msg\n";
    $oDB->insert("soil_moisture", $data);
}


?>
