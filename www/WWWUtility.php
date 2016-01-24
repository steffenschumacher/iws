<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WWWUtility
 *
 * @author ssch
 */
class WWWUtility {

    /**
     * 
     * @param mixed $sArg   Value of argument
     * @param type $sName   Name of argument
     * @param type $sPattern    Optional - input must match pattern
     * @param array $aNamedFields    Optional - if used, array of field => value is returned. 
     * @return mixed    Either value or array described in $aNamedFields
     * @throws InvalidArgumentException
     */
    public static function validateArg(array &$aArg, $sName, $sPattern = NULL, $aNamedFields = NULL) {
        if (!isset($aArg[$sName])) {
            throw new InvalidArgumentException("Missing '$sName' in arguments!");
        }
        $sArg = $aArg[$sName];
        if (isset($sPattern)) {
            if (!preg_match($sPattern, $sArg, $aMatches)) {
                throw new InvalidArgumentException("Illegal $sName argument: $sArg doesn't match $sPattern");
            }
            if (isset($aNamedFields)) {
                $aRetval = array();
                var_dump($aMatches);
                foreach ($aNamedFields as $sField) {
                    if (isset($aMatches[$sField]))
                        $aRetval[$sField] = $aMatches[$sField];
                }
                return $aRetval;
            }
        }
        return $sArg;
    }

    public static function validateCommaSeparatedArg(array &$aArg, $sName) {
        if (!isset($aArg[$sName])) {
            throw new InvalidArgumentException("Missing '$sName' in arguments!");
        } else if(!preg_match("/^\w+(,\w+)*$/", $aArg[$sName])) {
            throw new InvalidArgumentException("$sName isn't formatted correctly (v1,v2,v3..): ".$aArg[$sName]);
        }
        return explode(',', $aArg[$sName]);
    }
}
