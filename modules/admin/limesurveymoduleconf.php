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

    new_lti_app(null, true, null);

} else if (isset($_GET['delete_template'])) {

    delete_lti_app(getDirectReference($_GET['delete_template']));
    Session::flash('message',$langLimesurveyAppDeleteSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/limesurveymoduleconf.php");

} else if (isset($_POST['new_lti_app'])) { // Create

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    add_update_lti_app($_POST['title'], $_POST['desc'], $_POST['lti_url'], $_POST['lti_version'], $_POST['lti_key'], $_POST['lti_secret'],
        $_POST['lti_public_keyset_url'], $_POST['lti_initiate_login_url'], $_POST['lti_redirection_uri'], $_POST['lti_launchcontainer'],
        $_POST['status'], $_POST['lti_courses'], LTI_TYPE, null, true,
        false, null);
    Session::flash('message',$langLimesurveyAppAddSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/limesurveymoduleconf.php");

} else if (isset($_POST['update_lti_app'])) { // Update

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    add_update_lti_app($_POST['title'], $_POST['desc'], $_POST['lti_url'], $_POST['lti_version'], $_POST['lti_key'], $_POST['lti_secret'],
        $_POST['lti_public_keyset_url'], $_POST['lti_initiate_login_url'], $_POST['lti_redirection_uri'], $_POST['lti_launchcontainer'],
        $_POST['status'], $_POST['lti_courses'], LTI_TYPE, true, LTI_TYPE,
        true, getDirectReference($_GET['id']));
    // Display result message
    Session::flash('message',$langLimesurveyAppAddSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/limesurveymoduleconf.php");

} else { // Display config edit form
    if (isset($_GET['edit_template'])) {

        $pageName = $langEdit;
        $navigation[] = array('url' => 'limesurveymoduleconf.php', 'name' => $langLimesurveyConf);

        edit_lti_app(getDirectReference($_GET['edit_template']));

    } else { //display available TII templates

        $tool_content .= action_bar(array(
            array('title' => $langNewLimesurveyTool,
                'url' => "limesurveymoduleconf.php?add_template",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success')
            ));

        $q = Database::get()->queryArray("SELECT * FROM lti_apps WHERE is_template = true AND type = 'limesurvey' AND course_id is null ORDER BY title ASC");
        if (count($q) > 0) {
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                <tr class='list-header'><th>$langTitle</th>
                    <th>$langUnitDescr</th>
                    <th>$langLimesurveyEnabled</th>
                    <th class='text-end' aria-label='$langSettingSelect'>".icon('fa-gears')."</th></tr>
                </thead>";
            foreach ($q as $lti) {
                $enabled_lti_template = ($lti->enabled == 1)? $langYes : $langNo;
                $tool_content .= "<tr>" .
                    "<td>$lti->title</td>" .
                    "<td>$lti->description</td>" .
                    "<td>$enabled_lti_template</td>" .
                    "<td class='option-btn-cell text-end'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => "$_SERVER[SCRIPT_NAME]?edit_template=" . getIndirectReference($lti->id),
                            'icon' => 'fa-edit'),
                        array('title' => $langDelete,
                            'url' => "$_SERVER[SCRIPT_NAME]?delete_template=" . getIndirectReference($lti->id),
                            'icon' => 'fa-xmark',
                            'class' => 'delete',
                            'confirm' => $langConfirmDelete))) . "</td>" .
                    "</tr>";
            }
            $tool_content .= "</table></div>";

        } else {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAvailableLimesurveyTemplates</span></div></div>";
        }
    }
}

draw($tool_content, 3, null, $head_content);
