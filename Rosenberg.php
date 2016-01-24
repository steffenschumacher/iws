<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(__FILE__)).'/MQTTProcessor.php';
require_once(dirname(__FILE__)).'/Database.php';
/**
 * Description of Rosenberg
 *
 * @author ssch
 */
class Rosenberg extends MQTTProcessor {
    protected $oInfluxDB;
    private $sTopic;
    private $iTopicLen;
    private $aDataSet;
    
    
    function __construct(Database $oInfluxDB) {
        $this->oInfluxDB = $oInfluxDB;
        $this->sTopic = '/weather/rosenberg/';
        $this->iTopicLen = strlen($this->sTopic);
        $this->aDataSet = array();
    }

    public function process($topic, $msg) {
        $sType = substr($topic, $this->iTopicLen);
        switch($sType) {
            case 'rxCheckPercent':
            case 'interval_minute':
            case 'ptr':
            case 'outTempBatteryStatus':
            case 'delay':
            case 'status':
            case 'usUnits':
            case 'appTemp':
                break;  //Ignore these
            
            case 'inHumidity':  //Final part of dataset
                $this->aDataSet[$sType] = $msg;
                $this->store();
                
            default:
                $this->aDataSet[$sType] = $msg;
        }
    }
    
    public static function getTemperatureSeries() {
        return array(
            "dewpoint_C" => array("name" => "outdoor dew point", "defaultOn" => true),
            "heatindex_C" => array("name" => "heat index", "defaultOn" => false),
            "inDewpoint" => array("name" => "indoor dew point", "defaultOn" => false),
            "windchill_C" => array("name" => "wind chill temperature (outdoor)", "defaultOn" => true),
            "outTemp_C" => array("name" => "outdoor temperature", "defaultOn" => true),
            "inTemp_C" => array("name" => "indoor temperature", "defaultOn" => true)            
        );
    }
    
    public static function getPrecipationSeries() {
        return array(
            "rain_cm" => array("name" => "rain", "defaultOn" => true),
            "rainTotal" => array("name" => "total rain", "defaultOn" => false),
            "dayRain_cm" => array("name" => "day rain", "defaultOn" => true),
            "rain24_cm" => array("name" => "24h rain", "defaultOn" => true),
            "hourRain_cm" => array("name" => "1h rain", "defaultOn" => true),
            "rainRate_cm_per_hour" => array("name" => "rain/1h", "defaultOn" => true)
        );
    }
    
    public static function getBarometerSeries() {
        return array(
            "pressure_mbar" => array("name" => "pressure", "defaultOn" => true),
            "barometer_mbar" => array("name" => "barometer", "defaultOn" => true),
            "altimeter_mbar" => array("name" => "altimeter", "defaultOn" => true)
        );
    }
    
    public static function getHumiditySeries() {
        return array(
            "outHumidity" => array("name" => "outside humidity", "defaultOn" => true),
            "humidex" => array("name" => "humidex", "defaultOn" => false),
            "inHumidity" => array("name" => "inside humidity", "defaultOn" => true)
        );
    }
    
    public static function getWindSeries() {
        return array(
            "windGust_kph" => array("name" => "wind gust", "defaultOn" => true),
            "windSpeed_kph" => array("name" => "wind speed", "defaultOn" => true)
        );
    }
    
    
    private function store() {
        print "storing rosenberg data:\n";
        var_dump($this->aDataSet);
        $time = (int)$this->aDataSet['dateTime'] .'s';
        unset ($this->aDataSet['dateTime']);
        $data = array(
                array('tags' => array('station' => 'ternevej 11'),
                    'fields' => $this->aDataSet,
                    'time' => $time));
        $this->oInfluxDB->insert('rosenberg', $data);
        $this->aDataSet = array();
    }
    
    public function getData($iStart, $iEnd, $aSeries) {
        $sStart = $iStart.'s';
        $sEnd = $iEnd.'s';
        $sQuery = "SELECT " . implode(", ", $aSeries) . " FROM rosenberg where time >= $sStart and time <= $sEnd";
        print $sQuery;
        $aResult = $this->oInfluxDB->query($sQuery);
        return $aResult;
    }
    
    public function getTopics() {
        $aTopics = array();
        $aTopics["/weather/rosenberg/+"] = $this->getDefaultProcessArgs();
        return $aTopics;
    }

}
