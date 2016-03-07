<?php

/* ========================================================================
 * Open eClass 3.3
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
 * ======================================================================== */


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/modalconfirmation.php';
require_once 'include/mailconfig.php';

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

    // Login screen / link checkboxes
    $('#hide_login_check').click(function(event) {
        if (!$('#hide_login_check').is(":checked")) {
            $('#hide_login_link_check').prop('checked', false);
        }
        $('#hide_login_link_check').prop('disabled', !$('#hide_login_check').is(":checked"));
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

    // MyDocs checkboxes and inputs
    function mydocsCheckboxQuota(checkbox, input) {
        $(checkbox).change(function (event) {
            $(input).prop('disabled', !$(this).is(':checked'));
        }).change();
    }
    mydocsCheckboxQuota('#mydocs_teacher_enable_id', '#mydocs_teacher_quota_id');
    mydocsCheckboxQuota('#mydocs_student_enable_id', '#mydocs_student_quota_id');

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

    $('#social_sharing_links').change(function(event) {
        if ($('#social_sharing_links').is(":checked")) {
            if ($('#personal_blog_enable').is(":checked")) {
                $('#personal_blog_sharing_enable').prop('disabled', false);
            }
        } else {
            $('#personal_blog_sharing_enable').prop('disabled', true);
        }
    });

    if (!$('#social_sharing_links').is(":checked")) {
        $('#personal_blog_sharing_enable').prop('disabled', true);
    }

    $('#personal_blog_enable').change(function(event) {
        if ($('#personal_blog_enable').is(":checked")) {
            $('#personal_blog_commenting_enable').prop('disabled', false);
            $('#personal_blog_rating_enable').prop('disabled', false);
            if ($('#social_sharing_links').is(":checked")) {
                $('#personal_blog_sharing_enable').prop('disabled', false);
            }
        } else {
            $('#personal_blog_commenting_enable').prop('disabled', true);
            $('#personal_blog_rating_enable').prop('disabled', true);
            $('#personal_blog_sharing_enable').prop('disabled', true);
        }
    });

    if (!$('#personal_blog_enable').is(":checked")) {
        $('#personal_blog_commenting_enable').prop('disabled', true);
        $('#personal_blog_rating_enable').prop('disabled', true);
        $('#personal_blog_sharing_enable').prop('disabled', true);
    }

    $('input[name=submit]').click(function() {
        $('#personal_blog_commenting_enable').prop('disabled', false);
        $('#personal_blog_rating_enable').prop('disabled', false);
        $('#personal_blog_sharing_enable').prop('disabled', false);
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

    $('#registration_link').change(function() {
        var type = $(this).val();
        if (type == 'show_text') {
            $('#registration-info-block').show();
        } else {
            $('#registration-info-block').hide();
        }
    }).change();

    $mail_form_js
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

$data['registration_link_options'] = $registration_link_options = array('show' => $langShow, 'hide' => $langHide, 'show_text' => $langRegistrationShowText);

// Save new `config` table
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
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
        set_config('default_language', $_POST['default_language']);
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

    $config_vars = array('email_required' => true,
        'email_verification_required' => true,
        'am_required' => true,
        'dont_display_login_form' => true,
        'hide_login_link' => true,
        'dropbox_allow_student_to_student' => true,
        'dropbox_allow_personal_messages' => true,
        'personal_blog' => true,
        'personal_blog_commenting' => true,
        'personal_blog_rating' => true,
        'personal_blog_sharing' => true,
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
        'opencourses_enable' => true,
        'mydocs_student_enable' => true,
        'mydocs_teacher_enable' => true);

    register_posted_variables($config_vars, 'all', 'intval');

    if (isset($_POST['registration_link']) and
        isset($registration_link_options[$_POST['registration_link']])) {
        set_config('registration_link', $_POST['registration_link']);
    } else {
        set_config('registration_link', 'show');
    }

    if (isset($_POST['registration_info'])) {
        set_config('registration_info', purify($_POST['registration_info']));
    }

    store_mail_config();

    if (isset($_POST['mydocs_student_quota'])) {
        set_config('mydocs_student_quota', floatval($_POST['mydocs_student_quota']));
    }
    if (isset($_POST['mydocs_teacher_quota'])) {
        set_config('mydocs_teacher_quota', floatval($_POST['mydocs_teacher_quota']));
    }

    if (!in_array($_POST['course_guest'], array('on', 'off', 'link'))) {
        set_config('course_guest', 'off');
    } else {
        set_config('course_guest', $_POST['course_guest']);
    }

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
    // Display link to index.php
    $data['action_bar'] = 
        action_bar([
                        [
                            'title' => $langBack,
                            'url' => "index.php",
                            'icon' => 'fa-reply',
                            'level' => 'primary-label'
                        ]
                    ]);

    if (function_exists('imagettfbbox')) {
        $data['cbox_display_captcha'] = get_config('display_captcha') ? 'checked' : '';
        $data['message_display_captcha'] = $data['disable_display_captcha'] = '';
    } else {
        $data['cbox_display_captcha'] = '';
        $data['disable_display_captcha'] = 'disabled';
        $data['message_display_captcha'] = '<div>' . $lang_display_captcha_unsupported . '</div>';
    }


        $active_ui_languages = explode(' ', get_config('active_ui_languages'));
        $langdirs = active_subdirs($webDir . '/lang', 'messages.inc.php');
        $data['sel'] = [];
        $data['selectable_langs'] = [];
        $data['cbox_dont_display_login_form'] = get_config('dont_display_login_form') ? 'checked' : '';
        $data['cbox_hide_login_link'] = get_config('hide_login_link') ? 'checked' : '';
        foreach ($language_codes as $langcode => $langname) {
            if (in_array($langcode, $langdirs)) {
                $loclangname = $langNameOfLang[$langname];
                if (in_array($langcode, $active_ui_languages)) {
                    $data['selectable_langs'][$langcode] = $loclangname;
                }
                $checked = in_array($langcode, $active_ui_languages) ? ' checked' : '';
                $data['sel'][] = "<div class='checkbox'>
                            <label>
                                <input type='checkbox' name='av_lang[]' value='$langcode'$checked>
                                $loclangname
                            </label>
                        </div>";

            }
        }


    $data['cbox_course_multidep'] = get_config('course_multidep') ? 'checked' : '';
    $data['cbox_user_multidep']  = get_config('user_multidep') ? 'checked' : '';
    $data['cbox_restrict_owndep']  = get_config('restrict_owndep') ? 'checked' : '';
    $data['cbox_restrict_teacher_owndep']  = get_config('restrict_teacher_owndep') ? 'checked' : '';
    $data['cbox_allow_teacher_clone_course']  = get_config('allow_teacher_clone_course') ? 'checked' : '';
    $data['town_dis']  = get_config('restrict_owndep') ? '' : 'disabled';
    $data['cbox_insert_xml_metadata']  = get_config('insert_xml_metadata') ? 'checked' : '';
    $data['cbox_course_metadata']  = get_config('course_metadata') ? 'checked' : '';
    $data['cbox_opencourses_enable']  = get_config('opencourses_enable') ? 'checked' : '';

  

        
    $data['cbox_case_insensitive_usernames'] = get_config('case_insensitive_usernames') ? 'checked' : '';
    $data['cbox_email_required'] = get_config('email_required') ? 'checked' : '';
    $data['cbox_email_verification_required'] = get_config('email_verification_required') ? 'checked' : '';
    $data['cbox_am_required'] = get_config('am_required') ? 'checked' : '';
    $data['cbox_dropbox_allow_student_to_student'] = get_config('dropbox_allow_student_to_student') ? 'checked' : '';
    $data['cbox_dropbox_allow_personal_messages'] = get_config('dropbox_allow_personal_messages') ? 'checked' : '';
    $data['cbox_personal_blog'] = get_config('personal_blog') ? 'checked' : '';
    $data['cbox_personal_blog_commenting'] = get_config('personal_blog_commenting') ? 'checked' : '';
    $data['cbox_personal_blog_rating'] = get_config('personal_blog_rating') ? 'checked' : '';
    $data['cbox_personal_blog_sharing'] = get_config('personal_blog_sharing') ? 'checked' : '';
    $data['cbox_block_username_change'] = get_config('block_username_change') ? 'checked' : '';
    $data['cbox_enable_mobileapi'] = get_config('enable_mobileapi') ? 'checked' : '';
    $data['max_glossary_terms'] = get_config('max_glossary_terms');
    $data['cbox_enable_indexing'] = get_config('enable_indexing') ? 'checked' : '';
    $data['cbox_enable_search'] = get_config('enable_search') ? 'checked' : '';
    $data['cbox_enable_common_docs'] = get_config('enable_common_docs') ? 'checked' : '';
    $data['cbox_mydocs_student_enable'] = get_config('mydocs_student_enable') ? 'checked' : '';
    $data['cbox_mydocs_teacher_enable'] = get_config('mydocs_teacher_enable') ? 'checked' : '';
    $data['mydocs_student_quota'] = floatval(get_config('mydocs_student_quota'));
    $data['mydocs_teacher_quota'] = floatval(get_config('mydocs_teacher_quota'));
    $data['cbox_enable_social_sharing_links'] = get_config('enable_social_sharing_links') ? 'checked' : '';
    $data['cbox_login_fail_check'] = get_config('login_fail_check') ? 'checked' : '';
    $data['id_enable_mobileapi'] = (check_auth_active(7) || check_auth_active(6)) ? "id='mobileapi_enable'" : '';



    $data['cbox_disable_log_actions'] = get_config('disable_log_actions') ? 'checked' : '';
    $data['cbox_disable_log_course_actions'] = get_config('disable_log_course_actions') ? 'checked' : '';
    $data['cbox_disable_log_system_actions'] = get_config('disable_log_system_actions') ? 'checked' : '';


}
$data['menuTypeID'] = 3;
view('admin.other.eclassconf', $data);
//draw($tool_content, 3, null, $head_content);
