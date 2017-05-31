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

$require_login = true;
$require_current_course = true;
$require_help = true;
$helpTopic = 'Progress';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'functions.php';
require_once 'process_functions.php';
require_once 'ExerciseEvent.php';
require_once 'AssignmentEvent.php';
require_once 'CommentEvent.php';
require_once 'BlogEvent.php';
require_once 'WikiEvent.php';
require_once 'ForumEvent.php';
require_once 'LearningPathEvent.php';
require_once 'RatingEvent.php';
require_once 'ViewingEvent.php';
require_once 'CourseParticipationEvent.php';

$toolName = $langCertificates;

load_js('tools.js');
load_js('jquery');
load_js('datatables');
load_js('datatables_filtering_delay');

@$head_content .= "
<script type='text/javascript'>
$(function() {    
    var oTable = $('#users_table{$course_id}').DataTable ({
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],
               'fnDrawCallback': function( oSettings ) {
                            $('#users_table{$course_id}_wrapper label input').attr({
                              class : 'form-control input-sm',
                              placeholder : '$langSearch...'
                            });
                        },
               'sPaginationType': 'full_numbers',
                'bSort': true,
                'oLanguage': {
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                       'sInfoFiltered': '',
                       'sInfoPostFix':  '',
                       'sSearch':       '',
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
    });
    $('#user_attendances_form').on('submit', function (e) {
        oTable.rows().nodes().page.len(-1).draw();
    });
$('input[id=button_groups]').click(changeAssignLabel);
    $('input[id=button_some_users]').click(changeAssignLabel);
    $('input[id=button_some_users]').click(ajaxParticipants);
    $('input[id=button_all_users]').click(hideParticipants);
    function hideParticipants()
    {
        $('#participants_tbl').addClass('hide');
        $('#users_box').find('option').remove();
        $('#all_users').show();
    }
    function changeAssignLabel()
    {
        var assign_to_specific = $('input:radio[name=specific_attendance_users]:checked').val();
        if(assign_to_specific>0){
           ajaxParticipants();
        }
        if (this.id=='button_groups') {
           $('#users').text('$langGroups');
        }
        if (this.id=='button_some_users') {
           $('#users').text('$langUsers');
        }
    }
    function ajaxParticipants()
    {
        $('#all_users').hide();
        $('#participants_tbl').removeClass('hide');
        var type = $('input:radio[name=specific_attendance_users]:checked').val();
        $.post('$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=".q($_REQUEST['attendance_id'])."&editUsers=1',
        {
          assign_type: type
        },
        function(data,status){
            var index;
            var parsed_data = JSON.parse(data);
            var select_content = '';
            var select_content_2 = '';
            if (type==2) {
                for (index = 0; index < parsed_data.length; ++index) {
                    select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['name'] + '<\/option>';
                }
            }
            if (type==1) {
                for (index = 0; index < parsed_data[0].length; ++index) {
                    select_content += '<option value=\"' + parsed_data[0][index]['id'] + '\">' + parsed_data[0][index]['surname'] + ' ' + parsed_data[0][index]['givenname'] + '<\/option>';
                }
                for (index = 0; index < parsed_data[1].length; ++index) {
                    select_content_2 += '<option value=\"' + parsed_data[1][index]['id'] + '\">' + parsed_data[1][index]['surname'] + ' ' + parsed_data[1][index]['givenname'] + '<\/option>';
                }
            }
            $('#users_box').find('option').remove().end().append(select_content);
            $('#participants_box').find('option').remove().end().append(select_content_2);

        });
    }
});
</script>";

