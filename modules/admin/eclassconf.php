<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modalconfirmation.php';
$toolName = $langEclassConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */
        
function loginFailPanel(e) {
    duration = null;
    if (e) {
        duration = 400;
    }
        
    if ($('#login_fail_check').is(":checked")) {
        $('#login_fail_threshold').show(duration);
        $('#login_fail_deny_interval').show(duration);
        $('#login_fail_forgive_interval').show(duration);
    }
    else {
        $('#login_fail_threshold').hide(duration);
        $('#login_fail_deny_interval').hide(duration);
        $('#login_fail_forgive_interval').hide(duration);
    }
}

$(document).ready(function() {
/* Check if we are in safari and fix Bootstrap Affix*/
if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
    var stickywidget = $('#floatMenu');
    var explicitlySetAffixPosition = function() {
        stickywidget.css('left',stickywidget.offset().left+'px');
    };
    /* Before the element becomes affixed, add left CSS that is equal to the distance of the element from the left of the screen */
    stickywidget.on('affix.bs.affix',function(){
        stickywidget.removeAttr('style');
        explicitlySetAffixPosition();
    }); 
    stickywidget.on('affixed-bottom.bs.affix',function(){
        stickywidget.css('left', 'auto');
    });
    /* On resize of window, un-affix affixed widget to measure where it should be located, set the left CSS accordingly, re-affix it */
    $(window).resize(function(){
        if(stickywidget.hasClass('affix')) {
            stickywidget.removeClass('affix');
            explicitlySetAffixPosition();
            stickywidget.addClass('affix');
        }
    });
}
    // Course Settings checkboxes
    $('#uown').click(function(event) {
        if (!$('#uown').is(":checked")) {
            $('#town').prop('checked', false);
        }
        $('#town').prop('disabled', !$('#uown').is(":checked"));
    });
    
    // Login Fail Panel
    loginFailPanel();
    $('#login_fail_check').click(function(event) {
        loginFailPanel(true);
    });
    
    // Open Courses checkboxes
    $('#opencourses_enable').click(function(event) {
        if ($('#opencourses_enable').is(":checked")) {
            if ($('#course_metadata').is(":checked")) {
                $('#course_metadata').prop('disabled', true);
            } else {
                $('#course_metadata')
                    .prop('checked', true)
                    .prop('disabled', true)
                    .change();
            }
        } else {
            $('#course_metadata').prop('disabled', false);
        }
    });
    
    if ($('#opencourses_enable').is(":checked")) {
        $('#course_metadata').prop('disabled', true);
    }
    
    // Search Engine checkboxes
    $('#confirmIndexDialog').modal({
        show: false,
        keyboard: false,
        backdrop: 'static'
    });
        
    $("#confirmIndexCancel").click(function() {
        $('#index_enable')
            .prop('checked', false)
            .prop('disabled', false);
        $('#search_enable').prop('checked', false);
        $("#confirmIndexDialog").modal("hide");
    });
        
    $("#confirmIndexOk").click(function() {
        $("#confirmIndexDialog").modal("hide");
    });
    
    $('#search_enable').change(function(event) {
        if ($('#search_enable').is(":checked")) {
            if ($('#index_enable').is(":checked")) {
                $('#index_enable').prop('disabled', true);
            } else {
                $('#index_enable')
                    .prop('checked', true)
                    .prop('disabled', true)
                    .change();
            }
        } else {
            $('#index_enable').prop('disabled', false);
        }
    });
    
    if ($('#search_enable').is(":checked")) {
        $('#index_enable').prop('disabled', true);
    }
    
    $('#index_enable').change(function(event) {
        if ($('#index_enable').is(":checked")) {
            $("#confirmIndexDialog").modal("show");
        }
    });
        
    // Mobile API Confirmations    
    $('#confirmMobileAPIDialog').modal({
        show: false,
        keyboard: false,
        backdrop: 'static'
    });
        
    $("#confirmMobileAPICancel").click(function() {
        $('#mobileapi_enable').prop('checked', false);
        $("#confirmMobileAPIDialog").modal("hide");
    });
        
    $("#confirmMobileAPIOk").click(function() {
        $("#confirmMobileAPIDialog").modal("hide");
    });
        
    $('#mobileapi_enable').change(function(event) {
        if ($('#mobileapi_enable').is(":checked")) {
            $("#confirmMobileAPIDialog").modal("show");
        }
    });

});

