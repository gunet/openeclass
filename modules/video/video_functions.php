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

require_once 'modules/search/classes/ConstantsUtil.php';
require_once 'modules/search/classes/SearchEngineFactory.php';

function headlink($label, $this_sort) {
    global $sort, $reverse, $course_code;

    $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code;

    if ($sort == $this_sort) {
        $this_reverse = !$reverse;
        $icon = ($reverse) ? 'fa-solid fa-caret-down': 'fa-solid fa-caret-up';
    } else {
        $this_reverse = $reverse;
        $icon = '';
    }

    return "<a class='TextBold text-decoration-none text-nowrap' href='{$base_url}&amp;sort=$this_sort" . ($this_reverse ? '&amp;rev=1' : '') . "'>
                <i class='$icon'></i>  $label</a>";
}

/**
 *
 * @param type $table
 * @return return table name
 */
function select_table($table) {
    if ($table == 'videolink') {
        return $table;
    } else {
        return 'video';
    }
}

function select_proper_filters($requestDocsFilter) {
    $filterv = 'WHERE true';
    $filterl = 'WHERE true';
    $compatiblePlugin = true;

    switch ($requestDocsFilter) {
        case 'image':
            $ors = '';
            $first = true;
            foreach (MultimediaHelper::getSupportedImages() as $imgfmt) {
                if ($first) {
                    $ors .= "path LIKE '%$imgfmt%'";
                    $first = false;
                } else {
                    $ors .= " OR path LIKE '%$imgfmt%'";
                }
            }

            $filterv = "WHERE ( $ors )";
            $filterl = "WHERE false";
            break;
        case 'zip':
            $filterv = $filterl = "WHERE false";
            break;
        case 'media':
            $compatiblePlugin = false;
            break;
        case 'eclmedia':
        case 'file':
        default:
            break;
    }

    return array($filterv, $filterl, $compatiblePlugin);
}

/**
 * @brief add / edit video category
 */
function submit_video_category($course_id, $course_code) {

    register_posted_variables(array('categoryname' => true,
        'description' => true), 'all', 'trim');
    $pdesc = purify($_POST['description']);
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('categoryname'))->message($GLOBALS['langTheFieldIsRequired'])->label('');
    if($v->validate()) {
        if (isset($_POST['id'])) {
            Database::get()->query("UPDATE `video_category` SET name = ?s, description = ?s WHERE id = ?d", $_POST['categoryname'], $pdesc, $_POST['id']);
            $catlinkstatus = $GLOBALS['langCategoryModded'];
        } else {
            Database::get()->query("INSERT INTO `video_category` SET name = ?s, description = ?s, course_id = ?d", $_POST['categoryname'], $pdesc, $course_id);
            $catlinkstatus = $GLOBALS['langCategoryAdded'];
        }
    } else {
        Session::flashPost()->Messages($GLOBALS['langFormErrors'])->Errors($v->errors());
        redirect_to_home_page("modules/video/editCategory.php?course=" . $course_code);
    }
}

/**
 * @brief delete video / videolink
 */
