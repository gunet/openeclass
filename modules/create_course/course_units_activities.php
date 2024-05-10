<?php
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'portfolio';
$helpSubTopic = 'create_course';

if (isset($_GET['edit_act'])) {
    $require_current_course = true;
}
require_once '../../include/baseTheme.php';

if ($session->status !== USER_TEACHER && !$is_departmentmanage_user) { // if we are not teachers or department managers
    redirect_to_home_page();
}

require_once 'include/log.class.php';
require_once 'include/lib/course.class.php';
require_once 'functions.php';

$course = new Course();

$tmp_pageName = $langCourseEdit;

load_js('tools.js');

$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */


function checkedBoxes() {

    var checkboxes_in_class = document.getElementsByName('in_class');
    var checkboxes_after_class = document.getElementsByName('after_class');
    var checkboxes_in_home = document.getElementsByName('in_home');
    var checked_in_class = [];
    var checked_after_class = [];
    var checked_in_home = [];
    widnow.alert();
    for(let i=0; i<parseInt(checkboxes_in_class.length); i++) {
        widnow.alert();
        if(checkboxes_in_class[i].checked){
            checked_in_class.push(checkboxes[i].attr('id'));
        }
    }

    for(let j=1; j<parseInt(checkboxes_in_home.length); j++) {
        if(checkboxes_in_home[j].checked){
            checked_in_home.push(checkboxes[j].attr('id'));
        }
    }

    for(let k=1; k<parseInt(checkboxes_after_class.length); k++) {
        if(checkboxes_after_class[k].checked){
            checked_after_class.push(checkboxes[k].attr('id'));
        }
    }

    document.getElementsByName('checked_in_class').value=checked_in_class;
    document.getElementsByName('checked_in_home').value=checked_in_home;
    document.getElementsByName('checked_after_class').value=checked_after_class;

}

/* ]]> */
</script>
hContent;

if (isset($_GET['edit_act'])) {
    $unit_id = $_GET['edit_act'];
    $course_code = $_GET['course'];
}

register_posted_variables(array('title' => true, 'password' => true, 'prof_names' => true));
if (empty($prof_names)) {
    $prof_names = "$_SESSION[givenname] $_SESSION[surname]";
}

$ids_list = array();

