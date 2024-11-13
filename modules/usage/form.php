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

/**
 * @file form.php
 * @brief display form for creating graph statistics
 */
$require_login = true;

$statsIntervalOptions = '<option value="1" >' . q($langPerDay) . "</option>" .
        '<option value="7">' . q($langPerWeek) . "</option>" .
        '<option value="30" selected>' . q($langPerMonth) . "</option>" .
        '<option value="365">' . q($langPerYear) . "</option>";

if($stats_type == 'course'){
    if (isset($_GET['id'])) { // get specific user
        $u = Database::get()->querySingle("SELECT u.id, CONCAT(givenname,' ',surname,' (',username,')') name FROM course_user cu
                                                                JOIN user u ON cu.user_id=u.id
                                                            WHERE course_id=?d AND cu.user_id=?d", $course_id, $_GET['id']);
        $statsUserOptions = '<option value="'.$u->id.'" >' . q($u->name) . "</option>";
    } else { /**Get users of course**/
        $result = Database::get()->queryArray("SELECT u.id, CONCAT(givenname,' ',surname,' (',username,')') name FROM course_user cu
                                                                JOIN user u ON cu.user_id=u.id
                                                            WHERE course_id=?d
                                                            ORDER BY surname, givenname", $course_id);
        $statsUserOptions = '<option value="0" >' . $langUsers . "</option>";
        foreach($result as $u){
            $statsUserOptions .= '<option value="'.$u->id.'" >' . q($u->name) . "</option>";
        }
    }

    $mod_opts = '<option value="-1">' . $langAllModules . "</option>";
    $result = Database::get()->queryArray("SELECT module_id id FROM course_module WHERE visible = 1 AND course_id = ?d", $course_id);
    foreach($result as $m){
       $mod_opts .= '<option value="'.$m->id.'" >' . which_module($m->id) . "</option>";
    }
}
elseif($stats_type == 'admin'){
    /**Get course departments/categories**/
    $result = Database::get()->queryArray("SELECT chid id, chname name, IF(parent=-1,0,pars) depth  FROM "
            . "(SELECT chid, chname, chlft, COUNT(DISTINCT parid) pars, MAX(parid) parent FROM (SELECT IFNULL(h1.id,-1) parid, h1.name parname, h2.name chname, h2.id chid, h1.lft parlft, h2.lft chlft from hierarchy h1 right "
            . "  JOIN hierarchy h2 ON h1.lft<h2.lft and h1.rgt>=h2.rgt order by h1.id, h2.id) x "
            . "  group by chid) y "
            . "LEFT JOIN hierarchy h ON y.parent=h.id ORDER BY chlft");
    $statsDepOptions = "";
    foreach($result as $d){
       $indentation = "";
       for($i=0;$i<$d->depth;$i++){
           $indentation .= "&nbsp;&nbsp;";
       }
       $statsDepOptions .= '<option value="'.$d->id.'" >' . q($indentation.hierarchy::unserializeLangField($d->name)) . "</option>\n";
    }
}
else{
    /**Get courses of user**/
    if (isset($_GET['u'])) {
        $result = Database::get()->queryArray("SELECT c.id, c.code, c.title FROM course_user cu JOIN course c ON cu.course_id=c.id WHERE user_id=?d", $_GET['u']);
    } else {
        $result = Database::get()->queryArray("SELECT c.id, c.code, c.title FROM course_user cu JOIN course c ON cu.course_id=c.id WHERE user_id=?d", $uid);
    }
    $statsCourseOptions = '<option value="0" >' . $langAllCourses . "</option>\n";
    foreach($result as $c){
       $statsCourseOptions .= '<option value="'.$c->id.'" >' . q($c->title) . "</option>\n";
    }
}


$tool_content .= "<div class='col-12 mt-3'>";
$tool_content .= '<div class="form-wrapper form-edit p-0 border-0" data-placement="top">';
$tool_content .= '<div class="form-group" data-placement="top">';

$endDate_obj = new DateTime();
$enddate = $endDate_obj->format('d-m-Y');
$showUntil = q($enddate);
$startDate_obj = $endDate_obj->sub(new DateInterval('P6M'));
$startdate = $startDate_obj->format('d-m-Y');
$showFrom = q($startdate);

$tool_content .= "<div class='row'>
        <div class='col-md-6 col-12'>
            <label for='startdate' class='text-start control-label-notes'>$langFrom</label>
            <input class='form-control flex-fill' name='startdate' id='startdate' type='text' value = '$showFrom'>
        </div>";
$tool_content .= "
            <div class='col-md-6 col-12'>
                <label for='enddate' class='text-start control-label-notes mt-md-0 mt-3'>$langUntil</label>
                <input class='form-control' name='enddate' id='enddate' type='text' value = '$showUntil'>
            </div></div>";
$tool_content .= '<div class="row mt-3">
<div class="col-md-6 col-12"><select name="interval" id="interval" class="form-select" aria-label="'.$langType.'">' . $statsIntervalOptions . '</select></div>';

//$tool_content .= "<a id='toggle-view'><i class='fa fa-list' data-toggle='tooltip' data-placement='top' title data-original-title='lala'></i></a>";

if($stats_type == 'course'){

    $tool_content .= '
    <div class="col-md-6 col-12 mt-md-0 mt-3" style="display:none;"><select aria-label="'.$langUser.'" name="module" id="module" class="form-select">' . $mod_opts . '</select></div>';

    $tool_content .= '
    <div class="col-md-6 col-12 mt-md-0 mt-3"><select name="user" id="user" aria-label="'.$langUser.'" class="form-select">' . $statsUserOptions . '</select></div>';
}
elseif($stats_type == 'admin'){
    $tool_content .= '
    <div class="col-md-6 col-12 mt-md-0 mt-3"><select aria-label="'.$langDepartmentsList.'" name="department" id="department" class="form-select">' . $statsDepOptions . '</select></div>';
}
elseif($stats_type == 'user'){
    $tool_content .= '
    <div class="col-md-6 col-12 mt-md-0 mt-3"><select aria-label="'.$langCourse.'" name="course" id="course" class="form-select">' . $statsCourseOptions . '</select></div>';
}
$tool_content .= "</div>";
//<a id="list-view" class="btn btn-default"  data-placement="top" title="'.$langDetails.'" data-toggle="tooltip" data-original-title="'.$langDetails.'"><span class="fa fa-list"  data-toggle="tooltip" data-placement="top"></span></a>

$tool_content .= '<div class="float-end pt-4">
    <div id="toggle-view" class="btn-group gap-2">
        <a id="plots-view" class="btn submitAdminBtn submitAdminBtnClassic active rounded-2" data-bs-placement="top" title="'.$langPlots.'" data-bs-toggle="tooltip" data-bs-original-title="'.$langPlots.'" aria-label="'.$langPlots.'">
            <span class="fa fa-bar-chart" data-bs-toggle="tooltip" data-bs-placement="top"></span>
        </a>
        <a id="list-view" class="btn submitAdminBtn submitAdminBtnClassic rounded-2" data-bs-placement="top" title="'.$langDetails.'" data-bs-toggle="tooltip" data-bs-original-title="'.$langDetails.'" aria-label="'.$langDetails.'">
            <span class="fa fa-list" data-bs-toggle="tooltip" data-bs-placement="top"></span>
        </a>';
$tool_content .= ($stats_type == 'course')? '<a id="logs-view" class="btn submitAdminBtn submitAdminBtnClassic rounded-2"  data-bs-placement="top" title="'.$langUsersLog.'" data-bs-toggle="tooltip" data-bs-original-title="'.$langUsersLog.'" aria-label="'.$langUsersLog.'"><span class="fa fa-list-alt"  data-bs-toggle="tooltip" data-bs-placement="top"></span></a>':'';

$tool_content .= '</div>
</div>';

$tool_content .= '</div><div style="clear:both;"></div></div></div>';
