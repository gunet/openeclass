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

use Sabre\DAV\Client;

require_once 'credentialdrive.php';
include "SabreDAV/vendor/autoload.php";

class WebDAV extends CredentialDrive {

    public function getDisplayName() {
        return "WebDAV";
    }

    /**
     * 
     * @param CloudFile $cloudfile
     * @param string $path
     */
    public function store($cloudfile, $path) {
        if (!$this->isAuthorized())
            return CloudDriveResponse::AUTHORIZATION_ERROR;
        list($baseURL, $pathURL) = $this->tokenizeURL($this->url());
        return $this->downloadToFile($baseURL . $cloudfile->id(), $path, null, $this->username() . ":" . $this->password());
    }

    protected function connect($url, $username, $password) {
        $client = new Client(array(
            'baseUri' => $url,
            'userName' => $username,
            'password' => $password,
        ));
        try {
            $response = $client->options();
            return $response ? $client : null;
        } catch (Exception $exc) {
            return null;
        }
    }

    protected function getFileList($connection, $path) {
        $url = $this->url();
        list($baseURL, $pathURL) = $this->tokenizeURL($url);
        if (strlen($path) < 1) {
            $requrl = $url;
            $reqpath = $pathURL;
        } else {
            $requrl = $baseURL . $path;
            $reqpath = $path;
        }
        $suffixlen = strlen($reqpath);
        $response = $connection->propfind($requrl, array('{DAV:}getcontentlength'), 1);
        $files = array();
        if ($response)
            foreach ($response as $fullname => $meta) {
                $size = array_key_exists('{DAV:}getcontentlength', $meta) ? $meta['{DAV:}getcontentlength'] : null;
                $filename = substr($fullname, $suffixlen);
                if (strcmp(substr($filename, strlen($filename) - 1), "/") == 0)
                    $filename = substr($filename, 0, strlen($filename) - 1);
                $files[] = new CloudFile(urldecode($filename), $fullname, $size == null, $size, $this->getName());
            }
        return $files;
    }

    private function tokenizeURL($url) {
        $host = parse_url($url);
        $path = $host['path'];
        $base = substr($url, 0, strlen($url) - strlen($path));
        if (strcmp(substr($path, strlen($path) - 1), "/") != 0)
            $path .= "/";
        return array($base, $path);
    }

}
