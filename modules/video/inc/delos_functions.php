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

require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/opendelosapp.php';
require_once 'include/log.class.php';

$opendelosapp = ExtAppManager::getApp(strtolower(OpenDelosApp::NAME));

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function getDelosExtEnabled() {
    global $opendelosapp;
    return $opendelosapp->isEnabled();
}

function getDelosPublicURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::URL)->value();
}

function getDelosPrivateURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::PRIVATE_URL)->value();
}

function getDelosCheckAuthURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::CHECKAUTH_URL)->value();
}

function getDelosRLoginURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::RLOGIN_URL)->value();
}

function getDelosRLoginCASURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::RLOGINCAS_URL)->value();
}

function getDelosLmsURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::LMS_URL)->value();
}

function getDelosSecret() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::SECRET)->value();
}

function isDelosEnabled() {
    global $opendelosapp;
    if ($opendelosapp && getDelosExtEnabled() && getDelosPublicURL() && getDelosPrivateURL()
        && getDelosCheckAuthURL() && getDelosRLoginURL() && getDelosRLoginCASURL()
        && getDelosLmsURL() && getDelosSecret()) {
        return true;
    }
    return false;
}

function getDelosJavaScript() {
    $head = '';
    if (isset($_GET['form_input']) && $_GET['form_input'] === 'opendelos') {
        $head .= <<<hContent
<script type='text/javascript'>
$(document).ready(function() {

    $('tr').click(function(event) {
        if (event.target.type !== 'checkbox') {
            $(':checkbox', this).trigger('click');
        }
    });

});
</script>
hContent;
    }
    return $head;
}

function getDelosSignedToken() {
    global $course_code;

    // encrypt token header
    $header = array(
        "alg" => "HS256",
        "typ" => "JWT"
    );
    $stringifiedHeader = json_encode($header);
    $encodedHeader = base64url_encode($stringifiedHeader);

    // encrypt token data
    $data = array(
        "url" => getDelosLmsURL(),
        "rid" => $course_code,
    );
    $stringifiedData = json_encode($data);
    $encodedData = base64url_encode($stringifiedData);

    // encrypt token and encode token
    $token = $encodedHeader . "." . $encodedData;
    $signature = base64url_encode(hash_hmac('sha256', $token, getDelosSecret(), true));
    $signedToken = $token . "." . $signature;

    return $signedToken;
}

function requestDelosJSON() {
    global $course_code;
    $jsonPublicObj = null;
    $jsonPrivateObj = null;
    $checkAuth = false;

    if (isDelosEnabled()) {
        // construct proper url for public resources
        $delospublicurl = getDelosPublicURL();
        $delospublicurl .= (stringEndsWith($delospublicurl, "/")) ? '' : '/';
        $jsonpublicurl = $delospublicurl . $course_code;

        // construct http headers
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-CustomToken: ' . getDelosSignedToken(),
            'Referer: http://' . getDelosLmsURL()
        );

        // request public json from opendelos
        $jsonpublic = httpGetRequest($jsonpublicurl, $headers);
        $jsonPublicObj = ($jsonpublic) ? json_decode($jsonpublic) : null;

        // request check for auth from opendelos
//        $authresp = httpGetRequest(getDelosCheckAuthURL(), $headers);
//        error_log("delos check auth response:");
//        error_log($authresp);
//        if (strpos($authresp, "Valid") !== false) {
//            $checkAuth = true;
//        }

        // private resources
//        if ($checkAuth) {
            // construct proper url for private resources
//            $delosprivateurl = getDelosPrivateURL();
//            $delosprivateurl .= (stringEndsWith($delosprivateurl, "/")) ? '' : '/';
//            $jsonprivateurl = $delosprivateurl . $course_code;

            // request private json from opendelos
//            $jsonprivate = httpGetRequest($jsonprivateurl, $headers);
//            error_log("delos private response:");
//            error_log($jsonprivate);
//            $jsonPrivateObj = ($jsonprivate) ? json_decode($jsonprivate) : null;

//        }
    }

    return array($jsonPublicObj, $jsonPrivateObj, $checkAuth);
}

function httpGetRequest($url, $headers = array()) {
    $response = null;
    if (!extension_loaded('curl')) {
        return $response;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function storeDelosResources($jsonPublicObj, $jsonPrivateObj, $checkAuth) {
    global $course_id;
    $submittedResources = $_POST['delosResources'];
    $submittedCategory = $_POST['selectcategory'];

    foreach ($submittedResources as $rid) {
        $stored = Database::get()->querySingle("SELECT id 
            FROM videolink 
            WHERE course_id = ?d 
            AND category = ?d 
            AND url LIKE '%rid=" . $rid . "'", $course_id, $submittedCategory);
        foreach ($jsonPublicObj->resources as $resource) {
            if ($resource->resourceID === $rid) {
                $vL = $resource->videoLecture;
                $url = $jsonPublicObj->playerBasePath . '?rid=' . $rid;
                $title = $vL->title;
                $description = $vL->description;
                $creator = $vL->rights->creator->name;
                $publisher = $vL->organization->name;
                $date = $vL->date;

                if ($stored) {
                    $id = $stored->id;
                    Database::get()->query("UPDATE videolink SET 
                        url = ?s, title = ?s, description = ?s, creator = ?s, publisher = ?s, date = ?t 
                        WHERE course_id = ?d 
                        AND category = ?d 
                        AND id = ?d", canonicalize_url($url), $title, $description, $creator, $publisher, $date, $course_id, $submittedCategory, $id);
                } else {
                    $id = Database::get()->query('INSERT INTO videolink 
                        (course_id, url, title, description, category, creator, publisher, date)
                        VALUES (?d, ?s, ?s, ?s, ?d, ?s, ?s, ?t)', 
                        $course_id, canonicalize_url($url), $title, $description, $submittedCategory, $creator, $publisher, $date)->lastInsertID;
                }
                
                // index and log
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_VIDEOLINK, $id);
                Log::record($course_id, MODULE_ID_VIDEO, LOG_INSERT, array('id' => $id,
                    'url' => canonicalize_url($url),
                    'title' => $title,
                    'description' => ellipsize(canonicalize_whitespace(strip_tags($description)), 50, '+')));
            }
        }
    }
}

function getCurrentVideoLinks($course_id) {
    $current = array();
    Database::get()->queryFunc("SELECT url, date FROM videolink WHERE course_id = ?d", function($vl) use (&$current) {
        $current[$vl->url] = $vl->date;
    }, $course_id);
    return $current;
}