function delete_video($id, $table, $course_id, $course_code, $webDir) {

    $myrow = Database::get()->querySingle("SELECT * FROM $table WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $title = $myrow->title;

    if ($table == "video") {
        unlink("$webDir/video/$course_code/" . $myrow->path);
    }

    Database::get()->query("DELETE FROM $table WHERE course_id = ?d AND id = ?d", $course_id, $id);

    // index and log
    $searchEngine = SearchEngineFactory::create();
    if ($table == 'video') {
        $searchEngine->indexResource(ConstantsUtil::REQUEST_REMOVE, ConstantsUtil::RESOURCE_VIDEO, $id);
    } elseif ($table == 'videolink') {
        $searchEngine->indexResource(ConstantsUtil::REQUEST_REMOVE, ConstantsUtil::RESOURCE_VIDEOLINK, $id);
    }

    Log::record($course_id, MODULE_ID_VIDEO, LOG_DELETE, array('id' => $id, 'title' => $title));
}

/**
 * @brief delete video category
 * @param type $id
 */
function delete_video_category($id) {
    Database::get()->query("DELETE FROM video_category WHERE id = ?d", $id);
}

function getLinksOfCategory($cat_id, $is_editor, $filterv, $order, $course_id, $filterl, $is_in_tinymce, $compatiblePlugin) {

    global $langCreator;

    $uncatresults = array();

    $vis_q = ($is_editor) ? '' : "AND visible = 1";
    if ($cat_id > 0) {
        $results['video'] = Database::get()->queryArray("SELECT * FROM video $filterv AND course_id = ?d AND category = ?d $vis_q $order", $course_id, $cat_id);
        $results['videolink'] = Database::get()->queryArray("SELECT * FROM videolink $filterl AND course_id = ?d AND category = ?d $vis_q $order", $course_id, $cat_id);
    } else {
        $results['video'] = Database::get()->queryArray("SELECT * FROM video $filterv AND course_id = ?d AND (category IS NULL OR category = 0) $vis_q $order", $course_id);
        $results['videolink'] = Database::get()->queryArray("SELECT * FROM videolink $filterl AND course_id = ?d AND (category IS NULL OR category = 0) $vis_q $order", $course_id);
    }

    foreach ($results as $table => $result) {
        foreach ($result as $myrow) {
            $myrow->course_id = $course_id;
            $resultObj = new stdClass();
            $resultObj->myrow = $myrow;
            $resultObj->table = $table;

            if (resource_access($myrow->visible, $myrow->public) || $is_editor) {
                switch ($table) {
                    case 'video':
                        $vObj = MediaResourceFactory::initFromVideo($myrow);
                        if ($is_in_tinymce && !$compatiblePlugin) { // use Access/DL URL for non-modable tinymce plugins
                            $vObj->setPlayURL($vObj->getAccessURL());
                        }
                        $resultObj->vObj = $vObj;
                        $resultObj->link_href = MultimediaHelper::chooseMediaAhref($vObj);
                        $resultObj->link_to_save = $vObj->getAccessURL() . '&amp;attachment';
                        break;
                    case "videolink":
                        $resultObj->vObj = $vObj = MediaResourceFactory::initFromVideoLink($myrow);
                        $resultObj->link_href = MultimediaHelper::chooseMedialinkAhref($vObj);
                        $resultObj->link_to_save = $vObj->getPath();
                        break;
                    default:
                        exit;
                }

                $resultObj->row_class = (!$myrow->visible) ? 'not_visible' : 'visible' ;
                $resultObj->extradescription = '';

                if (!$is_in_tinymce) {
                    if ($myrow->description) {
                        $resultObj->extradescription .= "<div class='help-block'>" . q($myrow->description) . "</div>";
                    }
                    if (!empty($myrow->creator)) {
                        $resultObj->extradescription .= "<div class='help-block mt-2'>$langCreator: " . q($myrow->creator) . "</div>";
                    }
                }
                $uncatresults[] = $resultObj;
            }
        }
    }

    return ($uncatresults);
}

function getQuotaInfo($course_code, $webDir) {

    $diskQuotaVideo = Database::get()->querySingle("SELECT video_quota FROM course WHERE code=?s", $course_code)->video_quota;
    $updir = $webDir . "/video/" . $course_code; //path to upload directory
    if (!file_exists($updir)) {
        mkdir($updir, 0755);
    }
    $diskUsed = dir_total_space($updir);

    return array($diskQuotaVideo, $updir, $diskUsed);
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


function getCurrentVideoLinks() {
    global $course_id;
    $current = array();
    Database::get()->queryFunc("SELECT url, date FROM videolink WHERE course_id = ?d", function($vl) use (&$current) {
        $current[$vl->url] = $vl->date;
    }, $course_id);
    return $current;
}
