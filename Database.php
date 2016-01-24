<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
foreach(glob(dirname(__FILE__).'/InfluxPHP/lib/InfluxPHP/*.php') as $sFile) {
    require_once($sFile);
}
/**
 * Description of Database
 *
 * @author ssch
 */
class Database {
    protected $db; //crodas\InfluxPHP\DB
    public function __construct($sHost = "localhost", $iPort = 8086, $sUser = 'iws', $sPassword = 'detgulehus', $bCheckExistence = true) {
        $client = new crodas\InfluxPHP\Client($sHost, $iPort, $sUser, $sPassword);
        if($bCheckExistence) {
            $aDBs = $client->getDatabases();
            if(isset($aDBs)) {
                foreach($aDBs as $oDB) {
                    if($oDB->getName() == 'iws') {
                        print "iws database exists - skipping creation..\n";
                        $this->db = $oDB;
                        return;
                    }
                }
            }
            print "creating missing database for iws ts data..\n";
            $this->db = $client->createDatabase("iws");
            $client->grantPrivilege(crodas\InfluxPHP\Client::PRIV_ALL, "iws", "iws");
        } else {
            $this->db = $client->getDatabase("iws");
        }
    }

    public function insert($name, array &$data) {
        $this->db->insert($name, $data);
    }
    
    public function query($sQuery) {
        return $this->db->query($sQuery);
    }
    
    public function execute($sQuery) {
        $this->db->query($sQuery);
    }
}
