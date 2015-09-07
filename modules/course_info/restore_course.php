<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * ======================================================================== */

$require_departmentmanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/pclzip/pclzip.lib.php';
require_once 'include/phpass/PasswordHash.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'restore_functions.php';

$treeObj = new Hierarchy();
$courseObj = new Course();

load_js('jstree3');

list($js, $html) = $treeObj->buildCourseNodePicker();
$head_content .= $js;

$pageName = $langRestoreCourse;
$navigation[] = array('url' => '../admin/index.php', 'name' => $langAdmin);

// Default backup version
if (isset($_FILES['archiveZipped']) and $_FILES['archiveZipped']['size'] > 0) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    validateUploadedFile($_FILES['archiveZipped']['name'], 3);

    $tool_content .= "<fieldset>
        <legend>" . $langFileSent . "</legend>
        <table class='table-default'>
                   <tr><th width='150'>$langFileSentName</td><td>" . q($_FILES['archiveZipped']['name']) . "</th></tr>
                   <tr><th>$langFileSentSize</td><td>" . q($_FILES['archiveZipped']['size']) . "</th></tr>
                   <tr><th>$langFileSentType</td><td>" . q($_FILES['archiveZipped']['type']) . "</th></tr>
                   <tr><th>$langFileSentTName</td><td>" . q($_FILES['archiveZipped']['tmp_name']) . "</th></tr>
                </table></fieldset>
                        <fieldset>
        <legend>" . $langFileUnzipping . "</legend>
        <table class='table-default'>
                    <tr><td>" . unpack_zip_show_files($_FILES['archiveZipped']['tmp_name']) . "</td></tr>
                </table></fieldset>";
} elseif (isset($_POST['send_path']) and isset($_POST['pathToArchive'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $pathToArchive = $_POST['pathToArchive'];
    validateUploadedFile(basename($pathToArchive), 3);
    if (get_file_extension($pathToArchive) !== 'zip') {
        $tool_content .= "<div class='alert alert-danger'>" . $langErrorFileMustBeZip . "</div>";
    } else if (file_exists($pathToArchive)) {
        $tool_content .= "<fieldset>
        <legend>" . $langFileUnzipping . "</legend>
        <table class='table-default'>";
        $tool_content .= "<tr><td>" . unpack_zip_show_files($pathToArchive) . "</td></tr>";
        $tool_content .= "</table></fieldset>";
    } else {
        $tool_content .= "<div class='alert alert-danger'>$langFileNotFound</div>";
    }
} elseif (isset($_POST['create_restored_course'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    register_posted_variables(array('restoreThis' => true,
        'course_code' => true,
        'course_lang' => true,
        'course_title' => true,
        'course_desc' => true,
        'course_vis' => true,
        'course_prof' => true), 'all');
    create_restored_course($tool_content,  getDirectReference($restoreThis) , $course_code, $course_lang, $course_title, $course_desc, $course_vis, $course_prof);
} elseif (isset($_POST['do_restore'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $base = getDirectReference($_POST['restoreThis']);
    if (!file_exists($base . '/config_vars')) {
        $tool_content .= "<div class='alert alert-warning'>$langInvalidArchive</div>";
        draw($tool_content, 3);
        exit;
    }
    if (($data = get_serialized_file('course'))) {
        // 3.0-style backup
        $data = $data[0];
        if (isset($data['fake_code'])) {
            $data['public_code'] = $data['fake_code'];
        }
        $hierarchy = get_serialized_file('hierarchy');
        $course_units = get_serialized_file('course_units');
        $unit_resources = get_serialized_file('unit_resources');
        if (isset($data['description'])) {
            $description = $data['description'];
        } elseif (($unit_data = search_table_dump($course_units, 'order', -1))) {
            if (($resource_data = search_table_dump($unit_resources, 'order', -1))) {
                $description = purify($resource_data['comments']);
            }
        } else {
            $description = '';
        }
        $tool_content = course_details_form($data['public_code'], $data['title'], $data['prof_names'], $data['lang'], null, $data['visible'], $description, $hierarchy);
    } elseif (($data = get_serialized_file('cours'))) {
        // 2.x-style backup
        $data = $data[0];
        if (isset($data['fake_code'])) {
            $data['public_code'] = $data['fake_code'];
        }
        $faculte = get_serialized_file('faculte');
        $course_units = get_serialized_file('course_units');
        $unit_resources = get_serialized_file('unit_resources');
        $description = '';
        if (($unit_data = search_table_dump($course_units, 'order', -1))) {
            if (($resource_data = search_table_dump($unit_resources, 'order', -1))) {
                $description = purify($resource_data['comments']);
            }
        }
        $tool_content = course_details_form($data['public_code'], $data['intitule'], $data['titulaires'], $data['languageCourse'], $data['type'], $data['visible'], $description, $faculte);
    } else {
        // Old-style backup
        $data = parse_backup_php($base . '/backup.php');
        $tool_content = course_details_form($data['code'], $data['title'], $data['prof_names'], $data['lang'], $data['type'], $data['visible'], $data['description'], $data['faculty']);
    }
} else {

// -------------------------------------
// Display restore info form
// -------------------------------------
    enableCheckFileSize();
    $tool_content .= "<div class='alert alert-info'><label>$langFirstMethod</label> $langRequest1</div>
        <div class='form-wrapper'>
            <form role='form' class='form-horizontal' action='" . $_SERVER['SCRIPT_NAME'] . "' method='post' enctype='multipart/form-data'>            
            <div class='form-group'>
                <div class='col-sm-4'>" .
                    fileSizeHidenInput() . "
                    <input type='file' name='archiveZipped' />
                </div>
                <div class='col-sm-6'>
                    <input class='btn btn-primary' type='submit' name='send_archive' value='" . $langSend . "'>
                    <span class='help-block'><small>$langMaxFileSize " .ini_get('upload_max_filesize') . "</small></span>
                </div>
            </div>
            ". generate_csrf_token_form_field() ."  
            </form>
        </div> 
    <div class='alert alert-info'>
        <label>$langSecondMethod</label> $langRequest2</div>        
        <div class='form-wrapper'>
          <form role='form' class='form-inline' action='" . $_SERVER['SCRIPT_NAME'] . "' method='post'>
            <div class='form-group'>
                <input type='text' class='form-control' name='pathToArchive'>
            </div>
            <div class='form-group'>
                <input class='btn btn-primary' type='submit' name='send_path' value='" . $langSend . "'>
            </div>
          ". generate_csrf_token_form_field() ."  
          </form>
        </div>";
}
draw($tool_content, 3, null, $head_content);
