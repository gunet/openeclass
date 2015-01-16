<?php
require_once 'connector.php';

class CompileDnnaConnector implements AutoJudgeConnector {
    public function compile(AutoJudgeConnectorInput $input) {
        //set POST variables
        $url           = 'http://compile.dnna.gr/api/code/run';
        $fields_string = null;
        $fields        = array(
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
        $output->output = trim($result['output']);

        return $output;
    }

    public function getConfigFields() {
        return array();
    }

    public function getName() {
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
}