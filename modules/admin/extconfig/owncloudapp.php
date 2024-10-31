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

require_once 'credentialdriveapp.php';

class OwnCloudApp extends CredentialDriveApp {

    public function getDisplayName() {
        return "OwnCloud";
    }

    public function getShortDescription() {
        return $GLOBALS['langOwnCloudShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langOwnCloudLongDescription'];
    }

}
