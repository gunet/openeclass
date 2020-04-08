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

foreach (ExtAppManager::$AppNames as $appName) {
    require_once strtolower($appName) . '.php';
}
require_once realpath(dirname(__FILE__)) . '/../../db/database.php';

class ExtAppManager {

    public static $AppNames = array('GoogleDriveApp', 'OneDriveApp',
        'DropBoxApp', 'OwnCloudApp', 'WebDAVApp', 'FTPApp', 'OpenDelosApp',
        'BBBApp', 'OpenMeetings', 'WebConfApp', 'AutojudgeApp', 'AntivirusApp',
        'WafApp', 'secondfaApp', 'AnalyticsApp', 'UnPlagApp', 'TurnitinApp');
    private static $APPS = null;
    private static $ExclusiveAppNames = array(
        'tc'=>['BBBApp','OpenMeetings','WebConfApp']
    );
    private static $EXCLUSIVEAPPS = null;

    /**
     * @return ExtApp[]
     */
    public static function getApps() {
        if (self::$APPS == null) {
            $apps = array();
            foreach (self::$AppNames as $appName) {
                $app = new $appName();
                $apps[$app->getName()] = $app;
            }
            self::$APPS = $apps;
        }
        return self::$APPS;
    }

    /**
     * @return int[]ExtApp[]
     */
    public static function getExclusiveApps() {
        if (self::$EXCLUSIVEAPPS == null) {
            self::getApps();
            $apps = array();
            foreach (self::$ExclusiveAppNames as $k=>$appClassNames) {
                foreach($appClassNames as $appClassName) {

                     //Find already created instance, avoid creating another one, plugins could be front heavy
                    foreach(self::$APPS as $app) {
                        if ( $app->getClassName() === $appClassName ) {
                            $apps[$k][$app->getName()] = $app;
                            continue;
                        }
                    }
                    
                }
            }
            self::$EXCLUSIVEAPPS = $apps;
        }
        return self::$EXCLUSIVEAPPS;
    }
    
    /**
     * 
     * @param string $appname
     * @return ExtApp
     */
    public static function getApp($appname) {
        $apps = self::getApps();
        return array_key_exists($appname, $apps) ? $apps[$appname] : null;
    }
    
    public static function enableDisableApp($appname,$newstate=1,callable $disableFunc=null) {
        $app = self::getApp($appname);
        if ( !$app ) return false;
        if ( $newstate === 1) { //If enabling an app, check for exclusions
            self::getExclusiveApps();
            foreach(self::$EXCLUSIVEAPPS as $k=>$excapps) {
                if (array_key_exists($appname,$excapps)) {
                    foreach($excapps as $apptoswitch) {
                        if ( $apptoswitch == $app ) continue; //don't disable myself
                        $apptoswitch->setEnabled(0); //disable excluded app
                        if ( $disableFunc )
                            $disableFunc($apptoswitch);//tell caller we're disabling this app
                    }
                }
            }
        }
        $app->SetEnabled($newstate);
        return true;
    }

}

abstract class ExtApp {

    private static $ENABLED = 'enabled';
    private $params = array();

    public function __construct() {
        $this->registerParam(new GenericParam($this->getName(), "Ενεργό", ExtApp::$ENABLED, "0", ExtParam::TYPE_BOOLEAN));
    }

    public function isEnabled() {
        $enabled = $this->getParam(ExtApp::$ENABLED);
        return $enabled && strcmp($enabled->value(), "1") == 0;
    }

    /**
     * 
     * @param boolean $status
     */
    function setEnabled($status) {
        $param = $this->getParam(ExtApp::$ENABLED);
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
    
    
    /**
     *
     * @return string
     */
    public function getClassName() {
        return static::class; //requires php 5.5+
    }
    
    /**
     * 
     * @return string
     */
    public function getConfigUrl() {
        return 'modules/admin/extapp.php?edit=' . $this->getName();
    }

    /**
     * Return true if the external app is configured (all params are set)
     * 
     * @return boolean true if the app is configured, else false
     */
    public function isConfigured() {
        foreach ($this->getParams() as $para) {
            if ($para->isRequired() && $para->value() === '') {
                return false;
            }
        }
        return true;
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
        $r = Database::get()->querySingle("SELECT `value` FROM config WHERE `key` = ?s", "base_url");
        if ($r) {
            return $r->value;
        } else {
            return null;
        }
    }

    public abstract function getDisplayName();

    public abstract function getShortDescription();

    public abstract function getLongDescription();

    public function getAppIcon() {
        return $this->getBaseURL() . "template/icons/" . $this->getName() . ".png";
    }

}

abstract class ExtTCApp extends ExtApp {
    protected $sessionType = null; //must be overriden in descendants
    
    public function update_tc_sessions() {
        if ( $this->sessionType === null ) die('[externals.php] Session type uninitialized');
        $r = Database::get()->querySingle("SELECT id FROM tc_servers
                                            WHERE `type` = '".$this->sessionType."' AND enabled = 'true'
                                            ORDER BY weight ASC");
        if ($r) {
            $tc_id = $r->id;
            Database::get()->query("UPDATE tc_session SET running_at = $tc_id");
            Database::get()->query("UPDATE course_external_server SET external_server = $tc_id");
        }
    }

    /**
     *
     * @param boolean $status
     */
    function setEnabled($status) {
        if ( $status==1 && !$this->isEnabled() ) {
            parent::setEnabled($status);
            $this->update_tc_sessions();
        }
        else
            parent::setEnabled($status);
    }
    
    /**
     * Return true if any TC servers of type $sessionType are enabled, else false
     *
     * @return boolean
     */
    public function isConfigured() {
        return Database::get()->querySingle("SELECT COUNT(*) AS count FROM tc_servers WHERE enabled='true' AND `type` = ?s",$this->sessionType)->count > 0;
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
    const TYPE_MULTILINE = 2;

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

    function isRequired() {
        return false;
    }

    abstract protected function retrieveValue();

    public abstract function persistValue();
}
