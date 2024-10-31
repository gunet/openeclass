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

abstract class OAuthDrive extends CloudDrive {

    protected function getClientID() {
        return $this->getExtApp()->getParam(OAuthDriveApp::CLIENTID)->value();
    }

    protected function getSecret() {
        return $this->getExtApp()->getParam(OAuthDriveApp::SECRET)->value();
    }

    protected function getRedirect() {
        return $this->getExtApp()->getParam(OAuthDriveApp::REDIRECT)->value();
    }

    public function isPresent() {
        return parent::isPresent() && $this->getClientID() && $this->getSecret() && $this->getRedirect();
    }

}
