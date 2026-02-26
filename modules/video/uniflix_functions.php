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

require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/uniflixapp.php';
require_once 'include/log.class.php';
require_once 'include/lib/curlutil.class.php';
require_once 'modules/search/classes/ConstantsUtil.php';
require_once 'modules/search/classes/SearchEngineFactory.php';

define('UNIFLIXTOKEN', 'UNIFLIXTOKEN');
define('UNIFLIXTOKENTIMESTAMP', 'UNIFLIXTOKENTIMESTAMP');
define('UNIFLIXTOKENEXPIRE', 60);

$uniflixapp = ExtAppManager::getApp(strtolower(UniFlixApp::NAME));

function getUniFlixExtEnabled() {
    global $uniflixapp;

    return $uniflixapp->isEnabled();
}

function getUniFlixURL() {
    global $uniflixapp;

    return $uniflixapp->getParam(UniFlixApp::URL)->value();
}

function getUniFlixPublicAPI() {
    global $uniflixapp;

    return $uniflixapp->getParam(UniFlixApp::PUBLIC_API)->value();
}

function getUniFlixPrivateAPI() {
    global $uniflixapp;

    return $uniflixapp->getParam(UniFlixApp::PRIVATE_API)->value();
}

function getUniFlixRLoginAPI() {
    global $uniflixapp;

    return $uniflixapp->getParam(UniFlixApp::RLOGIN_API)->value();
}

function getUniFlixRLoginCASAPI() {
    global $uniflixapp;

    return $uniflixapp->getParam(UniFlixApp::RLOGINCAS_API)->value();
}

function getUniFlixLmsURL() {
    global $uniflixapp;

    return $uniflixapp->getParam(UniFlixApp::LMS_URL)->value();
}

function getUniFlixSecret() {
    global $uniflixapp;

    return $uniflixapp->getParam(UniFlixApp::SECRET)->value();
}

function isUniFlixEnabled() {
    global $uniflixapp;

    if ($uniflixapp && getUniFlixExtEnabled() && getUniFlixURL() && getUniFlixPublicAPI()
        && getUniFlixPrivateAPI() && getUniFlixRLoginAPI() && getUniFlixRLoginCASAPI()
        && getUniFlixLmsURL() && getUniFlixSecret()) {
        return true;
    }
    return false;
}

function getUniFlixJavaScript() {
    global $langOk;
    $head = '';
    if (isset($_GET['form_input']) && $_GET['form_input'] === 'uniflix') {
        $head .= <<<hContent
<script type='text/javascript'>
$(document).ready(function() {

    $('tr').click(function(event) {
        if (event.target.type !== 'checkbox') {
            $(':checkbox', this).trigger('click');
        }
    });

    $('.fileModal').click(function (e) {
        e.preventDefault();

        var fileURL = $(this).attr('href');
        var fileTitle = $(this).attr('title');

        bootbox.dialog({
            size: 'large',
            title: fileTitle,
            message: '<div class=\"row\">'+
                        '<div class=\"col-sm-12\">'+
                            '<div class=\"iframe-container\"><iframe title=\"'+fileTitle+'\" id=\"fileFrame\" src=\"'+fileURL+'\"></iframe></div>'+
                        '</div>'+
                    '</div>',
            buttons: {
                ok: {
                    label: '$langOk',
                    className: 'submitAdminBtn',
                    callback: function (d) {
                        window.location.reload(true);
                    }
                }
            }
        });
    });

});
</script>
hContent;
    }
    return $head;
}

function getUniFlixButton() {
    global $course_code, $langAddUniFlixVideoLink;

    return array('title' => $langAddUniFlixVideoLink,
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;form_input=uniflix",
        'icon' => 'fa-plus-circle',
        'level' => 'primary-label',
        'button-class' => 'btn-success');
}

function getUniFlixSignedToken() {
    global $course_code;

    $reuse = false;
    if (isset($_SESSION[UNIFLIXTOKENTIMESTAMP][$course_code]) && isset($_SESSION[UNIFLIXTOKEN][$course_code])) {
        if (time() < $_SESSION[UNIFLIXTOKENTIMESTAMP][$course_code]) {
            $reuse = true;
        }
    }

    if ($reuse) {
        return $_SESSION[UNIFLIXTOKEN][$course_code];
    } else {
        return generateUniFlixSignedToken();
    }
}

function generateUniFlixSignedToken() {
    global $course_code;

    // encrypt token header
    $header = array(
        "alg" => "HS256",
        "typ" => "JWT"
    );
    $stringifiedHeader = json_encode($header);
    $encodedHeader = base64url_encode($stringifiedHeader);

    // Set token expiration time to X minutes from now
    $exp_time_in_seconds = time() + UNIFLIXTOKENEXPIRE * 60;

    // encrypt token data
    $data = array(
        "url" => getUniFlixLmsURL(),
        "rid" => $course_code,
        "exp" => $exp_time_in_seconds,
        "redirect_url" => $GLOBALS['urlServer'] . "modules/video/index.php?course=$course_code&amp;form_input=uniflix",
    );
    $stringifiedData = json_encode($data);
    $encodedData = base64url_encode($stringifiedData);

    // encrypt token and encode token
    $token = $encodedHeader . "." . $encodedData;
    $signature = base64url_encode(hash_hmac('sha256', $token, getUniFlixSecret(), true));
    $signedToken = $token . "." . $signature;

    $_SESSION[UNIFLIXTOKEN][$course_code] = $signedToken;
    $_SESSION[UNIFLIXTOKENTIMESTAMP][$course_code] = $exp_time_in_seconds;

    return $signedToken;
}

