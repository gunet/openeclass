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

    /**
     * HTTP POST method for JSON usage
     *
     * @param string $url
     * @param array $postData
     * @return array
     */
    public static function httpPostJsonRequest(string $url, array $postData): array {
        $response = null;
        $http_code = null;
        $headers = array();
        if (!extension_loaded('curl')) {
            return array($response, $http_code, $headers);
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        if(!curl_errno($ch)) {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);

        return array($response, $http_code, $headers);
    }

    /**
     * Fetches content from Internet. Uses cURL extension. Only downloads from http(s) sources are supported.
     *
     * @param string $url URL starting with http(s)://
     * @param int $connecttimeout timeout for connection to server; this is the timeout that usually happens if the remote server is completely down (default 20 seconds);
     * @param bool $certverify If false, the peer's SSL certificate will not be checked.
     * @return string|bool false if request failed, true if content as a string.
     */
    public static function downloadFileContent(string $url, int $connecttimeout = 20, bool $certverify = true): bool|string {
        // Only http and https links supported.
        if (!preg_match('|^https?://|i', $url)) {
            return false;
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certverify);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connecttimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);

        $curlResponse = curl_exec($ch);
        $info         = curl_getinfo($ch);
        $error_no     = curl_errno($ch);

        if ($error_no) {
            $error = curl_error($ch);
            error_log("CurlUtil Error: cURL request for \"$url\" failed with: $error ($error_no)");
            return false;
        }

        $response = new stdClass();

        if (empty($info['http_code'])) {
            // Support only true http connections (Location: file:// does not work).
            $response->status        = '0';
            $response->headers       = array();
            $response->response_code = 'Unknown cURL error';
            $response->results       = false; // ignore the result
            $response->error         = 'Unknown cURL error';
        } else {
            $response->status        = (string) $info['http_code'];
            $response->results       = $curlResponse;
            $response->error         = '';
        }

        if ($info['http_code'] != 200) {
            error_log("CurlUtil Error: cURL request for \"$url\" failed, HTTP response code: " . $info['http_code']);
            return false;
        }

        curl_close($ch);
        return $response->results;
    }

}
