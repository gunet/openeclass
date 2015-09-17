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

require_once 'autojudgeapp.php';

class AutojudgeDnnaApp extends AutojudgeApp implements AutoJudgeConnector {
    public function compile(AutoJudgeConnectorInput $input) {
        //set POST variables
        $url           = 'http://compile.dnna.gr/api/code/run';
        $fields_string = null;
        $fields        = array(
            //'client_secret' => AutojudgeApp::getAutoJudgeApp(get_class($this))->getParam('key')->value(),
            'input'         => $input->input,
            'source'        => urlencode($input->code),
            'lang'          => $input->lang,
        );

        // url-ify the data for the POST
        foreach ($fields as $key => $value) {
            $fields_string .= $key.'='.$value.'&';
        }
        // Remove last '&' character;
        rtrim($fields_string, '&');

        // Open curl connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        // Execute post
        $origResult = curl_exec($ch);
        $result = json_decode($origResult, true);
        if(!$result) {
            $output = new AutoJudgeConnectorResult();
            $output->compileStatus = 'ERROR';
            $output->output = curl_error($ch).' '.$origResult;
            curl_close($ch);
            return $output;
        }
        // Close curl connection
        curl_close($ch);

        $output = new AutoJudgeConnectorResult();
        $output->compileStatus = $result['compile_status'];
        $output->output = trim($result['output']);

        return $output;
    }

    public function getConfigFields() {
        return array();
    }

    public function getServiceURL() {
        return 'compile.dnna.gr';
    }

    public function getSupportedLanguages() {
        // Open curl connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        $url = 'http://compile.dnna.gr/api/languages';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        // Execute post
        $result = json_decode(curl_exec($ch), true);
        // Close curl connection
        curl_close($ch);

        if(!$result) { return array('Error connecting to compilation service. Please report this to dnna@dnna.gr' => 'error'); }

        return $result;
    }

    public function supportsInput() {
        return true;
    }

    public function getName() {
        return 'compile.dnna.gr';
    }
}