$display = TRUE;
if (isset($_REQUEST['certificate_id'])) {
    $param_name = 'certificate_id';
    $element_id = $_REQUEST['certificate_id'];
    $element = 'certificate';
    $element_title = get_title($element, $element_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
}

if (isset($_REQUEST['badge_id'])) {
    $param_name = 'badge_id';
    $element_id = $_REQUEST['badge_id'];
    $element = 'badge';
    $element_title = get_title($element, $element_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
}

if ($is_editor) {
    
    // Top menu
    $tool_content .= "<div class='row'><div class='col-sm-12'>";
    if(isset($_GET['edit'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $pageName = $langConfig;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['act_mod'])) { // modify certificate activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $pageName = $langEditChange;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['add'])) { // add certificate activity
            $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);        
            $pageName = "$langAdd $langOfGradebookActivity";
            $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                      'icon' => 'fa fa-reply',
                      'level' => 'primary-label')
                ));
    } elseif (isset($_GET['newcert'])) { // new certificate activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
        $pageName = $langNewCertificate;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } elseif (isset($_GET['newbadge'])) { // new badge activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
        $pageName = $langNewBadge;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } elseif (isset($_GET['u'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;progressall=true", "name" => $langUsers);
        $pageName = "$langProgress $langsOfStudent";
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;progressall=true",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } elseif (isset($_GET['progressall'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);        
        $pageName = "$langProgress $langsOfStudents";
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));        
    } elseif (isset($_GET['preview'])) { // certificate preview
        cert_output_to_pdf($element_id, $uid);
    } elseif (!(isset($_REQUEST['certificate_id']) or (isset($_REQUEST['badge_id'])))) {
        $tool_content .= action_bar(
            array(
                array('title' => "$langBack",
                      'url' => "{$urlServer}courses/$course_code/index.php",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));
    }
    $tool_content .= "</div></div>";
    //end of the top menu
        
    if (isset($_GET['vis'])) { // activate or deactivate certificate
        if (has_activity($element, $element_id) > 0) {
            update_visibility($element, $element_id, $_GET['vis']);        
            Session::Messages($langGlossaryUpdated, 'alert-success');
        } else {
            Session::Messages($langNotActivated, 'alert-warning');
        }
        redirect_to_home_page("modules/progress/index.php?course=$course_code");
    }        
    if (isset($_POST['newCertificate']) or isset($_POST['newBadge'])) {  //add a new certificate / badge
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));                        
        $v->labels(array(
            'title' => "$langTheField $langTitle",
        ));
        if($v->validate()) {
            $table = (isset($_POST['newCertificate'])) ? 'certificate' : 'badge';
            $icon  = $_POST['template'];
            add_certificate($table, $_POST['title'], $_POST['description'], $_POST['message'], $icon, $_POST['issuer'], 0);
            Session::Messages("$langNewCertificateSuc", 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/progress/index.php?course=$course_code&new=1");
        }
    } elseif (isset($_POST['edit_element'])) { // modify certificate / badge
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));                        
        $v->labels(array(
            'title' => "$langTheField $langTitle",
        ));
        if($v->validate()) {
            //$active = isset($_POST['active']) ? 1 : 0;           
            //modify($element, $element_id, $_POST['title'], $_POST['description'], $_POST['message'], $_POST['template'], $_POST['issuer'], $active);
            modify($element, $element_id, $_POST['title'], $_POST['description'], $_POST['message'], $_POST['template'], $_POST['issuer']);
            Session::Messages("$langQuotaSuccess", 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/progress/index.php?course=$course_code&edit=1");
        }
    } elseif (isset($_POST['mod_cert_activity'])) { // modify certificate activity
        modify_certificate_activity($element, $element_id, $_POST['activity_id']);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    }
        // add resources to certificate
    elseif(isset($_POST['add_assignment'])) { // add assignment activity in certificate
        add_assignment_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_exercise'])) { // add exercise activity in certificate
        add_exercise_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_lp'])) { // add learning path activity in certificate
        add_lp_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_document'])) { // add document activity in certificate
        add_document_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');        
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_multimedia'])) { // add multimedia activity in certificate
        add_multimedia_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_poll'])) { // add poll activity in certificate
        add_poll_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_wiki'])) { // add wiki activity in certificate
        add_wiki_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_ebook'])) { // add ebook activity in certificate
        add_ebook_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_forum'])) { // add forum activity in certificate
        add_forum_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_blog'])) {
        add_blog_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_blogcomment'])) {
        add_blogcomment_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_participation'])) {
        add_courseparticipation_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    }
    
    elseif (isset($_GET['del_cert_res'])) { // delete certificate / badge activity
        if (resource_usage($element, $_GET['del_cert_res'])) { // check if resource has been used by user
            Session::Messages("$langUsedCertRes", "alert-warning");
            redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
        } else { // delete it otherwise
            delete_activity($element, $element_id, $_GET['del_cert_res']);
            Session::Messages("$langAttendanceDel", "alert-success");
            redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
        }
    } elseif (isset($_GET['del_cert'])) {  //  delete certificate 
        if (delete_certificate('certificate', $_GET['del_cert'])) {
            Session::Messages("$langGlossaryDeleted", "alert-success");
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::Messages("$langUsedCertRes", "alert-warning");
        }
    } elseif (isset($_GET['del_badge'])) {  //  badge
        if (delete_certificate('badge', $_GET['del_badge'])) {
            Session::Messages("$langGlossaryDeleted", "alert-success");
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::Messages("$langUsedCertRes", "alert-warning");
        }        
    } elseif (isset($_GET['newcert'])) {
        certificate_settings('certificate'); // create new certificate
        $display = FALSE;
    }  elseif (isset($_GET['newbadge'])) {
        certificate_settings('badge'); // create new badge
        $display = FALSE;
    } elseif (isset($_GET['edit'])) { // edit certificate /badge settings
        certificate_settings($element, $element_id);
        $display = FALSE;
    } elseif (isset($_GET['add']) and isset($_GET['act'])) { // insert certificate / badge activity
        insert_activity($element, $element_id, $_GET['act']);
        $display = FALSE;
    } elseif (isset($_GET['act_mod'])) { // modify certificate / badge activity
        display_modification_activity($element, $element_id, $_GET['act_mod']);
        $display = FALSE;
    } elseif (isset($_GET['progressall'])) { // display users progress (teacher view)
        display_users_progress($element, $element_id);
        $display = FALSE;
    } elseif (isset($_GET['u'])) { // display detailed user progress
        display_user_progress_details($element, $element_id, $_GET['u']);
        $display = FALSE;
    }
} elseif (isset($_GET['u'])) { // student view
        $pageName = $element_title;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
}

if (isset($display) and $display == TRUE) {
    if ($is_editor) {
        if (isset($element_id)) {
            $pageName = $element_title;
            // display certificate settings and resources            
            display_activities($element, $element_id);
        } else { // display all certificates         
            display_certificates();
            display_badges();
        }
    } else {        
        check_user_details($uid); // security check
        if (isset($element_id)) {            
            if (isset($_GET['p']) and $_GET['p']) {
                check_cert_details($uid, $element, $element_id); // security check
                cert_output_to_pdf($element_id, $uid);
            } else {
                $pageName = $element_title;
                // display detailed user progress
                display_user_progress_details($element, $element_id, $uid);
            }
        } else {
            // display certificate (student view)
            student_view_progress();
            exit;
        }
    }
}

draw($tool_content, 2, null, $head_content);