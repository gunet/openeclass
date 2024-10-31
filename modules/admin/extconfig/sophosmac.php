<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

require_once 'antivirusapp.php';

class SophosMac extends AntivirusApp implements AntivirusConnector {
    public function check($input) {

        $executable = escapeshellcmd(get_config('sophosmac_executable'));
        $filelocation =  escapeshellcmd(preg_replace('/[^A-Za-z0-9-.\/]/', '', $input));
        $infectedfolder = escapeshellcmd(get_config('sophosmac_move_param'));
        if (!file_exists($infectedfolder)) {
            mkdir($infectedfolder, 0700);
        }
        if (!file_exists($executable) || !$this->verifyExecutable()) {
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            return $output;
        }
        $cmd = $executable . ' -sc -nc -archive -ss -move='.$infectedfolder. ' ' . $filelocation . ' 2>&1';
        exec($cmd, $cmdoutput, $result);
        if ($result == 0){
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_OK;
            $output->output = trim($cmdoutput);
            return $output;
        }
        elseif ($result == 3){
            //For the admin log implode("|",$cmdoutput)
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_INFECTED;
            $output->output = $GLOBALS['langAntivirusInfected'];
            return $output;
        }
        else {
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            $output->output = trim($cmdoutput);
            return $output;
        }
    }


    public function verifyExecutable(){
        $executable = get_config('sophosmac_executable');
        if ($executable == ""){
            return 0;
        }
        if (!file_exists($executable)){
            return 0;
        }
        $default_executable_path = exec('which sweep 2>/dev/null');
        if($default_executable_path && file_exists($default_executable_path) && $executable !== $default_executable_path){
            return 0;
        }
        if (preg_match('/[^A-Za-z0-9-.\/]/', $executable)) {
            return 0;
        }
        if (!preg_match('/.*\/sweep$/', $executable)) {
            return 0;
        }
        if (strpos(realpath($executable),getcwd())) {
            return 0;
        }
        return 1;
    }

    public function preloadConfigFields(){
        if (get_config('sophosmac_move_param')==""){
                set_config('sophosmac_move_param','/tmp/infected/');
            }
            if (get_config('sophosmac_executable')=="" || !$this->verifyExecutable()){
                $default_executable_path = exec('which sweep 2>/dev/null');
                if($default_executable_path && file_exists($default_executable_path)){
                    set_config('sophosmac_executable', $default_executable_path);
                }else{
                    set_config('sophosmac_executable','sweep');
                }
            }
    }

    public function getConfigFields() {
        $this->preloadConfigFields();
        return array(
            'sophosmac_move_param' => 'Move Infected Files Folder',
            'sophosmac_executable' => 'Sophos (Sweep) Location'
        );
    }


    public function getName() {
        return 'Sophos Mac';
    }
}
