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

require_once 'google-api-php-client/autoload.php';

final class GoogleDrive extends CloudDrive {

    private $client = null;

    private function init() {
        if ($this->client == null) {
            $client = new Google_Client();
            $client->setClientId($this->getClientID());
            $client->setClientSecret($this->getSecret());
            $client->setRedirectUri($this->getRedirect());
            $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
            $this->client = $client;
        }
    }

    public function isPresent() {
        return true;
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
            return new CloudFile($name, $file['selfLink'], true, null);
        } else {
            return new CloudFile($name, $file['webContentLink'], false, null);
        }
    }

}
