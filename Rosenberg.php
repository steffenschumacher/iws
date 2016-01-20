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
    
    private function store() {
        print "storing rosenberg data:\n";
        var_dump($this->aDataSet);
        $time = $this->aDataSet['dateTime'];
        unset ($this->aDataSet['dateTime']);
        $data = array(
                array('tags' => array('station' => 'ternevej 11'),
                    'fields' => $this->aDataSet,
                    'time' => date("c", $time)));
        $this->oInfluxDB->insert('rosenberg', $data);
        $this->aDataSet = array();
    }
    
    public function getTopics() {
        $aTopics = array();
        $aTopics["/weather/rosenberg/+"] = $this->getDefaultProcessArgs();
        return $aTopics;
    }

}
