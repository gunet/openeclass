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

const LTI_TYPE = 'limesurvey';

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/lti_consumer/lti-functions.php';

$toolName = $langLimesurveyConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('bootstrap-datetimepicker');
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


if (isset($_GET['add_template'])) {

    $pageName = $langNewLimesurveyTool;
    $navigation[] = array('url' => 'limesurveymoduleconf.php', 'name' => $langLimesurveyConf);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "limesurveymoduleconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    new_lti_app(true, null, null); // TODO: can we have a default lime url?

} else if (isset($_GET['delete_template'])) {

    delete_lti_app(getDirectReference($_GET['delete_template']));
    Session::Messages($langLimesurveyAppDeleteSuccessful, 'alert-success');
    redirect_to_home_page("modules/admin/limesurveymoduleconf.php");

} else if (isset($_POST['new_lti_app'])) { // Create

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    add_update_lti_app($_POST['title'], $_POST['desc'], $_POST['lti_url'], $_POST['lti_key'], $_POST['lti_secret'],
        $_POST['lti_launchcontainer'], $_POST['status'], $_POST['lti_courses'], null, true,
        false, null, LTI_TYPE);
    Session::Messages($langLimesurveyAppAddSuccessful, 'alert-success');
    redirect_to_home_page("modules/admin/limesurveymoduleconf.php");

} else if (isset($_POST['update_lti_app'])) { // Update

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    add_update_lti_app($_POST['title'], $_POST['desc'], $_POST['lti_url'], $_POST['lti_key'], $_POST['lti_secret'],
        $_POST['lti_launchcontainer'], $_POST['status'], $_POST['lti_courses'], null, true,
        true, getDirectReference($_GET['id']), LTI_TYPE);
    // Display result message
    Session::Messages($langLimesurveyAppAddSuccessful, 'alert-success');
    redirect_to_home_page("modules/admin/limesurveymoduleconf.php");

} else { // Display config edit form
    if (isset($_GET['edit_template'])) {

        $pageName = $langEdit;
        $navigation[] = array('url' => 'limesurveymoduleconf.php', 'name' => $langLimesurveyConf);
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "limesurveymoduleconf.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));

        edit_lti_app(getDirectReference($_GET['edit_template']));

    } else { //display available TII templates

        $tool_content .= action_bar(array(
            array('title' => $langNewLimesurveyTool,
                'url' => "limesurveymoduleconf.php?add_template",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langBack,
                'url' => "extapp.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));

        $q = Database::get()->queryArray("SELECT * FROM lti_apps WHERE is_template = true AND type = 'limesurvey' AND course_id is null ORDER BY title ASC");
        if (count($q) > 0) {
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                <tr><th class = 'text-center'>$langTitle</th>
                    <th class = 'text-center'>$langNewLTIAppSessionDesc</th>
                    <th class = 'text-center'>$langLimesurveyEnabled</th>
                    <th class = 'text-center'>".icon('fa-gears')."</th></tr>
                </thead>";
            foreach ($q as $lti) {
                $enabled_lti_template = ($lti->enabled == 1)? $langYes : $langNo;
                $tool_content .= "<tr>" .
                    "<td>$lti->title</td>" .
                    "<td>$lti->description</td>" .
                    "<td class = 'text-center'>$enabled_lti_template</td>" .
                    "<td class='option-btn-cell'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => "$_SERVER[SCRIPT_NAME]?edit_template=" . getIndirectReference($lti->id),
                            'icon' => 'fa-edit'),
                        array('title' => $langDelete,
                            'url' => "$_SERVER[SCRIPT_NAME]?delete_template=" . getIndirectReference($lti->id),
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langConfirmDelete))) . "</td>" .
                    "</tr>";
            }
            $tool_content .= "</table></div>";

        } else {
            $tool_content .= "<div class='alert alert-warning'>$langNoAvailableLimesurveyTemplates</div>";
        }
    }
}

draw($tool_content, 3, null, $head_content);