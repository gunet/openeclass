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

class AutojudgeApp extends ExtApp {
    public static $ServiceNames = array("AutojudgeDnnaApp", "AutojudgeHackerearthApp", "AutojudgeCodepadApp");

    public function __construct() {
        parent::__construct();
    }

    public function getLongDescription() {
        return $GLOBALS['langAutojudgeDescription'];
    }

    public function getShortDescription() {
        return $GLOBALS['langAutojudgeDescription'];
    }

    public function getDisplayName() {
        return 'AutoJudge';
    }

    public function getConfigUrl() {
        return 'modules/admin/autojudgemoduleconf.php';
    }

    /**
     * Returns true if a compilation service has been selected
     *
     * @return boolean
     */
    public function isConfigured() {
        return (q(get_config('autojudge_connector')) != null);
    }

    public static function getAutojudge() {
        $autojudge = ExtAppManager::getApp('autojudge');
        $connector = q(get_config('autojudge_connector'));
        if(!$connector) {
            $connector = new AutojudgeDnnaApp();
        } else {
            $connector = new $connector();
        }
        $connector->setEnabled($autojudge->isEnabled());
        return $connector;
    }

    /**
     * @return ExtApp[]
     */
    public static function getAutoJudgeServices() {
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

interface AutoJudgeConnector {
    public function compile(AutoJudgeConnectorInput $input);

    public function getSupportedLanguages();

    public function supportsInput();

    public function getServiceURL();

    public function getName();
}

class AutoJudgeConnectorResult {
    public $compileStatus;

    public $output;

    const COMPILE_STATUS_OK = 'OK';
}

class AutoJudgeConnectorInput {
    public $input;

    public $code;

    public $lang;
}

foreach (AutojudgeApp::$ServiceNames as $serviceName)
    require_once strtolower($serviceName) . '.php';