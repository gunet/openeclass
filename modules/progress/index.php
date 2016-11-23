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
require_once 'CommentEvent.php';
require_once 'BlogEvent.php';
require_once 'WikiEvent.php';
require_once 'ForumEvent.php';
require_once 'LearningPathEvent.php';
require_once 'RatingEvent.php';
require_once 'ViewingEvent.php';

$toolName = $langCertificates;

//Datepicker
load_js('tools.js');
load_js('jquery');
load_js('bootstrap-datetimepicker');
load_js('datatables');
load_js('datatables_filtering_delay');

@$head_content .= "
<script type='text/javascript'>
$(function() {
    $('#startdatepicker, #enddatepicker').datetimepicker({
            format: 'dd-mm-yyyy',
            pickerPosition: 'bottom-left',
            language: '".$language."',
            autoclose: true
        });
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
    $certificate_id = $_REQUEST['certificate_id'];
    $certificate = Database::get()->querySingle("SELECT * FROM certificate WHERE id = ?d", $certificate_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
    $pageName = $langEditChange;
}


if ($is_editor) {
    if (isset($_GET['vis'])) { // activate or deactivate certificate
        modify_certificate_visility($certificate_id, $_GET['vis']);        
        Session::Messages($langGlossaryUpdated, 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code");
    }        
    if (isset($_POST['newCertificate'])) {  //add a new certificate
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));                        
        $v->labels(array(
            'title' => "$langTheField $langTitle",
        ));
        if($v->validate()) {
            add_certificate($_POST['title'], $_POST['description'], $_POST['message'], $_POST['template'], $_POST['issuer'], $_POST['active']);
            Session::Messages("$langNewCertificateSuc", 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/progress/index.php?course=$course_code&new=1");
        }
    } elseif (isset($_POST['editCertificate'])) { // modify certificate
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));                        
        $v->labels(array(
            'title' => "$langTheField $langTitle",
        ));
        if($v->validate()) {
            $active = isset($_POST['active']) ? 1 : 0;
            modify_certificate($certificate_id, $_POST['title'], $_POST['description'], $_POST['message'], $_POST['template'], $_POST['issuer'], $active);
            Session::Messages("$langQuotaSuccess", 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/progress/index.php?course=$course_code&edit=1");
        }
    }
    
    
    elseif(isset($_POST['add_assignment'])) { // add assignment activity in certificate
        add_assignment_to_certificate($certificate_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');        
    } elseif (isset($_POST['add_exercise'])) { // add exericise activity in certificate
        add_exercise_to_certificate($certificate_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');        
    } elseif (isset($_POST['add_lp'])) { // add learning path activity in certificate
        add_lp_to_certificate($certificate_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
    }


    // Top menu
    $tool_content .= "<div class='row'><div class='col-sm-12'>";

    if(isset($_GET['edit'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id", "name" => $certificate->title);
        $pageName = $langConfig;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['modify'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id", "name" => $certificate->title);
        $pageName = $langEditChange;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['add'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id", "name" => $certificate->title);
        if (isset($_GET['addActivityAs'])) {
            $pageName = "$langAdd $langInsertWork";
        } elseif (isset($_GET['addActivityEx'])) {
            $pageName = "$langAdd $langInsertExercise";
        } elseif (isset($_GET['addActivityLp'])) {
            $pageName = "$langAdd $langLearningPath1";
        } else {
            $pageName = $langGradebookAddActivity;
        }
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['new'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
        $pageName = $langNewCertificate;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } elseif (isset($_GET['certificate_id']) && $is_editor) {
        $pageName = get_certificate_title($certificate_id);
    } elseif (!isset($_GET['certificate_id'])) {
        $tool_content .= action_bar(
            array(
                array('title' => "$langNewCertificate",
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;new=1",
                      'icon' => 'fa-plus',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success')));
    }
    $tool_content .= "</div></div>";

    //end of the top menu
   

    //FORM: create / edit new activity
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])) {
        add_certificate_other_activity($certificate_id);
        $display = FALSE;
    }
    
    //UPDATE/INSERT DB: add or edit activity to attendance module (edit concerns and course activities like lps)
    elseif(isset($_POST['submitCertificateActivity'])) {

        $threshold = isset($_POST['threshold']) ? $_POST['threshold'] : 0;
        $operator = isset($_POST['operator']) ? $_POST['operator'] : 0;

        if(isset($_POST['type'])){
          $type = $_POST['type'];
          if($type == MODULE_ID_BLOG){
            $activity = "blog";
          }
          if($type == MODULE_ID_COMMENTS){
            $activity = CommentEvent::BLOG_ACTIVITY;
          }
          if($type == "38a"){
            $type = MODULE_ID_COMMENTS;
            $activity = CommentEvent::COURSE_ACTIVITY;
          }
          if($type == MODULE_ID_FORUM){
            $activity = "forum";
          }
          if($type == MODULE_ID_RATING){
            $activity = RatingEvent::SOCIALBOOKMARK_ACTIVITY;
          }
          if($type == "39a"){
            $type = MODULE_ID_RATING;
            $activity = RatingEvent::FORUM_ACTIVITY;
          }
          if($type == MODULE_ID_WIKI){
            $activity = "wiki";
          }
          Database::get()->query("INSERT INTO certificate_criterion
                                      SET certificate = ?d, activity_type = ?s, module = ?s, `operator` = ?s, threshold = ?f",
                                  $certificate_id, $activity, $type, $operator, $threshold);
        }elseif ($_POST['id']) {
            //update
            $id = $_POST['id'];
            Database::get()->query("UPDATE certificate_criterion SET `operator` = ?s, threshold = ?f
                                        WHERE id = ?d", $operator, $threshold, $id);
            Session::Messages("$langGradebookEdit", "alert-success");
            redirect_to_home_page("modules/progress/index.php?course=$course_code&certificate_id=$certificate_id");
        }

    }

    elseif (isset($_GET['delete'])) {
        delete_certificate_activity($certificate_id, getDirectReference($_GET['delete']));
        redirect_to_home_page("modules/progress/index.php?course=$course_code&certificate_id=$certificate_id");
    }
   
    elseif (isset($_GET['del_cert_id'])) {  //  delete certificate
        delete_certificate($_GET['del_cert_id']);
        Session::Messages("$langGlossaryDeleted", "alert-success");
        redirect_to_home_page("modules/progress/index.php?course=$course_code");
    }
    elseif (isset($_GET['new'])) {
        certificate_settings(); // create new certificate
        $display = FALSE;
    } elseif (isset($_GET['edit'])) { // edit certificate settings
        certificate_settings($certificate_id);
        $display = FALSE;
    } elseif (isset($_GET['add'])) { // insert certificate activity
        insert_activity($certificate_id, $_GET['act']);
        $display = FALSE;
    }

}

if (isset($display) and $display == TRUE) {
    // display certificate
    if (isset($certificate_id)) {
        if ($is_editor) {
            display_certificate_activities($certificate_id);
        } else {
            $pageName = $certificate->title;
            student_view_certificate($certificate_id); // student view
        }
    } else { // display all certifcates
        display_certificates();
    }
}

//Display content in template
draw($tool_content, 2, null, $head_content);
