<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 *
 * @author ssch
 */
abstract class MQTTProcessor {
    public abstract function getTopics();
    public abstract function process($topic, $msg);
    protected function getDefaultProcessArgs() {
        return array("qos"=>0, "function"=>array($this, "process"));
    }
}
