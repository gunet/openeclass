<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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
 * ======================================================================== */


class Access {
    public $isValid = false;
    public $token;

    /**
     * Try to determine if a token was set in the request and if so return it
     * @param string $token
     * @return string|null;
     */
    public static function getToken() {
        if (isset($_GET['token']) and $_GET['token'] !== '') {
            return $_GET['token'];
        } elseif (isset($_POST['token']) and $_POST['token'] !== '') {
            return $_POST['token'];
        } elseif (isset($_SERVER['Authorization'])) {
            $header = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = trim($_SERVER['HTTP_AUTHORIZATION']);
        } else {
            foreach (getallheaders() as $name => $value) {
                if (strtolower($name) == 'authorization') {
                    $header = trim($value);
                    break;
                }
            }
        }
        if (isset($header) and preg_match('/Bearer\s+(\S+)/', $header, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Check a token for validity and return the corresponding Access object
     * @param string $token
     * @return Access;
     */
    public static function fromToken($token) {
        $access = new Access();
        $access->token = $token;

        $ip = Log::get_client_ip();
        $result = Database::get()->querySingle('SELECT * FROM api_token WHERE token = ?s', $token);
        if ($result and $result->enabled and (!$result->ip or $result->ip == $ip)) {
            $access->isValid = true;
        }
        return $access;
    }

    /**
     * End the request with an error
     * @param int $code Error code
     * @param string $message Error message
     */
    public static function error($code, $message) {
        header('Content-Type: application/json');
        echo json_encode([
            'errorcode' => $code,
            'errormessage' => $message,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
