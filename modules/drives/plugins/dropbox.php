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

require_once 'oauthdrive.php';
require_once 'vendor/autoload.php';

use Dropbox as dbx;

final class DropBox extends OAuthDrive {

    private $appInfo = null;

    const CLIENT = "Open eClass/3.0";
    const SESSION_TOKEN = "dropbox-auth-csrf-token";

    private function init() {
        if (!$this->appInfo) {
            $this->appInfo = dbx\AppInfo::loadFromJson(array("key" => $this->getClientID(), "secret" => $this->getSecret()));
        }
    }

    public function isValid() {
        return true;
    }

    public function getDisplayName() {
        return "DropBox";
    }

    public function isAuthorized() {
        $token = $this->getAuthorizeToken();
        if (!$token)
            return false;
        $this->init();
        $dbxClient = new Dropbox\Client($this->getAuthorizeToken(), Dropbox::CLIENT);
        try {
            $dbxClient->getAccountInfo();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function getAuthURL() {
        $this->init();
        $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, Dropbox::SESSION_TOKEN);
        $webAuth = new dbx\WebAuth($this->appInfo, Dropbox::CLIENT, $this->getRedirect(), $csrfTokenStore);
        $authorizeUrl = $webAuth->start();
        return $authorizeUrl;
    }

    public function getCallbackToken() {
        $name = $this->getCallbackName();
        return isset($_GET[$name]) ? array($name => $_GET[$name], 'state' => $_GET['state']) : null;
    }

    public function authorize($code) {
        $this->init();
        try {
            $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, Dropbox::SESSION_TOKEN);
            $webAuth = new dbx\WebAuth($this->appInfo, Dropbox::CLIENT, $this->getRedirect(), $csrfTokenStore);
            list($accessToken, $userId, $urlState) = $webAuth->finish($code);
            $this->setAuthorizeToken($accessToken);
            return true;
        } catch (Dropbox\Exception $ex) {
            return false;
        }
    }

    public function getFiles($dir) {
        $access = $this->getAuthorizeToken();
        $files = array();
        $dbxClient = new dbx\Client($access, Dropbox::CLIENT);
        $metadata = $dbxClient->getMetadataWithChildren(strlen($dir) == 0 ? "/" : $dir);
        $pathsize = strlen($metadata['path']);
        if ($pathsize > 1)
            $pathsize++;
        foreach ($metadata['contents'] as $file) {
            $path = $file['path'];
            $files[] = new CloudFile(substr($path, -(strlen($path) - $pathsize)), $file['path'], $file['is_dir'], $file['bytes'], $this->getName());
        }
        return $files;
    }

    public function store($cloudfile, $path) {
        if (!$this->isAuthorized())
            return CloudDriveResponse::AUTHORIZATION_ERROR;
        try {
            $fout = fopen($path, "w+b");
            $dbxClient = new dbx\Client($this->getAuthorizeToken(), Dropbox::CLIENT);
            $fileMetadata = $dbxClient->getFile($cloudfile->id(), $fout);
            fclose($fout);
            if (is_null($fileMetadata))
                return CloudDriveResponse::FILE_NOT_FOUND;
            return CloudDriveResponse::OK;
        } catch (Exception $ex) {
            return CloudDriveResponse::FILE_NOT_SAVED;
        }
    }

}
