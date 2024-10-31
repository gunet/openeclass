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

class DropBoxApp extends OAuthDriveApp {

    public function getDisplayName() {
        return "DropBox";
    }

    public function getShortDescription() {
        return $GLOBALS['langDropboxShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langDropboxLongDescription'];
    }

    protected function getURLDefaultValue() {
        return $this->getBaseURL() . "modules/drives/plugins/dropbox_callback.php";
    }

    protected function getAppParamName() {
        return "App key";
    }

    protected function getKeyParamName() {
        return "App secret";
    }

    protected function getURLParamName() {
        return "Redirect URIs";
    }

}
