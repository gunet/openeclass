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

final class OneDrive extends OAuthDrive {

    private $client_id;
    private $redirect_url;
    private $client_secret;

    public function __construct() {
        if ($this->isPresent()) {
            $this->client_id = $this->getClientID();
            $this->redirect_url = $this->getRedirect();
            $this->client_secret = $this->getSecret();
        }
    }

    public function isValid() {
        return true;
    }

    public function getDisplayName() {
        return "OneDrive";
    }

    public function isAuthorized() {
        $token = $this->getAuthorizeToken();
        if (!$token)
            return false;
        $result = $this->retrieve("https://apis.live.net/v5.0/me/skydrive?access_token=$token");
        return !property_exists($result, "error");
    }

    public function getAuthURL() {
        return "https://login.live.com/oauth20_authorize.srf?client_id=" . $this->client_id . "&scope=wl.skydrive&response_type=code&redirect_uri=" . urlencode($this->redirect_url);
    }

    public function authorize($code) {
        $this->setAuthorizeToken($this->retrieve("https://login.live.com/oauth20_token.srf", "client_id=$this->client_id&redirect_uri=$this->redirect_url&client_secret=$this->client_secret&code=$code&grant_type=authorization_code")
                ->access_token);
        return true;
    }

    public function getFiles($dir) {
        $access = $this->getAuthorizeToken();
        $files = array();
        $wantsRoot = strcmp($dir, "") == 0;
        if ($wantsRoot) {
            $parent_id = $this->retrieve("https://apis.live.net/v5.0/me/skydrive?access_token=$access")->id;
            $result = $this->retrieve("https://apis.live.net/v5.0/$parent_id/files?access_token=$access")->data;
            foreach ($result as $file) {
                $files[] = $this->getCloudFile($file);
            }
        } else {
            $result = $this->retrieve("https://apis.live.net/v5.0/$dir/files?access_token=$access")->data;
            foreach ($result as $file) {
                $files[] = $this->getCloudFile($file);
            }
        }
        return $files;
    }

    private function retrieve($get, $post = null) {
        return json_decode($this->downloadToOutput($get, $post));
    }

    private function getCloudFile($file) {
        $name = $file->name;
        $id = $file->id;
        if (strcmp(substr($id, 0, 5), "file.") == 0) {
            return new CloudFile($name, $file->source, false, null, $this->getName());
        } else {
            return new CloudFile($name, $id, true, null, $this->getName());
        }
    }

    public function store($cloudfile, $path) {
        if (!$this->isAuthorized())
            return CloudDriveResponse::AUTHORIZATION_ERROR;
        return $this->downloadToFile($cloudfile->id(), $path);
    }

}
