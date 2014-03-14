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
if (get_config('betacms')) { // added support for betacms
    require_once 'modules/betacms_bridge/include/bcms.inc.php';
}

$TBL_USER_DEPARTMENT = 'user_department';

require_once 'include/log.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'functions.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

$nameTools = $langCourseCreate;

load_js('jquery');
load_js('jquery-ui');
load_js('jstree');
load_js('pwstrength.js');

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

if (get_config("betacms")) { // added support for betacms
    // Import from BetaCMS Bridge
    doImportFromBetaCMSBeforeCourseCreation();
}

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
    $tool_content .= "<p class='caution'>$langCreateCourseNotAllowedNode</p>
                    <p class='eclass_button'><a href='$_SERVER[PHP_SELF]'>$langBack</a></p>";
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
                        @$tool_content .= "<tr><th colspan='2'>$langDescrInfo <span class='smaller'>$langUncompulsory</span>
                            <br /> ".  rich_text_editor('description', 4, 20, $description)."</th></tr>";        
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
        <tr>
                <td>$langCourseKeywords&nbsp;<br />
                <input type='text' name='course_keywords' size='65' value='" . q($course_keywords) . "' />
                </td>
        </tr>
        <tr>
                <td class='right'><input type='submit' name='back1' value='&laquo; $langPreviousStep ' />&nbsp;<input type='submit' name='create3' value='$langNextStep &raquo;' /></td>
        </tr>
        </table>
        </fieldset>
        <div align='right' class='smaller'>$langFieldsOptionalNote</div>
        <br />";
} elseif (isset($_POST['create3']) or isset($_POST['back2'])) {
    $nameTools = $langCreateCourse . " (" . $langCreateCourseStep . " 3 " . $langCreateCourseStep2 . " 3 )";
    $tool_content .= "<fieldset><legend>$langCreateCourseStep3Title</legend>
                <div style='padding: 3px;'><em>$langOptPassword</em> 
                <input id='coursepassword' type='text' name='password' value='" . q($password) . "' class='FormData_InputText' id='password' />
                </div>
                <table class='tbl' width='100%'>
                <tr>
                <td class='sub_title1'>$langAvailableTypes<br></td>
                </tr>
                <tr>
                <td>
                <table class='tbl' width='100%'>
                <tr class='smaller'>
                <th width='130'><img src='$themeimg/lock_open.png' title='" . $m['legopen'] . "' width='16' height='16' /> " . $m['legopen'] . "</th>
                <td><input id='courseopen' name='formvisible' type='radio' value='2' checked='checked' /></td>
                <td>$langPublic</td>
                </tr>
                <tr class='smaller'>
                <th valign='top'><img src='$themeimg/lock_registration.png' title='" . $m['legrestricted'] . "' width='16' height='16' /> " . $m['legrestricted'] . "</th>
                <td valign='top'><input id='coursewithregistration' name='formvisible' type='radio' value='1' /></td>
                <td>
                $langPrivOpen<br />                
                </td>
                </tr>
                <tr class='smaller'>
                <th valign='top'><img src='$themeimg/lock_closed.png' title='" . $m['legclosed'] . "' width=\"16\" height='16' /> " . $m['legclosed'] . "</th>
                <td valign='top'><input id='courseclose' name='formvisible' type='radio' value='0' /></td>
                <td>$langPrivate</td>
                </tr>
                <tr class='smaller'>
                <th valign='top'><img src='$themeimg/lock_inactive.png' title='" . $m['linactive'] . "' width='16' height='16' /> " . $m['linactive'] . "</th>
                <td valign='top'><input id='courseinactive' name='formvisible' type='radio' value='3' /></td>
                <td>$langCourseInactive</td>
                </tr>
                </table>
                <br />
                </td>
        </tr>        
        <tr>
                <td class='sub_title1'>$langSubsystems</td>
        </tr>
        <tr>
        <td>
        <table class='tbl smaller' width='100%'>
        <tr>";
    $tool_content .= create_td($modules[MODULE_ID_AGENDA], MODULE_ID_AGENDA, 1);
    $tool_content .= "<th width='2' >&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_DROPBOX], MODULE_ID_DROPBOX, 0);
    $tool_content .= "</tr><tr class='even'>";
    $tool_content .= create_td($modules[MODULE_ID_LINKS], MODULE_ID_LINKS, 1);
    $tool_content .= "<th>&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_GROUPS], MODULE_ID_GROUPS, 0);
    $tool_content .= "</tr><tr>";
    $tool_content .= create_td($modules[MODULE_ID_DOCS], MODULE_ID_DOCS, 1);
    $tool_content .= "<th>&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_CHAT], MODULE_ID_CHAT, 0);
    $tool_content .= "</tr><tr class='even'>";
    $tool_content .= create_td($modules[MODULE_ID_VIDEO], MODULE_ID_VIDEO, 0);
    $tool_content .= "<th>&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_DESCRIPTION], MODULE_ID_DESCRIPTION, 1);
    $tool_content .= "</tr><tr>";
    $tool_content .= create_td($modules[MODULE_ID_ASSIGN], MODULE_ID_ASSIGN, 0);
    $tool_content .= "<th>&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_QUESTIONNAIRE], MODULE_ID_QUESTIONNAIRE, 0);
    $tool_content .= "</tr><tr class='even'>";
    $tool_content .= create_td($modules[MODULE_ID_ANNOUNCE], MODULE_ID_ANNOUNCE, 1);
    $tool_content .= "<th>&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_LP], MODULE_ID_LP, 0);
    $tool_content .= "</tr><tr>";
    $tool_content .= create_td($modules[MODULE_ID_FORUM], MODULE_ID_FORUM, 0);
    $tool_content .= "<th>&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_WIKI], MODULE_ID_WIKI, 0);
    $tool_content .= "</tr><tr class='even'>";
    $tool_content .= create_td($modules[MODULE_ID_EXERCISE], MODULE_ID_EXERCISE, 0);
    $tool_content .= "<th>&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_GLOSSARY], MODULE_ID_GLOSSARY, 1);
    $tool_content .= "</tr><tr>";
    $tool_content .= create_td($modules[MODULE_ID_EBOOK], MODULE_ID_EBOOK, 0);    
    $tool_content .= "<th>&nbsp;</th>";
    $tool_content .= create_td($modules[MODULE_ID_BBB], MODULE_ID_BBB, 0);    
    $tool_content .= "</tr>
                </table>
                <br />
                </td>
        </tr>
        <tr>
                <td class='right'>
                <input type='submit' name='back2' value='&laquo; $langPreviousStep ' />&nbsp;
                <input type='submit' name='create_course' value='$langFinalize' />
                </td>
        </tr>
        </table>
        </fieldset>
        <div class='right smaller'>$langFieldsOptionalNote</div>
        <br />";
} // end of create3
// create the course and the course database
if (isset($_POST['create_course'])) {

    // validation in case it skipped JS validation
    $validationFailed = false;
    if (count($departments) < 1 || empty($departments[0])) {
        Session::set_flashdata($langEmptyAddNode, 'alert1');
        $validationFailed = true;
    }
    
    if (empty($title) || empty($prof_names)) {
        Session::set_flashdata($langFieldsMissing, 'alert1');
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
                        keywords = '',
                        created = NOW()", $code, $language, $title, intval($_POST['formvisible']), 
            intval($course_license), $prof_names, $code, floatval($doc_quota * 1024 * 1024), 
            floatval($video_quota * 1024 * 1024), floatval($group_quota * 1024 * 1024), 
            floatval($dropbox_quota * 1024 * 1024), $password);
    $new_course_id = $result->lastInsertID;

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
    
    $_SESSION['courses'][$code] = USER_TEACHER;

    // ----------- Import from BetaCMS Bridge -----------
    if (get_config('betacms')) {
        $tool_content .= doImportFromBetaCMSAfterCourseCreation($code, $mysqlMainDb, $webDir);
    }
    // --------------------------------------------------
    $tool_content .= "<p class='success'><b>$langJustCreated:</b> " . q($title) . "<br>
                        <span class='smaller'>$langEnterMetadata</span></p>
                        <p class='eclass_button'><a href='../../courses/$code/index.php'>$langEnter</a></p>";
    // logging
    Log::record(0, 0, LOG_CREATE_COURSE, array('id' => $new_course_id,
                                            'code' => $code,
                                            'title' => $title,
                                            'language' => $language,
                                            'visible' => $_POST['formvisible']));
} // end of submit
draw($tool_content, 1, null, $head_content);

