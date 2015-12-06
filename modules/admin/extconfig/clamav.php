<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ========================================================================
 */

require_once 'antivirusapp.php';

class ClamAv extends AntivirusApp implements AntivirusConnector {
    public function check($input) {

        $executable = escapeshellcmd(get_config('clamav_executable')); 
        $filelocation =  escapeshellcmd(preg_replace('/[^A-Za-z0-9-.\/]/', '', $input));
        $infectedfolder = escapeshellcmd(get_config('clamav_move_param'));
        if (!file_exists($infectedfolder)) {
            mkdir($infectedfolder, 0700);
        }
        if (!file_exists($executable) || !$this->verifyExecutable()) {
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            return $output;
        }
        $cmd = $executable . '  --move='.$infectedfolder. ' ' . $filelocation . ' 2>&1';
        exec($cmd, $cmdoutput, $result);
        if ($result == 0){
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_OK;
            $output->output = trim($cmdoutput);
            return $output;
        }
        elseif ($result == 1){
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
        $executable = get_config('clamav_executable');
        if ($executable == ""){
            return 0;
        }
        if (!file_exists($executable)){
            return 0;
        }
        $default_executable_path = exec('which clamscan 2>/dev/null');
        if($default_executable_path && file_exists($default_executable_path) && $executable !== $default_executable_path){
            return 0;
        }
        if (preg_match('/[^A-Za-z0-9-.\/]/', $executable)) {
            return 0;
        }
        if (!preg_match('/.*\/clamscan$/', $executable)) {
            return 0;
        }
        if (strpos(realpath($executable),getcwd())) {
            return 0;
        }
        return 1;
    }

    public function preloadConfigFields(){
        if (get_config('clamav_move_param')==""){
                set_config('clamav_move_param','/tmp/infected/');
            }
            if (get_config('clamav_executable')=="" || !$this->verifyExecutable()){
                $default_executable_path = exec('which clamscan 2>/dev/null');
                if($default_executable_path && file_exists($default_executable_path)){
                    set_config('clamav_executable', $default_executable_path);
                }else{
                    set_config('clamav_executable','clamscan');
                }
            }
    }
    
    public function getConfigFields() {
        $this->preloadConfigFields();
        return array(
            'clamav_move_param' => 'Move Infected Files Folder',
            'clamav_executable' => 'ClamScan Location'
        );
    }


    public function getName() {
        return 'ClamAv Linux/Mac';
    }
}