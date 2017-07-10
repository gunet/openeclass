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

function getDelosButton() {
    global $course_code, $langAddOpenDelosVideoLink;
    return array('title' => $langAddOpenDelosVideoLink,
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;form_input=opendelos",
        'icon' => 'fa-plus-circle',
        'level' => 'primary-label',
        'button-class' => 'btn-success');
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

function displayDelosForm($jsonPublicObj, $jsonPrivateObj, $checkAuth, $currentVideoLinks) {
    global $course_id, $course_code, $langTitle, $langDescription, $langcreator, $langpublisher, $langDate,
           $langSelect, $langAddModulesButton, $langOpenDelosReplaceInfo, $langCategory, $langNoVideo,
           $langOpenDelosPublicVideos, $langOpenDelosPrivateVideos, $urlServer;

    $html = '';
    $html .= "<form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>";
    $html .= <<<delosform
<div class="table-responsive">
    <table class="table-default">
        <tbody>
            <tr class="list-header">
                <th>$langTitle</th>
                <th>$langDescription</th>
                <th>$langcreator</th>
                <th>$langpublisher</th>
                <th>$langDate</th>
                <th>$langSelect</th>
            </tr>
            <tr class="list-header">
                <th colspan="6">$langOpenDelosPublicVideos</th>
            </tr>
delosform;

    if ($jsonPublicObj !== null && property_exists($jsonPublicObj, "resources")) {
        $i = 1;
        foreach ($jsonPublicObj->resources as $resource) {
            $trclass = (($i % 2) === 0) ? 'even' : 'odd';
            $vL = $resource->videoLecture;
            $rid = $resource->resourceID;
            $url = $jsonPublicObj->playerBasePath . '?rid=' . $rid;
            $title = $vL->title;
            $description = $vL->description;
            $creator = $vL->rights->creator->name;
            $publisher = $vL->organization->name;
            $date = $vL->date;
            $dateTS = strtotime($date);
            $alreadyAdded = '';
            if (isset($currentVideoLinks[$url])) {
                $alreadyAdded = '<span style="color:red">*';
                $currentTS = strtotime($currentVideoLinks[$url]);
                if ($dateTS > $currentTS) {
                    $alreadyAdded .= '*';
                }
                $alreadyAdded .= '</span>';
            }

            $html .= <<<delosform
                <tr class="$trclass">
                    <td align="left"><a href="$url" class="fileURL" target="_blank" title="$title">$title</a></td>
                    <td>$description</td>
                    <td>$creator</td>
                    <td>$publisher</td>
                    <td>$date</td>
                    <td class="center" width="10">
                        <input name="delosResources[]" value="$rid" type="checkbox"/> $alreadyAdded
                    </td>
                </tr>
delosform;
            $i++;
        }
    } else {
        $html .= "<tr><td colspan='6'><div class='alert alert-warning' role='alert'>$langNoVideo</div></td></tr>";
    }

    /*$html .= <<<delosform
            <tr class="list-header">
                <th colspan="6">$langOpenDelosPrivateVideos</th>
            </tr>
delosform;*/

//    if (!$checkAuth) {
//        $authUrl = getDelosRLoginURL();
//        $authUrl = getDelosRLoginCASURL();
//        $authUrl .= "?token=" . getDelosSignedToken();
//        $authUrl = $urlServer . "modules/video/rldelos.php";
//        $authHref = "<a onclick=\"window.open('$authUrl', '_blank', 'location=yes,height=570,width=520,scrollbars=yes,status=yes');\" href='#'>here</a> or ";
//        $authHref .= "<a href='$authUrl' target='_blank'>here2</a>";
//        $html .= "<tr><td colspan='6'><div class='alert alert-warning' role='alert'>you need to auth. click $authHref</div></td></tr>"; // TODO: implement
//    } else {
//        if ($jsonPrivateObj !== null && property_exists($jsonPrivateObj, "resources")) {
// TODO: implement
//        } else {
//            $html .= "<tr><td colspan='6'><div class='alert alert-warning' role='alert'>$langNoVideo</div></td></tr>";
//        }
//    }

    /*$html .= <<<delosform
            <tr>
                <th colspan="4">
                    <div class='form-group'>
                        <label for='Category' class='col-sm-2 control-label'>$langCategory:</label>
                        <div class='col-sm-10'>
                            <select class='form-control' name='selectcategory'>
                                <option value='0'>--</option>
delosform;
    $resultcategories = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY `name`", $course_id);
    foreach ($resultcategories as $myrow) {
        $html .= "<option value='$myrow->id'";
        $html .= '>' . q($myrow->name) . "</option>";
    }
    $html .= <<<delosform
                            </select>
                        </div>
                    </div>
                </th>
delosform;*/
    $html .= <<<delosform
                <th colspan="2">
                    <div class="pull-right">
                        <input class="btn btn-primary" name="add_submit_delos" value="$langAddModulesButton" type="submit">        
                    </div>
                </th>
            </tr>
        </tbody>
    </table>
</div></form>
delosform;
    $html .= "<div class='alert alert-warning' role='alert'>$langOpenDelosReplaceInfo</div>";

    return $html;
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
                    $q = Database::get()->query('INSERT INTO videolink (course_id, url, title, description, category, creator, publisher, date)
                        VALUES (?d, ?s, ?s, ?s, ?d, ?s, ?s, ?t)', $course_id, canonicalize_url($url), $title, $description, $submittedCategory, $creator, $publisher, $date);
                    $id = $q->lastInsertID;
                }
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_VIDEOLINK, $id);
                $txt_description = ellipsize(canonicalize_whitespace(strip_tags($description)), 50, '+');
                Log::record($course_id, MODULE_ID_VIDEO, LOG_INSERT, array('id' => $id,
                    'url' => canonicalize_url($url),
                    'title' => $title,
                    'description' => $txt_description));
            }
        }
    }
}

function getCurrentVideoLinks() {
    global $course_id;
    $current = array();
    Database::get()->queryFunc("SELECT url, date FROM videolink WHERE course_id = ?d", function($vl) use (&$current) {
        $current[$vl->url] = $vl->date;
    }, $course_id);
    return $current;
}
