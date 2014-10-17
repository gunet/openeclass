<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'CreateCourse';

require_once '../../include/baseTheme.php';

if ($session->status !== USER_TEACHER) { // if we are not teachers
    redirect_to_home_page();
}

require_once 'include/log.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'functions.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

$nameTools = $langCourseCreate;

load_js('jquery-ui');
load_js('jstree');
load_js('pwstrength.js');

//Datepicker
load_js('tools.js');
load_js('jquery-ui-timepicker-addon.min.js');

$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

function deactivate_input_password () {
        $('#coursepassword').attr('disabled', 'disabled');
        $('#coursepassword').addClass('invisible');
}

function activate_input_password () {
        $('#coursepassword').removeAttr('disabled', 'disabled');
        $('#coursepassword').removeClass('invisible');
}

function displayCoursePassword() {

        if ($('#courseclose,#courseiactive').is(":checked")) {
                deactivate_input_password ();
        } else {
                activate_input_password ();
        }
}

function checkrequired(which, entry, entry2) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if ((tempobj.name == entry) || (tempobj.name == entry2)) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langFieldsMissing");
		return false;
	} else {
		return true;
	}
}

    var lang = {
hContent;
$head_content .= "pwStrengthTooShort: '" . js_escape($langPwStrengthTooShort) . "', ";
$head_content .= "pwStrengthWeak: '" . js_escape($langPwStrengthWeak) . "', ";
$head_content .= "pwStrengthGood: '" . js_escape($langPwStrengthGood) . "', ";
$head_content .= "pwStrengthStrong: '" . js_escape($langPwStrengthStrong) . "'";
$head_content .= <<<hContent
    };

    function showCCFields() {
        $('#cc').show();
    }
    function hideCCFields() {
        $('#cc').hide();
    }

    $(document).ready(function() {

        $('input[name=start_date]').datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function (date) {
                var date2 = $('input[name=start_date]').datepicker('getDate');
                if($('input[name=start_date]').datepicker('getDate')>$('input[name=finish_date]').datepicker('getDate')){
                    date2.setDate(date2.getDate() + 7);
                    $('input[name=finish_date]').datepicker('setDate', date2);
                    $('input[name=finish_date]').datepicker('option', 'minDate', date2);
                }else{
                    $('input[name=finish_date]').datepicker('option', 'minDate', date2);
                }
            }
        });

        $('input[name=finish_date]').datepicker({
            dateFormat: 'yy-mm-dd',
            onClose: function () {
                var dt1 = $('input[name=start_date]').datepicker('getDate');
                var dt2 = $('input[name=finish_date]').datepicker('getDate');
                if (dt2 <= dt1) {
                    var minDate = $('input[name=finish_date]').datepicker('option', 'minDate');
                    $('input[name=finish_date]').datepicker('setDate', minDate);
            }
        }
        });
        
        if($('input[name=start_date]').datepicker("getDate") === null){
            $('input[name=start_date]').datepicker("setDate", new Date());
            var date2 = $('input[name=start_date]').datepicker('getDate');
            date2.setDate(date2.getDate() + 7);
            $('input[name=finish_date]').datepicker('setDate', date2);
            $('input[name=finish_date]').datepicker('option', 'minDate', date2);
        }else{
            var date2 = $('input[name=finish_date]').datepicker('getDate');
            $('input[name=finish_date]').datepicker('option', 'minDate', date2);
        }
        
        if($('input[name=finish_date]').datepicker("getDate") === null){
            $('input[name=finish_date]').datepicker("setDate", 7);
        }
        
        $('#weeklyDates').hide();
        
        $('input[name=view_type]').change(function () {
            if ($('#weekly').is(":checked")) {
                $('#weeklyDates').show();
            } else {
                $('#weeklyDates').hide();
            }
        }).change();
        
        
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });

        displayCoursePassword();

        $('#courseopen').click(function(event) {
                activate_input_password();
        });
        $('#coursewithregistration').click(function(event) {
                activate_input_password();
        });
        $('#courseclose').click(function(event) {
                deactivate_input_password();
        });
        $('#courseinactive').click(function(event) {
                deactivate_input_password();
        });

        $('input[name=l_radio]').change(function () {
            if ($('#cc_license').is(":checked")) {
                showCCFields();
            } else {
                hideCCFields();
            }
        }).change();

    });

