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

require_once 'vendor/autoload.php';
require_once 'oauthdrive.php';

final class GoogleDrive extends OAuthDrive {

    private $client = null;

    private function init() {
        if ($this->client == null) {
            $client = new Google_Client();
            $client->setClientId($this->getClientID());
            $client->setClientSecret($this->getSecret());
            $client->setRedirectUri($this->getRedirect());
//            $client->setApplicationName("Open eClass");
            $client->addScope(Google_Service_Drive::DRIVE);
            $this->client = $client;
        }
    }

    public function getDisplayName() {
        return "GoogleDrive";
    }

    public function getAuthURL() {
        $this->init();
        return $this->client->createAuthUrl();
    }

    public function authorize($callbackToken) {
        $this->init();
        $this->setAuthorizeToken($this->client->authenticate($callbackToken));
        return $this->isAuthorized();
    }

    public function isAuthorized() {
        $authToken = $this->getAuthorizeToken();
        if (!$authToken)
            return false;
        $this->init();
        $this->client->setAccessToken($this->getAuthorizeToken());
        return !$this->client->isAccessTokenExpired();
    }

    public function getFiles($dir) {
        $files = array();
        if ($this->isAuthorized()) {
            $drive_service = new Google_Service_Drive($this->client);
            $files_list = $drive_service->files->listFiles(array())->getItems();
            $wantsRoot = strcmp($dir, "") == 0;
            foreach ($files_list as $file) {
                if ($wantsRoot) {
                    foreach ($file['modelData']['parents'] as $item) {
                        if ($item['isRoot']) {
                            $files[] = $this->getCloudFile($file);
                            break;
                        }
                    }
                } else {
                    foreach ($file['modelData']['parents'] as $item) {
                        if (strcmp($dir, $item['parentLink']) == 0) {
                            $files[] = $this->getCloudFile($file);
                            break;
                        }
                    }
                }
            }
        }
        return $files;
    }

    private function getCloudFile($file) {
        $name = $file['title'];
        if (strpos($file['mimeType'], '.folder') !== false) {
            return new CloudFile($name, $file['selfLink'], true, null, $this->getName());
        } else {
            return new CloudFile($name, $file->getDownloadURL(), false, null, $this->getName());
        }
    }

    /**
     * 
     * @param CloudFile $cloudfile
     * @param type $path
     * @return type
     */
    public function store($cloudfile, $path) {
        if (!$this->isAuthorized())
            return CloudDriveResponse::AUTHORIZATION_ERROR;
        $key = file_get_contents(realpath(dirname(__FILE__)) . "/googledrive_key.p12");
        if (!$key)
            return CloudDriveResponse::AUTHORIZATION_ERROR;
        $email = $this->getExtApp()->getParam(GoogleDriveApp::EMAIL)->value();
        $cred = new Google_Auth_AssertionCredentials($email, array(Google_Service_Drive::DRIVE), $key);
        $this->client->setAssertionCredentials($cred);
        $cred->sub = $email;

        $request = new Google_Http_Request($cloudfile->id(), 'GET', null, null);
        $httpRequest = $this->client->getAuth()->authenticatedRequest($request);
        if ($httpRequest->getResponseHttpCode() == 200) {
            try {
                $fout = fopen($path, "w+b");
                file_put_contents($path, $httpRequest->getResponseBody());
                fclose($fout);
                return CloudDriveResponse::OK;
            } catch (Exception $ex) {
                return CloudDriveResponse::FILE_NOT_SAVED;
            }
        } else {
            return CloudDriveResponse::FILE_NOT_FOUND;
        }
    }

}
