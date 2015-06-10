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
require_once 'include/log.php';

$opendelosapp = ExtAppManager::getApp(strtolower(OpenDelosApp::NAME));

function getDelosURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::URL)->value();
}

function getDelosExtEnabled() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::ENABLED)->value();
}

function isDelosEnabled() {
    global $opendelosapp;
    if ($opendelosapp && getDelosExtEnabled() && getDelosURL()) {
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

function requestDelosJSON() {
    global $course_code;
    $jsonObj = null;
    if (isDelosEnabled()) {
        $jsonbaseurl = getDelosURL();
        $jsonbaseurl .= (stringEndsWith($jsonbaseurl, "/")) ? '' : '/';
        $jsonurl = $jsonbaseurl . $course_code;
        // request json from opendelos
        $json = httpGetRequest($jsonurl);        
        $jsonObj = ($json) ? json_decode($json) : null;
    }
    return $jsonObj;
}

function httpGetRequest($url) {
    $response = null;
    if (!extension_loaded('curl')) {
        $response = file_get_contents($url);
    } else {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }
    return $response;
}

function displayDelosForm($jsonObj, $currentVideoLinks) {
    global $course_id, $course_code, $langTitle, $langDescr, $langcreator, $langpublisher, $langDate,
           $langSelect, $langAddModulesButton, $langOpenDelosReplaceInfo, $langCategory;
    
    if ($jsonObj === null) {
        return '';
    }
    
    $html = '';
    $html .= "<form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>";                
    $html .= <<<delosform
<div class="table-responsive">
    <table class="table-default">
        <tbody>
            <tr class="list-header">
                <th>$langTitle</th>
                <th>$langDescr</th>
                <th>$langcreator</th>
                <th>$langpublisher</th>
                <th>$langDate</th>
                <th>$langSelect</th>
            </tr>
delosform;
    
    $i = 1;
    foreach ($jsonObj->resources as $resource) {
        $trclass = (($i % 2) === 0 ) ? 'even' : 'odd';
        $vL = $resource->videoLecture;
        $rid = $resource->resourceID;
        $url = $jsonObj->playerBasePath . '?rid=' . $rid;
        $title = $vL->title;
        $description = $vL->description;
        $creator = $vL->rights->creator->name;
        $publisher = $vL->rights->editor->name;
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
                    $html .=  "<option value='$myrow->id'";
                    $html .= '>' . q($myrow->name) . "</option>";
                }
                $html .= <<<delosform
                            </select>
                        </div>
                    </div>
                </th>
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

function storeDelosResources($jsonObj) {
    global $course_id;
    $submittedResources = $_POST['delosResources'];
    $submittedCategory = $_POST['selectcategory'];
    
    foreach ($submittedResources as $rid) {
        $stored = Database::get()->querySingle("SELECT id 
            FROM videolink 
            WHERE course_id = ?d 
            AND category = ?d 
            AND url LIKE '%rid=" . $rid . "'", $course_id, $submittedCategory);
        foreach($jsonObj->resources as $resource) {
            if ($resource->resourceID === $rid) {
                $vL = $resource->videoLecture;
                $url = $jsonObj->playerBasePath . '?rid=' . $rid;
                $title = $vL->title;
                $description = $vL->description;
                $creator = $vL->rights->creator->name;
                $publisher = $vL->rights->editor->name;
                $date = $vL->date;

                if ($stored) {
                    $id = $stored->id;
                    $q = Database::get()->query("UPDATE videolink SET 
                        url = ?s, title = ?s, description = ?s, creator = ?s, publisher = ?s, date = ?t 
                        WHERE course_id = ?d 
                        AND category = ?d 
                        AND id = ?d", canonicalize_url($url), $title, $description, $creator, $publisher, $date, $course_id, $submittedCategory, $id);
                } else {
                    $q = Database::get()->query('INSERT INTO videolink (course_id, url, title, description, category, creator, publisher, date)
                        VALUES (?d, ?s, ?s, ?s, ?d, ?s, ?s, ?t)',
                        $course_id, canonicalize_url($url), $title, $description, $submittedCategory, $creator, $publisher, $date);
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