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

define('DELOSTOKEN', 'DELOSTOKEN');
define('DELOSTOKENTIMESTAMP', 'DELOSTOKENTIMESTAMP');
define('DELOSTOKENEXPIRE', 60);

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

function getDelosURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::URL)->value();
}

function getDelosPublicAPI() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::PUBLIC_API)->value();
}

function getDelosPrivateAPI() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::PRIVATE_API)->value();
}

function getDelosRLoginAPI() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::RLOGIN_API)->value();
}

function getDelosRLoginCASAPI() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::RLOGINCAS_API)->value();
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
    if ($opendelosapp && getDelosExtEnabled() && getDelosURL() && getDelosPublicAPI()
        && getDelosPrivateAPI() && getDelosRLoginAPI() && getDelosRLoginCASAPI()
        && getDelosLmsURL() && getDelosSecret()) {
        return true;
    }
    return false;
}

function getDelosJavaScript() {
    global $langOk;
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
    
    $('.fileModal').click(function (e) {
        e.preventDefault();
        
        var fileURL = $(this).attr('href');
        var fileTitle = $(this).attr('title');
        
        bootbox.dialog({
            size: 'large',
            title: fileTitle,
            message: '<div class=\"row\">'+
                        '<div class=\"col-sm-12\">'+
                            '<div class=\"iframe-container\"><iframe id=\"fileFrame\" src=\"'+fileURL+'\"></iframe></div>'+
                        '</div>'+
                    '</div>',
            buttons: {
                ok: {
                    label: '$langOk',
                    className: 'btn-default',
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

    $reuse = false;
    if (isset($_SESSION[DELOSTOKENTIMESTAMP][$course_code]) && isset($_SESSION[DELOSTOKEN][$course_code])) {
        if (time() < $_SESSION[DELOSTOKENTIMESTAMP][$course_code]) {
            $reuse = true;
        }
    }

    if ($reuse) {
        return $_SESSION[DELOSTOKEN][$course_code];
    } else {
        return generateDelosSignedToken();
    }
}

function generateDelosSignedToken() {
    global $course_code;

    // encrypt token header
    $header = array(
        "alg" => "HS256",
        "typ" => "JWT"
    );
    $stringifiedHeader = json_encode($header);
    $encodedHeader = base64url_encode($stringifiedHeader);

    // Set token expiration time to X minutes from now
    $exp_time_in_seconds = time() + DELOSTOKENEXPIRE * 60;

    // encrypt token data
    $data = array(
        "url" => getDelosLmsURL(),
        "rid" => $course_code,
        "exp" => $exp_time_in_seconds,
    );
    $stringifiedData = json_encode($data);
    $encodedData = base64url_encode($stringifiedData);

    // encrypt token and encode token
    $token = $encodedHeader . "." . $encodedData;
    $signature = base64url_encode(hash_hmac('sha256', $token, getDelosSecret(), true));
    $signedToken = $token . "." . $signature;

    $_SESSION[DELOSTOKEN][$course_code] = $signedToken;
    $_SESSION[DELOSTOKENTIMESTAMP][$course_code] = $exp_time_in_seconds;

    return $signedToken;
}

function getDelosSignedTokenForVideo($rid) {
    // encrypt token header
    $header = array(
        "alg" => "HS256",
        "typ" => "JWT"
    );
    $stringifiedHeader = json_encode($header);
    $encodedHeader = base64url_encode($stringifiedHeader);

    // Set token expiration time to X minutes from now
    $exp_time_in_seconds = time() + DELOSTOKENEXPIRE * 60;

    // encrypt token data
    $data = array(
        "url" => getDelosLmsURL(),
        "rid" => $rid,
        "exp" => $exp_time_in_seconds,
    );
    $stringifiedData = json_encode($data);
    $encodedData = base64url_encode($stringifiedData);

    // encrypt token and encode token
    $token = $encodedHeader . "." . $encodedData;
    $signature = base64url_encode(hash_hmac('sha256', $token, getDelosSecret(), true));
    $signedToken = $token . "." . $signature;

    return $signedToken;
}

function isCASUser() {
    global $uid;
    $ret = false;
    $q = Database::get()->querySingle("SELECT password FROM user WHERE id = ?d", $uid);
    if ($q && $q->password == 'cas') {
        $ret = true;
    }
    return $ret;
}

function requestDelosJSON() {
    global $course_code;
    $jsonPublicObj = null;
    $jsonPrivateObj = null;
    $checkAuth = false;

    if (isDelosEnabled()) {
        // construct proper url for public resources
        $delospublicurl = getDelosURL() . getDelosPublicAPI();
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
        list($jsonpublic, $codepublic) = httpGetRequest($jsonpublicurl, $headers);
        $jsonPublicObj = ($jsonpublic && $codepublic == 200) ? json_decode($jsonpublic) : null;

        // private resources
        // construct proper url for private resources
        $delosprivateurl = getDelosURL() . getDelosPrivateAPI();
        $delosprivateurl .= (stringEndsWith($delosprivateurl, "/")) ? '' : '/';
        $jsonprivateurl = $delosprivateurl . $course_code;

        // request private json from opendelos
        list($jsonprivate, $codeprivate) = httpGetRequest($jsonprivateurl, $headers);
        if ($codeprivate == 200) {
            $checkAuth = true;
            $jsonPrivateObj = json_decode($jsonprivate);
        }
    }

    return array($jsonPublicObj, $jsonPrivateObj, $checkAuth);
}

function httpGetRequest($url, $headers = array()) {
    $response = null;
    $http_code = null;
    if (!extension_loaded('curl')) {
        return array($response, $http_code);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    if(!curl_errno($ch)) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    curl_close($ch);

    return array($response, $http_code);
}

function displayDelosForm($jsonPublicObj, $jsonPrivateObj, $checkAuth, $currentVideoLinks) {
    global $course_id, $course_code, $langTitle, $langDescription, $langcreator, $langpublisher, $langDate,
           $langSelect, $langAddModulesButton, $langOpenDelosReplaceInfo, $langCategory, $langNoVideo,
           $langOpenDelosPublicVideos, $langOpenDelosPrivateVideos, $urlServer, $langOpenDelosAuth,
           $langOpenDelosRequireAuth, $langOpenDelosRequireAuthHere;

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
            $urltoken = '&token=' . getDelosSignedTokenForVideo($rid);
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
                    <td align="left"><a href="$url . $urltoken" class="fileURL" target="_blank" title="$title">$title</a></td>
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

    $html .= <<<delosform
            <tr class="list-header">
                <th colspan="6">$langOpenDelosPrivateVideos</th>
            </tr>
delosform;

    if (!$checkAuth) {
        $authUrl = (isCASUser()) ? getDelosURL() . getDelosRLoginCASAPI() : getDelosURL() . getDelosRLoginAPI();
        $authUrl .= "?token=" . getDelosSignedToken();
        $authHref = "<a href='$authUrl' class='fileModal' target='_blank' title='$langOpenDelosAuth'>$langOpenDelosRequireAuthHere</a>";
        $html .= "<tr><td colspan='6'><div class='alert alert-warning' role='alert'>" . $langOpenDelosRequireAuth . " " .  $authHref . "</div></td></tr>";
    } else {
        if ($jsonPrivateObj !== null && property_exists($jsonPrivateObj, "resources")) {
            $i = 1;
            foreach ($jsonPrivateObj->resources as $resource) {
                $trclass = (($i % 2) === 0) ? 'even' : 'odd';
                $vL = $resource->videoLecture;
                $rid = $resource->resourceID;
                $url = $jsonPrivateObj->playerBasePath . '?rid=' . $rid;
                $urltoken = '&token=' . getDelosSignedTokenForVideo($rid);
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
                    <td align="left"><a href="$url . $urltoken" class="fileURL" target="_blank" title="$title">$title</a></td>
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
    }

    $html .= <<<delosform
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
delosform;
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

        // public
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

        // private
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
