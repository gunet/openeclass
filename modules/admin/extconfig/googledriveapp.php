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

require_once 'oauthdriveapp.php';

class GoogleDriveApp extends OAuthDriveApp {

    const EMAIL = "email";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "e-mail εγκατάστασης", GoogleDriveApp::EMAIL));
    }

    public function getDisplayName() {
        return "GoogleDrive";
    }

    public function getShortDescription() {
        return $GLOBALS['langGoogleDriveShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langGoogleDriveLongDescription'];
    }

    public function validateApp() {
        global $langErrorGoogleDrive;

        $keyPath = realpath(dirname(__FILE__) . "/../../drives/plugins") . "/googledrive_key.p12";
        if (file_exists($keyPath))
            return null;

        return "$langErrorGoogleDrive $keyPath" ;
    }

    protected function getURLDefaultValue() {
        return $this->getBaseURL() . "modules/drives/popup.php?clouddrive=googledrive";
    }

    protected function getAppParamName() {
        return "Client ID";
    }

    protected function getKeyParamName() {
        return "Client secret";
    }

    protected function getURLParamName() {
        return "Redirect URIs";
    }

}
