<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langWebConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('validation.js');
load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {                
        $('#select-courses').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-courses').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-courses').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-courses').val(stringVal).trigger('change');
        });
    });
</script>";

$available_themes = active_subdirs("$webDir/template", 'theme.html');

if (isset($_GET['delete_server'])) {
    $id = $_GET['delete_server'];
    Database::get()->querySingle("DELETE FROM tc_servers WHERE id=?d", $id);
    // Display result message   
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/webconf.php');
}
// Save new config.php
else if (isset($_POST['submit'])) {
    $hostname = $_POST['hostname_form'];    
    $enabled = $_POST['enabled'];
    $tc_courses = $_POST['tc_courses'];
    if (in_array(0, $tc_courses)) {
        $allcourses = 1; // tc server is assigned to all courses
    } else {
        $allcourses = 0; // tc server is assigned to specific courses
    }
    
    if (isset($_POST['id_form'])) {
        $id = $_POST['id_form'];
        Database::get()->querySingle("UPDATE tc_servers SET 
                                    hostname = ?s,
                                    enabled=?s,
                                    all_courses=?d
                            WHERE id =?d", $hostname, $enabled, $allcourses, $id);
        Database::get()->query("DELETE FROM course_external_server WHERE external_server = ?d", $id);
        if ($allcourses == 0) {        
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $id);
            }
        }
    } else {        
        $q = Database::get()->query("INSERT INTO tc_servers (`type`, hostname, enabled, max_rooms, max_users, weight, all_courses) 
                                        VALUES ('webconf', ?s, ?s, 0, 0, 1, ?d)", $hostname, $enabled, $allcourses);
        $tc_id = $q->lastInsertID;
        if ($allcourses == 0) {
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $tc_id);
            }
        }
    }
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/webconf.php');
} // end of if($submit)

if (isset($_GET['add_server']) || isset($_GET['edit_server'])) {
    $pageName = isset($_GET['add_server']) ? $langAddServer : $langEdit;
    $toolName = $langWebConf;
    $navigation[] = array('url' => 'webconf.php', 'name' => $langWebConf);
    $data['action_bar'] = action_bar([
            [
                'title' => $langBack,
                'url' => "webconf.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            ]
        ]);
    $data['enabled'] = true;    
    if (isset($_GET['add_server'])) {        
        $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course 
                                            WHERE id NOT IN (SELECT course_id FROM course_external_server) 
                                            AND visible != " . COURSE_INACTIVE . "
                                            ORDER BY title");        
        $data['listcourses'] = "<option value='0' selected><h2>$langToAllCourses</h2></option>";
        foreach ($courses_list as $c) {
            $data['listcourses'] .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
        }
    } else {           
        $data['wc_server'] = $_GET['edit_server'];
        $data['server'] = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $data['wc_server']);
        if ($data['server']->enabled == "false") {
            $data['enabled'] = false;
        }         
         $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                                        NOT IN (SELECT course_id FROM course_external_server) 
                                                        AND visible != " . COURSE_INACTIVE . "
                                                    ORDER BY title");
        $listcourses = '';
        if ($data['server']->all_courses == '1') {
            $listcourses .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
        } else {
            $tc_courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                        IN (SELECT course_id FROM course_external_server WHERE external_server = ?d) 
                                        ORDER BY title", $data['wc_server']);
            if (count($tc_courses_list) > 0) {
                foreach($tc_courses_list as $c) {
                    $listcourses .= "<option value='$c->id' selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
                }
                $listcourses .= "<option value='0'><h2>$langToAllCourses</h2></option>";
            }
        }
        foreach($courses_list as $c) {
            $listcourses .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
        }        
        $data['listcourses'] = $listcourses;                 
    }
    $view = 'admin.other.extapps.webconf.create';
    
} else {    // Display config.php edit form
    //display available WebConf servers
    $data['action_bar'] = action_bar(array(
        array('title' => $langAddServer,
            'url' => "webconf.php?add_server",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'),
        array('title' => $langBack,
            'url' => "extapp.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    $data['wc_servers'] = Database::get()->queryArray("SELECT * FROM tc_servers WHERE `type` = 'webconf'");
    $view = 'admin.other.extapps.webconf.index';
}

$data['menuTypeID'] = 3;
view($view, $data);

