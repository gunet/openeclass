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

require_once 'autojudgeapp.php';

class AutojudgeHackerearthApp extends AutojudgeApp implements AutoJudgeConnector {
    public function compile(AutoJudgeConnectorInput $input) {
        //set POST variables
        //$url           = 'https://api.hackerearth.com/code/run/';
        $url = 'https://api.hackerearth.com/v4/partner/code-evaluation/submissions/';
        $fields_string = null;
        $fields        = array(
            'client_secret' => q(get_config('autojudge_hackerEarthKey')),
            'input'         => $input->input,
            'source'        => urlencode($input->code),
            'lang'          => $input->lang,
            'content_type'  => 'application/json'
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
        $output->output = trim($result['run_status']['output']);

        return $output;
    }

    public function getConfigFields() {
        return array(
            'autojudge_hackerEarthKey' => 'Hackerearth API Key',
        );
    }

    public function getServiceURL() {
        return 'hackerearth.com';
    }

    public function getSupportedLanguages() {
        return array(
            'C' => 'c',
            'CPP' => 'cpp',
            'CPP11' => 'cpp11',
            'CLOJURE' => 'clj',
            'CSHARP' => 'cs',
            'JAVA' => 'java',
            'JAVASCRIPT' => 'js',
            'HASKELL' => 'hs',
            'PERL' => 'pl',
            'PHP' => 'php',
            'PYTHON' => 'py',
            'RUBY' => 'rb',
        );
    }

    public function supportsInput() {
        return true;
    }

    public function getName() {
        return 'Hackerearth';
    }
}
