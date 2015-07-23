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
require_once 'include/simplehtmldom/simple_html_dom.php';

class AutojudgeCodepadApp extends AutojudgeApp implements AutoJudgeConnector {
    public function compile(AutoJudgeConnectorInput $input) {
        //set POST variables
        $url = 'http://codepad.org/';
        $fields_string = null;
        $fields = array(
            'code' => urlencode($input->code),
            'lang' => $input->lang,
            'run' => 'True',
            'private' => 'True',
            'submit' => 'Submit',
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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // Execute post
        $result = curl_exec($ch);
        // Close curl connection
        curl_close($ch);

        $html = str_get_html($result);
        $ret = $html->find('div.code .highlight', 3);

        $output = new AutoJudgeConnectorResult();
        $output->compileStatus = $output::COMPILE_STATUS_OK;
        $output->output = trim($ret->plaintext);

        return $output;
    }

    public function getConfigFields() {
        return array();
    }

    public function getServiceURL() {
        return 'codepad.org';
    }

    public function getSupportedLanguages() {
        return array(
            'C' => 'c',
            'C++' => 'cpp',
            'D' => 'd',
            'Haskell' => 'hs',
            'Lua' => 'lua',
            'OCaml' => 'ml',
            'PHP' => 'php',
            'Perl' => 'pl',
            'Plain Text' => 'txt',
            'Python' => 'py',
            'Ruby' => 'rb',
            'Scheme' => 'scm',
            'Tcl' => 'tcl',
        );
    }

    public function supportsInput() {
        return false;
    }

    public function getName() {
        return 'Codepad';
    }
}