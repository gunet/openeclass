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
        return $apps[$appname];
    }

}

abstract class ExtApp {

    private $params = array();

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

    public function getParam($paramName) {
        return $this->params[$paramName];
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
        return Database::get()->querySingle("SELECT `value` FROM config WHERE `key` = ?s", "base_url")->value;
    }

    public abstract function getDisplayName();

    public function getShortDescription() {
        
        global $langGoogleDriveShortDescription, $langOneDriveShortDescription,
                $langDropBoxShortDescription, $langOwnCloudShortDescription,
                $langWebDAVShortDescription, $langFtpShortDescription;
        
        $desc = '';
        switch ($this->getDisplayName()) {
            
            case "GoogleDrive": $desc = $langGoogleDriveShortDescription;
                        break;
            case "OneDrive": $desc = $langOneDriveShortDescription;
                        break;
            case "DropBox": $desc = $langDropBoxShortDescription;
                        break;
            case "OwnCloud": $desc = $langOwnCloudShortDescription;
                        break;
            case "WebDAV": $desc = $langWebDAVShortDescription;
                        break;
            case "FTP": $desc = $langFtpShortDescription;
                        break;            
        }
        return $desc;
    }

    public function getLongDescription() {
        
        global $langGoogleDriveLongDescription, $langOneDriveLongDescription,
                $langDropBoxLongDescription, $langOwnCloudLongDescription,
                $langWebDAVLongDescription, $langFtpLongDescription;
        
        $desc = '';
        switch ($this->getDisplayName()) {
            
            case "GoogleDrive": $desc = $langGoogleDriveLongDescription;
                        break;
            case "OneDrive": $desc = $langOneDriveLongDescription;
                        break;
            case "DropBox": $desc = $langDropBoxLongDescription;
                        break;
            case "OwnCloud": $desc = $langOwnCloudLongDescription;
                        break;
            case "WebDAV": $desc = $langWebDAVLongDescription;
                        break;
            case "FTP": $desc = $langFtpLongDescription;
                        break;            
        }
        return $desc;       
    }

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

    const TYPE_STRING = 0;
    const TYPE_BOOLEAN = 1;

    function __construct($display, $name, $defaultValue = "") {
        $this->display = $display;
        $this->name = $name;
        $this->value = ExtParam::$UNSET;
        $this->defaultValue = $defaultValue;
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

    abstract protected function retrieveValue();

    public abstract function persistValue();
}
