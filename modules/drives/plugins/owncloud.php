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

require_once 'webdav.php';

final class OwnCloud extends WebDAV {

    public function getDisplayName() {
        return "OwnCloud";
    }

    protected function connect($url, $username, $password) {
        return parent::connect($this->fixURL($url), $username, $password);
    }

    protected function url() {
        return $this->fixURL(parent::url());
    }

    private function fixURL($baseUrl) {
        if (strcmp(substr($baseUrl, strlen($baseUrl) - 1), "/") != 0)
            $baseUrl .= "/";
        $baseUrl .= "remote.php/webdav";
        return $baseUrl;
    }

}
