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

foreach (ExtAppManager::$AppCategories as $category => $appNames) {
    foreach ($appNames as $appName) {
        require_once strtolower($appName) . '.php';
    }
}

class ExtAppManager {

    public static $AppCategories = [
        'general' => ['APITokenApp', 'H5PApp', 'TurnitinApp', 'LtiPublishApp', 'LimesurveyApp', 'PanoptoApp'],
        'teleconference' => ['BBBApp', 'ZoomApp', 'WebexApp','GoogleMeetApp', 'JitsiApp', 'MicrosoftTeamsApp', 'OpenDelosApp'],
        'cloud' => ['GoogleDriveApp', 'OneDriveApp', 'DropBoxApp', 'OwnCloudApp', 'WebDAVApp', 'FTPApp'],
        'other' => ['AnalyticsApp', 'AntivirusApp', 'secondfaApp', 'UserWayApp', 'AutojudgeApp', 'ColmoocApp', 'UnPlagApp', 'UnPlagApp'],
    ];


    private static $APPS = null;
    private static $CATEGORIES = null;
    /**
     * @return ExtApp[]
     */

    /**
     * Get all external apps.
     *
     * This method initializes and returns an array of all external apps.
     * It loads the apps from the categories defined in the $AppCategories array,
     * instantiates each app, and stores them in the $APPS array.
     * The categories of the apps are stored in the $CATEGORIES array.
     *
     * @return ExtApp[] An array of all external apps.
     */
    public static function getApps() {
        if (ExtAppManager::$APPS == null) {
            $apps = array();
            $categories = array();
            foreach (ExtAppManager::$AppCategories as $category => $appNames) {
                foreach ($appNames as $appName) {
                    require_once strtolower($appName) . '.php';
                    $app = new $appName();
                    $apps[$app->getName()] = $app;
                    $categories[$app->getName()] = $category;
                }
            }
            ExtAppManager::$APPS = $apps;
            ExtAppManager::$CATEGORIES = $categories;
        }
        return ExtAppManager::$APPS;
    }

    /**
     * Get the category of a given app.
     *
     * This method returns the category of the specified app name.
     * If the app name does not exist in the categories, it returns 'unknown'.
     *
     * @param string $appName The name of the app.
     * @return string The category of the app, or 'unknown' if the app name is not found.
     */
    public static function getAppCategory($appName) {
        return ExtAppManager::$CATEGORIES[$appName] ?? 'unknown';
    }

    /**
     * Get the instance of a given app.
     *
     * This method returns the instance of the specified app name.
     * If the app name does not exist in the apps array, it returns null.
     *
     * @param string $appname The name of the app.
     * @return ExtApp|null The instance of the app, or null if the app name is not found.
     */
    public static function getApp($appname) {
        $apps = ExtAppManager::getApps();
        return array_key_exists($appname, $apps) ? $apps[$appname] : null;
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
            $val = isset($_POST[$name]) ? trim($_POST[$name]) : "";
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
        return get_config('base_url') ?? '';
    }

    public abstract function getDisplayName();

    public abstract function getShortDescription();

    public abstract function getLongDescription();

    public function getAppIcon() {
        return $this->getBaseURL() . "resources/icons/" . $this->getName() . ".png";
    }

    public function update_tc_sessions($type) {

        $r = Database::get()->querySingle("SELECT id FROM tc_servers
                                            WHERE `type` = '$type' AND enabled = 'true'
                                            ORDER BY weight ASC");
        if ($r) {
            $tc_id = $r->id;
            Database::get()->query("UPDATE tc_session SET running_at = $tc_id");
            Database::get()->query("UPDATE course_external_server SET external_server = $tc_id");
        }
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
