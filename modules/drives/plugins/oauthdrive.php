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

abstract class OAuthDrive extends CloudDrive {

    protected function getCallbackName() {
        return "code";
    }

    public function getCallbackToken() {
        $name = $this->getCallbackName();
        return isset($_GET[$name]) ? $_GET[$name] : null;
    }

    protected function getAuthorizeName() {
        return $this->getName() . "_session_authorize";
    }

    protected function setAuthorizeToken($code) {
        $_SESSION[$this->getAuthorizeName()] = $code;
    }

    public function getAuthorizeToken() {
        $name = $this->getAuthorizeName();
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

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