if (!isset($_POST['final_submit'])) {

    if (!isset($_GET['edit_act'])) { //show activities selection when creation

        $toolName = $langCourseCreate;

            $validationFailed = false;

            if (empty($_SESSION['goals']) or $_SESSION['goals'][0] == "") {
                Session::Messages($langEmptyGoal);
                $validationFailed = true;
            }

            if (empty($_SESSION['units']) or $_SESSION['goals'][0] == "") {
                Session::Messages($langEmptyUnit);
                $validationFailed = true;
            }


            if (empty($_SESSION['stunum'])||empty($_SESSION['lectnum'])||empty($_SESSION['lecthours'])||empty($_SESSION['homehours'])) {
                Session::Messages($langFieldsMissing);
                $validationFailed = true;

            }


            if ($validationFailed) {
                redirect_to_home_page('modules/create_course/flipped_classroom.php');
            }

            $mtitles_in_home = $mtitles_in_class = $mtitles_after_class = $mids_in_home = $mids_in_class = $mids_after_class = array();

            $maxUnitId = Database::get()->querySingle("SELECT MAX(id) as muid FROM course_units");

            $act_list_in_home = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_IN_HOME."'");

            $act_list_after_class = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_AFTER_CLASS."'");

            $act_list_in_class = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_IN_CLASS."'");

            $tc_disabled = (count(get_enabled_tc_services()) == 0);
            foreach ($act_list_in_home as $item_in_home) {
                if ($item_in_home->activity_ID == MODULE_ID_TC and $tc_disabled) { // hide teleconference when no tc servers are enabled
                    continue;
                }

                $mid = getIndirectReference($item_in_home->activity_ID);
                $mtitle = q($activities[$item_in_home->activity_ID]['title']);

                $mtitles_in_home[$item_in_home->activity_ID] =$mtitle;
            }

            foreach ($act_list_after_class as $item_after_class) {
                if ($item_after_class->activity_ID == MODULE_ID_TC and $tc_disabled) { // hide teleconference when no tc servers are enabled
                    continue;
                }
                $mid = getIndirectReference($item_after_class->activity_ID);
                $mtitle = q($activities[$item_after_class->activity_ID]['title']);

                $mtitles_after_class[$item_after_class->activity_ID]=$mtitle;

            }

            foreach ($act_list_in_class as $item_in_class) {
                if ($item_in_class->activity_ID == MODULE_ID_TC and $tc_disabled) { // hide teleconference when no tc servers are enabled
                    continue;
                }
                $mid = getIndirectReference($item_in_class->activity_ID);
                $mtitle = q($activities[$item_in_class->activity_ID]['title']);

                $mtitles_in_class[$item_in_class->activity_ID]=$mtitle;

            }

            $tool_content .= action_bar(array(
                array(
                    'title' => $langBack,
                    'url' => $urlServer.'modules/create_course/flipped_classroom.php',
                    'icon' => 'fa-reply',
                    'level' => 'primary',
                    'button-class' => 'btn-default'
                )
            ), false);


            $tool_content .= "
            <div class='row m-auto'>
            <div class='col-lg-6 col-12 px-0'>
                <div class='form-wrapper '>
                    <form id='activities' class='form-horizontal' role='form' method='post' name='createform' action='$_SERVER[SCRIPT_NAME]'>
                    <div class='card cardPanel border-0'>
                        <div class='card-header px-0 border-0'>
                            $langActSelect
                        </div>
                    </div>


                    <fieldset>
                        <div class='table-responsive'>
                            <table class='table table-default'>
                            <thead>
                            <tr class='list-header'><td></td>
                            <th class='px-0' scope='col'><label for='title' class='col-sm-2 '>$langActivities</th>
                ";
                $i=1;
                foreach ($_SESSION['units'] as $utitle) {
                    $tool_content .= "<th class='px-0' scope='col'><label for='title' class='col-md-10 ' title='$utitle'>".$i.' '.ellipsize($utitle,20).":</label></th>";
                    $i++;
                }


                $tool_content .= "
                                </tr></thead>
                                <tr>
                                    <th scope='row'>$langActInHome:</th>";

                $end=end($mtitles_in_home);

                foreach($mtitles_in_home as $title_home) {
                    $j=1;
                    $tool_content .= "<td>$title_home</td>";
                    $newUnitId =$maxUnitId->muid +1;
                    foreach ($_SESSION['units'] as $utitle) {
                        $tool_content .= "<td><label class='label-container'><input type='checkbox' name='in_home[]' id='".$j."_".$newUnitId."_".array_search($title_home,$mtitles_in_home)."' value='".$j."_".$newUnitId."_".array_search($title_home,$mtitles_in_home)."'></input><span class='checkmark'></span></label></td>";
                        $newUnitId ++;
                        $j++;

                    }
                    if ($title_home == $end) {
                        $tool_content .= "</tr><tr><td style='background-color:#E8EDF8;'></td>";
                    } else {
                        $tool_content .= "</tr><tr><td></td>";
                    }

                }

                $tool_content .="<td style='background-color:#E8EDF8;'></td>";

                foreach ($_SESSION['units'] as $utitle) {
                    $tool_content .="<td style='background-color:#E8EDF8;'></td>";
                }

                $tool_content .= "
                    </tr>
                    <tr>
                        <th scope='row'>$langActInClass:</th>";

                $end=end($mtitles_in_class);
                foreach($mtitles_in_class as $title_class) {
                    $k=1;
                    $tool_content .= "<td>$title_class</td>";
                    $newUnitId =$maxUnitId->muid +1;
                    foreach ($_SESSION['units'] as $utitle) {
                        $tool_content .= "
                            <td><label class='label-container'><input type='checkbox' name='in_class[]' id='".$k."_".$newUnitId."_".array_search($title_class,$mtitles_in_class)."' value='".$k."_".$newUnitId."_".array_search($title_class,$mtitles_in_class)."'></input><span class='checkmark'></span></label></td>";
                        $newUnitId ++;
                        $k++;
                    }

                    if ($title_class == $end) {
                        $tool_content .= "</tr><tr><td style='background-color:#E8EDF8;'></td>";
                    }else{
                        $tool_content .= "</tr><tr><td></td>";
                    }
                }

                $tool_content .="<td style='background-color:#E8EDF8;'></td>";

                foreach ($_SESSION['units'] as $utitle) {
                    $tool_content .="<td style='background-color:#E8EDF8;'></td>";
                }


                $tool_content .= "</tr>
                <tr>
                    <th scope='row'>$langActAfterClass:</th>";

                $end=end($mtitles_after_class);
                foreach($mtitles_after_class as $title_after_class) {
                    $z=1;
                    $tool_content .= "<td>$title_after_class</td>";
                    $newUnitId =$maxUnitId->muid +1;
                    foreach($_SESSION['units'] as $utitle) {

                        $tool_content .= "
                            <td><label class='label-container'><input type='checkbox' name='after_class[]' id='".$z."_".$newUnitId."_".array_search($title_after_class,$mtitles_after_class)."' value='".$z."_".$newUnitId."_".array_search($title_after_class,$mtitles_after_class)."'></input><span class='checkmark'></span></label></td>";
                        $newUnitId++;
                        $z++;
                    }
                    if ($title_after_class == $end){
                        $tool_content .= "</tr><tr><td style='background-color:#E8EDF8;'></td>";
                    } else {
                        $tool_content .= "</tr><tr><td></td>";
                    }

                }

                $tool_content .="<td style='background-color:#E8EDF8;'></td>";

                foreach ($_SESSION['units'] as $utitle) {
                    $tool_content .="<td style='background-color:#E8EDF8;'></td>";
                }

                $tool_content .= "</tr>
                        </table>
                    </div>
                    <div class='form-group mt-4'>
                        <div class='col-12 d-flex justify-content-end'>
                            <input id='final_sub' class='btn submitAdminBtn me-2' type='submit' name='final_submit' value='" . q($langFinalSubmit) . "' onClick=\"check()\">
                            <a class='btn cancelAdminBtn' href='{$urlServer}main/portfolio.php' class='btn btn-default'>$langCancel</a>
                        </div>
                    </div>
                    <input type='hidden' name='next'>
                    <input name='checked_in_class' type='hidden' value='1'></input>
                    <input name='checked_in_home' type='hidden' value='2'></input>
                    <input name='checked_after_class' type='hidden' value='3'></input>

                </fieldset>". generate_csrf_token_form_field() ."
            </form>
        </div>
        </div>
        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
        </div>
        ";

    } else { //show activities selection when it is edit
        $toolName = $langCourseEdit;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "{$urlServer}modules/units/?course=$course_code&id=$unit_id",
                'icon' => 'fa-reply',
                'level' => 'primary')),false);


        $unit_title = Database::get()->querySingle("SELECT title FROM course_units WHERE id =?d",$unit_id);
        $_SESSION['title'] = $unit_title->title;
        $_SESSION['edit_act'] = $_GET['edit_act'];
        $mtitles_in_home = $mtitles_in_class = $mtitles_after_class = $mids_in_home = $mids_in_class = $mids_after_class = array();


        $act_list_in_home = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_IN_HOME."'");

        $act_list_after_class = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_AFTER_CLASS."'");

        $act_list_in_class = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_IN_CLASS."'");


        foreach ($act_list_in_home as $item_in_home) {
            if ($item_in_home->activity_ID == MODULE_ID_TC and count(is_configured_tc_server()) == 0) { // hide teleconference when no tc servers are enabled
                continue;
            }

            $mid = getIndirectReference($item_in_home->activity_ID);
            $mtitle = q($activities[$item_in_home->activity_ID]['title']);

            $mtitles_in_home[$item_in_home->activity_ID] =$mtitle;
        }

        foreach ($act_list_after_class as $item_after_class) {
            if ($item_after_class->activity_ID == MODULE_ID_TC and count(is_configured_tc_server()) == 0) { // hide teleconference when no tc servers are enabled
                continue;
            }
            $mid = getIndirectReference($item_after_class->activity_ID);
            $mtitle = q($activities[$item_after_class->activity_ID]['title']);

            $mtitles_after_class[$item_after_class->activity_ID]=$mtitle;

        }

        foreach ($act_list_in_class as $item_in_class) {
            if ($item_in_class->activity_ID == MODULE_ID_TC and count(is_configured_tc_server()) == 0) { // hide teleconference when no tc servers are enabled
                continue;
            }
            $mid = getIndirectReference($item_in_class->activity_ID);
            $mtitle = q($activities[$item_in_class->activity_ID]['title']);

            $mtitles_in_class[$item_in_class->activity_ID]=$mtitle;

        }

        $tool_content .= "<div class='form-wrapper'><fieldset>
            <form class='form-horizontal' role='form' method='post' name='createform' action='$_SERVER[SCRIPT_NAME]?course=$course_code&edit_act=$unit_id' onsubmit=\"return validateNodePickerForm();\">
                 
                <h4>$langActSelect</h4>
                        
                <fieldset>
                    <div class='table-responsive mt-0'>
                    <table class='table table-default'>
                        <thead><tr class='list-header'>
                        <td class='px-0'></td>
                        <th class='px-0' scope='col'><label for='title' class='col-sm-2 '>$langActivities</th>";
            $i=1;

            $tool_content .= "<th class='px-0' scope='col'> <label for='title' class='col-md-10' title='$unit_title->title'>".ellipsize($unit_title->title,20).":</label></th>";
            $tool_content .= "
                            </tr></thead>
                            <tr>
                                <th scope='row'>$langActInHome:</th>";

            $end=end($mtitles_in_home);
            foreach($mtitles_in_home as $title_home) {

                $act_id = array_search($title_home,$mtitles_in_home);

                $q =Database::get()->querySingle("SELECT id FROM course_units_activities WHERE unit_id =?d and activity_id=?s and course_code=?s",$unit_id,$act_id,$course_code);

                $tool_content .= "<td>$title_home</td>";

                if ($q) {
                    $tool_content .= "
                        <td><label class='label-container'><input type='checkbox' name='in_home[]' id='".$unit_id."_". $act_id."' value='".$unit_id."_".$act_id."' checked><span class='checkmark'></span></label></td>";
                } else {
                    $tool_content .= "
                        <td><label class='label-container'><input type='checkbox' name='in_home[]' id='".$unit_id."_". $act_id."' value='".$unit_id."_".$act_id."'><span class='checkmark'></span></label></td>";
                }

                if ($title_home == $end){
                    $tool_content .= "</tr><tr><td style='background-color:transparent;'></td>";
                } else {
                    $tool_content .= "</tr><tr><td></td>";
                }
            }
            $tool_content .="<td style='background-color:transparent;'></td>";
            $tool_content .="<td style='background-color:transparent;'></td>";
            $tool_content .= "
                </tr>
                <tr>
                    <th scope='row'>$langActInClass:</th>";

            $end=end($mtitles_in_class);
            foreach($mtitles_in_class as $title_class) {
                $act_id = array_search($title_class,$mtitles_in_class);
                $q =Database::get()->querySingle("SELECT id FROM course_units_activities WHERE unit_id =?d and activity_id=?s and course_code=?s",$unit_id,$act_id,$course_code);

                $tool_content .= "<td>$title_class</td>";

                if($q) {
                    $tool_content .= "
                        <td><label class='label-container'><input type='checkbox' name='in_class[]' id='".$unit_id."_". $act_id."' value='".$unit_id."_".$act_id."' checked></input><span class='checkmark'></span></label></td>";
                } else {
                    $tool_content .= "<td><label class='label-container'><input type='checkbox' name='in_class[]' id='".$unit_id."_". $act_id."' value='".$unit_id."_".$act_id."'></input><span class='checkmark'></span></label></td>";
                }

                if ($title_class == $end) {
                    $tool_content .= "</tr><tr><td style='background-color:transparent;'></td>";
                } else {
                    $tool_content .= "</tr><tr><td></td>";
                }
            }

            $tool_content .="<td style='background-color:transparent;'></td>";
            $tool_content .="<td style='background-color:transparent;'></td>";
            $tool_content .= "
            </tr>
            <tr>
                <th scope='row'>$langActAfterClass:</th>";

            $end=end($mtitles_after_class);
            foreach($mtitles_after_class as $title_after_class) {

                $act_id =array_search($title_after_class,$mtitles_after_class);

                $q =Database::get()->querySingle("SELECT id FROM course_units_activities WHERE unit_id =?d and activity_id=?s and course_code=?s",$unit_id,$act_id,$course_code);

                $tool_content .= "<td>$title_after_class</td>";

                if($q) {
                    $tool_content .= "
                        <td><label class='label-container'><input type='checkbox' name='after_class[]' id='".$unit_id."_". $act_id."' value='".$unit_id."_".$act_id."' checked></input><span class='checkmark'></span></label></td>";
                } else {
                    $tool_content .= "
                        <td><label class='label-container'><input type='checkbox' name='after_class[]' id='".$unit_id."_". $act_id."' value='".$unit_id."_".$act_id."'></input><span class='checkmark'></span></label></td>";
                }

                if ($title_after_class == $end) {
                    $tool_content .= "</tr><tr><td></td>";
                } else {
                    $tool_content .= "</tr><tr><td></td>";
                }

            }

            $tool_content .="<td></td>";
            $tool_content .="<td></td>";

            $tool_content .= "</tr>
                    </table>
                </div>
                <div class='form-group mt-5'>
                    <div class='col-12 d-flex justify-content-end align-items-center'>
                        <a href='{$urlServer}modules/units/?course=".$course_code."&id=".$unit_id."' class='btn cancelAdminBtn me-2'>$langCancel</a>
                        <input id='final_sub' class='btn submitAdminBtn' type='submit' name='final_submit' value='" . q($langSubmit) . "' >
                    </div>
                </div>
                <input type='hidden' name='next'>
                <input name='checked_in_class' type='hidden' value='1'></input>
                <input name='checked_in_home' type='hidden' value='2'></input>
                <input name='checked_after_class' type='hidden' value='3'></input>

                </form></fieldset>". generate_csrf_token_form_field() ."
            </div>";
    }

} else {   //complete actions

    if (!isset($_GET['edit_act'])){ //complete actions if it is creation
        $language = $_SESSION['language'];
            $units = $_SESSION['units'];
            $goals =$_SESSION['goals'];

            $commentsGoals = "";

            $stunum = $_SESSION['stunum'];
            $lectnum = $_SESSION['lectnum'];
            $lecthours = $_SESSION['lecthours'];
            $homehours = $_SESSION['homehours'];
            $totalhours = $_SESSION['totalhours'];

            $validationFailed = false;
            $validationFailed_activities = false;

            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

            if (count($_SESSION['code']) < 1 || empty($_SESSION['code'][0])) {
                Session::Messages($langEmptyAddNode);
                $validationFailed = true;
            }

            if (empty($_SESSION['title'])) {
                Session::Messages($langFieldsMissing);
                $validationFailed = true;
            }

            if ($validationFailed_activities) {
                redirect_to_home_page('modules/create_course/course_units_activities.php');
            }

            // create new course code: uppercase, no spaces allowed
            $code = strtoupper(new_code($_SESSION['code'][0]));
            $code = str_replace(' ', '', $code);

            // include_messages
            include "lang/$language/common.inc.php";
            $extra_messages = "config/{$language_codes[$language]}.inc.php";
            if (file_exists($extra_messages)) {
                include $extra_messages;
            } else {
                $extra_messages = false;
            }
            include "lang/$language/messages.inc.php";
            if (file_exists('config/config.php')) {
                if(get_config('show_always_collaboration') and get_config('show_collaboration')){
                  include "lang/$language/messages_collaboration.inc.php";
                }elseif(!get_config('show_always_collaboration') and get_config('show_collaboration')){
                    include "lang/$language/messages_eclass_collaboration.inc.php";
                }
            }
            if ($extra_messages) {
                include $extra_messages;
            }

            // create course directories
            if (!create_course_dirs($code)) {
                Session::Messages($langGeneralError, 'alert-danger');
                redirect_to_home_page('modules/create_course/create_course.php');
            }

            // get default quota values
            $doc_quota = get_config('doc_quota');
            $group_quota = get_config('group_quota');
            $video_quota = get_config('video_quota');
            $dropbox_quota = get_config('dropbox_quota');

            // get course_license
            if (isset($_SESSION['l_radio'])) {
                $l = $_SESSION['l_radio'];
                switch ($l) {
                    case 'cc':
                        if (isset($_SESSION['cc_use'])) {
                            $course_license = intval($_SESSION['cc_use']);
                        }
                        break;
                    case '10':
                        $course_license = 10;
                        break;
                    default:
                        $course_license = 0;
                        break;
                }
            }

            if (empty($_SESSION['public_code'])) {
                $public_code = $code;
            } else {
                $public_code = mb_substr($_SESSION['public_code'], 0, 20);
            }


            $description = purify( $_SESSION['content']);

            $result = Database::get()->query(
                "INSERT INTO course SET
                                code = ?s,
                                lang = ?s,
                                title = ?s,
                                visible = ?d,
                                course_license = ?d,
                                prof_names = ?s,
                                public_code = ?s,
                                doc_quota = ?f,
                                video_quota = ?f,
                                group_quota = ?f,
                                dropbox_quota = ?f,
                                password = ?s,
                                flipped_flag = 2,
                                lectures_model = ?d,
                                view_type = 'units',
                                start_date = " . DBHelper::timeAfter() . ",
                                keywords = '',
                                created = " . DBHelper::timeAfter() . ",
                                glossary_expand = 0,
                                glossary_index = 1,
                                description = ?s",
                        $code,
                        $language,
                        $_SESSION['title'],
                        $_SESSION['formvisible'],
                        $course_license,
                        $prof_names,
                        $public_code,
                        $doc_quota * 1024 * 1024,
                        $video_quota * 1024 * 1024,
                        $group_quota * 1024 * 1024,
                        $dropbox_quota * 1024 * 1024,
                        $_SESSION['password'],
                        $_SESSION['lectures_model'],
                        $description);

            $new_course_id = $result->lastInsertID;
            if (!$new_course_id) {
                Session::Messages($langGeneralError);
                redirect_to_home_page('modules/create_course/create_course.php');
            }

            //create course modules
            create_modules($new_course_id);

            Database::get()->query(
                "INSERT INTO course_user SET
                                    course_id = ?d,
                                    user_id = ?d,
                                    status = " . USER_TEACHER . ",
                                    tutor = 1,
                                    reg_date = " . DBHelper::timeAfter() . ",
                                    document_timestamp = " . DBHelper::timeAfter() . "",
                                $new_course_id,
                                $uid);

            $maxOrderUnit = Database::get()->querySingle("SELECT MAX(`order`) as morder FROM course_units WHERE course_id=?d",$new_course_id);

            if ($maxOrderUnit->morder ==NULL){
                $maxOrderUnit->morder = 1;
            }


            foreach ($_SESSION['units'] as $unit){
                $maxOrderUnit->morder += 1;
                Database::get()->query(
                    "INSERT INTO course_units SET
                                                title = ?s,
                                                visible = 1,
                                                public = 1,
                                                `order` =".$maxOrderUnit->morder.",
                                                course_id = ?d",
                    $unit,
                    $new_course_id
                );
            }

            $maxOrderGoal = Database::get()->querySingle("SELECT MAX(`order`) as morder FROM course_description WHERE course_id=?d",$new_course_id);

            if(empty($maxOrderGoal->morder)) {
                $maxOrderGoal->morder = 1;
            }

            $commentsGoals .= "<ul>";
            foreach ($_SESSION['goals'] as $goal){
                $commentsGoals .= "<li>".$goal."</li>";
                Database::get()->query("INSERT INTO course_learning_objectives SET
                        course_code = ?s,
                        title = ?s", $code, $goal
                );
            }
            $commentsGoals .= "</ul>";

            $commentLectModel ="<p>";

            if($_SESSION['lectures_model']==1){
                $commentLectModel .="$langLectMixed";

            }else if($_SESSION['lectures_model']==2){
                $commentLectModel .="$langLectFromHome";
            }

            $commentsClassInfo ="<ul>
                                    <li>$langStuNum: $stunum </li>
                                    <li>$langLectNum: $lectnum </li>
                                    <li>$langLectHours: $lecthours </li>
                                    <li>$langHomeHours: $homehours </li>
                                    <li>$langTotalHours: $totalhours</li>
                                </ul>";

            Database::get()->query("INSERT INTO course_description SET
                        course_id = ?d,
                        title = ?s,
                        comments = ?s,
                        type = ?d,
                        `order` = ?d,
                        `visible` =?d,
                        update_dt = NOW()", $new_course_id, $langGoals, purify($commentsGoals), 2, $maxOrderGoal->morder, 1
            );

            Database::get()->query("INSERT INTO course_description SET
                        course_id = ?d,
                        title = ?s,
                        comments = ?s,
                        type = ?d,
                        `order` = ?d,
                        `visible` =?d,
                        update_dt = NOW()", $new_course_id, $langLectModel, purify($commentLectModel), 10, $maxOrderGoal->morder, 1
            );

            Database::get()->query("INSERT INTO course_description SET
                        course_id = ?d,
                        title = ?s,
                        comments = ?s,
                        type = ?d,
                        `order` = ?d,
                        `visible` =?d,
                        update_dt = NOW()", $new_course_id, $langClassInfoTitle, purify($commentsClassInfo), 10, $maxOrderGoal->morder, 1
            );

            Database::get()->query("INSERT INTO course_class_info SET
                        student_number = ?s,
                        lessons_number = ?d,
                        lesson_hours = ?d,
                        home_hours = ?d,
                        total_hours = ?d,
                        course_code =?s", $stunum, $lectnum, $lecthours, $homehours, $totalhours, $code
            );

            $nrlz_tools_in_class ="";
            $nrlz_tools_in_home ="";
            $nrlz_tools_after_class = "";

            if(isset($_POST['in_class'])){
                foreach($_POST['in_class'] as $in_class){
                    $nrlz_in_class = explode("_",$in_class);

                    $activity_id = $nrlz_in_class[2];
                    $unit_id = $nrlz_in_class[1];

                    $tool_ids = $activities[$activity_id]['tools'];

                    foreach ($tool_ids as $ids){
                        $nrlz_tools_in_class .=$ids." ";
                    }


                    Database::get()->query(
                        "INSERT INTO course_units_activities SET
                                                    course_code = ?s,
                                                    activity_id = ?s,
                                                    unit_id = ?d,
                                                    tool_ids = ?s,
                                                    activity_type=?d,
                                                    `visible` =?d",
                        $code,
                        $activity_id,
                        $unit_id,
                        $nrlz_tools_in_class,
                        0,
                        1
                    );

                    $nrlz_tools_in_class ="";

                }
            }

            if(isset($_POST['in_home'])){
                foreach($_POST['in_home'] as $in_home){
                    $nrlz_in_home = explode("_",$in_home);

                    $activity_id = $nrlz_in_home[2];
                    $unit_id = $nrlz_in_home[1];

                    $tool_ids = $activities[$activity_id]['tools'];

                    foreach ($tool_ids as $ids){
                        $nrlz_tools_in_home .=$ids." ";
                    }


                    Database::get()->query(
                        "INSERT INTO course_units_activities SET
                                                    course_code = ?s,
                                                    activity_id = ?s,
                                                    unit_id = ?d,
                                                    tool_ids = ?s,
                                                    activity_type=?d,
                                                    `visible` =?d",
                        $code,
                        $activity_id,
                        $unit_id,
                        $nrlz_tools_in_home,
                        1,
                        1
                    );
                    $nrlz_tools_in_home ="";
                }
            }
            if(isset($_POST['after_class'])){
                foreach($_POST['after_class'] as $after_class){
                    $nrlz_after_class = explode("_",$after_class);

                    $activity_id = $nrlz_after_class[2];
                    $unit_id = $nrlz_after_class[1];

                    $tool_ids = $activities[$activity_id]['tools'];

                    foreach ($tool_ids as $ids){
                        $nrlz_tools_after_class .=$ids." ";
                    }


                    Database::get()->query(
                        "INSERT INTO course_units_activities SET
                                                    course_code = ?s,
                                                    activity_id = ?s,
                                                    unit_id = ?d,
                                                    tool_ids = ?s,
                                                    activity_type=?d,
                                                    `visible` =?d",
                        $code,
                        $activity_id,
                        $unit_id,
                        $nrlz_tools_after_class,
                        2,
                        1
                    );
                    $nrlz_tools_after_class ="";
                }
            }

            $course->refresh($new_course_id, $_SESSION['code']);

            // create courses/<CODE>/index.php
            course_index($code);

            // add a default forum category
            Database::get()->query("INSERT INTO forum_category
                                    SET cat_title = ?s,
                                    course_id = ?d", $langForumDefaultCat, $new_course_id);

            $_SESSION['courses'][$code] = USER_TEACHER;

            $tool_content .= "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span><b>$langJustCreated:</b> " . q($_SESSION['title']) . "<br>
                                <span class='smaller'>$langEnterMetadata</span></span></div>";
            $tool_content .= action_bar(array(
                array(
                    'title' => $langEnter,
                    'url' => $urlAppend . "courses/$code/",
                    'icon' => 'fa-arrow-right',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'
                )
            ));

            // logging
            Log::record(0, 0, LOG_CREATE_COURSE, array(
                'id' => $new_course_id,
                'code' => $code,
                'title' => $_SESSION['title'],
                'language' => $language,
                'visible' => $_SESSION['formvisible']
            ));

    } else {      //complete actions if it is edit course activities
        $validationFailed = false;
        if ($validationFailed) {
            redirect_to_home_page('modules/create_course/course_units_activities.php?course='.$course_code.'&edit_act='.$unit_id);
        }

        $result = Database::get()->query(
            "DELETE FROM course_units_activities WHERE course_code=?s and unit_id=?d",
            $course_code,
            $unit_id
        );

        $nrlz_tools_in_class ="";
        $nrlz_tools_in_home ="";
        $nrlz_tools_after_class = "";


        if(isset($_POST['in_class'])){
            foreach($_POST['in_class'] as $in_class){
                $nrlz_in_class = explode("_",$in_class);

                $activity_id = $nrlz_in_class[1];
                $unit_id = $nrlz_in_class[0];

                $tool_ids = $activities[$activity_id]['tools'];

                foreach ($tool_ids as $ids){
                    $nrlz_tools_in_class .=$ids." ";
                }

                Database::get()->query(
                    "INSERT INTO course_units_activities SET
                                                course_code = ?s,
                                                activity_id = ?s,
                                                unit_id = ?d,
                                                tool_ids = ?s,
                                                activity_type=?d,
                                                `visible` =?d",
                    $course_code,
                    $activity_id,
                    $unit_id,
                    $nrlz_tools_in_class,
                    0,
                    1
                );
                $nrlz_tools_in_class ="";
            }
        }

        if(isset($_POST['in_home'])){
            foreach($_POST['in_home'] as $in_home){
                $nrlz_in_home = explode("_",$in_home);

                $activity_id = $nrlz_in_home[1];
                $unit_id = $nrlz_in_home[0];

                $tool_ids = $activities[$activity_id]['tools'];

                foreach ($tool_ids as $ids){
                    $nrlz_tools_in_home .=$ids." ";
                }


                Database::get()->query(
                    "INSERT INTO course_units_activities SET
                                                course_code = ?s,
                                                activity_id = ?s,
                                                unit_id = ?d,
                                                tool_ids = ?s,
                                                activity_type=?d,
                                                `visible` =?d",
                    $course_code,
                    $activity_id,
                    $unit_id,
                    $nrlz_tools_in_home,
                    1,
                    1
                );
                $nrlz_tools_in_home ="";
            }
        }

        if(isset($_POST['after_class'])){
            foreach($_POST['after_class'] as $after_class){
                $nrlz_after_class = explode("_",$after_class);

                $activity_id = $nrlz_after_class[1];
                $unit_id = $nrlz_after_class[0];

                $tool_ids = $activities[$activity_id]['tools'];

                foreach ($tool_ids as $ids){
                    $nrlz_tools_after_class .=$ids." ";
                }


                Database::get()->query(
                    "INSERT INTO course_units_activities SET
                                                course_code = ?s,
                                                activity_id = ?s,
                                                unit_id = ?d,
                                                tool_ids = ?s,
                                                activity_type=?d,
                                                `visible` =?d",
                    $course_code,
                    $activity_id,
                    $unit_id,
                    $nrlz_tools_after_class,
                    2,
                    1
                );
                $nrlz_tools_after_class ="";
            }
        }

        $tool_content .= "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span><b>$langUnitJustEdited:</b> " . q($_SESSION['title']) . "<br></span></div>";
        $tool_content .= action_bar(array(
            array(
                'title' => $langEnter,
                'url' => $urlAppend . "modules/units/index.php?course=$course_code&id=$unit_id",
                'icon' => 'fa-arrow-right',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            )
        ));
    }
}

if (isset($_GET['edit_act'])) {
    draw($tool_content, 2, null, $head_content);
} else {
    draw($tool_content, 1, null, $head_content);
}
