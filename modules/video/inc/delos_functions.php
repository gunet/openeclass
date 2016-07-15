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

function getDelosURL() {
    global $opendelosapp;
    return $opendelosapp->getParam(OpenDelosApp::URL)->value();
}

function getDelosExtEnabled() {
    global $opendelosapp;
    return $opendelosapp->isEnabled();
}

function isDelosEnabled() {
    // DEBUG
    //return true;
    // END DEBUG
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

function getDelosButton($course_code, $urlAppend) {
    return array('title' => $GLOBALS['langAddOpenDelosVideoLink'],
        'url' => $urlAppend . "modules/video/edit.php?course=" . $course_code . "&amp;form_input=opendelos",
        'icon' => 'fa-plus-circle',
        'level' => 'primary-label',
        'button-class' => 'btn-success');
}

function requestDelosJSON() {
    // DEBUG
    //$json = '{"playerBasePath" : "http://opendelos.org/playerBasePath", "resources":[{"resourceID" : "1", "videoLecture" : {"title" : "title1", "description" : "description1", "date" : "2016-07-14 12:00:00", "rights" : {"creator" : {"name" : "crname1"}}, "organization" : {"name" : "orgname1"}}}, {"resourceID" : "2", "videoLecture" : {"title" : "title2", "description" : "description2", "date" : "2016-07-15 12:00:00", "rights" : {"creator" : {"name" : "crname2"}}, "organization" : {"name" : "orgname2"}}} ]}';
    //return json_decode($json);
    // END DEBUG
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
        foreach ($jsonObj->resources as $resource) {
            if ($resource->resourceID === $rid) {
                $vL = $resource->videoLecture;
                $url = $jsonObj->playerBasePath . '?rid=' . $rid;
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