/**
 * @brief create entries in table `module`
 * @param type $cid
 * @param type $sbsystems
 */
function create_modules($cid, $sbsystems) {
    
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_AGENDA . ", " . $sbsystems[MODULE_ID_AGENDA] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_LINKS . ", " . $sbsystems[MODULE_ID_LINKS] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_DOCS . ", " . $sbsystems[MODULE_ID_DOCS] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_VIDEO . ", " . $sbsystems[MODULE_ID_VIDEO] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_ASSIGN . ", " . $sbsystems[MODULE_ID_ASSIGN] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_ANNOUNCE . ", " . $sbsystems[MODULE_ID_ANNOUNCE] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_FORUM . ", " . $sbsystems[MODULE_ID_FORUM] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_EXERCISE . ", " . $sbsystems[MODULE_ID_EXERCISE] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_GROUPS . ", " . $sbsystems[MODULE_ID_GROUPS] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_DROPBOX . ", " . $sbsystems[MODULE_ID_DROPBOX] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_GLOSSARY . ", " . $sbsystems[MODULE_ID_GLOSSARY] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_EBOOK . ", " . $sbsystems[MODULE_ID_EBOOK] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_CHAT . ", " . $sbsystems[MODULE_ID_CHAT] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_DESCRIPTION . ", " . $sbsystems[MODULE_ID_DESCRIPTION] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_QUESTIONNAIRE . ", " . $sbsystems[MODULE_ID_QUESTIONNAIRE] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_LP . ", " . $sbsystems[MODULE_ID_LP] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_WIKI . ", " . $sbsystems[MODULE_ID_WIKI] . ", $cid)");
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (" . MODULE_ID_BBB . ", " . $sbsystems[MODULE_ID_BBB] . ", $cid)");

}

/**
 * @brief create <td>....</td> for each module
 * @global type $themeimg
 * @param type $m
 * @param type $value
 * @param type $selected
 * @return type
 */
function create_td($m, $value, $selected) {

    global $themeimg;

    $checkbox = '';
    if ($selected) {
        $checkbox = "checked='checked'";
    }
    $td = "<td width='10' ><img src='$themeimg/$m[image]_on.png' alt='' height='16' width='16' /></td>
        <td width='150'>$m[title]</td>
        <td width='30' ><input name='subsystems[]' type='checkbox' value='$value' $checkbox /></td>";

    return $td;
}
