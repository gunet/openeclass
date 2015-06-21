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

abstract class CredentialDrive extends CloudDrive {

    private $url;
    private $username;
    private $password;

    public function authorize($callbackToken) {
        $json = $_SESSION[$callbackToken];
        unset($_SESSION[$callbackToken]);
        if (is_null($json))
            return false;
        $this->setAuthorizeToken($json);
        return ($this->isAuthorized());
    }

    public function isAuthorized() {
        if (is_null($this->url)) {
            $token = $this->getAuthorizeToken();
            if ($token) {
                $array = json_decode($token);
                $url = $array->l;
                $username = $array->u;
                $password = $array->p;
                if ($this->checkCredentials($url, $username, $password)) {
                    $this->url = $url;
                    $this->username = $username;
                    $this->password = $password;
                    return true;
                }
            }
        }
        return false;
    }

    public function getAuthURL() {
        return "plugins/credential_auth.php?" . $this->getDriveDefaultParameter();
    }

    public function getDefaultURL() {
        return $this->getExtApp()->getParam(CredentialDriveApp::URL)->value();
    }

    public function encodeCredentials($url, $username, $password) {
        $uuid = uniqid("", true);
        $_SESSION[$uuid] = json_encode(array('l' => $url, "u" => $username, "p" => $password));
        return $uuid;
    }

    public function getFiles($dir) {
        if (!$this->isAuthorized())
            return null;
        $connection = $this->connect($this->url, $this->username, $this->password);
        if (!$connection)
            return null;
        return $this->getFileList($connection, $dir);
    }

    protected function url() {
        return $this->url;
    }

    protected function username() {
        return $this->username;
    }

    protected function password() {
        return $this->password;
    }

    public function checkCredentials($url, $username, $password) {
        if (!$url || !$username || !$password)
            return false;
        return !is_null($this->connect($url, $username, $password));
    }

    protected abstract function connect($url, $username, $password);

    protected abstract function getFileList($connection, $path);
}