function getUniFlixSignedTokenForVideo($rid) {
    // encrypt token header
    $header = array(
        "alg" => "HS256",
        "typ" => "JWT"
    );
    $stringifiedHeader = json_encode($header);
    $encodedHeader = base64url_encode($stringifiedHeader);

    // Set token expiration time to X minutes from now
    $exp_time_in_seconds = time() + UNIFLIXTOKENEXPIRE * 60;

    // encrypt token data
    $data = array(
        "url" => getUniFlixLmsURL(),
        "rid" => $rid,
        "exp" => $exp_time_in_seconds,
    );
    $stringifiedData = json_encode($data);
    $encodedData = base64url_encode($stringifiedData);

    // encrypt token and encode token
    $token = $encodedHeader . "." . $encodedData;
    $signature = base64url_encode(hash_hmac('sha256', $token, getUniFlixSecret(), true));
    $signedToken = $token . "." . $signature;

    return $signedToken;
}

function requestUniFlixJSON() {
    global $course_code;
    $jsonPublicObj = null;
    $jsonPrivateObj = null;
    $checkAuth = false;

    if (isUniFlixEnabled()) {
        // construct proper url for public resources
        $uniflixpublicurl = getUniFlixURL() . getUniFlixPublicAPI();
        $uniflixpublicurl .= (stringEndsWith($uniflixpublicurl, "/")) ? '' : '/';
        $jsonpublicurl = $uniflixpublicurl . $course_code;

        // construct http headers
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-CustomToken: ' . getUniFlixSignedToken(),
            'Referer: http://' . getUniFlixLmsURL()
        );

        // request public json from uniflix
        list($jsonpublic, $codepublic) = CurlUtil::httpGetRequest($jsonpublicurl, $headers);
        $jsonPublicObj = ($jsonpublic && $codepublic == 200) ? json_decode($jsonpublic) : null;

        // private resources
        // construct proper url for private resources
        $uniflixprivateurl = getUniFlixURL() . getUniFlixPrivateAPI();
        $uniflixprivateurl .= (stringEndsWith($uniflixprivateurl, "/")) ? '' : '/';
        $jsonprivateurl = $uniflixprivateurl . $course_code;

        // request private json from uniflix
        list($jsonprivate, $codeprivate) = CurlUtil::httpGetRequest($jsonprivateurl, $headers);
        if ($codeprivate == 200) {
            $checkAuth = true;
            $jsonPrivateObj = json_decode($jsonprivate);
        }
    }

    return array($jsonPublicObj, $jsonPrivateObj, $checkAuth);
}

function storeUniFlixResources($jsonPublicObj, $jsonPrivateObj, $checkAuth) {
    global $course_id;
    $submittedResources = $_POST['UniFlixResources'];
    $submittedCategory = $_POST['selectcategory'];
    $searchEngine = SearchEngineFactory::create();

    foreach ($submittedResources as $rid) {
        $stored = Database::get()->querySingle("SELECT id
            FROM videolink
            WHERE course_id = ?d
            AND category = ?d
            AND url LIKE ?s", $course_id, $submittedCategory, "%rid=$rid");

        // public
        if ($jsonPublicObj !== null && property_exists($jsonPublicObj, "resources")) {
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
                        $q = Database::get()->query('INSERT INTO videolink (course_id, url, title, description, category, creator, publisher, date)
                        VALUES (?d, ?s, ?s, ?s, ?d, ?s, ?s, ?t)', $course_id, canonicalize_url($url), $title, $description, $submittedCategory, $creator, $publisher, $date);
                        $id = $q->lastInsertID;
                    }
                    $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_VIDEOLINK, $id);
                    $txt_description = ellipsize(canonicalize_whitespace(strip_tags($description)), 50, '+');
                    Log::record($course_id, MODULE_ID_VIDEO, LOG_INSERT, array('id' => $id,
                        'url' => canonicalize_url($url),
                        'title' => $title,
                        'description' => $txt_description));
                }
            }
        }

        // private
        if ($jsonPrivateObj !== null && property_exists($jsonPrivateObj, "resources")) {
            foreach ($jsonPrivateObj->resources as $resource) {
                if ($resource->resourceID === $rid) {
                    $vL = $resource->videoLecture;
                    $url = $jsonPrivateObj->playerBasePath . '?rid=' . $rid;
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
                        $q = Database::get()->query('INSERT INTO videolink (course_id, url, title, description, category, creator, publisher, date)
                            VALUES (?d, ?s, ?s, ?s, ?d, ?s, ?s, ?t)', $course_id, canonicalize_url($url), $title, $description, $submittedCategory, $creator, $publisher, $date);
                        $id = $q->lastInsertID;
                    }
                    $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_VIDEOLINK, $id);
                    $txt_description = ellipsize(canonicalize_whitespace(strip_tags($description)), 50, '+');
                    Log::record($course_id, MODULE_ID_VIDEO, LOG_INSERT, array('id' => $id,
                        'url' => canonicalize_url($url),
                        'title' => $title,
                        'description' => $txt_description));
                }
            }
        }
    }
}
