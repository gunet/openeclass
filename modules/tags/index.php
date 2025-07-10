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


$require_current_course = true;
$require_help = false;
$guest_allowed = true;


include '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.class.php';
require_once 'modules/search/lucene/indexer.class.php';
// The following is added for statistics purposes
require_once 'include/action.php';

// Special case for static modules
$modules[MODULE_ID_UNITS] = array('title' => $langCourseUnits, 'link' => 'units', 'image' => '');

if (isset($_GET['tag']) && strlen($_GET['tag'])) {
    $tag = $_GET['tag'];
    $tag_elements = Database::get()->queryArray("SELECT * FROM `tag_element_module`, `tag` WHERE `tag`.`name` = ?s AND `tag`.`id` =  `tag_element_module`.`tag_id` AND `tag_element_module`.`course_id` = ?d ORDER BY module_id", $tag, $course_id);
    $toolName = "$langTag: " . $tag;
    //check the element type
    $latest_module_id = 0;
    foreach ($tag_elements as $tag) {
        if ($tag->module_id !== $latest_module_id && $latest_module_id){
            $tool_content .= "</div></div>";
        }
        if ($tag->module_id !== $latest_module_id){
            $tool_content .= "
            <div class='col-12 mt-3'>
                    <div class='card panelCard card-default px-lg-4 py-lg-3'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>" .
				$modules[$tag->module_id]['title'] . "
                            </h3>
                        </div>
                        <div class='card-body'>";
        }
        if ($tag->module_id == MODULE_ID_ANNOUNCE){
            $announce = Database::get()->querySingle("SELECT title, content FROM announcement WHERE id = ?d ", $tag->element_id);
            $link = "<a href='{$urlAppend}modules/announcements/index.php?course=$course_code&amp;an_id={$tag->element_id}'>" . q($announce->title) . "</a><br>";
        }
        if ($tag->module_id == MODULE_ID_ASSIGN){
            $work = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d ", $tag->element_id);
            $link = "<a href='{$urlAppend}modules/work/index.php?course=$course_code&amp;id={$tag->element_id}'>" . q($work->title) . "</a><br>";
        }
        if ($tag->module_id == MODULE_ID_EXERCISE){
            $exercise = Database::get()->querySingle("SELECT title FROM exercise WHERE id = ?d ", $tag->element_id);
            $link = "<a href='{$urlAppend}modules/exercise/admin.php?course=$course_code&amp;exerciseId={$tag->element_id}'>" . q($exercise->title) . "</a><br>";
        }
        if ($tag->module_id == MODULE_ID_UNITS){
            $unit = Database::get()->querySingle("SELECT title FROM course_units WHERE id = ?d ", $tag->element_id);
            $link = "<a href='{$urlAppend}modules/units/index.php?course=$course_code&amp;id={$tag->element_id}'>" . q($unit->title) . "</a><br>";
        }
        $tool_content .= "
                    <ul class='mb-0'>
                        <li>$link</li>
                    </ul>
                ";
        $latest_module_id = $tag->module_id;
    }
    if ($tag_elements) {
        $tool_content .= "</div></div></div>";
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langTagNotFound</span></div></div>";
    }
}

draw($tool_content, 2, null, $head_content);
