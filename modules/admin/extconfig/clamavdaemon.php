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

class clamavdaemon extends AntivirusApp implements AntivirusConnector {
    public function check($input) {

        $filelocation =  escapeshellcmd(preg_replace('/[^A-Za-z0-9-.\/]/', '', $input));

        if (!$host || !$port){
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            return $output;
        }
        
        $connector =  @fsockopen($host, $port);
        
        if(!$connector) {
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            return $output;            
        }

        if(fwrite($connector, "SCAN {$filelocation}\n") === FALSE) {
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            return $output;    
        }
        
        $cmdoutput = fgets($connector);

        if(!$cmdoutput){
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            return $output;    
        }

        fclose($connector);

        if (preg_match('/.*: OK$/', $cmdoutput)){
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_OK;
            $output->output = trim($cmdoutput);
            return $output;
        }
        elseif (preg_match('/.*: (.*) FOUND$/', $cmdoutput)){
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_INFECTED;
            $output->output = trim($cmdoutput);
            return $output;
        }
        else {
            $output = new AntivirusConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            $output->output = trim($cmdoutput);
            return $output;
        }
    }


    public function preloadConfigFields(){
        if (get_config('clamd_host')==""){
                set_config('clamd_host','127.0.0.1');
        }
        if (get_config('clamd_port')==""){
                set_config('clamd_port',9321);
        }
    }

    public function getConfigFields() {
        $this->preloadConfigFields();
        return array(
            'clamd_host' => 'ClamAv Host',
            'clamd_port' => 'ClamAv Port'
        );
    }


    public function getName() {
        return 'ClamAv Deamon';
    }
}