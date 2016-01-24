<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(__FILE__)."/../Rosenberg.php");
require_once(dirname(__FILE__).'/../Config.php');
require_once(dirname(__FILE__)."/WWWUtility.php");
if(php_sapi_name() !== 'cli') {
    processGet($_GET);
} else {
    $args1 = array('type' => 'group', 'name' => 'temp');
    $args2 = array('type' => 'data', 'series' => 'dewpoint_C,heatindex_C,inDewpoint', 'from' => time()-86400, 'duration' => 86400);
    processGet($args1);
}

function processGet(array $aArgs) {
    header('Content-Type: application/json');
    try {
        $sType = WWWUtility::validateArg($aArgs, 'type', '/^(data|group)$/');
        switch($sType) {
            case 'group': 
                print processGroup($aArgs);
                break;

            case 'data':
                print processData($aArgs);
        }
    } catch (InvalidArgumentException $iae) {
        print "error: " . $iae->getMessage() . ";";
    }
}

function processGroup(array &$aArgs) {
        $sSeriesGroup = WWWUtility::validateArg($aArgs, 'name', "/^(temp|prec|baro|humid|wind)/");
        
        switch($sSeriesGroup) {
            case 'temp';
                return json_encode(Rosenberg::getTemperatureSeries());
            case 'prec':
                return json_encode(Rosenberg::getPrecipationSeries());
            case 'baro':
                return json_encode(Rosenberg::getBarometerSeries());
            case 'humid':
                return json_encode(Rosenberg::getHumiditySeries());
            case 'wind':
                return json_encode(Rosenberg::getWindSeries());   
        }
}

function processData(array &$aArgs) {
    $aSeries = WWWUtility::validateCommaSeparatedArg($aArgs, 'series');
    $iFrom = WWWUtility::validateArg($aArgs, 'from', '/^\d+$/');
    $iDuration = WWWUtility::validateArg($aArgs, 'duration', '/^\d+$/');
    $oRosenberg = initRosenberg();
    $aData = $oRosenberg->getData($iFrom, ($iFrom+$iDuration), $aSeries);
    $aJsonArray = array();
    foreach($aSeries as $sDataKey) {
        $aJsonArray[$sDataKey] = array('label' => $sDataKey, 'data' => array());
    }
    foreach($aData as $aDataPoint) {
        $iTime = $aDataPoint->time;
        foreach($aSeries as $sDataKey) {
            $fValue = (isset($aDataPoint->$sDataKey) ? (float)$aDataPoint->$sDataKey : null);
            array_push($aJsonArray[$sDataKey]['data'], array($iTime*1000, $fValue));
        } 
    }
    return json_encode($aJsonArray);
}

function initRosenberg() {
    return new Rosenberg(
            new Database(
                    Config::$sInfluxDBHost, 
                    Config::$iInfluxDBPort,
                    Config::$sInfluxDBUser,
                    Config::$sInfluxDBPassword,
                    false
                )
            );
}


