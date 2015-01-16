<?php
require_once 'connector.php';

class HackerEarthConnector implements AutoJudgeConnector {
    public function compile(AutoJudgeConnectorInput $input) {
        //set POST variables
        $url           = 'http://api.hackerearth.com/code/run/';
        $fields_string = null;
        $fields        = array(
            'client_secret' => q(get_config('hackerEarthKey')),
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
        $result = json_decode(curl_exec($ch), true);
        // Close curl connection
        curl_close($ch);

        $output = new AutoJudgeConnectorResult();
        $output->compileStatus = $result['compile_status'];
        $output->output = trim($result['run_status']['output']);

        return $output;
    }

    public function getConfigFields() {
        global $langHackerEarth;
        return array(
            'hackerEarthKey' => $langHackerEarth
        );
    }

    public function getName() {
        return 'HackerEarth';
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
}