/* ]]> */
</script>
EOF;

define('MONTHS', 30 * 24 * 60 * 60);

// schedule indexing if necessary
if (Session::get('scheduleIndexing')) {
    $tool_content .= "<div class='alert alert-warning'>{$langIndexingNeeded} <a id='idxpbut' href='../search/idxpopup.php' onclick=\"return idxpopup('../search/idxpopup.php', 600, 500)\">{$langHere}.</a></div>";
    
    $head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */

var idxwindow = null;

function idxpopup(url, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);

    if (idxwindow == null || idxwindow.closed) {
        idxwindow = window.open(url, 'idxpopup', 'resizable=yes, scrollbars=yes, status=yes, width='+w+', height='+h+', top='+top+', left='+left);
        if (window.focus && idxwindow !== null) {
            idxwindow.focus();
        }
    } else {
        idxwindow.focus();
    }

    return false;
}

$(document).ready(function() {

    $('#idxpbut').click();

});

/* ]]> */
</script>
EOF;
}

// Save new `config` table
if (isset($_POST['submit'])) {
    $active_lang_codes = array();
    if (isset($_POST['av_lang'])) {
        foreach ($_POST['av_lang'] as $langcode) {
            if (isset($language_codes[$langcode])) {
                $active_lang_codes[] = $langcode;
            }
        }
    }
    if (!count($active_lang_codes)) {
        $active_lang_codes = array('el');
    }
    if (isset($_POST['default_language']) and
            isset($language_codes[$_POST['default_language']])) {
        set_config('language', $_POST['default_language']);
    }

    set_config('active_ui_languages', implode(' ', $active_lang_codes));
    set_config('base_url', $_POST['formurlServer']);
    set_config('phpMyAdminURL', $_POST['formphpMyAdminURL']);
    set_config('phpSysInfoURL', $_POST['formphpSysInfoURL']);
    set_config('email_sender', $_POST['formemailAdministrator']);
    set_config('admin_name', $_POST['formadministratorName']);
    set_config('site_name', $_POST['formsiteName']);
    set_config('phone', $_POST['formtelephone']);
    set_config('email_helpdesk', $_POST['formemailhelpdesk']);
    set_config('institution', $_POST['formInstitution']);
    set_config('institution_url', $_POST['formInstitutionUrl']);
    set_config('postaddress', $_POST['formpostaddress']);
    set_config('fax', $_POST['formfax']);
    set_config('account_duration', MONTHS * $_POST['formdurationAccount']);
    set_config('min_password_len', intval($_POST['min_password_len']));
    set_config('student_upload_whitelist', $_POST['student_upload_whitelist']);
    set_config('teacher_upload_whitelist', $_POST['teacher_upload_whitelist']);
    set_config('email_announce', $_POST['email_announce']);
    
    $config_vars = array('email_required' => true,
        'email_verification_required' => true,
        'dont_mail_unverified_mails' => true,
        'email_from' => true,
        'am_required' => true,
        'dont_display_login_form' => true,
        'dropbox_allow_student_to_student' => true,
        'dropbox_allow_personal_messages' => true,
        'block_username_change' => true,
        'display_captcha' => true,
        'insert_xml_metadata' => true,
        'enable_mobileapi' => true,
        'doc_quota' => true,
        'group_quota' => true,
        'video_quota' => true,
        'dropbox_quota' => true,
        'max_glossary_terms' => true,        
        'case_insensitive_usernames' => true,
        'course_multidep' => true,
        'user_multidep' => true,
        'restrict_owndep' => true,
        'restrict_teacher_owndep' => true,
        'allow_teacher_clone_course' => true,
        'disable_log_actions' => true,
        'disable_log_course_actions' => true,
        'disable_log_system_actions' => true,
        'user_registration' => true,
        'eclass_stud_reg' => true,
        'alt_auth_stud_reg' => true,
        'eclass_prof_reg' => true,
        'alt_auth_prof_reg' => true,
        'enable_indexing' => true,
        'enable_search' => true,
        'enable_common_docs' => true,
        'enable_social_sharing_links' => true,
        'login_fail_check' => true,
        'login_fail_threshold' => true,
        'login_fail_deny_interval' => true,
        'login_fail_forgive_interval' => true,
        'actions_expire_interval' => true,
        'log_expire_interval' => true,
        'log_purge_interval' => true,
        'course_metadata' => true,
        'opencourses_enable' => true);

    register_posted_variables($config_vars, 'all', 'intval');
    
    if ($GLOBALS['opencourses_enable'] == 1) {
        $GLOBALS['course_metadata'] = 1;
    }

    if ($GLOBALS['enable_search'] == 1) {
        $GLOBALS['enable_indexing'] = 1;
    }

    // restrict_owndep and restrict_teacher_owndep are interdependent
    if ($GLOBALS['restrict_owndep'] == 0) {
        $GLOBALS['restrict_teacher_owndep'] = 0;
    }

    $scheduleIndexing = false;
    // indexing was previously off, but now set to on, need to schedule re-indexing
    if (!get_config('enable_indexing') && $enable_indexing) {
        $scheduleIndexing = true;
        Database::get()->query("DELETE FROM idx_queue");
        Database::get()->queryFunc("SELECT id FROM course", function($r) {
            Database::get()->query("INSERT INTO idx_queue (course_id) VALUES (?d)", $r->id);
        });
    }

    // indexing was previously on, but now set to off, need to empty it
    if (get_config('enable_indexing') && !$enable_indexing) {
        require_once 'modules/search/indexer.class.php';
        Indexer::deleteAll();
    }

    // update table `config`
    foreach ($config_vars as $varname => $what) {
        set_config($varname, $GLOBALS[$varname]);
    }

    // Display result message
    Session::flash('scheduleIndexing', $scheduleIndexing);
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/eclassconf.php');

} // end of if($submit)
else {
    // Display config.php edit form
    $head_content .= "
        <script>
        $(function() {
            $('body').scrollspy({ target: '#affixedSideNav' });
        });
        </script>
    ";
    // Display link to index.php
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    
    if (function_exists('imagettfbbox')) {
        $cbox_display_captcha = get_config('display_captcha') ? 'checked' : '';
        $message_display_captcha = $disable_display_captcha = '';
    } else {
        $cbox_display_captcha = '';
        $disable_display_captcha = 'disabled';
        $message_display_captcha = '<div>' . $lang_display_captcha_unsupported . '</div>';
    }
    $tool_content .= "
<div class='row'>
    <div class='col-sm-9'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
            <div class='panel panel-default' id='one'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langBasicCfgSetting</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='formurlServer' class='col-sm-2 control-label'>$langSiteUrl:</label>
                           <div class='col-sm-10'>
                                <input class='FormData_InputText form-control' type='text' name='formurlServer' id='formurlServer' value='" . q($urlServer) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formphpMyAdminURL' class='col-sm-2 control-label'>$langphpMyAdminURL:</label>
                           <div class='col-sm-10'>
                                <input class='FormData_InputText form-control' type='text' name='formphpMyAdminURL' id='formphpMyAdminURL' value='" . q(get_config('phpMyAdminURL')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formphpSysInfoURL' class='col-sm-2 control-label'>$langSystemInfoURL:</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formphpSysInfoURL' id='formphpSysInfoURL' value='" . q(get_config('phpSysInfoURL')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formemailAdministrator' class='col-sm-2 control-label'>$langAdminEmail:</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formemailAdministrator' id='formemailAdministrator' value='" . q(get_config('email_sender')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formemailAdministrator' class='col-sm-2 control-label'>$langDefaultAdminName:</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formadministratorName' id='formadministratorName' value='" . q(get_config('admin_name')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formsiteName' class='col-sm-2 control-label'>$langCampusName:</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formsiteName' id='formsiteName' value='" . q(get_config('site_name')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formpostaddress' class='col-sm-2 control-label'>$langPostMail</label>
                           <div class='col-sm-10'>
                               <textarea class='form-control' name='formpostaddress' id='formpostaddress'>" . q(get_config('postaddress')) . "</textarea>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formtelephone' class='col-sm-2 control-label'>$langPhone:</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formtelephone' id='formtelephone' value='" . q(get_config('phone')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formfax' class='col-sm-2 control-label'>$langFax</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formfax' id='formfax' value='" . q(get_config('fax')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formemailhelpdesk' class='col-sm-2 control-label'>$langHelpDeskEmail:</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formemailhelpdesk' id='formemailhelpdesk' value='" . q(get_config('email_helpdesk')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formInstitution' class='col-sm-2 control-label'>$langInstituteShortName:</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formInstitution' id='formInstitution' value='" . $Institution . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formInstitutionUrl' class='col-sm-2 control-label'>$langInstituteName:</label>
                           <div class='col-sm-10'>
                               <input class='FormData_InputText form-control' type='text' name='formInstitutionUrl' id='formInstitutionUrl' value='" . $InstitutionUrl . "'>
                           </div>
                        </div>
                    </fieldset>     
                </div>
            </div>
            <div class='panel panel-default' id='two'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langUpgReg</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='user_registration' class='col-sm-3 control-label'>$langUserRegistration:</label>
                           <div class='col-sm-9'>
                                ". selection(array('1' => $langActivate, '0' => $langDeactivate), 'user_registration', get_config('user_registration'), "class='form-control' id='user_registration'"). "
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='eclass_stud_reg' class='col-sm-3 control-label'>$langUserAccount $langViaeClass:</label>
                           <div class='col-sm-9'>
                                ". selection(array('0' => $langDisableEclassStudReg, '1' => $langReqRegUser, '2' => $langDisableEclassStudRegType), 'eclass_stud_reg', get_config('eclass_stud_reg'), "class='form-control' id='eclass_stud_reg'") ."
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='alt_auth_stud_reg' class='col-sm-3 control-label'>$langUserAccount $langViaAltAuthMethods:</label>
                           <div class='col-sm-9'>
                                ". selection(array('0' => $langDisableEclassStudReg, '1' => $langReqRegUser, '2' => $langDisableEclassStudRegType), 'alt_auth_stud_reg', get_config('alt_auth_stud_reg'), "class='form-control' id='alt_auth_stud_reg'") ."
                           </div>
                        </div> 
                        <div class='form-group'>
                           <label for='eclass_prof_reg' class='col-sm-3 control-label'>$langProfAccount $langViaeClass:</label>
                           <div class='col-sm-9'>
                                ". selection(
                                        array(
                                            '0' => $langDisableEclassProfReg, 
                                            '1' => $langReqRegProf
                                        ), 
                                        'eclass_prof_reg', 
                                        get_config('eclass_prof_reg'),
                                        "class='form-control' id='eclass_prof_reg'"
                                    ) ."
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='alt_auth_prof_reg' class='col-sm-3 control-label'>$langProfAccount $langViaAltAuthMethods:</label>
                           <div class='col-sm-9'>
                                ". selection(
                                        array(
                                                '0' => $langDisableEclassProfReg,
                                                '1' => $langReqRegProf
                                            ), 
                                        'alt_auth_prof_reg', 
                                        get_config('alt_auth_prof_reg'),
                                        "class='form-control' id='alt_auth_prof_reg'"
                                    ) ."
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formdurationAccount' class='col-sm-3 control-label'>$langUserDurationAccount&nbsp;($langMonthsUnit): </label>
                           <div class='col-sm-9'>
                                <input type='text' class='form-control' name='formdurationAccount' id='formdurationAccount' maxlength='3' value='" . intval(get_config('account_duration') / MONTHS) . "'>
                           </div>
                        </div>                         
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$lang_display_captcha_label:</label>
                            <div class='checkbox col-sm-9'>
                                <label>
                                    <input type='checkbox' name='display_captcha' value='1' $cbox_display_captcha $disable_display_captcha>
                                    $lang_display_captcha
                                </label>$message_display_captcha
                            </div>
                        </div>
                    </fieldset>    
                </div>
            </div>";
        $active_ui_languages = explode(' ', get_config('active_ui_languages'));
        $langdirs = active_subdirs($webDir . '/lang', 'messages.inc.php');
        $sel = array();
        $selectable_langs = array();
        $cbox_dont_display_login_form = get_config('dont_display_login_form') ? 'checked' : '';
        foreach ($language_codes as $langcode => $langname) {
            if (in_array($langcode, $langdirs)) {
                $loclangname = $langNameOfLang[$langname];
                if (in_array($langcode, $active_ui_languages)) {
                    $selectable_langs[$langcode] = $loclangname;
                }
                $checked = in_array($langcode, $active_ui_languages) ? ' checked' : '';
                $sel[] = "<div class='checkbox'>
                            <label>
                                <input type='checkbox' name='av_lang[]' value='$langcode'$checked>
                                $loclangname
                            </label>
                        </div>";
                            
            }
        }   
$tool_content .= "<div class='panel panel-default' id='three'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langEclassThemes</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='default_language' class='col-sm-3 control-label'>$langMainLang: </label>
                           <div class='col-sm-9'>" .
                               selection($selectable_langs, 'default_language', get_config('language'),
                                         "class='form-control' id='default_language'") .
                           "</div>
                        </div> 
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$langSupportedLanguages:</label>
                            <div class='col-sm-9'>            
                            " . implode(' ', $sel) . "
                            </div>
                        </div>                        
                        <div class='form-group'>
                           <label for='theme' class='col-sm-3 control-label'>$lang_login_form: </label>
                           <div class='col-sm-9'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dont_display_login_form' value='1' $cbox_dont_display_login_form>    
                                        $lang_dont_display_login_form
                                    </label>
                                </div>                              
                           </div>
                        </div>                        
                    </fieldset>
                </div>
            </div>";

    $cbox_dont_mail_unverified_mails = get_config('dont_mail_unverified_mails') ? 'checked' : '';
    $cbox_email_from = get_config('email_from') ? 'checked' : '';
    $tool_content .= "
            <div class='panel panel-default' id='four'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langEmailSettings</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dont_mail_unverified_mails' value='1' $cbox_dont_mail_unverified_mails>    
                                        $lang_dont_mail_unverified_mails
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='email_from' value='1' $cbox_email_from>
                                        $lang_email_from
                                    </label>
                                </div>                                
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formEmailAnnounce' class='col-sm-2 control-label'>$langEmailAnnounce:</label>
                           <div class='col-sm-10'>
                                <input type='text' class='form-control' name='email_announce' id='formEmailAnnounce' value='".get_config('email_announce')."'>
                           </div>
                        </div>
                    </fieldset>
                </div>
            </div>";

    $cbox_course_multidep = get_config('course_multidep') ? 'checked' : '';
    $cbox_user_multidep = get_config('user_multidep') ? 'checked' : '';
    $cbox_restrict_owndep = get_config('restrict_owndep') ? 'checked' : '';
    $cbox_restrict_teacher_owndep = get_config('restrict_teacher_owndep') ? 'checked' : '';
    $cbox_allow_teacher_clone_course = get_config('allow_teacher_clone_course') ? 'checked' : '';
    $town_dis = get_config('restrict_owndep') ? '' : 'disabled';
    $cbox_insert_xml_metadata = get_config('insert_xml_metadata') ? 'checked' : '';
    $cbox_course_metadata = get_config('course_metadata') ? 'checked' : '';
    $cbox_opencourses_enable = get_config('opencourses_enable') ? 'checked' : '';
    $tool_content .= "
            <div class='panel panel-default' id='five'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langCourseSettings</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='course_multidep' value='1' $cbox_course_multidep>
                                        $lang_course_multidep
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='user_multidep' value='1' $cbox_user_multidep>
                                        $lang_user_multidep
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='uown' type='checkbox' name='restrict_owndep' value='1' $cbox_restrict_owndep>
                                        $lang_restrict_owndep
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='town' type='checkbox' name='restrict_teacher_owndep' value='1' $town_dis $cbox_restrict_teacher_owndep>
                                        $lang_restrict_teacher_owndep
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='allow_teacher_clone_course' value='1' $cbox_allow_teacher_clone_course>
                                        $lang_allow_teacher_clone_course
                                    </label>
                                </div>
                           </div>
                        </div>                        
                    </fieldset>
                </div>
            </div>
            <div class='panel panel-default' id='six'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langMetaCommentary</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='insert_xml_metadata' value='1' $cbox_insert_xml_metadata>
                                        $lang_insert_xml_metadata
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' id='course_metadata' name='course_metadata' value='1' $cbox_course_metadata>
                                        $lang_course_metadata
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' id='opencourses_enable' name='opencourses_enable' value='1' $cbox_opencourses_enable>
                                        $lang_opencourses_enable
                                    </label>
                                </div>                                  
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>";

    $cbox_case_insensitive_usernames = get_config('case_insensitive_usernames') ? 'checked' : '';
    $cbox_email_required = get_config('email_required') ? 'checked' : '';
    $cbox_email_verification_required = get_config('email_verification_required') ? 'checked' : '';
    $cbox_am_required = get_config('am_required') ? 'checked' : '';
    $cbox_dropbox_allow_student_to_student = get_config('dropbox_allow_student_to_student') ? 'checked' : '';
    $cbox_dropbox_allow_personal_messages = get_config('dropbox_allow_personal_messages') ? 'checked' : '';
    $cbox_block_username_change = get_config('block_username_change') ? 'checked' : '';
    $cbox_enable_mobileapi = get_config('enable_mobileapi') ? 'checked' : '';
    $max_glossary_terms = get_config('max_glossary_terms');
    $cbox_enable_indexing = get_config('enable_indexing') ? 'checked' : '';
    $cbox_enable_search = get_config('enable_search') ? 'checked' : '';
    $cbox_enable_common_docs = get_config('enable_common_docs') ? 'checked' : '';
    $cbox_enable_social_sharing_links = get_config('enable_social_sharing_links') ? 'checked' : '';
    $cbox_login_fail_check = get_config('login_fail_check') ? 'checked' : '';
    $id_enable_mobileapi = (check_auth_active(7) || check_auth_active(6)) ? "id='mobileapi_enable'" : '';

        $tool_content .= "
            <div class='panel panel-default' id='seven'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langOtherOptions</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='case_insensitive_usernames' value='1' $cbox_case_insensitive_usernames>
                                        $langCaseInsensitiveUsername
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='email_required' value='1' $cbox_email_required>
                                        $lang_email_required
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='email_verification_required' value='1' $cbox_email_verification_required>
                                        $lang_email_verification_required
                                    </label>
                                </div>  
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='am_required' value='1' $cbox_am_required>
                                        $lang_am_required
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='index_enable' type='checkbox' name='enable_indexing' value='1' $cbox_enable_indexing>
                                        $langEnableIndexing
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='search_enable' type='checkbox' name='enable_search' value='1' $cbox_enable_search>
                                        $langEnableSearch
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dropbox_allow_student_to_student' value='1' $cbox_dropbox_allow_student_to_student>
                                        $lang_dropbox_allow_student_to_student
                                    </label>
                                </div>  
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dropbox_allow_personal_messages' value='1' $cbox_dropbox_allow_personal_messages>
                                        $lang_dropbox_allow_personal_messages
                                    </label>
                                </div>  
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='block_username_change' value='1' $cbox_block_username_change>
                                        $lang_block_username_change
                                    </label>
                                </div>                                  
                                <div class='checkbox'>
                                    <label>
                                        <input $id_enable_mobileapi type='checkbox' name='enable_mobileapi' value='1' $cbox_enable_mobileapi>
                                        $lang_enable_mobileapi
                                    </label>
                                </div>      
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='enable_common_docs' value='1' $cbox_enable_common_docs>
                                        $langEnableCommonDocs
                                    </label>
                                </div>      
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='enable_social_sharing_links' value='1' $cbox_enable_social_sharing_links>
                                        $langEnableSocialSharingLiks
                                    </label>
                                </div>                                      
                           </div>                           
                        </div>
                        <hr><br>
                        <div class='form-group'>
                           <label for='min_password_len' class='col-sm-4 control-label'>$langMinPasswordLen: </label>
                           <div class='col-sm-8'>
                                <input type='text' class='form-control' name='min_password_len' id='min_password_len' value='" . intval(get_config('min_password_len')) . "'>                            
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='min_password_len' class='col-sm-4 control-label'>$lang_max_glossary_terms </label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='max_glossary_terms' value='$max_glossary_terms'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='min_password_len' class='col-sm-4 control-label'>$langActionsExpireInterval ($langMonthsUnit):</label>
                           <div class='col-sm-8'>
                                <input type='text' class='form-control' name='actions_expire_interval' value='" . get_config('actions_expire_interval') . "'>
                           </div>
                        </div>                         
                    </fieldset>
                </div>
            </div>
            <div class='panel panel-default' id='eight'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langDefaultQuota</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='doc_quota' class='col-sm-4 control-label'>$langDocQuota (Mb):</label>
                           <div class='col-sm-8'>
                                <input class='FormData_InputText form-control' type='text' name='doc_quota' id='doc_quota' value='" . get_config('doc_quota') . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='video_quota' class='col-sm-4 control-label'>$langVideoQuota (Mb):</label>
                           <div class='col-sm-8'>
                                <input class='FormData_InputText form-control' type='text' name='video_quota' id='video_quota' value='" . get_config('video_quota') . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='group_quota' class='col-sm-4 control-label'>$langGroupQuota (Mb):</label>
                           <div class='col-sm-8'>
                                <input class='FormData_InputText form-control' type='text' name='group_quota' id='group_quota' value='" . get_config('group_quota') . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='dropbox_quota' class='col-sm-4 control-label'>$langDropboxQuota (Mb):</label>
                           <div class='col-sm-8'>
                                <input class='FormData_InputText form-control' type='text' name='dropbox_quota' id='dropbox_quota' value='" . get_config('dropbox_quota') . "'>
                           </div>
                        </div>                         
                    </fieldset>
                </div>
            </div>
            <div class='panel panel-default' id='nine'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langUploadWhitelist</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='student_upload_whitelist' class='col-sm-4 control-label'>$langStudentUploadWhitelist:</label>
                           <div class='col-sm-8'>
                                <textarea class='form-control' rows='6' name='student_upload_whitelist' id='student_upload_whitelist'>" . get_config('student_upload_whitelist') . "</textarea>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='teacher_upload_whitelist' class='col-sm-4 control-label'>$langTeacherUploadWhitelist:</label>
                           <div class='col-sm-8'>
                                <textarea class='form-control' rows='6' name='teacher_upload_whitelist' id='teacher_upload_whitelist'>" . get_config('teacher_upload_whitelist') . "</textarea>
                           </div>
                        </div>                         
                    </fieldset>
                </div>
            </div>";

    $cbox_disable_log_actions = get_config('disable_log_actions') ? 'checked' : '';
    $cbox_disable_log_course_actions = get_config('disable_log_course_actions') ? 'checked' : '';
    $cbox_disable_log_system_actions = get_config('disable_log_system_actions') ? 'checked' : '';
    
$tool_content .= "    
            <div class='panel panel-default' id='ten'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langLogActions</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='disable_log_actions' value='1' $cbox_disable_log_actions>
                                        $lang_disable_log_actions
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='disable_log_course_actions' value='1' $cbox_disable_log_course_actions>
                                        $lang_disable_log_course_actions
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='disable_log_system_actions' value='1' $cbox_disable_log_system_actions>
                                        $lang_disable_log_system_actions
                                    </label>
                                </div>                                
                            </div>
                        </div>
                        <hr><br>
                        <div class='form-group'>
                           <label for='log_expire_interval' class='col-sm-4 control-label'>$langLogExpireInterval ($langMonthsUnit):</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='log_expire_interval' id='log_expire_interval' value='" . get_config('log_expire_interval') . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='log_purge_interval' class='col-sm-4 control-label'>$langLogPurgeInterval ($langMonthsUnit):</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='log_purge_interval' id='log_purge_interval' value='" . get_config('log_purge_interval') . "'>
                           </div>
                        </div>                        
                    </fieldset>
                </div>
            </div>
            <div class='panel panel-default' id='eleven'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langLoginFailCheck</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input id='login_fail_check' type='checkbox' name='login_fail_check' value='1' $cbox_login_fail_check>
                                        $langEnableLoginFailCheck
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='form-group' id='login_fail_threshold'>
                           <label for='login_fail_threshold' class='col-sm-4 control-label'>$langLoginFailThreshold:</label>
                           <div class='col-sm-8'>
                                <input class='FormData_InputText form-control' type='text' name='login_fail_threshold' id='login_fail_threshold' value='" . get_config('login_fail_threshold') . "'>
                           </div>                          
                        </div>
                        <div class='form-group' id='login_fail_deny_interval'>
                           <label for='login_fail_deny_interval' class='col-sm-4 control-label'>$langLoginFailDenyInterval ($langMinute):</label>
                           <div class='col-sm-8'>
                                <input class='FormData_InputText form-control' type='text' name='login_fail_deny_interval' id='login_fail_deny_interval' value='" . get_config('login_fail_deny_interval') . "'>
                           </div>                          
                        </div>
                        <div class='form-group' id='login_fail_forgive_interval'>
                           <label for='login_fail_forgive_interval' class='col-sm-4 control-label'>$langLoginFailForgiveInterval ($langHours):</label>
                           <div class='col-sm-8'>
                                <input class='FormData_InputText form-control' type='text' name='login_fail_forgive_interval' id='login_fail_forgive_interval' value='" . get_config('login_fail_forgive_interval') . "'>
                           </div>                          
                        </div>                         
                    </fieldset>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-12'>            
                    <input class='btn btn-primary' type='submit' name='submit' value='$langModify'>
                    <a class='btn btn-default' href='index.php'>$langCancel</a>
                </div>
            </div>
        </form>
    </div>";
    $head_content .= "
        <script>
        $(function() {
            $('#floatMenu').affix({
              offset: {
                top: 230,
                bottom: function () {
                  return (this.bottom = $('.footer').outerHeight(true))
                }
              }
            })
        });
        </script>";
    $tool_content .= "
        <div class='col-sm-3 hidden-xs' id='affixedSideNav'>
            <ul id='floatMenu' class='nav nav-pills nav-stacked well well-sm' role='tablist'>
                <li class='active'><a href='#one'>$langBasicCfgSetting</a></li>
                <li><a href='#two'>$langUpgReg</a></li>
                <li><a href='#three'>$langEclassThemes</a></li>
                <li><a href='#four'>$langEmailSettings</a></li>
                <li><a href='#five'>$langCourseSettings</a></li>
                <li><a href='#six'>$langMetaCommentary</a></li>
                <li><a href='#seven'>$langOtherOptions</a></li>
                <li><a href='#eight'>$langDefaultQuota</a></li>
                <li><a href='#nine'>$langUploadWhitelist</a></li>
                <li><a href='#ten'>$langLogActions</a></li>
                <li><a href='#eleven'>$langLoginFailCheck</a></li>     
            </ul>
        </div>
    </div>";
    // Modal dialogs
    $tool_content .= modalConfirmation('confirmIndexDialog', 'confirmIndexLabel', $langConfirmEnableIndexTitle, $langConfirmEnableIndex, 'confirmIndexCancel', 'confirmIndexOk');
    $tool_content .= modalConfirmation('confirmMobileAPIDialog', 'confirmMobileAPILabel', $langConfirmEnableMobileAPITitle, $langConfirmEnableMobileAPI, 'confirmMobileAPICancel', 'confirmMobileAPIOk');

    // After restored values have been inserted into form then bring back
    // values from original config.php, so the rest of the page can be displayed correctly
    if (isset($_GET['restore']) && $_GET['restore'] == "yes") {
        @include('config/config.php');
    }
}

draw($tool_content, 3, null, $head_content);