/* ]]> */
</script>
hContent;

register_posted_variables(array('title' => true, 'password' => true, 'prof_names' => true));
if (empty($prof_names)) {
    $prof_names = "$_SESSION[givenname] $_SESSION[surname]";
}

$tool_content .= "<form method='post' name='createform' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return validateNodePickerForm() && checkrequired(this, 'title', 'prof_names');\">";

$departments = isset($_POST['department']) ? $_POST['department'] : array();
$deps_valid = true;

foreach ($departments as $dep) {
    if (get_config('restrict_teacher_owndep') && !$is_admin && !in_array($dep, $user->getDepartmentIds($uid))) {
        $deps_valid = false;
    }
}

// Check if the teacher is allowed to create in the departments he chose
if (!$deps_valid) {
    $nameTools = "";
    $tool_content .= "<div class='alert alert-danger'>$langCreateCourseNotAllowedNode</div>
                    <p class='pull-right'><a class='btn btn-default' href='$_SERVER[PHP_SELF]'>$langBack</a></p>";
    draw($tool_content, 1, null, $head_content);
    exit();
}


// display form
if (!isset($_POST['create_course'])) {
        $tool_content .= "
        <fieldset>
          <legend>$langCreateCourseStep1Title</legend>
            <table class='tbl' width='100%'>
            <tr>
              <th>$langTitle:</th>
              <td><input type='text' name='title' size='60' value='" . $title . "' /></td>
            </tr>
            <tr>
              <th>$langFaculty:</th>
              <td>";
        $allow_only_defaults = ( get_config('restrict_teacher_owndep') && !$is_admin ) ? true : false;
        list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $user->getDepartmentIds($uid), 'allow_only_defaults' => $allow_only_defaults));
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</td></tr>";

        $tool_content .= "
        <tr>
        <th>$langTeachers:</th>
        <td><input type='text' name='prof_names' size='60' value='" . q($prof_names) . "' /></td>
        </tr>
        <tr>
        <th class='left'>$langLanguage:</th>
        <td>" . lang_select_options('localize') . "</td>
        </tr>";
        $tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";
                        @$tool_content .= "<tr><th colspan='2'>$langDescrInfo <span class='smaller'>$langOptional</span>
                            <br /> ".  rich_text_editor('description', 4, 20, $description)."</th></tr>";
         $tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";


        $tool_content .= "
        <tr><td class='sub_title1' colspan='2'>$langCourseFormat</td></tr>
        <tr><td colspan='2'><input type='radio' name='view_type' value='simple' id='simple'><label for='simple'>&nbsp;$langCourseSimpleFormat</label></td></tr>
        <tr><td colspan='2'><input type='radio' name='view_type' value='units' checked id='units'><label for='units'>&nbsp;$langWithCourseUnits</label></td></tr>
        <tr><td colspan='2'><input type='radio' name='view_type' value='weekly' id='weekly'><label for='weekly'>&nbsp;$langCourseWeeklyFormat</label></td></tr>
        
        
        <tr id='weeklyDates'>
            <td colspan=4>$langStartDate <input class='dateInForm' type='text' name='start_date' value='' readonly='true'>
            $langDuration <input class='dateInForm' type='text' name='finish_date' value='' readonly='true'></td>
        </tr>";
        $tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";

        foreach ($license as $id => $l_info) {
            if ($id and $id < 10) {
                $cc_license[$id] = $l_info['title'];
            }
        }

        $tool_content .= "<tr><td class='sub_title1' colspan='2'>$langOpenCoursesLicense</td></tr>
        <tr><td colspan='2'><input type='radio' name='l_radio' value='0' checked>
            {$license[0]['title']}
        </td>
        </tr>
        <tr><td colspan='2'><input type='radio' name='l_radio' value='10'>
            {$license[10]['title']}
        </td>
        </tr>
        <tr><td colspan='2'><input id = 'cc_license' type='radio' name='l_radio' value='cc'/>
            $langCMeta[course_license]
        </td>
        </tr>
        <tr id = 'cc'><td>&nbsp;</td><td>" . selection($cc_license, 'cc_use') . "</td></tr>";

        $tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";
        $tool_content .= "<tr><th class='left' colspan='2'>$langAvailableTypes</th></tr>
        <tr>
        <td colspan='2'>
        <table class='sub_title1' width='100%'>
         <tr>
        <th width='170'>$langOptPassword</th>
            <td colspan='2'><input id='coursepassword' type='text' name='password' 'password' value='".@q($password)."' class='FormData_InputText' autocomplete='off' /></td>
        </tr>
        <tr>
            <th width='170'><img src='$themeimg/lock_open.png' alt='$m[legopen]' title='$m[legopen]' width='16' height='16' />&nbsp;$m[legopen]:</th>
            <td width='1'><input id='courseopen' type='radio' name='formvisible' value='2'checked='checked' /></td>
            <td class='smaller'>$langPublic</td>
        </tr>
        <tr>
            <th><img src='$themeimg/lock_registration.png' alt='$m[legrestricted]' title='$m[legrestricted]' width='16' height='16' />&nbsp;$m[legrestricted]:</th>
            <td><input id='coursewithregistration' type='radio' name='formvisible' value='1' /></td>
            <td class='smaller'>$langPrivOpen</td>
        </tr>
        <tr>
            <th><img src='$themeimg/lock_closed.png' alt='$m[legclosed]' title='$m[legclosed]' width='16' height='16' />&nbsp;$m[legclosed]:</th>
            <td><input id='courseclose' type='radio' name='formvisible' value='0' /></td>
            <td class='smaller'>$langPrivate</td>
        </tr>
         <tr>
            <th><img src='$themeimg/lock_inactive.png' alt='$m[linactive]' title='$m[linactive]' width='16' height='16' />&nbsp;$m[linactive]:</th>
            <td><input id='courseinactive' type='radio' name='formvisible' value='3' /></td>
            <td class='smaller'>$langCourseInactive</td>
        </tr>
        </table>";
        $tool_content .= "</td>
        </tr>
        <tr>
          <td class='right'>&nbsp;
            <input type='submit' name='create_course' value='".q($langCourseCreate)."' />
          </td>
        </tr>
        </table>";
        $tool_content .= "<div class='right smaller'>$langFieldsOptionalNote</div>";
        $tool_content .= "</fieldset>";
        $tool_content .= "</form>";

} else  { // create the course and the course database
    // validation in case it skipped JS validation
    $validationFailed = false;
    if (count($departments) < 1 || empty($departments[0])) {
        Session::Messages($langEmptyAddNode);
        $validationFailed = true;
    }

    if (empty($title) || empty($prof_names)) {
        Session::Messages($langFieldsMissing);
        $validationFailed = true;
    }

    if ($validationFailed) {
        header("Location:" . $urlServer . "modules/create_course/create_course.php");
        exit;
    }

    $nameTools = $langCourseCreate;

    // create new course code: uppercase, no spaces allowed
    $code = strtoupper(new_code($departments[0]));
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
    if ($extra_messages) {
        include $extra_messages;
    }

    // create course directories
    create_course_dirs($code);

    // get default quota values
    $doc_quota = get_config('doc_quota');
    $group_quota = get_config('group_quota');
    $video_quota = get_config('video_quota');
    $dropbox_quota = get_config('dropbox_quota');

    // get course_license
    if (isset($_POST['l_radio'])) {
        $l = $_POST['l_radio'];
        switch ($l) {
            case 'cc':
                if (isset($_POST['cc_use'])) {
                    $course_license = intval($_POST['cc_use']);
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

    if (ctype_alnum($_POST['view_type'])) {
        $view_type = $_POST['view_type'];
        if ($view_type == "weekly" && ($_POST['start_date'] != '' && $_POST['start_date'] != '0000-00-00')) {
            $view_type == "weekly";
        } else {
            $view_type = "units";
        }
    }
    if (empty($_POST['start_date'])) {
        $_POST['start_date'] = '0000-00-00';
    }
    if (empty($_POST['finish_date'])) {
        $_POST['finish_date'] = '0000-00-00';
    }

    $result = Database::get()->query("INSERT INTO course SET
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
                        view_type = ?s,
                        start_date = ?t,
                        finish_date = ?t,
                        keywords = '',
                        created = " . DBHelper::timeAfter() . ",
                        glossary_expand = 0,
                        glossary_index = 1", $code, $language, $title, $_POST['formvisible'],
            intval($course_license), $prof_names, $code, $doc_quota * 1024 * 1024,
            $video_quota * 1024 * 1024, $group_quota * 1024 * 1024,
            $dropbox_quota * 1024 * 1024, $password, $view_type, $_POST['start_date'], $_POST['finish_date']);
    $new_course_id = $result->lastInsertID;
    if (!$new_course_id) {
        Session::Messages($langGeneralError);
        redirect_to_home_page('modules/create_course/create_course.php');
    }

    //===================course format and start and finish date===============
    if ($view_type == "weekly") {

        //get the last inserted id as the course id
        $course_id = $new_course_id;

        $begin = new DateTime($_POST['start_date']);

        //check if there is no end date
        if ($_POST['finish_date'] == "" || $_POST['finish_date'] == '0000-00-00') {
            $end = new DateTime($begin->format("Y-m-d"));
            $end->add(new DateInterval('P26W'));
        } else {
            $end = new DateTime($_POST['finish_date']);
        }

        $daterange = new DatePeriod($begin, new DateInterval('P1W'), $end);

        foreach ($daterange as $date) {
            //===============================
            //new weeks
            //get the end week day
            $endWeek = new DateTime($date->format("Y-m-d"));
            $endWeek->modify('+6 day');

            //value for db
            $startWeekForDB = $date->format("Y-m-d");

            if ($endWeek->format("Y-m-d") < $end->format("Y-m-d")) {
                $endWeekForDB = $endWeek->format("Y-m-d");
            } else {
                $endWeekForDB = $end->format("Y-m-d");
            }
            //================================
            Database::get()->query("INSERT INTO course_weekly_view (course_id, start_week, finish_week) VALUES (?d, ?t, ?t)", $course_id, $startWeekForDB, $endWeekForDB);
        }
    }

    //=======================================================


    // create course  modules
    create_modules($new_course_id);

    Database::get()->query("INSERT INTO course_user SET
                                        course_id = ?d,
                                        user_id = ?d,
                                        status = 1,
                                        tutor = 1,
                                        reg_date = CURDATE()", intval($new_course_id), intval($uid));

    Database::get()->query("INSERT INTO group_properties SET
                                        course_id = ?d,
                                        self_registration = 1,
                                        multiple_registration = 0,
                                        forum = 1,
                                        private_forum = 0,
                                        documents = 1,
                                        wiki = 0,
                                        agenda = 0", intval($new_course_id));
    $course->refresh($new_course_id, $departments);

    $description = purify($_POST['description']);
    $unit_id = description_unit_id($new_course_id);
    if (!empty($description)) {
        add_unit_resource($unit_id, 'description', -1, $langDescription, $description);
    }

    // creation of course index.php
    course_index($code);

    //add a default forum category
    Database::get()->query("INSERT INTO forum_category
                            SET cat_title = ?s,
                            course_id = ?d", $langForumDefaultCat, $new_course_id);

    $_SESSION['courses'][$code] = USER_TEACHER;

    $tool_content .= "<div class='alert alert-success'><b>$langJustCreated:</b> " . q($title) . "<br>
                        <span class='smaller'>$langEnterMetadata</span></div>";
    $tool_content .= action_bar(array(
                array('title' => $langEnter,
                    'url' => "../../courses/$code/index.php",
                    'icon' => 'fa-arrow-right',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success')));
    
    // logging
    Log::record(0, 0, LOG_CREATE_COURSE, array('id' => $new_course_id,
                                            'code' => $code,
                                            'title' => $title,
                                            'language' => $language,
                                            'visible' => $_POST['formvisible']));
} // end of submit
draw($tool_content, 1, null, $head_content);

