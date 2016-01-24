<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(__FILE__)."/../Rosenberg.php");

$sRaspberryHost = "192.168.1.10";
$oDB = new Database($sRaspberryHost);
$oRosenberg = new Rosenberg($oDB);

$oRosenberg->getData(time()-86400, time(), array_keys($oRosenberg->getTemperatureSeries()));
