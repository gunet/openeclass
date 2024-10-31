<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

class CurlUtil {

    /**
     * HTTP GET method
     *
     * @param string $url
     * @param array $headers
     * @param bool $downloadFile
     * @param string $localFilename
     * @return array
     */
    public static function httpGetRequest(string $url, array $headers = array(), bool $downloadFile = false, string $localFilename = ''): array {
        $response = null;
        $http_code = null;
        if (!extension_loaded('curl')) {
            return array($response, $http_code);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($downloadFile) {
            $fp = fopen($localFilename, 'w');
            curl_setopt($ch, CURLOPT_FILE, $fp);
        }

        $response = curl_exec($ch);
        if(!curl_errno($ch)) {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);
        if ($downloadFile) {
            fclose($fp);
        }

        return array($response, $http_code);
    }

    /**
     * HTTP POST method
     *
     * @param string $url
     * @param array $postData
     * @param bool $downloadFile
     * @param string $localFilename
     * @return array
     */
    public static function httpPostRequest(string $url, array $postData, bool $downloadFile = false, string $localFilename = ''): array {
        $response = null;
        $http_code = null;
        $headers = array();
        if (!extension_loaded('curl')) {
            return array($response, $http_code, $headers);
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) { // ignore invalid headers
                return $len;
            }

            $name = strtolower(trim($header[0]));
            if (!array_key_exists($name, $headers)) {
                $headers[$name] = [trim($header[1])];
            } else {
                $headers[$name][] = trim($header[1]);
            }

            return $len;
        });
        if ($downloadFile) {
            $fp = fopen($localFilename, 'w');
            curl_setopt($ch, CURLOPT_FILE, $fp);
        }

        $response = curl_exec($ch);
        if(!curl_errno($ch)) {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);
        if ($downloadFile) {
            fclose($fp);
        }

        return array($response, $http_code, $headers);
    }

}
