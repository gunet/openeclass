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

require_once 'genericrequiredparam.php';

class WafApp extends ExtApp {
    public static $ServiceNames = array("phpids");

    public function __construct() {
        parent::__construct();
    }

    public function getLongDescription() {
        return $GLOBALS['langWafDescription'];
    }

    public function getShortDescription() {
        return $GLOBALS['langWafDescription'];
    }

    public function getDisplayName() {
        return 'WAF';
    }

    public function getConfigUrl() {
        return 'modules/admin/wafmoduleconf.php';
    }

    /**
     * Returns true if a compilation service has been selected
     *
     * @return boolean
     */
    public function isConfigured() {
        return (q(get_config('waf_connector')) != null);
    }

    public static function block($output) {
        require_once 'include/tools.php';
        Session::Messages($output);
        header("Location: {$_SERVER['SCRIPT_NAME']}");
        exit();
    }

    public static function getWaf() {
        $antivirus = ExtAppManager::getApp('waf');
        $connector = q(get_config('waf_connector'));
        if(!$connector) {
            $connector = new phpids();
        } else {
            $connector = new $connector();
        }       
        $param = $connector->getParam('enabled');
        if ($param) {
            $param->setValue($antivirus->isEnabled());
        }
        return $connector;
    }

    /**
     * @return ExtApp[]
     */
    public static function getWafServices() {
        if (self::$ServiceNames == null) {
            $apps = array();
            foreach (self::$ServiceNames as $serviceName) {
                $service = new $serviceName();
                $apps[$service->getName()] = $service;
            }
            self::$ServiceNames = $apps;
        }
        return self::$ServiceNames;
    }
}

interface WafConnector {
    public function check();

    public function getConfigFields();

    public function getName();

    public function updateRules();
}

class WafConnectorResult {
    public $status;

    public $output;

    const STATUS_OK = 'OK';
    const STATUS_BLOCKED = 'BLOCKED';
    const STATUS_NOTCHECKED = 'HTTP REQUEST COULD NOT BE CHECKED';
}

class WafConnectorInput {
    public $input;

    public $code;

    public $lang;
}

foreach (WafApp::$ServiceNames as $serviceName)
    require_once strtolower($serviceName) . '.php';