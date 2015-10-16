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

require_once 'wafapp.php';
$loader = require_once 'vendor/autoload.php';

class phpids extends WafApp implements WafConnector {
    public function check() {
        $cmdoutput = "";
        try {
            if (file_exists('courses/temp/inuse_filter.json')){
                $request = array(
                    'REQUEST' => $_REQUEST,
                    'GET' => $_GET,
                    'POST' => $_POST,
                    'COOKIE' => $_COOKIE
                );
                $init = new IDS\Init();
                $init->init('vendor/phpids/phpids/lib/IDS/Config/Config.ini.php');
//                $init->config['General']['base_path'] = 'vendor/phpids/phpids/lib/IDS/';
                $init->config['General']['filter_type'] = 'json';
                $init->config['General']['filter_path'] = 'courses/temp/inuse_filter.json';
                $init->config['General']['tmp_path'] = 'courses/temp/';
                $init->config['General']['use_base_path'] = false;
                $init->config['General']['scan_keys'] = false;
                $init->config['Caching']['path'] = 'courses/temp/default_filter.cache';
                $ids = new IDS\Monitor($init);
                $result = $ids->run($request);
                $cmdoutput = $result;
                if (!$result->isEmpty()) {

                    $output = new WafConnectorResult();
                    $output->status = $output::STATUS_BLOCKED;
                    $output->output = trim($cmdoutput);
                    return $output;
                
                } else {
               
                    $output = new WafConnectorResult();
                    $output->status = $output::STATUS_OK;
                    $output->output = trim($cmdoutput);
                    return $output;
               
                }
            } else {
               
                $output = new WafConnectorResult();
                $output->status = $output::STATUS_NOTCHECKED;
                $output->output = trim($cmdoutput);
                return $output;
               
            }
        } catch (Exception $e) {
           
            $output = new WafConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            $output->output = $e;//trim($cmdoutput);
            return $output;
        
        }

    }

    public function unsetValue(array $array, $value, $strict = TRUE)
    {
        if(($key = array_search($value, $array, $strict)) !== FALSE) {
            unset($array[$key]);
        }
        return $array;
    }

    public function array2string($data){
        $log_a = "";
        foreach ($data as $key => $value) {
            if ($log_a!=""){
                $log_a .= ",";
            }
            $log_a .= json_encode($value);
        }
        return $log_a;
    }

    public function updateRules(){
        $json_str = file_get_contents('vendor/phpids/phpids/lib/IDS/default_filter.json');
        $filters = json_decode($json_str);
        if (!$filters) {
            return 'Error';
        }
        $filtersarray = json_decode($json_str, true);
        $myfilters = $filters->filters->filter;
        $mynewfilters = $myfilters;
        foreach ($myfilters as $filter) {
            $id = $filter->id;
            if (get_config('phpids_rule'.$id) == 0  || get_config('phpids_rule'.$id)==""){
                $mynewfilters = $this->unsetValue($mynewfilters,$filter);
            }
        }
        $packedmynewfilters = "{\"filters\":{\"filter\":[".$this->array2string($mynewfilters)."]}}";
        
        file_put_contents('courses/temp/inuse_filter.json', $packedmynewfilters);
        if (file_exists('courses/temp/default_filter.cache')){
            unlink('courses/temp/default_filter.cache');
        }    
        return 1;
    }


    public function preloadConfigFields(){
        $preload = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78];
        $notpreload = [1];
        foreach($preload as $id){
            if (get_config('phpids_rule'.$id)==""){
                set_config('phpids_rule'.$id, '1');
            }
        }
        foreach($notpreload as $id){
            if (get_config('phpids_rule'.$id)==""){
                set_config('phpids_rule'.$id, '0');
            }
        }
    }

    public function getRules(){
        $filters = json_decode(file_get_contents('vendor/phpids/phpids/lib/IDS/default_filter.json'));
        if (!$filters) {
            return 'Error';
        }
        $rules = array();
        $filters = $filters->filters->filter;
        foreach ($filters as $filter) {
            $id          =  $filter->id;
            $rule        =  $filter->rule;
            $description =  $filter->description;
            $impact      =  $filter->impact;
            $rules['phpids_rule'.$id] = array('rule'=>$rule,'description'=>$description, 'impact'=>$impact); 
        }
        return $rules;

    }
    
    public function getConfigFields() {
        $rules = $this ->getRules();

        $this->preloadConfigFields();

        $config = array();

        foreach($rules as $id=>$rule){
            $config[$id]=substr($id,11);
        }
        return $config;
    }


    public function getName() {
        return 'PHPIDS';
    }
}