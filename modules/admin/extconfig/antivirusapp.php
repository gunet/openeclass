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

require_once 'genericrequiredparam.php';

class AntivirusApp extends ExtApp {
    public static $ServiceNames = array("clamav","clamavdaemon","sophosmac");

    public function __construct() {
        parent::__construct();
    }

    public function getLongDescription() {
        return $GLOBALS['langAntivirusDescription'];
    }

    public function getShortDescription() {
        return $GLOBALS['langAntivirusDescription'];
    }

    public function getDisplayName() {
        return 'Antivirus';
    }

    public function getConfigUrl() {
        return 'modules/admin/antivirusmoduleconf.php';
    }

    /**
     * Returns true if a compilation service has been selected
     *
     * @return boolean
     */
    public function isConfigured() {
        return (q(get_config('antivirus_connector')) != null);
    }

    public static function getAntivirus() {
        $antivirus = ExtAppManager::getApp('antivirus');
        $connector = q(get_config('antivirus_connector'));
        if(!$connector) {
            $connector = new ClamAv();
        } else {
            $connector = new $connector();
        }
        $param = $connector->getParam('enabled');
        if ($param) {
            $param->setValue($antivirus->isEnabled());
        }
        return $connector;
    }

    public static function block($output) {
        require_once 'include/tools.php';
        Session::flash('message',$output);
        Session::flash('alert-class', 'alert-warning');
        header("Location: {$_SERVER['SCRIPT_NAME']}");
        exit();
    }
    /**
     * @return ExtApp[]
     */
    public static function getAntivirusServices() {
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

interface AntivirusConnector {
    public function check($input);

    public function getConfigFields();

    public function getName();
}

class AntivirusConnectorResult {
    public $status;

    public $output;

    const STATUS_OK = 'OK';
    const STATUS_INFECTED = 'INFECTED';
    const STATUS_NOTCHECKED = 'FILE COULD NOT BE CHECKED';
}

foreach (AntivirusApp::$ServiceNames as $serviceName)
    require_once strtolower($serviceName) . '.php';
