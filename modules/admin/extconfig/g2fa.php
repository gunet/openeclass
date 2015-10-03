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

require_once 'secondfaapp.php';

class g2fa extends secondfaapp implements secondfaConnector {
    public function check($userid, $answer, $google2fa_secret) {
        $twofa = new PragmaRX\Google2FA\Google2FA();
        $result =  $twofa->verifyKey($google2fa_secret, $answer);

        if ($result == true){
            $output = new secondfaConnectorResult();
            $output->status = $output::STATUS_OK;
            $output->output = "OK";
            return $output;
        }
        elseif ($result == false){
            //For the admin log implode("|",$cmdoutput)
            $output = new secondfaConnectorResult();
            $output->status = $output::STATUS_FAIL;
            $output->output = "FAIL";
            return $output;
        }
        else {
            $output = new secondfaConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            $output->output = "UNKNOWN";
            return $output;
        }
    }

    public function generateSecret($userid, $company, $email) {
        $twofa = new PragmaRX\Google2FA\Google2FA();
        $google2fa_secret = $twofa->generateSecretKey();
        $google2fa_url = $twofa->getQRCodeInline($company,$email,$google2fa_secret,200);
        return array($google2fa_url, $google2fa_secret);
    }


    public function preloadConfigFields(){
            
    }
    
    public function getConfigFields(){
        $this->preloadConfigFields();
        return array();
    }

    public function getName() {
        return 'Google Authenticator';
    }
}