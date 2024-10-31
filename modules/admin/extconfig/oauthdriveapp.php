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

abstract class OAuthDriveApp extends ExtApp {

    const CLIENTID = "clientid";
    const SECRET = "secret";
    const REDIRECT = "redirect";

    public function __construct() {
        parent::__construct();
        $drivename = $this->getName();
        $this->registerParam(new GenericRequiredParam($drivename, $this->getAppParamName(), OAuthDriveApp::CLIENTID));
        $this->registerParam(new GenericRequiredParam($drivename, $this->getKeyParamName(), OAuthDriveApp::SECRET));
        $this->registerParam(new GenericRequiredParam($drivename, $this->getURLParamName(), OAuthDriveApp::REDIRECT, $this->getURLDefaultValue()));
    }

    protected function getAppParamName() {
        global $langAppCode;

        return $langAppCode;
    }

    protected function getKeyParamName() {
        global $langAppKey;

        return $langAppKey;
    }

    protected function getURLParamName() {
        global $langReturnAddress;

        return $langReturnAddress;
    }

    public function getShortDescription() {
        global $langUnitDescr;

        return "$langUnitDescr " . $this->getDisplayName();
    }

    public function getLongDescription() {
        global $langDescription;

        return "$langDescription " . $this->getDisplayName();
    }

    protected abstract function getURLDefaultValue();
}
