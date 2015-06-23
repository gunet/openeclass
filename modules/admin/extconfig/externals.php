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

foreach (ExtAppManager::$AppNames as $appName)
    require_once strtolower($appName) . '.php';
require_once realpath(dirname(__FILE__)) . '/../../db/database.php';
require_once realpath(dirname(__FILE__)) . '/../../../include/main_lib.php';

class ExtAppManager {

    public static $AppNames = array("GoogleDriveApp", "OneDriveApp", "DropBoxApp", "OwnCloudApp", "WebDAVApp", "FTPApp", "OpenDelosApp");
    private static $APPS = null;

    /**
     * @return ExtApp[]
     */
    public static function getApps() {
        if (ExtAppManager::$APPS == null) {
            $apps = array();
            foreach (ExtAppManager::$AppNames as $appName) {
                $app = new $appName();
                $apps[$app->getName()] = $app;
            }
            ExtAppManager::$APPS = $apps;
        }
        return ExtAppManager::$APPS;
    }

    /**
     * 
     * @param string $appname
     * @return ExtApp
     */
    public static function getApp($appname) {
        $apps = ExtAppManager::getApps();
        return array_key_exists($appname, $apps) ? $apps[$appname] : null;
    }

}

abstract class ExtApp {

    const ENABLED = 'enabled';
    private $params = array();

    public function __construct() {
        $this->registerParam(new GenericParam($this->getName(), "Ενεργό", ExtApp::ENABLED, "0", ExtParam::TYPE_BOOLEAN));
    }

    public function isEnabled() {
        $enabled = $this->getParam(ExtApp::ENABLED);
        return $enabled && strcmp($enabled->value(), "1") == 0;
    }

    /**
     * 
     * @param boolean $status
     */
    function setEnabled($status) {
        $param = $this->getParam(ExtApp::ENABLED);
        if ($param) {
            $param->setValue($status ? 1 : 0);
            $param->persistValue();
        }
    }

    /**
     * @param ExtParam $param
     */
    protected function registerParam($param) {
        $this->params[$param->name()] = $param;
    }

    /**
     * @return ExtParam[]
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * 
     * @param string $paramName
     * @return ExtParam
     */
    public function getParam($paramName) {
        return array_key_exists($paramName, $this->params) ? $this->params[$paramName] : null;
    }

    /**
     * 
     * @return string
     */
    public function getName() {
        return strtolower(str_replace(' ', '', $this->getDisplayName()));
    }

    public function storeParams() {
        $response = null;
        foreach ($this->getParams() as $param) {
            $name = $param->name();
            $val = isset($_POST[$name]) ? $_POST[$name] : "";
            $param->setValue($val);
        }
        if (($response = $this->validateApp()))
            return $response;
        foreach ($this->getParams() as $param) {
            if (($response = $param->validateParam()))
                return $response;
        }
        foreach ($this->getParams() as $param) {
            $param->persistValue();
        }
        return null;
    }

    public function validateApp() {
        return null;
    }

    protected function getBaseURL() {
        return get_config('base_url');
    }

    public abstract function getDisplayName();

    public abstract function getShortDescription();

    public abstract function getLongDescription();

    public function getAppIcon() {
        return $this->getBaseURL() . "template/icons/" . $this->getName() . ".png";
    }

}

abstract class ExtParam {

    private static $UNSET = "[[[[ <<<<<<< ----- unset ----- >>>>>>> ]]]]";
    private $display;
    private $name;
    private $value;
    private $defaultValue;
    private $type;

    const TYPE_STRING = 0;
    const TYPE_BOOLEAN = 1;

    function __construct($display, $name, $defaultValue = "", $type = ExtParam::TYPE_STRING) {
        $this->display = $display;
        $this->name = $name;
        $this->value = ExtParam::$UNSET;
        $this->defaultValue = $defaultValue;
        $this->type = $type;
    }

    function display() {
        return $this->display;
    }

    function name() {
        return $this->name;
    }

    function value() {
        if ($this->value === ExtParam::$UNSET)
            $this->setValue($this->retrieveValue());
        if ($this->value == "")
            $this->value = $this->defaultValue;
        return $this->value;
    }

    function setValue($value) {
        $this->value = $value;
    }

    public function validateParam() {
        return null;
    }

    function getType() {
        return $this->type;
    }

    abstract protected function retrieveValue();

    public abstract function persistValue();
}
