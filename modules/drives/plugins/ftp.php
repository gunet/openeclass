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

require_once 'credentialdrive.php';

final class FTP extends CredentialDrive {

    public function getDisplayName() {
        return "FTP";
    }

    public function isPresent() {
        return parent::isPresent();
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
        return $this->downloadToFile($this->url() . "/" . $cloudfile->id(), $path, null, $this->username() . ":" . $this->password());
    }

    protected function connect($url, $username, $password) {
        if (substr(strtolower($url), 0, 6) == "ftp://")
            $url = substr($url, 6);
        $connection = @ftp_connect($url);
        if ($connection) {
            try {
                if (@ftp_login($connection, $username, $password)) {
                    return $connection;
                }
            } catch (Exception $exc) {
            }
            ftp_close($connection);
        }
        return null;
    }

    protected function getFileList($connection, $path) {
        @ftp_chdir($connection, $path);
        $list = ftp_nlist($connection, ".");
        $files = array();
        if ($list) {
            foreach ($list as $filename) {
                $fullpath = strlen($path) < 1 ? $filename : $path . "/" . $filename;
                $size = ftp_size($connection, $filename);
                $files[] = new CloudFile($filename, $fullpath, $size < 0, $size, $this->getName());
            }
        }
        ftp_close($connection);
        return $files;
    }

}
