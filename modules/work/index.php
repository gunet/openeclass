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
 * ========================================================================

  ============================================================================
  @Description: Main script for the work tool
  ============================================================================
 */

$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'Work';

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'work_functions.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/gradebook/functions.php';
require_once 'modules/attendance/functions.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'include/lib/csv.class.php';
require_once 'modules/progress/AssignmentEvent.php';

// For colorbox, fancybox, shadowbox use
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();
/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_ASSIGN);
/* * *********************************** */

require_once 'modules/usage/usage.lib.php';
$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.4.10/c3.css' />";
load_js('d3/d3.min.js');
load_js('c3-0.4.10/c3.min.js');

$workPath = $webDir . "/courses/" . $course_code . "/work";
$works_url = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langWorks);
$toolName = $langWorks;

//-------------------------------------------
// main program
//-------------------------------------------
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    
    if (isset($_POST['group_filter'])) {
        $result = Database::get()->queryArray("SELECT user_id FROM `group_members` WHERE group_id = ?d", $_POST['group_filter']);
        $data = [];
        foreach ($result as $row) {
            $data[] = $row->user_id;
        }
    }
    if (isset($_POST['sid'])) {
        $sid = $_POST['sid'];
        $data['submission_text'] = Database::get()->querySingle("SELECT submission_text FROM assignment_submit WHERE id = ?d", $sid)->submission_text;
    }
    if (isset($_POST['assign_type'])) {
        if ($_POST['assign_type']) {
            $data = Database::get()->queryArray("SELECT name,id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
        } else {
            $data = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                    FROM user, course_user
                                    WHERE user.id = course_user.user_id
                                    AND course_user.course_id = ?d AND course_user.status = 5
                                    AND user.id ORDER BY surname", $course_id);

        }
    }
    echo json_encode($data);
    exit;
}

//Gets the student's assignment file ($file_type=NULL)
//or the teacher's assignment ($file_type=1)
if (isset($_GET['get'])) {
    if (isset($_GET['file_type']) && $_GET['file_type']==1) {
        $file_type = intval($_GET['file_type']);
    } else {
        $file_type = NULL;
    }
    if (!send_file(intval($_GET['get']), $file_type)) {
        Session::Messages($langFileNotFound, 'alert-danger');
    }
}

// Only course admins can download all assignments in a zip file
if ($is_editor) {
    if (isset($_GET['download'])) {
        $as_id = intval($_GET['download']);
        // Allow unlimited time for creating the archive
        set_time_limit(0);
        if (!download_assignments($as_id)) {
            Session::Messages($langNoAssignmentsExist, 'alert-danger');
            redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$as_id);
        }
    }
}

if ($is_editor) {
    load_js('tools.js');
    global $themeimg, $m;
    $head_content .= "
    <script type='text/javascript'>
    ";
    if(AutojudgeApp::getAutojudge()->isEnabled()) {
    $head_content .= "
    function check_weights() {
        /* function to check weight validity */
        if($('#hidden-opt').is(':visible') && $('#auto_judge').is(':checked')) {
            var weights = document.getElementsByClassName('auto_judge_weight');
            var weight_sum = 0;
            var max_grade = parseFloat(document.getElementById('max_grade').value);
            max_grade = Math.round(max_grade * 1000) / 1000;

            for (i = 0; i < weights.length; i++) {
                // match ints or floats
                w = weights[i].value.match(/^\d+\.\d+$|^\d+$/);
                if(w != null) {
                    w = parseFloat(w);
                    if(w >= 0  && w <= max_grade)  // 0->max_grade allowed
                    {
                        /* allow 3 decimal digits */
                        weight_sum += w;
                        continue;
                    }
                    else{
                        alert('Weights must be between 1 and max_grade!');
                        return false;
                    }
                }
                else {
                    alert('Only numbers as weights!');
                    return false;
                }
            }
            diff = Math.round((max_grade - weight_sum) * 1000) / 1000;
            if (diff >= 0 && diff <= 0.001) {
                return true;
            }
            else {
                alert('Weights do not sum up to ' + max_grade +
                    '!\\n(Remember, 3 decimal digits precision)');
                return false;
            }
        }
        else
            return true;
    }
    function updateWeightsSum() {
        var weights = document.getElementsByClassName('auto_judge_weight');
        var weight_sum = 0;
        var max_grade = parseFloat(document.getElementById('max_grade').value);
        max_grade = Math.round(max_grade * 1000) / 1000;

        for (i = 0; i < weights.length; i++) {
            // match ints or floats
            w = weights[i].value.match(/^\d+\.\d+$|^\d+$/);
            if(w != null) {
                w = parseFloat(w);
                if(w >= 0  && w <= max_grade)  // 0->max_grade allowed
                {
                    /* allow 3 decimal digits */
                    weight_sum += w;
                    continue;
                }
                else{
                    $('#weights-sum').html('-');
                    $('#weights-sum').css('color', 'red');
                    return;
                }
            }
            else {
                $('#weights-sum').html('-');
                $('#weights-sum').css('color', 'red');
                return;
            }
        }
        $('#weights-sum').html(weight_sum);
        diff = Math.round((max_grade - weight_sum) * 1000) / 1000;
        if (diff >= 0 && diff <= 0.001) {
            $('#weights-sum').css('color', 'green');
        } else {
            $('#weights-sum').css('color', 'red');
        }
    }
    $(document).ready(function() {
        updateWeightsSum();
        $('.auto_judge_weight').change(updateWeightsSum);
        $('#max_grade').change(updateWeightsSum);
    });
    ";
    }
    $head_content .= "
    $(function() {
        $('.onlineText').click( function(e){
            e.preventDefault();
            var sid = $(this).data('id');
            $.ajax({
              type: 'POST',
              url: '',
              datatype: 'json',
              data: {
                 sid: sid
              },
              success: function(data){
                data = $.parseJSON(data);
                bootbox.alert({
                    size: 'large',
                    message: data.submission_text ? data.submission_text : '',
                });
              },
              error: function(xhr, textStatus, error){
                  console.log(xhr.statusText);
                  console.log(textStatus);
                  console.log(error);
              }
            });
        });
        $('input[name=group_submissions]').click(changeAssignLabel);
        $('input[id=assign_button_some]').click(ajaxAssignees);
        $('input[id=assign_button_all]').click(hideAssignees);
        ";
        if(AutojudgeApp::getAutojudge()->isEnabled()) {
        $head_content .= "
        $('input[name=auto_judge]').click(changeAutojudgeScenariosVisibility);
        $(document).ready(function() { changeAutojudgeScenariosVisibility.apply($('input[name=auto_judge]')); });
        ";
        }
        $head_content .= "
        function hideAssignees()
        {
            $('#assignees_tbl').addClass('hide');
            $('#groupFiltering').addClass('hide').find('#groupFilter').val('0');
            $('#assignee_box').find('option').remove();
        }
        function changeAssignLabel()
        {
            var assign_to_specific = $('input:radio[name=assign_to_specific]:checked').val();
            if(assign_to_specific==1){
               ajaxAssignees();
            }
            if (this.id=='group_button') {
               $('#assign_button_all_text').text('$m[WorkToAllGroups]');
               $('#assign_button_some_text').text('$m[WorkToGroup]');
               $('#assignees').text('$langGroups');
            } else {
               $('#assign_button_all_text').text('$m[WorkToAllUsers]');
               $('#assign_button_some_text').text('$m[WorkToUser]');
               $('#assignees').text('$langStudents');
            }
        }
        $('#groupFilter').change(function() {
            if ($(this).val() == 0) {
                    $('#assign_box option').each(function()
                    {
                        $(this).show();
                    });                
            } else {
                $.ajax({
                  type: 'POST',
                  url: '',
                  datatype: 'json',
                  data: {
                     group_filter : $(this).val()
                  },
                  success: function(data){
                    data = $.parseJSON(data);
                    $('#assign_box option').each(function()
                    {
                        if($.inArray($(this).val(), data) >= 0) { 
                            $(this).show();
                        } else {
                            $(this).hide();
                        }

                    });
                  },
                  error: function(xhr, textStatus, error){
                      console.log(xhr.statusText);
                      console.log(textStatus);
                      console.log(error);
                  }
                });           
            }
        });
        function ajaxAssignees()
        {
            $('#assignees_tbl').removeClass('hide');
            var type = $('input:radio[name=group_submissions]:checked').val();
            $.post('$works_url[url]',
            {
              assign_type: type
            },
            function(data,status){
                var index;
                var parsed_data = JSON.parse(data);
                var select_content = '';
                if(type==0){
                    $('#groupFiltering').removeClass('hide');
                    for (index = 0; index < parsed_data.length; ++index) {
                        select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['surname'] + ' ' + parsed_data[index]['givenname'] + '<\/option>';
                    }
                } else {
                    $('#groupFiltering').addClass('hide').find('#groupFilter').val('0');
                    for (index = 0; index < parsed_data.length; ++index) {
                        select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['name'] + '<\/option>';
                    }
                }
                $('#assignee_box').find('option').remove();
                $('#assign_box').find('option').remove().end().append(select_content);
            });
        }";
        if(AutojudgeApp::getAutojudge()->isEnabled()) {
        $head_content .= "
        function changeAutojudgeScenariosVisibility() {
            if($(this).is(':checked')) {
                $(this).parent().parent().find('table').show();
                $('#lang').parent().parent().show();
            } else {
                $(this).parent().parent().find('table').hide();
                $('#lang').parent().parent().hide();
            }
        }
        $('#autojudge_new_scenario').click(function(e) {
            var rows = $(this).parent().parent().parent().find('tr').size()-1;
            // Clone the first line
            var newLine = $(this).parent().parent().parent().find('tr:first').clone();
            // Replace 0 wth the line number
            newLine.html(newLine.html().replace(/auto_judge_scenarios\[0\]/g, 'auto_judge_scenarios['+rows+']'));
            // Initialize the remove event and show the button
            newLine.find('.autojudge_remove_scenario').show();
            newLine.find('.autojudge_remove_scenario').click(removeRow);
            // Clear out any potential content
            newLine.find('input').val('');
            // Insert it just before the final line
            newLine.insertBefore($(this).parent().parent().parent().find('tr:last'));
            // Add the event handler
            newLine.find('.auto_judge_weight').change(updateWeightsSum);
            e.preventDefault();
            return false;
        });
        // Remove row
        function removeRow(e) {
            $(this).parent().parent().remove();
            e.preventDefault();
            return false;
        }
        $('.autojudge_remove_scenario').click(removeRow);
        $(document).on('change', 'select.auto_judge_assertion', function(e) {
            e.preventDefault();
            var value = $(this).val();

            // Change selected attr.
            $(this).find('option').each(function() {
                if ($(this).attr('selected') == 'selected') {
                    $(this).removeAttr('selected');
                } else if ($(this).attr('value') == value) {
                    $(this).attr('selected', true);
                }
            });
            var row       = $(this).parent().parent();
            var tableBody = $(this).parent().parent().parent();
            var indexNum  = row.index() + 1;

            if (value === 'eq' ||
                value === 'same' ||
                value === 'notEq' ||
                value === 'notSame' ||
                value === 'startsWith' ||
                value === 'endsWith' ||
                value === 'contains'
            ) {
                tableBody.find('tr:nth-child('+indexNum+')').find('input.auto_judge_output').removeAttr('disabled');
            } else {
                tableBody.find('tr:nth-child('+indexNum+')').find('input.auto_judge_output').val('');
                tableBody.find('tr:nth-child('+indexNum+')').find('input.auto_judge_output').attr('disabled', 'disabled');
            }
            return false;
        });
        ";
        }
        $head_content .= "
    });

    </script>";

    $head_content .= "<script type='text/javascript'>
            var gradesChartData = null;

            $(document).ready(function(){
                if(gradesChartData != null){
                    draw_plots();
                }
            });

        function draw_plots(){
            var options = null;
            options = {
                data: {
                    json: gradesChartData,
                    x: 'grade',
                    types:{
                        percentage: 'line'
                    },
                    axes: {percentage: 'y'},
                    names:{percentage:'%'},
                    colors:{percentage:'#e9d460'}
                },
                legend: {
                        show:false
                    },
                bar: {
                    width: {
                        ratio:0.8
                        }
                    },
                axis:{
                    x: {
                      type: 'category'
                    }, 
                    y: {
                       max: 100, 
                       min: 0, 
                       padding: {
                           top:0, 
                           bottom:0
                       }
                   }
                },
                bindto: '#grades_chart'
            };
            c3.generate(options);
    }
    </script>";
        
        
    $email_notify = (isset($_POST['email']) && $_POST['email']);
    if (isset($_POST['grade_comments'])) {
        $work_title = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", intval($_POST['assignment']))->title;
        $pageName = $work_title;
        $navigation[] = $works_url;
        submit_grade_comments($_POST);
    } elseif (isset($_GET['add'])) {
        $pageName = $langNewAssign;
        $navigation[] = $works_url;
        new_assignment();

    } elseif (isset($_POST['new_assign'])) {
        add_assignment();
    } elseif (isset($_GET['as_id'])) {
        $as_id = intval($_GET['as_id']);
        $id = intval($_GET['id']);
        if(delete_user_assignment($as_id)){
            Session::Messages($langDeleted, 'alert-success');
        } else {
            Session::Messages($langDelError, 'alert-danger');
        }
        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
    } elseif (isset($_POST['grades'])) {
        $navigation[] = $works_url;
        submit_grades(intval($_POST['grades_id']), $_POST['grades'], $email_notify);
    } elseif (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        $work_title = q(Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $id)->title);
        $work_id_url = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&id=$id",
            'name' => $work_title);
        if (isset($_POST['on_behalf_of'])) {
            if (isset($_POST['user_id'])) {
                $user_id = intval($_POST['user_id']);
            } else {
                $user_id = $uid;
            }
            $pageName = $langAddGrade;
            $navigation[] = $works_url;
            $navigation[] = $work_id_url;
            submit_work($id, $user_id);
        } elseif (isset($_REQUEST['choice'])) {
            $choice = $_REQUEST['choice'];
            if ($choice == 'disable') {
                if (!resource_belongs_to_progress_data(MODULE_ID_ASSIGN, $id)) {
                    if (Database::get()->query("UPDATE assignment SET active = '0' WHERE id = ?d", $id)->affectedRows > 0) {
                        Session::Messages($langAssignmentDeactivated, 'alert-success');
                    }
                } else {
                    Session::Messages($langResourceBelongsToCert, 'alert-warning');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'enable') {
                if (Database::get()->query("UPDATE assignment SET active = '1' WHERE id = ?d", $id)->affectedRows > 0) {
                    Session::Messages($langAssignmentActivated, 'alert-success');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'do_delete') {                
                    if (!resource_belongs_to_progress_data(MODULE_ID_ASSIGN, $id)) {
                        if(delete_assignment($id)) {
                            Session::Messages($langDeleted, 'alert-success');
                        } else {
                            Session::Messages($langDelError, 'alert-danger');
                        }
                    } else {
                        Session::Messages($langResourceBelongsToCert, 'alert-warning');
                    }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'do_delete_file') {
                if(delete_teacher_assignment_file($id)){
                    Session::Messages($langDelF, 'alert-success');
                } else {
                    Session::Messages($langDelF, 'alert-danger');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id.'&choice=edit');
            } elseif ($choice == 'do_purge') {
                if (!resource_belongs_to_progress_data(MODULE_ID_ASSIGN, $id)) {
                    if (purge_assignment_subs($id)) {
                        Session::Messages($langAssignmentSubsDeleted, 'alert-success');
                    }
                } else {
                    Session::Messages($langResourceBelongsToCert, 'alert-warning');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'edit') {
                $pageName = $m['WorkEdit'];
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                show_edit_assignment($id);
            } elseif ($choice == 'do_edit') {
                $pageName = $langWorks;
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                edit_assignment($id);
            } elseif ($choice == 'add') {
                $pageName = $langAddGrade;
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                show_submission_form($id, groups_with_no_submissions($id), true);
            } elseif ($choice == 'plain') {
                show_plain_view($id);
            } elseif ($choice == 'export') {
                export_grades_to_csv($id);
            }
        } else {
            $pageName = $work_title;
            $navigation[] = $works_url;
            if (isset($_GET['disp_results'])) {
                show_assignment($id, true);
            } elseif (isset($_GET['disp_non_submitted'])) {
                show_non_submitted($id);
            } else {
                show_assignment($id);
            }
        }
    } else {
        $pageName = $langWorks;
        show_assignments();
    }
} else {
    if (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        if (isset($_POST['work_submit'])) {
            $pageName = $m['SubmissionStatusWorkInfo'];
            $navigation[] = $works_url;
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id", 'name' => $langWorks);
            submit_work($id);
        } else {
            $work_title = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $id)->title;
            $pageName = $work_title;
            $navigation[] = $works_url;
            show_student_assignment($id);
        }
    } else {
        show_student_assignments();
    }
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);

//-------------------------------------
// end of main program
//-------------------------------------

/**
 * @brief insert the assignment into the database
 * @global type $tool_content
 * @global string $workPath
 * @global type $course_id
 * @global type $uid
 * @global type $langTheField
 * @global type $m
 * @global type $langTitle
 * @global type $course_code
 * @global type $langFormErrors
 * @global type $langNewAssignSuccess
 * @global type $langScales
 * @return type
 */
function add_assignment() {
    global $tool_content, $workPath, $course_id, $uid, $langTheField, $m, $langTitle,
        $course_code, $langFormErrors, $langNewAssignSuccess, $langScales;

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->rule('integer', array('group_submissions', 'assign_to_specific'));
    if (isset($_POST['max_grade'])) {
        $v->rule('required', array('max_grade'));
        $v->rule('numeric', array('max_grade'));
        $v->labels(array('max_grade' => "$langTheField $m[max_grade]"));
    }
    if (isset($_POST['scale'])) {
        $v->rule('required', array('scale'));
        $v->rule('numeric', array('scale'));
        $v->labels(array('scale' => "$langTheField $langScales"));
    }
    $v->labels(array('title' => "$langTheField $langTitle"));
    if($v->validate()) {
        $title = $_POST['title'];
        $desc = $_POST['desc'];
        $deadline = isset($_POST['WorkEnd']) && !empty($_POST['WorkEnd']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkEnd'])->format('Y-m-d H:i:s') : NULL;
        $submission_type = $_POST['submission_type'];
        $late_submission = isset($_POST['late_submission']) ? 1 : 0;
        $group_submissions = $_POST['group_submissions'];
        if (isset($_POST['scale'])) {
            $max_grade = max_grade_from_scale($_POST['scale']);
            $grading_scale_id = $_POST['scale'];
        } else {
            $max_grade = $_POST['max_grade'];
            $grading_scale_id = 0;
        }
        $assign_to_specific = $_POST['assign_to_specific'];
        $assigned_to = filter_input(INPUT_POST, 'ingroup', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
        $auto_judge           = isset($_POST['auto_judge']) ? filter_input(INPUT_POST, 'auto_judge', FILTER_VALIDATE_INT) : 0;
        $auto_judge_scenarios = isset($_POST['auto_judge_scenarios']) ? serialize($_POST['auto_judge_scenarios']) : "";
        $lang                 = isset($_POST['lang']) ? filter_input(INPUT_POST, 'lang') : '';
        $secret = uniqid('');

        if ($assign_to_specific == 1 && empty($assigned_to)) {
            $assign_to_specific = 0;
        }
        if (make_dir("$workPath/$secret") and make_dir("$workPath/admin_files/$secret")) {
            $id = Database::get()->query("INSERT INTO assignment (course_id, title, description, deadline, late_submission, comments, submission_type, submission_date, secret_directory, group_submissions, max_grade, grading_scale_id, assign_to_specific, auto_judge, auto_judge_scenarios, lang) "
                    . "VALUES (?d, ?s, ?s, ?t, ?d, ?s, ?d, ?t, ?s, ?d, ?f, ?d, ?d, ?d, ?s, ?s)", $course_id, $title, $desc, $deadline, $late_submission, '', $submission_type, date("Y-m-d H:i:s"), $secret, $group_submissions, $max_grade, $grading_scale_id, $assign_to_specific, $auto_judge, $auto_judge_scenarios, $lang)->lastInsertID;

            if ($id) {
                // tags
                if (isset($_POST['tags'])) {
                    $tagsArray = explode(',', $_POST['tags']);
                    $moduleTag = new ModuleElement($id);
                    $moduleTag->attachTags($tagsArray);
                }

                $secret = work_secret($id);

                $student_name = canonicalize_whitespace(uid_to_name($uid));
                $local_name = !empty($student_name)? $student_name : uid_to_name($uid, 'username');
                $am = Database::get()->querySingle("SELECT am FROM user WHERE id = ?d", $uid)->am;
                if (!empty($am)) {
                    $local_name .= $am;
                }
                $local_name = greek_to_latin($local_name);
                $local_name = replace_dangerous_char($local_name);
                if (!isset($_FILES) || !$_FILES['userfile']['size']) {
                    $_FILES['userfile']['name'] = '';
                    $_FILES['userfile']['tmp_name'] = '';
                } else {
                    validateUploadedFile($_FILES['userfile']['name'], 2);
                    if (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' . 'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' . 'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $_FILES['userfile']['name'])) {
                        $tool_content .= "<p class=\"caution\">$langUnwantedFiletype: {$_FILES['userfile']['name']}<br />";
                        $tool_content .= "<a href=\"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id\">$langBack</a></p><br />";
                        return;
                    }
                    $ext = get_file_extension($_FILES['userfile']['name']);
                    $filename = "$secret/$local_name" . (empty($ext) ? '' : '.' . $ext);
                    if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/admin_files/$filename")) {
                        @chmod("$workPath/admin_files/$filename", 0644);
                        $file_name = $_FILES['userfile']['name'];
                        Database::get()->query("UPDATE assignment SET file_path = ?s, file_name = ?s WHERE id = ?d", $filename, $file_name, $id);
                    }
                }
                if ($assign_to_specific && !empty($assigned_to)) {
                    if ($group_submissions == 1) {
                        $column = 'group_id';
                        $other_column = 'user_id';
                    } else {
                        $column = 'user_id';
                        $other_column = 'group_id';
                    }
                    foreach ($assigned_to as $assignee_id) {
                        Database::get()->query("INSERT INTO assignment_to_specific ({$column}, {$other_column}, assignment_id) VALUES (?d, ?d, ?d)", $assignee_id, 0, $id);
                    }
                }
                Log::record($course_id, MODULE_ID_ASSIGN, LOG_INSERT, array('id' => $id,
                    'title' => $title,
                    'description' => $desc,
                    'deadline' => $deadline,
                    'secret' => $secret,
                    'group' => $group_submissions));
                Session::Messages($langNewAssignSuccess, 'alert-success');
                redirect_to_home_page("modules/work/index.php?course=$course_code");
            } else {
                @rmdir("$workPath/$secret");
                Session::Mesages($langGeneralError, 'alert-danger');
                redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
            }
        } else {
            Session::Mesages($langErrorCreatingDirectory);
            redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
    }
}

/**
 * @brief edit assignment
 * @global type $tool_content
 * @global type $langEditSuccess
 * @global type $m
 * @global type $langTheField
 * @global type $course_code
 * @global type $course_id
 * @global type $uid
 * @global string $workPath
 * @global type $langFormErrors
 * @global type $langScales
 * @global type $langTitle
 * @param type $id
 * @return type
 */
function edit_assignment($id) {

    global $tool_content, $langEditSuccess, $m,
        $langTheField, $course_code, $course_id,
        $uid, $workPath, $langFormErrors, $langScales, $langTitle;

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->rule('integer', array('group_submissions', 'assign_to_specific'));
    if (isset($_POST['max_grade'])) {
        $v->rule('required', array('max_grade'));
        $v->rule('numeric', array('max_grade'));
        $v->labels(array('max_grade' => "$langTheField $m[max_grade]"));
    }
    if (isset($_POST['scale'])) {
        $v->rule('required', array('scale'));
        $v->rule('numeric', array('scale'));
        $v->labels(array('scale' => "$langTheField $langScales"));
    }
    $v->labels(array('title' => "$langTheField $langTitle"));
    if($v->validate()) {
        $row = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
        $title = $_POST['title'];
        $desc = purify($_POST['desc']);
        $submission_type = $_POST['submission_type'];
        $submission_date = isset($_POST['WorkStart']) && !empty($_POST['WorkStart']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkStart'])->format('Y-m-d H:i:s') : (new DateTime('NOW'))->format('Y-m-d H:i:s');
        $deadline = isset($_POST['WorkEnd']) && !empty($_POST['WorkEnd']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkEnd'])->format('Y-m-d H:i:s') : NULL;
        $late_submission = isset($_POST['late_submission']) ? 1 : 0;
        $group_submissions = $_POST['group_submissions'];
        if (isset($_POST['scale'])) {
            $max_grade = max_grade_from_scale($_POST['scale']);
            $grading_scale_id = $_POST['scale'];
        } else {
            $max_grade = $_POST['max_grade'];
            $grading_scale_id = 0;
        }
        $assign_to_specific = filter_input(INPUT_POST, 'assign_to_specific', FILTER_VALIDATE_INT);
        $assigned_to = filter_input(INPUT_POST, 'ingroup', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
        $auto_judge           = isset($_POST['auto_judge']) ? filter_input(INPUT_POST, 'auto_judge', FILTER_VALIDATE_INT) : 0;
        $auto_judge_scenarios = isset($_POST['auto_judge_scenarios']) ? serialize($_POST['auto_judge_scenarios']) : "";
        $lang                 = isset($_POST['lang']) ? filter_input(INPUT_POST, 'lang') : '';

        if ($assign_to_specific == 1 && empty($assigned_to)) {
             $assign_to_specific = 0;
         }

         if (!isset($_POST['comments'])) {
             $comments = '';
         } else {
             $comments = purify($_POST['comments']);
         }

         if (!isset($_FILES) || !$_FILES['userfile']['size']) {
             $_FILES['userfile']['name'] = '';
             $_FILES['userfile']['tmp_name'] = '';
             $filename = $row->file_path;
             $file_name = $row->file_name;
         } else {
             validateUploadedFile($_FILES['userfile']['name'], 2);
             if (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' .
                                'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' .
                                'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $_FILES['userfile']['name'])) {
                 $tool_content .= "<p class=\"caution\">$langUnwantedFiletype: {$_FILES['userfile']['name']}<br />";
                 $tool_content .= "<a href=\"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id\">$langBack</a></p><br />";
                 return;
             }
            $student_name = trim(uid_to_name($user_id));
            $local_name = !empty($student_name)? $student_name : uid_to_name($user_id, 'username');
             $am = Database::get()->querySingle("SELECT am FROM user WHERE id = ?d", $uid)->am;
             if (!empty($am)) {
                 $local_name .= $am;
             }
             $local_name = greek_to_latin($local_name);
             $local_name = replace_dangerous_char($local_name);
             $secret = $row->secret_directory;
             $ext = get_file_extension($_FILES['userfile']['name']);
             $filename = "$secret/$local_name" . (empty($ext) ? '' : '.' . $ext);
             if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/admin_files/$filename")) {
                 @chmod("$workPath/admin_files/$filename", 0644);
                 $file_name = $_FILES['userfile']['name'];
             }
         }
         Database::get()->query("UPDATE assignment SET title = ?s, description = ?s,
             group_submissions = ?d, comments = ?s, submission_type = ?d, deadline = ?t, late_submission = ?d, submission_date = ?t, max_grade = ?f,
             grading_scale_id = ?d, assign_to_specific = ?d, file_path = ?s, file_name = ?s,
             auto_judge = ?d, auto_judge_scenarios = ?s, lang = ?s
             WHERE course_id = ?d AND id = ?d", $title, $desc, $group_submissions,
             $comments, $submission_type, $deadline, $late_submission, $submission_date, $max_grade, $grading_scale_id, $assign_to_specific, $filename, $file_name, $auto_judge, $auto_judge_scenarios, $lang, $course_id, $id);

         Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);

         //tags
         if (isset($_POST['tags'])) {
            $tagsArray = explode(',', $_POST['tags']);
            $moduleTag = new ModuleElement($id);
            $moduleTag->syncTags($tagsArray);
         }

         if ($assign_to_specific && !empty($assigned_to)) {
             if ($group_submissions == 1) {
                 $column = 'group_id';
                 $other_column = 'user_id';
             } else {
                 $column = 'user_id';
                 $other_column = 'group_id';
             }
             foreach ($assigned_to as $assignee_id) {
                 Database::get()->query("INSERT INTO assignment_to_specific ({$column}, {$other_column}, assignment_id) VALUES (?d, ?d, ?d)", $assignee_id, 0, $id);
             }
         }
         Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $id,
                 'title' => $title,
                 'description' => $desc,
                 'deadline' => $deadline,
                 'group' => $group_submissions));   \

        Session::Messages($langEditSuccess,'alert-success');
        redirect_to_home_page("modules/work/index.php?course=$course_code");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id&choice=edit");
    }
}

/**
 * 
 * @global type $course_id
 * @global type $uid
 * @global type $langOnBehalfOfGroupComment
 * @global array $works_url
 * @global type $langOnBehalfOfUserComment
 * @global string $workPath
 * @global type $langUploadSuccess
 * @global type $langUploadError
 * @global type $course_code
 * @global type $langAutoJudgeInvalidFileType
 * @global type $langAutoJudgeScenariosPassed
 * @global type $is_editor
 * @param type $id
 * @param type $on_behalf_of
 */
function submit_work($id, $on_behalf_of = null) {
    global $course_id, $uid, $langOnBehalfOfGroupComment,
           $works_url, $langOnBehalfOfUserComment, $workPath,
           $langUploadSuccess, $langUploadError, $course_code,
           $langAutoJudgeInvalidFileType,
           $langAutoJudgeScenariosPassed, $is_editor;
    
    $row = Database::get()->querySingle("SELECT id, title, group_submissions, submission_type,
                            deadline, late_submission, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                            auto_judge, auto_judge_scenarios, lang, max_grade
                            FROM assignment
                            WHERE course_id = ?d AND id = ?d",
                            $course_id, $id);
    $auto_judge = $row->auto_judge;
    $auto_judge_scenarios = ($auto_judge == true) ? unserialize($row->auto_judge_scenarios) : null;
    $lang = $row->lang;
    $max_grade = $row->max_grade;
            
    if (AutojudgeApp::getAutojudge()->isEnabled() && $auto_judge) {
        $connector = AutojudgeApp::getAutojudge();
        $langExt = $connector->getSupportedLanguages();
    }

    $nav[] = $works_url;
    $nav[] = array('url' => "$_SERVER[SCRIPT_NAME]?id=$id", 'name' => q($row->title));

    $submit_ok = FALSE; // Default do not allow submission
    if (isset($uid) && $uid) { // check if logged-in
        if ($GLOBALS['status'] == USER_GUEST) { // user is guest
            $submit_ok = FALSE;
        } else { // user NOT guest
            if (isset($_SESSION['courses']) && isset($_SESSION['courses'][$_SESSION['dbname']])) {
                // user is registered to this lesson
                if (($row->time < 0 && (int) $row->deadline && !$row->late_submission) and !$on_behalf_of) {
                    $submit_ok = FALSE; // after assignment deadline
                } else {
                    $submit_ok = TRUE; // before deadline
                }
            } else {
                //user NOT registered to this lesson
                $submit_ok = FALSE;
            }
        }
    } //checks for submission validity end here
    if ($submit_ok) {
        $success_msgs = array();
        $error_msgs = array();
        //Preparing variables
        $user_id = isset($on_behalf_of) ? $on_behalf_of : $uid;
        if ($row->group_submissions) {
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : -1;
            $gids = user_group_info($on_behalf_of ? null : $user_id, $course_id);
        } else {
            $group_id = 0;
        }
        // If submission type is Online Text
        if($row->submission_type){
            $filename = '';
            $file_name = '';
            $success_msgs[] = $langUploadSuccess;
        } else { // If submission type is File
            if ($row->group_submissions) {
                $local_name = isset($gids[$group_id]) ? greek_to_latin($gids[$group_id]) : '';
            } else {
                $student_name = trim(uid_to_name($user_id));
                $local_name = !empty($student_name)? $student_name : uid_to_name($user_id, 'username');
                $am = Database::get()->querySingle("SELECT am FROM user WHERE id = ?d", $user_id)->am;
                if (!empty($am)) {
                    $local_name .= $am;
                }
                $local_name = greek_to_latin($local_name);
            }
            $local_name = replace_dangerous_char($local_name);
            if (isset($on_behalf_of) and !isset($_FILES)) {
                $_FILES['userfile']['name'] = '';
                $_FILES['userfile']['tmp_name'] = '';
                $no_files = true;
            } else {
                $no_files = false;
            }
            $file_name = $_FILES['userfile']['name'];
            validateUploadedFile($file_name, 2);
            $secret = work_secret($row->id);
            $ext = get_file_extension($file_name);
            $filename = "$secret/$local_name" . (empty($ext) ? '' : '.' . $ext);
            if ($no_files or move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/$filename")) {
                if ($no_files) {
                    $filename = '';
                } else {
                    @chmod("$workPath/$filename", 0644);
                }
                $success_msgs[] = $langUploadSuccess;
            } elseif(!$is_editor) {
                $error_msgs[] = $langUploadError;
                Session::Messages($error_msgs, 'alert-danger');
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
            }
        }

        $submit_ip = $_SERVER['REMOTE_ADDR'];
        $submission_text = isset($_POST['submission_text']) ? purify($_POST['submission_text']) : NULL;
        if (isset($on_behalf_of)) {
            if ($row->group_submissions) {
                $stud_comments = sprintf($langOnBehalfOfGroupComment, uid_to_name($uid), $gids[$group_id]);
            } else {
                $stud_comments = sprintf($langOnBehalfOfUserComment, uid_to_name($uid), uid_to_name($user_id));
            }
            $grade_comments = $_POST['stud_comments'];
            $grade_valid = filter_input(INPUT_POST, 'grade', FILTER_VALIDATE_FLOAT);
            (isset($_POST['grade']) && $grade_valid!== false) ? $grade = $grade_valid : $grade = NULL;
            $grade_ip = $submit_ip;
        } else {
            if ($row->group_submissions) {
                if (array_key_exists($group_id, $gids)) {
                    $del_submission_msg = delete_submissions_by_uid(-1, $group_id, $row->id, $filename);
                    if (!empty($del_submission_msg)) {
                        $success_msgs[] = $del_submission_msg;
                    }
                }
            } else {
                $del_submission_msg = delete_submissions_by_uid($user_id, -1, $row->id, $filename);
                if (!empty($del_submission_msg)) {
                    $success_msgs[] = $del_submission_msg;
                }
            }
            $stud_comments = $_POST['stud_comments'];
            $grade = NULL;
            $grade_comments = $grade_ip = "";
        }

        if (!$row->group_submissions || array_key_exists($group_id, $gids)) {
            $data = array(
                $user_id,
                $row->id,
                $submit_ip,
                $filename,
                $file_name,
                $submission_text,
                $stud_comments,
                $grade,
                $grade_comments,
                $grade_ip,
                $group_id
            );
            $sid = Database::get()->query("INSERT INTO assignment_submit
                                    (uid, assignment_id, submission_date, submission_ip, file_path,
                                     file_name, submission_text, comments, grade, grade_comments, grade_submission_ip,
                                     grade_submission_date, group_id)
                                     VALUES (?d, ?d, NOW(), ?s, ?s, ?s, ?s, ?s, ?f, ?s, ?s, NOW(), ?d)", $data)->lastInsertID;
            triggerGame($course_id, $user_id, $row->id);
            Log::record($course_id, MODULE_ID_ASSIGN, LOG_INSERT, array('id' => $sid,
                'title' => $row->title,
                'assignment_id' => $row->id,
                'filepath' => $filename,
                'filename' => $file_name,
                'comments' => $stud_comments,
                'group_id' => $group_id));
            if ($row->group_submissions) {
                $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE id = ?d", $sid)->group_id;
                $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                foreach ($user_ids as $user_id) {
                    update_attendance_book($user_id, $row->id, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                    update_gradebook_book($user_id, $row->id, $grade/$row->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                }
            } else {
                $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                // update attendance book as well
                update_attendance_book($quserid, $row->id, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                //update gradebook if needed
                update_gradebook_book($quserid, $id, $grade/$row->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
            }
            if ($on_behalf_of and isset($_POST['email'])) {
                $email_grade = $_POST['grade'];
                $email_comments = $_POST['stud_comments'];
                grade_email_notify($row->id, $sid, $email_grade, $email_comments);
            }
        }

        // Auto-judge: Send file to hackearth
        if(AutojudgeApp::getAutojudge()->isEnabled()) {
            if ($auto_judge && $ext === $langExt[$lang]) {
                    $content = file_get_contents("$workPath/$filename");
                    // Run each scenario and count how many passed
                     $auto_judge_scenarios_output = array(
                        array(
                            'student_output'=> '',
                            'passed'=> 0,
                        )
                    );

                    $passed = 0;
                    $i = 0;
                    $partial = 0;
                    $errorsComment = '';
                    $weight_sum = 0;
                    foreach($auto_judge_scenarios as $curScenario) {
                        $input = new AutoJudgeConnectorInput();
                        $input->input = $curScenario['input'];
                        $input->code = $content;
                        $input->lang = $lang;
                        $result = $connector->compile($input);
                        // Check if we have compilation errors.
                        if ($result->compileStatus !== $result::COMPILE_STATUS_OK) {
                            // Write down the error message.
                            $num = $i+1;
                            $errorsComment = $result->compileStatus." ".$result->output."<br />";
                            $auto_judge_scenarios_output[$i]['passed'] = 0;
                        } else {
                            // Get all needed values to run the assertion.
                            $auto_judge_scenarios_output[$i]['student_output'] = $result->output;
                            $scenarioOutputExpectation = trim($curScenario['output']);
                            $scenarionAssertion        = $curScenario['assertion'];
                            // Do it now.
                            $assertionResult = doScenarioAssertion(
                                $scenarionAssertion,
                                $auto_judge_scenarios_output[$i]['student_output'],
                                $scenarioOutputExpectation
                            );
                            // Check if assertion passed.
                            if ($assertionResult) {
                                $passed++;
                                $auto_judge_scenarios_output[$i]['passed'] = 1;
                                $partial += $curScenario['weight'];
                            } else {
                                $num = $i+1;
                                $auto_judge_scenarios_output[$i]['passed'] = 0;
                            }
                        }

                        $weight_sum += $curScenario['weight'];
                        $i++;
                    }

                    // 3 decimal digits precision
                    $grade = round($partial / $weight_sum * $max_grade, 3);
                    // allow an error of 0.001
                    if($max_grade - $grade <= 0.001)
                        $grade = $max_grade;
                    // Add the output as a comment
                    $comment = $langAutoJudgeScenariosPassed.': '.$passed.'/'.count($auto_judge_scenarios);
                    rtrim($errorsComment, '<br />');
                    if ($errorsComment !== '') {
                        $comment .= '<br /><br />'.$errorsComment;
                    }
                    submit_grade_comments(array(
                        'assignment' => $id,
                        'submission' => $sid,
                        'grade' => $grade,
                        'comments' => $comment,
                        'email' => false,
                        'auto_judge_scenarios_output' => $auto_judge_scenarios_output,
                        'preventUiAlterations' => true,
                    ));

            } else if ($auto_judge && $ext !== $langExt[$lang]) {
                if($lang == null) { die('Auto Judge is enabled but no language is selected'); }
                if($langExt[$lang] == null) { die('An unsupported language was selected. Perhaps platform-wide auto judge settings have been changed?'); }
                submit_grade_comments($id, $sid, 0, sprintf($langAutoJudgeInvalidFileType, $langExt[$lang], $ext), false, null, true);
            }
        }
        // End Auto-judge

        Session::Messages($success_msgs, 'alert-success');
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
    } else { // not submit_ok
        Session::Messages($langExerciseNotPermit);
        redirect_to_home_page("modules/work/index.php?course=$course_code");
    }
}


/**
 * @brief assignment - prof view only
 * @global type $tool_content
 * @global type $m
 * @global type $course_code
 * @global type $course_id
 * @global type $answer
 * @global type $desc
 * @global type $language
 * @global string $head_content 
 * @global type $langBack
 * @global type $langSave
 * @global type $langStudents
 * @global type $langMove
 * @global type $langWorkFile
 * @global type $langAssignmentStartHelpBlock
 * @global type $langAssignmentEndHelpBlock
 * @global type $langWorkSubType
 * @global type $langWorkOnlineText
 * @global type $langStartDate
 * @global type $langGradeNumbers
 * @global type $langGradeScalesSelect
 * @global type $langGradeType
 * @global type $langGradeScales
 * @global type $langAutoJudgeInputNotSupported
 * @global type $langAutoJudgeSum
 * @global type $langAutoJudgeNewScenario
 * @global type $langAutoJudgeEnable
 * @global type $langAutoJudgeInput
 * @global type $langAutoJudgeExpectedOutput
 * @global type $langOperator
 * @global type $langAutoJudgeWeight
 * @global type $langAutoJudgeProgrammingLanguage
 * @global type $langAutoJudgeAssertions
 * @global type $langDescription
 * @global type $langGroups
 * @global type $langType
 */
function new_assignment() {
    global $tool_content, $m, $course_code, $course_id,
           $desc, $language, $head_content,
           $langBack, $langSave, $langStudents, $langMove, $langWorkFile, $langAssignmentStartHelpBlock,
           $langAssignmentEndHelpBlock, $langWorkSubType, $langWorkOnlineText, $langStartDate,
           $langGradeNumbers, $langGradeScalesSelect, $langGradeType, $langGradeScales,
           $langAutoJudgeInputNotSupported, $langAutoJudgeSum, $langAutoJudgeNewScenario,
           $langAutoJudgeEnable, $langAutoJudgeInput, $langAutoJudgeExpectedOutput,
           $langOperator, $langAutoJudgeWeight, $langAutoJudgeProgrammingLanguage,
           $langAutoJudgeAssertions, $langDescription, $langTitle, $langGroups;
    

    load_js('bootstrap-datetimepicker');
    load_js('select2');

    $scales = Database::get()->queryArray('SELECT * FROM grading_scale WHERE course_id = ?d', $course_id);
    $scale_options = "<option value>-- $langGradeScalesSelect --</option>";
    foreach ($scales as $scale) {
        $scale_options .= "<option value='$scale->id'>$scale->title</option>";
    }
    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#scales').select2();
            $('input[name=grading_type]').on('change', function(e){
                var choice = $(this).val();
                if (choice == 0) {
                    $('#max_grade')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                } else {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                }
            });
            $('#WorkEnd, #WorkStart').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
            $('#enableWorkEnd, #enableWorkStart').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#'+dateType).prop('disabled', false);
                    $('#late_sub_row').removeClass('hide');
                } else {
                    $('input#'+dateType).prop('disabled', true);
                    $('#late_sub_row').addClass('hide');
                }
            });
        });

    </script>";

    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'level' => 'primary-label',
              'url' => "$_SERVER[PHP_SELF]?course=$course_code",
              'icon' => 'fa-reply')));
    $title_error = Session::getError('title');
    $max_grade_error = Session::getError('max_grade');
    $scale_error = Session::getError('scale');
    $max_grade = Session::has('max_grade') ? Session::get('max_grade') : 10;
    $scale = Session::getError('scale');
    $submission_type = Session::has('submission_type') ? Session::get('submission_type') : 0;
    $grading_type = Session::has('grading_type') ? Session::get('grading_type') : 0;
    $WorkStart = Session::has('WorkStart') ? Session::get('WorkStart') : (new DateTime('NOW'))->format('d-m-Y H:i');
    $WorkEnd = Session::has('WorkEnd') ? Session::get('WorkEnd') : "";
    $enableWorkStart = Session::has('enableWorkStart') ? Session::get('enableWorkStart') : null;
    $enableWorkEnd = Session::has('enableWorkEnd') ? Session::get('enableWorkEnd') : ($WorkEnd ? 1 : 0);
    enableCheckFileSize();
    $groups = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d", $course_id);
    $group_options = "";
    foreach ($groups as $group) {
        $group_options .= "<option value='$group->id'>$group->name</option>";
    }    
    $tool_content .= "
        <div class='row'><div class='col-sm-12'>
        <div class='form-wrapper'>
        <form class='form-horizontal' role='form' enctype='multipart/form-data' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <fieldset>
            <div class='form-group ".($title_error ? "has-error" : "")."'>
                <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
                <div class='col-sm-10'>
                  <input name='title' type='text' class='form-control' id='title' placeholder='$langTitle'>
                  <span class='help-block'>$title_error</span>
                </div>
            </div>
            <div class='form-group'>
                <label for='desc' class='col-sm-2 control-label'>$langDescription:</label>
                <div class='col-sm-10'>
                " . rich_text_editor('desc', 4, 20, $desc) . "
                </div>
            </div>
                <div class='form-group'>
                    <label for='userfile' class='col-sm-2 control-label'>$langWorkFile:</label>
                    <div class='col-sm-10'>" .
                      fileSizeHidenInput() . "
                      <input type='file' id='userfile' name='userfile'>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langGradeType:</label>
                    <div class='col-sm-10'>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='grading_type' value='0'". ($grading_type ? "" : " checked") .">
                             $langGradeNumbers
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='grading_type' value='1'". ($grading_type ? " checked" : "") .">
                            $langGradeScales
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group".($scale_error ? " has-error" : "").(!$grading_type ? " hidden" : "")."'>
                    <label for='title' class='col-sm-2 control-label'>$langGradeScales:</label>
                    <div class='col-sm-10'>
                      <select name='scale' class='form-control' id='scales' disabled>
                            $scale_options
                      </select>
                      <span class='help-block'>$scale_error</span>
                    </div>
                </div>
                <div class='form-group".($max_grade_error ? " has-error" : "").($grading_type ? " hidden" : "")."'>
                    <label for='title' class='col-sm-2 control-label'>$m[max_grade]:</label>
                    <div class='col-sm-10'>
                      <input name='max_grade' type='text' class='form-control' id='max_grade' placeholder='$m[max_grade]' value='$max_grade'>
                      <span class='help-block'>$max_grade_error</span>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langWorkSubType:</label>
                    <div class='col-sm-10'>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='submission_type' value='0'". ($submission_type ? "" : " checked") .">
                             $langWorkFile
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='submission_type' value='1'". ($submission_type ? " checked" : "") .">
                            $langWorkOnlineText
                          </label>
                        </div>
                    </div>
                </div>
                <div class='input-append date form-group".(Session::getError('WorkStart') ? " has-error" : "")."' id='enddatepicker' data-date='$WorkStart' data-date-format='dd-mm-yyyy'>
                    <label for='WorkStart' class='col-sm-2 control-label'>$langStartDate:</label>
                    <div class='col-sm-10'>
                       <div class='input-group'>
                           <span class='input-group-addon'>
                             <input style='cursor:pointer;' type='checkbox' id='enableWorkStart' name='enableWorkStart' value='1'".($enableWorkStart ? ' checked' : '').">
                           </span>
                           <input class='form-control' name='WorkStart' id='WorkStart' type='text' value='$WorkStart'".($enableWorkStart ? '' : ' disabled').">
                       </div>
                       <span class='help-block'>".(Session::hasError('WorkStart') ? Session::getError('WorkStart') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentStartHelpBlock")."</span>
                    </div>
                </div>
                <div class='input-append date form-group".(Session::getError('WorkEnd') ? " has-error" : "")."' id='enddatepicker' data-date='$WorkEnd' data-date-format='dd-mm-yyyy'>
                    <label for='exerciseEndDate' class='col-sm-2 control-label'>$m[deadline]:</label>
                    <div class='col-sm-10'>
                       <div class='input-group'>
                           <span class='input-group-addon'>
                             <input style='cursor:pointer;' type='checkbox' id='enableWorkEnd' name='enableWorkEnd' value='1'".($enableWorkEnd ? ' checked' : '').">
                           </span>
                           <input class='form-control' name='WorkEnd' id='WorkEnd' type='text' value='$WorkEnd'".($enableWorkEnd ? '' : ' disabled').">
                       </div>
                       <span class='help-block'>".(Session::hasError('WorkEnd') ? Session::getError('WorkEnd') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentEndHelpBlock")."</span>
                    </div>
                </div>
                <div class='form-group ". ($WorkEnd ? "" : "hide") ."' id='late_sub_row'>
                    <div class='col-xs-10 col-xs-offset-2'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' id='late_submission' name='late_submission' value='1'>
                            $m[late_submission_enable]
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$m[group_or_user]:</label>
                    <div class='col-sm-10'>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='user_button' name='group_submissions' value='0' checked>
                            $m[user_work]
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='group_button' name='group_submissions' value='1'>
                            $m[group_work]
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$m[WorkAssignTo]:</label>
                    <div class='col-sm-10'>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='assign_button_all' name='assign_to_specific' value='0' checked>
                            <span id='assign_button_all_text'>$m[WorkToAllUsers]</span>
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='assign_button_some' name='assign_to_specific' value='1'>
                            <span id='assign_button_some_text'>$m[WorkToUser]</span>
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>";
        $tool_content .= "       
                        <div class='row margin-bottom-thin hide' id='groupFiltering'>
                            <div class='col-sm-4'>
                                <select class='form-control' id='groupFilter'>
                                    <option value='0'>-- $langGroups --</option>
                                    $group_options
                                </select>
                            </div>
                        </div>";
        $tool_content .= "                      
                        <div class='table-responsive'>
                            <table id='assignees_tbl' class='table-default hide'>
                                <tr class='title1'>
                                  <td id='assignees'>$langStudents</td>
                                  <td class='text-center'>$langMove</td>
                                  <td>$m[WorkAssignTo]</td>
                                </tr>
                                <tr>
                                  <td>
                                    <select class='form-control' id='assign_box' size='10' multiple></select>
                                  </td>
                                  <td class='text-center'>
                                    <input type='button' onClick=\"move('assign_box','assignee_box')\" value='   &gt;&gt;   ' /><br /><input type='button' onClick=\"move('assignee_box','assign_box')\" value='   &lt;&lt;   ' />
                                  </td>
                                  <td width='40%'>
                                    <select class='form-control' id='assignee_box' name='ingroup[]' size='10' multiple></select>
                                  </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>";
                if(AutojudgeApp::getAutojudge()->isEnabled()) {
                    $connector = AutojudgeApp::getAutojudge();
                    $tool_content .= "
                    <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langAutoJudgeEnable:</label>
                    <div class='col-sm-10'>
                        <div class='radio'><input type='checkbox' id='auto_judge' name='auto_judge' value='1' /></div>
                        <table style='display: none;'>
                            <thead>
                                <tr>
                                  <th>$langAutoJudgeInput</th>
                                  <th>$langOperator</th>
                                  <th>$langAutoJudgeExpectedOutput</th>
                                  <th>$langAutoJudgeWeight</th>
                                  <th>".$m['delete']."</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                  <td><input type='text' name='auto_judge_scenarios[0][input]' ".($connector->supportsInput() ? '' : 'readonly="readonly" placeholder="'.$langAutoJudgeInputNotSupported.'"')." /></td>
                                  <td>
                                    <select name='auto_judge_scenarios[0][assertion]' class='auto_judge_assertion'>
                                        <option value='eq' selected='selected'>".$langAutoJudgeAssertions['eq']."</option>
                                        <option value='same'>".$langAutoJudgeAssertions['same']."</option>
                                        <option value='notEq'>".$langAutoJudgeAssertions['notEq']."</option>
                                        <option value='notSame'>".$langAutoJudgeAssertions['notSame']."</option>
                                        <option value='integer'>".$langAutoJudgeAssertions['integer']."</option>
                                        <option value='float'>".$langAutoJudgeAssertions['float']."</option>
                                        <option value='digit'>".$langAutoJudgeAssertions['digit']."</option>
                                        <option value='boolean'>".$langAutoJudgeAssertions['boolean']."</option>
                                        <option value='notEmpty'>".$langAutoJudgeAssertions['notEmpty']."</option>
                                        <option value='notNull'>".$langAutoJudgeAssertions['notNull']."</option>
                                        <option value='string'>".$langAutoJudgeAssertions['string']."</option>
                                        <option value='startsWith'>".$langAutoJudgeAssertions['startsWith']."</option>
                                        <option value='endsWith'>".$langAutoJudgeAssertions['endsWith']."</option>
                                        <option value='contains'>".$langAutoJudgeAssertions['contains']."</option>
                                        <option value='numeric'>".$langAutoJudgeAssertions['numeric']."</option>
                                        <option value='isArray'>".$langAutoJudgeAssertions['isArray']."</option>
                                        <option value='true'>".$langAutoJudgeAssertions['true']."</option>
                                        <option value='false'>".$langAutoJudgeAssertions['false']."</option>
                                        <option value='isJsonString'>".$langAutoJudgeAssertions['isJsonString']."</option>
                                        <option value='isObject'>".$langAutoJudgeAssertions['isObject']."</option>
                                    </select>
                                  </td>
                                  <td><input type='text' name='auto_judge_scenarios[0][output]' class='auto_judge_output' /></td>
                          <td><input type='text' name='auto_judge_scenarios[0][weight]' class='auto_judge_weight'/></td>
                                  <td><a href='#' class='autojudge_remove_scenario' style='display: none;'>X</a></td>
                                </tr>
                                <tr>
                                    <td> </td>
                                    <td> </td>
                                    <td> </td>
                                    <td style='text-align:center;'> $langAutoJudgeSum: <span id='weights-sum'>0</span></td>
                                    <td> <input type='submit' value='$langAutoJudgeNewScenario' id='autojudge_new_scenario' /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class='form-group'>
                  <label class='col-sm-2 control-label'>$langAutoJudgeProgrammingLanguage:</label>
                  <div class='col-sm-10'>
                    <select id='lang' name='lang'>";
                    foreach($connector->getSupportedLanguages() as $lang => $ext) {
                        $tool_content .= "<option value='$lang'>$lang</option>\n";
                    }
                    $tool_content .= "</select>
                  </div>
                </div>
                ";
                }
                $tool_content .= Tag::tagInput()."

            <div class='form-group'>
                <div class='col-sm-offset-2 col-sm-10'>".
                    form_buttons(array(
                        array(
                            'class'         => 'btn-primary',
                            'name'          => 'new_assign',
                            'value'         => $langSave,
                            'javascript'    => "selectAll('assignee_box',true)"
                        ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                        )
                    ))
                    ."</div>
            </div>
        </fieldset>
        </form></div></div></div>";
}

/**
 * @brief form for editing
 * @global type $tool_content
 * @global type $m
 * @global type $langBack
 * @global type $course_code
 * @global type $langSave
 * @global type $course_id
 * @global string $head_content
 * @global type $language
 * @global type $langAssignmentStartHelpBlock
 * @global type $langAssignmentEndHelpBlock
 * @global type $langStudents
 * @global type $langMove
 * @global type $langWorkFile
 * @global type $themeimg
 * @global type $langStartDate
 * @global type $langLessOptions
 * @global type $langMoreOptions
 * @global type $langWorkOnlineText
 * @global type $langWorkSubType
 * @global type $langGradeScalesSelect
 * @global type $langGradeType
 * @global type $langGradeNumbers
 * @global type $langGradeScales
 * @global type $langLessOptions
 * @global type $langMoreOptions
 * @global type $langAutoJudgeInputNotSupported
 * @global type $langGroups
 * @global type $langAutoJudgeSum
 * @global type $langAutoJudgeNewScenario
 * @global type $langAutoJudgeEnable
 * @global type $langDescription
 * @global type $langTitle
 * @global type $langAutoJudgeInput
 * @global type $langAutoJudgeExpectedOutput
 * @global type $langOperator
 * @global type $langAutoJudgeWeight
 * @global type $langAutoJudgeProgrammingLanguage
 * @global type $langAutoJudgeAssertions
 * @param type $id
 */
function show_edit_assignment($id) {

    global $tool_content, $m, $langBack, $course_code,
        $langSave, $course_id, $head_content, $language, $langAssignmentStartHelpBlock,
        $langAssignmentEndHelpBlock, $langStudents, $langMove, $langWorkFile, $themeimg, $langStartDate,
        $langLessOptions, $langMoreOptions, $langWorkOnlineText, $langWorkSubType,
        $langGradeScalesSelect, $langGradeType, $langGradeNumbers, $langGradeScales,
        $langLessOptions, $langMoreOptions, $langAutoJudgeInputNotSupported, $langGroups,
        $langAutoJudgeSum, $langAutoJudgeNewScenario, $langAutoJudgeEnable, $langDescription,
        $langTitle, $langAutoJudgeInput, $langAutoJudgeExpectedOutput, $langOperator,
        $langAutoJudgeWeight, $langAutoJudgeProgrammingLanguage, $langAutoJudgeAssertions;

    load_js('bootstrap-datetimepicker');
    load_js('select2');

    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#scales').select2();
            $('input[name=grading_type]').on('change', function(e){
                var choice = $(this).val();
                if (choice == 0) {
                    $('#max_grade')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                } else {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                }
            });
            $('#WorkEnd, #WorkStart').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
            $('#enableWorkEnd, #enableWorkStart').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#'+dateType).prop('disabled', false);
                    if (dateType == 'WorkEnd') $('#late_submission').prop('disabled', false);
                    $('#late_sub_row').removeClass('hide');
                } else {
                    $('input#'+dateType).prop('disabled', true);
                    if (dateType == 'WorkEnd') $('#late_submission').prop('disabled', true);
                    $('#late_sub_row').addClass('hide');
                }
            });
        });
    </script>";

    $row = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);

    $scales = Database::get()->queryArray('SELECT * FROM grading_scale WHERE course_id = ?d', $course_id);
    $scale_options = "<option value>-- $langGradeScalesSelect --</option>";
    foreach ($scales as $scale) {
        $scale_options .= "<option value='$scale->id'".($row->grading_scale_id == $scale->id ? " selected" : "").">$scale->title</option>";
    }

    if ($row->assign_to_specific) {
        //preparing options in select boxes for assigning to speficic users/groups
        $assignee_options='';
        $unassigned_options='';
        if ($row->group_submissions) {
            $assignees = Database::get()->queryArray("SELECT `group`.id AS id, `group`.name
                                   FROM assignment_to_specific, `group`
                                   WHERE `group`.id = assignment_to_specific.group_id AND assignment_to_specific.assignment_id = ?d", $id);
            $all_groups = Database::get()->queryArray("SELECT name,id FROM `group` WHERE course_id = ?d", $course_id);
            foreach ($assignees as $assignee_row) {
                $assignee_options .= "<option value='".$assignee_row->id."'>".$assignee_row->name."</option>";
            }
            $unassigned = array_udiff($all_groups, $assignees,
              function ($obj_a, $obj_b) {
                return $obj_a->id - $obj_b->id;
              }
            );
            foreach ($unassigned as $unassigned_row) {
                $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->name</option>";
            }
        } else {
            $assignees = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                   FROM assignment_to_specific, user
                                   WHERE user.id = assignment_to_specific.user_id AND assignment_to_specific.assignment_id = ?d", $id);
            $all_users = Database::get()->queryArray("SELECT user.id AS id, user.givenname, user.surname
                                    FROM user, course_user
                                    WHERE user.id = course_user.user_id
                                    AND course_user.course_id = ?d AND course_user.status = 5
                                    AND user.id", $course_id);
            foreach ($assignees as $assignee_row) {
                $assignee_options .= "<option value='$assignee_row->id'>$assignee_row->surname $assignee_row->givenname</option>";
            }
            $unassigned = array_udiff($all_users, $assignees,
              function ($obj_a, $obj_b) {
                return $obj_a->id - $obj_b->id;
              }
            );
            foreach ($unassigned as $unassigned_row) {
                $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->surname $unassigned_row->givenname</option>";
            }
        }
    }
    $WorkStart = $row->submission_date ? DateTime::createFromFormat('Y-m-d H:i:s', $row->submission_date)->format('d-m-Y H:i') : NULL;
    $WorkEnd = $row->deadline ? DateTime::createFromFormat('Y-m-d H:i:s', $row->deadline)->format('d-m-Y H:i') : NULL;
    $max_grade = Session::has('max_grade') ? Session::get('max_grade') : ($row->max_grade ? $row->max_grade : 10);
    $grading_type = Session::has('grading_type') ? Session::get('grading_type') : ($row->grading_scale_id ? 1 : 0);
    $enableWorkStart = Session::has('enableWorkStart') ? Session::get('enableWorkStart') : null;
    $enableWorkEnd = Session::has('enableWorkEnd') ? Session::get('enableWorkEnd') : ($WorkEnd ? 1 : 0);
    $comments = trim($row->comments);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'level' => 'primary-label',
              'url' => "$_SERVER[PHP_SELF]?course=$course_code",
              'icon' => 'fa-reply')));

    //Get possible validation errors
    $title_error = Session::getError('title');
    $max_grade_error = Session::getError('max_grade');
    $scale_error = Session::getError('scale');

    $groups = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d", $course_id);
    $group_options = "";
    foreach ($groups as $group) {
        $selected = '';
//        if($group_id == $group->id) {
//            $selected = ' selected';
//        }
        $group_options .= "<option value='$group->id'$selected>$group->name</option>";
    }
    $tool_content .= "
    <div class='form-wrapper'>
    <form class='form-horizontal' role='form' enctype='multipart/form-data' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
    <input type='hidden' name='id' value='$id' />
    <input type='hidden' name='choice' value='do_edit' />
    <fieldset>
            <div class='form-group ".($title_error ? "has-error" : "")."'>
                <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
                <div class='col-sm-10'>
                  <input name='title' type='text' class='form-control' id='title' value='".q($row->title)."' placeholder='$langTitle'>
                  <span class='help-block'>$title_error</span>
                </div>
            </div>
            <div class='form-group'>
                <label for='desc' class='col-sm-2 control-label'>$langDescription:</label>
                <div class='col-sm-10'>
                " . rich_text_editor('desc', 4, 20, $row->description) . "
                </div>
            </div>";
    if (!empty($comments)) {
    $tool_content .= "<div class='form-group'>
                <label for='desc' class='col-sm-2 control-label'>$m[comments]:</label>
                <div class='col-sm-10'>
                " . rich_text_editor('comments', 5, 65, $comments) . "
                </div>
            </div>";
    }

    $tool_content .= "
                <div class='form-group'>
                    <label for='userfile' class='col-sm-2 control-label'>$langWorkFile:</label>
                    <div class='col-sm-10'>
                      ".(($row->file_name)? "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$row->id&amp;file_type=1'>".q($row->file_name)."</a>"
                . "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=do_delete_file' onClick='return confirmation(\"$m[WorkDeleteAssignmentFileConfirm]\");'>
                                     <img src='$themeimg/delete.png' title='$m[WorkDeleteAssignmentFile]' /></a>" : "<input type='file' id='userfile' name='userfile' />")."
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langGradeType:</label>
                    <div class='col-sm-10'>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='grading_type' value='0'". ($grading_type ? "" : " checked") .">
                             $langGradeNumbers
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='grading_type' value='1'". ($grading_type ? " checked" : "") .">
                            $langGradeScales
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group".($scale_error ? " has-error" : "").(!$grading_type ? " hidden" : "")."'>
                    <label for='title' class='col-sm-2 control-label'>$langGradeScales:</label>
                    <div class='col-sm-10'>
                      <select name='scale' class='form-control' id='scales'".(!$grading_type ? " disabled" : "").">
                            $scale_options
                      </select>
                      <span class='help-block'>$scale_error</span>
                    </div>
                </div>
                <div class='form-group".($max_grade_error ? " has-error" : "").($grading_type ? " hidden" : "")."'>
                    <label for='title' class='col-sm-2 control-label'>$m[max_grade]:</label>
                    <div class='col-sm-10'>
                      <input name='max_grade' type='text' class='form-control' id='max_grade' placeholder='$m[max_grade]' value='$max_grade'>
                      <span class='help-block'>$max_grade_error</span>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langWorkSubType:</label>
                    <div class='col-sm-10'>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='submission_type' value='0'". ($row->submission_type ? "" : "checked") .">
                             $langWorkFile
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='submission_type' value='1' ". ($row->submission_type ? "checked" : "") .">
                            $langWorkOnlineText
                          </label>
                        </div>
                    </div>
                </div>
                <div class='input-append date form-group".(Session::getError('WorkStart') ? " has-error" : "")."' id='enddatepicker' data-date='$WorkStart' data-date-format='dd-mm-yyyy'>
                    <label for='WorkStart' class='col-sm-2 control-label'>$langStartDate:</label>
                    <div class='col-sm-10'>
                       <div class='input-group'>
                           <span class='input-group-addon'>
                             <input style='cursor:pointer;' type='checkbox' id='enableWorkStart' name='enableWorkStart' value='1'".($enableWorkStart ? ' checked' : '').">
                           </span>
                           <input class='form-control' name='WorkStart' id='WorkStart' type='text' value='$WorkStart'".($enableWorkStart ? '' : ' disabled').">
                       </div>
                       <span class='help-block'>".(Session::hasError('WorkStart') ? Session::getError('WorkStart') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentStartHelpBlock")."</span>
                    </div>
                </div>
                <div class='input-append date form-group".(Session::getError('WorkEnd') ? " has-error" : "")."' id='enddatepicker' data-date='$WorkEnd' data-date-format='dd-mm-yyyy'>
                    <label for='WorkEnd' class='col-sm-2 control-label'>$m[deadline]:</label>
                    <div class='col-sm-10'>
                       <div class='input-group'>
                           <span class='input-group-addon'>
                             <input style='cursor:pointer;' type='checkbox' id='enableWorkEnd' name='enableWorkEnd' value='1'".($enableWorkEnd ? ' checked' : '').">
                           </span>
                           <input class='form-control' name='WorkEnd' id='WorkEnd' type='text' value='$WorkEnd'".($enableWorkEnd ? '' : ' disabled').">
                       </div>
                       <span class='help-block'>".(Session::hasError('WorkEnd') ? Session::getError('WorkEnd') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentEndHelpBlock")."</span>
                    </div>
                </div>
                <div class='form-group ". ($WorkEnd ? "" : "hide") ."' id='late_sub_row'>
                    <div class='col-xs-10 col-xs-offset-2'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' id='late_submission' name='late_submission' value='1' ".(($row->late_submission)? 'checked' : '').">
                            $m[late_submission_enable]
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$m[group_or_user]:</label>
                    <div class='col-sm-10'>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='user_button' name='group_submissions' value='0' ".(($row->group_submissions==1) ? '' : 'checked').">
                            $m[user_work]
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='group_button' name='group_submissions' value='1' ".(($row->group_submissions==1) ? 'checked' : '').">
                            $m[group_work]
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$m[WorkAssignTo]:</label>
                    <div class='col-sm-10'>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='assign_button_all' name='assign_to_specific' value='0' ".($row->assign_to_specific ? '' : 'checked').">
                            <span id='assign_button_all_text'>$m[WorkToAllUsers]</span>
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='assign_button_some' name='assign_to_specific' value='1' ".($row->assign_to_specific ? 'checked' : '').">
                            <span id='assign_button_some_text'>".($row->group_submissions ? $m['WorkToGroup'] : $m['WorkToUser'])."</span>
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>";
    if ($row->assign_to_specific && !$row->group_submissions) {
        $tool_content .= "       
                        <div class='row margin-bottom-thin' id='groupFiltering'>
                            <div class='col-sm-4'>
                                <select class='form-control' id='groupFilter'>
                                    <option value='0'>-- $langGroups --</option>
                                    $group_options
                                </select>
                            </div>
                        </div>";        
    }

        $tool_content .= "                        
                        <div class='table-responsive'>
                            <table id='assignees_tbl' class='table-default ".(($row->assign_to_specific==1) ? '' : 'hide')."'>
                            <tr class='title1'>
                              <td id='assignees'>$langStudents</td>
                              <td class='text-center'>$langMove</td>
                              <td>$m[WorkAssignTo]</td>
                            </tr>
                            <tr>
                              <td>
                                <select class='form-control' id='assign_box' size='10' multiple>
                                ".((isset($unassigned_options)) ? $unassigned_options : '')."
                                </select>
                              </td>
                              <td class='text-center'>
                                <input type='button' onClick=\"move('assign_box','assignee_box')\" value='   &gt;&gt;   ' /><br /><input type='button' onClick=\"move('assignee_box','assign_box')\" value='   &lt;&lt;   ' />
                              </td>
                              <td>
                                <select class='form-control' id='assignee_box' name='ingroup[]' size='10' multiple>
                                ".((isset($assignee_options)) ? $assignee_options : '')."
                                </select>
                              </td>
                            </tr>
                            </table>
                        </div>
                    </div>
                </div>";
                if(AutojudgeApp::getAutojudge()->isEnabled()) {
                $auto_judge = $row->auto_judge;
                $lang = $row->lang;
                $tool_content .= "
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langAutoJudgeEnable:</label>
                    <div class='col-sm-10'>
                        <div class='radio'><input type='checkbox' id='auto_judge' name='auto_judge' value='1' ".($auto_judge == true ? "checked='1'" : '')." /></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>$langAutoJudgeInput</th>
                                    <th>$langOperator</th>
                                    <th>$langAutoJudgeExpectedOutput</th>
                                    <th>$langAutoJudgeWeight</th>
                                    <th>".$m['delete']."</th>
                                </tr>
                            </thead>
                            <tbody>";
                            $auto_judge_scenarios = $auto_judge == true ? unserialize($row->auto_judge_scenarios) : null;
                            $connector = AutojudgeApp::getAutojudge();
                            $rows    = 0;
                            $display = 'visible';
                            if ($auto_judge_scenarios != null) {
                                $scenariosCount = count($auto_judge_scenarios);
                                foreach ($auto_judge_scenarios as $aajudge) {
                                    $tool_content .=
                                    "<tr>
                                        <td><input type='text' value='".htmlspecialchars($aajudge['input'], ENT_QUOTES)."' name='auto_judge_scenarios[$rows][input]' ".($connector->supportsInput() ? '' : 'readonly="readonly" placeholder="'.$langAutoJudgeInputNotSupported.'"')." /></td>";

                                    $tool_content .=
                                    "<td>
                                        <select name='auto_judge_scenarios[$rows][assertion]' class='auto_judge_assertion'>
                                            <option value='eq'"; if ($aajudge['assertion'] === 'eq') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['eq']."</option>
                                            <option value='same'"; if ($aajudge['assertion'] === 'same') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['same']."</option>
                                            <option value='notEq'"; if ($aajudge['assertion'] === 'notEq') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['notEq']."</option>
                                            <option value='notSame'"; if ($aajudge['assertion'] === 'notSame') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['notSame']."</option>
                                            <option value='integer'"; if ($aajudge['assertion'] === 'integer') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['integer']."</option>
                                            <option value='float'"; if ($aajudge['assertion'] === 'float') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['float']."</option>
                                            <option value='digit'"; if ($aajudge['assertion'] === 'digit') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['digit']."</option>
                                            <option value='boolean'"; if ($aajudge['assertion'] === 'boolean') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['boolean']."</option>
                                            <option value='notEmpty'"; if ($aajudge['assertion'] === 'notEmpty') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['notEmpty']."</option>
                                            <option value='notNull'"; if ($aajudge['assertion'] === 'notNull') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['notNull']."</option>
                                            <option value='string'"; if ($aajudge['assertion'] === 'string') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['string']."</option>
                                            <option value='startsWith'"; if ($aajudge['assertion'] === 'startsWith') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['startsWith']."</option>
                                            <option value='endsWith'"; if ($aajudge['assertion'] === 'endsWith') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['endsWith']."</option>
                                            <option value='contains'"; if ($aajudge['assertion'] === 'contains') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['contains']."</option>
                                            <option value='numeric'"; if ($aajudge['assertion'] === 'numeric') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['numeric']."</option>
                                            <option value='isArray'"; if ($aajudge['assertion'] === 'isArray') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['isArray']."</option>
                                            <option value='true'"; if ($aajudge['assertion'] === 'true') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['true']."</option>
                                            <option value='false'"; if ($aajudge['assertion'] === 'false') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['false']."</option>
                                            <option value='isJsonString'"; if ($aajudge['assertion'] === 'isJsonString') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['isJsonString']."</option>
                                            <option value='isObject'"; if ($aajudge['assertion'] === 'isObject') {$tool_content .= " selected='selected'";} $tool_content .=">".$langAutoJudgeAssertions['isObject']."</option>
                                        </select>
                                    </td>";

                                    if (isset($aajudge['output'])) {
                                        $tool_content .= "<td><input type='text' value='".htmlspecialchars($aajudge['output'], ENT_QUOTES)."' name='auto_judge_scenarios[$rows][output]' class='auto_judge_output' /></td>";
                                    } else {
                                        $tool_content .= "<td><input type='text' value='' name='auto_judge_scenarios[$rows][output]' disabled='disabled' class='auto_judge_output' /></td>";
                                    }

                                    $tool_content .=
                                        "<td><input type='text' value='$aajudge[weight]' name='auto_judge_scenarios[$rows][weight]' class='auto_judge_weight'/></td>
                                        <td><a href='#' class='autojudge_remove_scenario' style='display: ".($rows <= 0 ? 'none': 'visible').";'>X</a></td>
                                    </tr>";

                                    $rows++;
                                }
                            } else {
                                $tool_content .= "<tr>
                                            <td><input type='text' name='auto_judge_scenarios[$rows][input]' /></td>
                                            <td>
                                                <select name='auto_judge_scenarios[$rows][assertion]' class='auto_judge_assertion'>
                                                    <option value='eq' selected='selected'>".$langAutoJudgeAssertions['eq']."</option>
                                                    <option value='same'>".$langAutoJudgeAssertions['same']."</option>
                                                    <option value='notEq'>".$langAutoJudgeAssertions['notEq']."</option>
                                                    <option value='notSame'>".$langAutoJudgeAssertions['notSame']."</option>
                                                    <option value='integer'>".$langAutoJudgeAssertions['integer']."</option>
                                                    <option value='float'>".$langAutoJudgeAssertions['float']."</option>
                                                    <option value='digit'>".$langAutoJudgeAssertions['digit']."</option>
                                                    <option value='boolean'>".$langAutoJudgeAssertions['boolean']."</option>
                                                    <option value='notEmpty'>".$langAutoJudgeAssertions['notEmpty']."</option>
                                                    <option value='notNull'>".$langAutoJudgeAssertions['notNull']."</option>
                                                    <option value='string'>".$langAutoJudgeAssertions['string']."</option>
                                                    <option value='startsWith'>".$langAutoJudgeAssertions['startsWith']."</option>
                                                    <option value='endsWith'>".$langAutoJudgeAssertions['endsWith']."</option>
                                                    <option value='contains'>".$langAutoJudgeAssertions['contains']."</option>
                                                    <option value='numeric'>".$langAutoJudgeAssertions['numeric']."</option>
                                                    <option value='isArray'>".$langAutoJudgeAssertions['isArray']."</option>
                                                    <option value='true'>".$langAutoJudgeAssertions['true']."</option>
                                                    <option value='false'>".$langAutoJudgeAssertions['false']."</option>
                                                    <option value='isJsonString'>".$langAutoJudgeAssertions['isJsonString']."</option>
                                                    <option value='isObject'>".$langAutoJudgeAssertions['isObject']."</option>
                                                </select>
                                            </td>
                                            <td><input type='text' name='auto_judge_scenarios[$rows][output]' class='auto_judge_output' /></td>
                                            <td><input type='text' name='auto_judge_scenarios[$rows][weight]' class='auto_judge_weight'/></td>
                                            <td><a href='#' class='autojudge_remove_scenario' style='display: none;'>X</a></td>
                                        </tr>
                                ";
                            }
                            $tool_content .=
                            "<tr>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td style='text-align:center;'> $langAutoJudgeSum: <span id='weights-sum'>0</span></td>
                                <td> <input type='submit' value='$langAutoJudgeNewScenario' id='autojudge_new_scenario' /></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class='form-group'>
                  <label class='col-sm-2 control-label'>$langAutoJudgeProgrammingLanguage:</label>
                  <div class='col-sm-10'>
                    <select id='lang' name='lang'>";
                    foreach($connector->getSupportedLanguages() as $llang => $ext) {
                        $tool_content .= "<option value='$llang' ".($llang === $lang ? "selected='selected'" : "").">$llang</option>\n";
                    }
                    $tool_content .= "</select>
                  </div>
                </div>";
                }
                $tool_content .= Tag::tagInput($id)."
            <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>".
                    form_buttons(array(
                        array(
                            'class'         => 'btn-primary',
                            'name'          => 'do_edit',
                            'value'         => $langSave,
                            'javascript'    => "selectAll('assignee_box',true)"
                        ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                        )
                    ))
                    ."</div>
            </div>
    </fieldset>
    </form></div>";
}



/**
 * @brief delete assignment
 * @global type $tool_content
 * @global string $workPath
 * @global type $course_code
 * @global type $webDir
 * @global type $langBack
 * @global type $langDeleted
 * @global type $course_id
 * @param type $id
 */
function delete_assignment($id) {

    global $tool_content, $workPath, $course_code, $webDir, $langBack, $langDeleted, $course_id;

    $secret = work_secret($id);
    $row = Database::get()->querySingle("SELECT title,assign_to_specific FROM assignment WHERE course_id = ?d
                                        AND id = ?d", $course_id, $id);
    if (count($row) > 0) {
        if (Database::get()->query("DELETE FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id)->affectedRows > 0){
            $uids = Database::get()->queryArray("SELECT uid FROM assignment_submit WHERE assignment_id = ?d", $id);
            Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $id);
            foreach ($uids as $user_id) {
                triggerGame($course_id, $user_id, $id);
            }
            if ($row->assign_to_specific) {
                Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);
            }
            move_dir("$workPath/$secret", "$webDir/courses/garbage/${course_code}_work_${id}_$secret");
            
            Log::record($course_id, MODULE_ID_ASSIGN, LOG_DELETE, array('id' => $id,
                'title' => $row->title));
            return true;
        }
        return false;
    }
    return false;
}
/**
 * @brief delete assignment's submissions
 * @global type $tool_content
 * @global string $workPath
 * @global type $course_code
 * @global type $webDir
 * @global type $langBack
 * @global type $langDeleted
 * @global type $course_id
 * @param type $id
 */
function purge_assignment_subs($id) {

	global $tool_content, $workPath, $webDir, $langBack, $langDeleted, $langAssignmentSubsDeleted, $course_code, $course_id;

	$secret = work_secret($id);
        $row = Database::get()->querySingle("SELECT title,assign_to_specific FROM assignment WHERE course_id = ?d
                                        AND id = ?d", $course_id, $id);
        $uids = Database::get()->queryArray("SELECT uid FROM assignment_submit WHERE assignment_id = ?d", $id);
        if (Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $id)->affectedRows > 0) {
            foreach ($uids as $user_id) {
                triggerGame($course_id, $user_id, $id);
            }
            if ($row->assign_to_specific) {
                Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);
            }
            move_dir("$workPath/$secret",
            "$webDir/courses/garbage/${course_code}_work_${id}_$secret");
            return true;
        }
        return false;
}
/**
 * @brief delete user assignment
 * @global string $tool_content
 * @global type $course_id
 * @global type $course_code
 * @global type $webDir
 * @param type $id
 */
function delete_user_assignment($id) {
    global $tool_content, $course_code, $webDir;

    $filename = Database::get()->querySingle("SELECT file_path FROM assignment_submit WHERE id = ?d", $id);
    $row = Database::get()->querySingle("SELECT s.uid, s.assignment_id, a.course_id FROM assignment_submit s JOIN assignment a ON (s.assignment_id = a.id) where s.id = ?d", $id);
    if (Database::get()->query("DELETE FROM assignment_submit WHERE id = ?d", $id)->affectedRows > 0) {
        triggerGame($row->course_id, $row->uid, $id);
        if ($filename->file_path) {
            $file = $webDir . "/courses/" . $course_code . "/work/" . $filename->file_path;
            if (!my_delete($file)) {
                return false;
            }
        }
        return true;
    }
    return false;
}
/**
 * @brief delete teacher assignment file
 * @global string $tool_content
 * @global type $course_id
 * @global type $course_code
 * @global type $webDir
 * @param type $id
 */
function delete_teacher_assignment_file($id) {
    global $tool_content, $course_code, $webDir;

    $filename = Database::get()->querySingle("SELECT file_path FROM assignment WHERE id = ?d", $id);
    $file = $webDir . "/courses/" . $course_code . "/work/admin_files/" . $filename->file_path;
    if (Database::get()->query("UPDATE assignment SET file_path='', file_name='' WHERE id = ?d", $id)->affectedRows > 0) {
        if (my_delete($file)) {
            return true;
        }
        return false;
    }
}
/**
 * @brief display user assignment
 * @global type $tool_content
 * @global type $m
 * @global type $uid
 * @global type $langUserOnly
 * @global type $langBack
 * @global type $course_code
 * @global type $course_id
 * @global type $course_code
 * @param type $id
 */
function show_student_assignment($id) {
    global $tool_content, $m, $uid, $langUserOnly, $langBack, $course_code,
        $course_id, $course_code, $langAssignmentWillBeActive;

    $user_group_info = user_group_info($uid, $course_id);
    if (!empty($user_group_info)) {
        $gids_sql_ready = implode(',',array_keys($user_group_info));
    } else {
        $gids_sql_ready = "''";
    }

    $row = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                         FROM assignment WHERE course_id = ?d AND id = ?d AND active = '1' AND
                                            (assign_to_specific = '0' OR assign_to_specific = '1' AND id IN
                                               (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d OR group_id != 0 AND group_id IN ($gids_sql_ready))
                                            )", $course_id, $id, $uid);
    if ($row) {
        $WorkStart = new DateTime($row->submission_date);
        $current_date = new DateTime('NOW');
        $interval = $WorkStart->diff($current_date);
        if ($WorkStart > $current_date) {
            Session::Messages($langAssignmentWillBeActive . ' ' . $WorkStart->format('d-m-Y H:i'));
            redirect_to_home_page("modules/work/index.php?course=$course_code");
        }

        $tool_content .= action_bar(array(
           array(
               'title' => $langBack,
               'icon' => 'fa-reply',
               'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
               'level' => "primary-label"
           )
        ));
        assignment_details($id, $row);

        $submit_ok = ($row->time > 0 || !(int) $row->deadline || $row->time <= 0 && $row->late_submission);
        $submissions_exist = false;

        if (!$uid) {
            $tool_content .= "<p>$langUserOnly</p>";
            $submit_ok = FALSE;
        } elseif ($GLOBALS['status'] == 10) {
            $tool_content .= "\n  <div class='alert alert-warning'>$m[noguest]</div>";
            $submit_ok = FALSE;;
        } else {
            foreach (find_submissions($row->group_submissions, $uid, $id, $user_group_info) as $sub) {
                $submissions_exist = true;
                if ($sub->grade != '') {
                    $submit_ok = false;

                }
                show_submission_details($sub->id);
            }
        }
        if ($submit_ok) {
            show_submission_form($id, $user_group_info, false, $submissions_exist);
        }
    } else {
        redirect_to_home_page("modules/work/?course=$course_code");
    }
}

function show_submission_form($id, $user_group_info, $on_behalf_of=false, $submissions_exist=false) {
    global $tool_content, $m, $langWorkFile, $langSave, $langSubmit, $uid,
    $langNotice3, $gid, $urlAppend, $langGroupSpaceLink, $langOnBehalfOf,
    $course_code, $course_id, $langBack, $is_editor, $langWorkOnlineText,
    $langGradeScalesSelect;

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);

    $group_select_hidden_input = $group_select_form = '';
    $is_group_assignment = is_group_assignment($id);
    if ($is_group_assignment) {
        if (!$on_behalf_of) {
            if (count($user_group_info) == 1) {
                $gids = array_keys($user_group_info);
                $group_link = $urlAppend . '/modules/group/document.php?gid=' . $gids[0];
                $group_select_hidden_input = "<input type='hidden' name='group_id' value='$gids[0]' />";
            } elseif ($user_group_info) {
                $group_select_form = "
                        <div class='form-group'>
                            <label for='group_id' class='col-sm-2 control-label'>$langGroupSpaceLink:</label>
                            <div class='col-sm-10'>
                              " . selection($user_group_info, 'group_id') . "
                            </div>
                        </div>";
            } else {
                $group_link = $urlAppend . 'modules/group/';
                $tool_content .= "<div class='alert alert-warning'>$m[this_is_group_assignment] <br />" .
                        sprintf(count($user_group_info) ?
                                        $m['group_assignment_publish'] :
                                        $m['group_assignment_no_groups'], $group_link) .
                        "</div>\n";
            }
        } else {
            $groups_with_no_submissions = groups_with_no_submissions($id);
            if (count($groups_with_no_submissions)>0) {
                $group_select_form = "
                        <div class='form-group'>
                            <label for='group_id' class='col-sm-2 control-label'>$langGroupSpaceLink:</label>
                            <div class='col-sm-10'>
                              " . selection($groups_with_no_submissions, 'group_id') . "
                            </div>
                        </div>";
            }else{
                Session::Messages($m['NoneWorkGroupNoSubmission'], 'alert-danger');
                redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
            }
        }
    } elseif ($on_behalf_of) {
            $users_with_no_submissions = users_with_no_submissions($id);
            if (count($users_with_no_submissions)>0) {
                $group_select_form = "
                        <div class='form-group'>
                            <label for='user_id' class='col-sm-2 control-label'>$langOnBehalfOf:</label>
                            <div class='col-sm-10'>
                              " .selection($users_with_no_submissions, 'user_id', '', "class='form-control'") . "
                            </div>
                        </div>";
            } else {
                Session::Messages($m['NoneWorkUserNoSubmission'], 'alert-danger');
                redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
            }
    }
    $notice = ($submissions_exist)?
    "<div class='alert alert-info'>" . icon('fa-info-circle') . " $langNotice3</div>": '';
    if ($assignment->grading_scale_id) {
        $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assignment->grading_scale_id, $course_id)->scales;
        $scales = unserialize($serialized_scale_data);
        $scale_options = "<option value> - </option>";
        foreach ($scales as $scale) {
            $scale_options .= "<option value='$scale[scale_item_value]'>$scale[scale_item_name]</option>";
        }
        $grade_field = "
                <select name='grade' class='form-control' id='scales'>
                    $scale_options
                </select>";
    } else {
        $grade_field = "<input class='form-control' type='text' name='grade' maxlength='4' size='3'> ($m[max_grade]: $assignment->max_grade)";
    }
    $extra = $on_behalf_of ? "
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>$m[grade]:</label>
                            <div class='col-sm-10'>
                              $grade_field
                              <input type='hidden' name='on_behalf_of' value='1'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>
                                <div class='checkbox'>
                                  <label>
                                    <input type='checkbox' name='email' id='email_button' value='1'>
                                    $m[email_users]
                                  </label>
                                </div>
                            </div>
                        </div>" : '';
    if (!$is_group_assignment || count($user_group_info) || $on_behalf_of) {
        if($assignment->submission_type){
            $submission_form = "
                        <div class='form-group'>
                            <label for='submission_text' class='col-sm-2 control-label'>$langWorkOnlineText:</label>
                            <div class='col-sm-10'>
                                ". rich_text_editor('submission_text', 10, 20, '') ."
                            </div>
                        </div>";
        } else {
            $submission_form = "
                        <div class='form-group'>
                            <label for='userfile' class='col-sm-2 control-label'>$langWorkFile:</label>
                            <div class='col-sm-10'>
                              <input type='file'  name='userfile' id='userfile'>
                            </div>
                        </div>";
        }
        $back_link = $is_editor ? "index.php?course=$course_code&id=$id" : "index.php?course=$course_code";
        $tool_content .= action_bar(array(
                array(
                    'title' => $langBack,
                    'icon' => 'fa-reply',
                    'level' => 'primary-label',
                    'url' => "index.php?course=$course_code&id=$id",
                    'show' => $is_editor
                )
            ))."
                    $notice
                    <div class='form-wrapper'>
                     <form class='form-horizontal' role='form' enctype='multipart/form-data' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
                        <input type='hidden' name='id' value='$id' />$group_select_hidden_input
                        <fieldset>
                        $group_select_form
                        $submission_form
                        <div class='form-group'>
                            <label for='stud_comments' class='col-sm-2 control-label'>$m[comments]:</label>
                            <div class='col-sm-10'>
                              <textarea class='form-control' name='stud_comments' id='stud_comments' rows='5'></textarea>
                            </div>
                        </div>
                        $extra
                        <div class='form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>".
                    form_buttons(array(
                        array(
                            'text'          => $langSave,
                            'name'          => 'work_submit',
                            'value'         => $langSubmit
                        ),
                        array(
                            'href' => $back_link
                        )
                    ))
                    ."</div>
                        </div>
                        </fieldset>
                     </form>
                     </div>
                     <div class='pull-right'><small>$GLOBALS[langMaxFileSize] " .
                ini_get('upload_max_filesize') . "</small></div><br>";
    }
}

/**
 * @brief Print a box with the details of an assignment
 * @global type $tool_content
 * @global type $is_editor
 * @global type $course_code
 * @global type $m
 * @global type $langDaysLeft
 * @global type $langEndDeadline
 * @global type $langDelAssign
 * @global type $langAddGrade
 * @global type $langZipDownload
 * @global type $langTags
 * @global type $langGraphResults
 * @global type $langWorksDelConfirm
 * @global type $langWorkFile
 * @global type $langEditChange
 * @global type $langExportGrades
 * @global type $langDescription
 * @global type $langTitle
 * @param type $id
 * @param type $row
 */
function assignment_details($id, $row) {
    global $tool_content, $is_editor, $course_code, $m, $langDaysLeft,
           $langEndDeadline, $langDelAssign, $langAddGrade, $langZipDownload, $langTags,
           $langGraphResults, $langWorksDelConfirm, $langWorkFile, 
           $langEditChange, $langExportGrades, $langDescription, $langTitle;

    if ($is_editor) {
        $tool_content .= action_bar(array(
            array(
                'title' => $langAddGrade,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=add",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            ),
            array(
                'title' => $langZipDownload,
                'icon' => 'fa-file-archive-o',
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;download=$id",
                'level' => 'primary'
            ),
            array(
                'title' => $langExportGrades,
                'icon' => 'fa-file-excel-o',
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=export"
            ),
            array(
                'title' => $langGraphResults,
                'icon' => 'fa-bar-chart',
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;disp_results=true"
            ),
            array(
                'title' => $m['WorkUserGroupNoSubmission'],
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;disp_non_submitted=true",
                'icon' => 'fa-minus-square'
            ),
            array(
                'title' => $langDelAssign,
                'icon' => 'fa-times',
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=do_delete",
                'button-class' => "btn-danger",
                'confirm' => "$langWorksDelConfirm"
            )
        ));
    }
    $deadline = (int)$row->deadline ? nice_format($row->deadline, true) : $m['no_deadline'];
    if ($row->time > 0) {
        $deadline_notice = "<br><span>($langDaysLeft " . format_time_duration($row->time) . ")</span>";
    } elseif ((int)$row->deadline) {
        $deadline_notice = "<br><span class='text-danger'>$langEndDeadline</span>";
    }

    $moduleTag = new ModuleElement($id);
    $tool_content .= "
    <div class='panel panel-action-btn-primary'>
        <div class='panel-heading'>
            <h3 class='panel-title'>
                $m[WorkInfo] &nbsp;
                ". (($is_editor) ?    
                "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=edit'>
                    <span class='fa fa-edit' title='' data-toggle='tooltip' data-original-title='$langEditChange'></span>
                </a>" : "")."                    
            </h3>
        </div>
        <div class='panel-body'>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langTitle:</strong>
                </div>
                <div class='col-sm-9'>
                    " . q($row->title) . "
                </div>
            </div>";
        if (!empty($row->description)) {
            $tool_content .= "<div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langDescription:</strong>
                </div>
                <div class='col-sm-9'>
                    $row->description
                </div>
            </div>";
        }
        if (!empty($row->comments)) {
            $tool_content .= "<div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$m[comments]:</strong>
                </div>
                <div class='col-sm-9'>
                    $row->comments
                </div>
            </div>";
        }
        if (!empty($row->file_name)) {
            $tool_content .= "<div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langWorkFile:</strong>
                </div>
                <div class='col-sm-9'>
                    <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$row->id&amp;file_type=1'>$row->file_name</a>
                </div>
            </div>";
        }
        $tool_content .= "
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$m[max_grade]:</strong>
                </div>
                <div class='col-sm-9'>
                    $row->max_grade
                </div>
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$m[start_date]:</strong>
                </div>
                <div class='col-sm-9'>
                    " . nice_format($row->submission_date, true) . "
                </div>
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$m[deadline]:</strong>
                </div>
                <div class='col-sm-9'>
                    $deadline ".(isset($deadline_notice) ? $deadline_notice : "")."
                </div>
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$m[group_or_user]:</strong>
                </div>
                <div class='col-sm-9'>
                    ".(($row->group_submissions == '0') ? $m['user_work'] : $m['group_work'])."
                </div>
            </div>";
        $tags_list = $moduleTag->showTags();
        if ($tags_list)
        $tool_content .= "
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langTags:</strong>
                </div>
                <div class='col-sm-9'>
                    $tags_list
                </div>
            </div> ";
$tool_content .= "
        </div>
    </div>";

}

// Show a table header which is a link with the appropriate sorting
// parameters - $attrib should contain any extra attributes requered in
// the <th> tags
function sort_link($title, $opt, $attrib = '') {
    global $tool_content, $course_code;
    $i = '';
    if (isset($_REQUEST['id'])) {
        $i = "&id=$_REQUEST[id]";
   }
    if (@($_REQUEST['sort'] == $opt)) {
        if (@($_REQUEST['rev'] == 1)) {
            $r = 0;
        } else {
            $r = 1;
        }
        $tool_content .= "
                  <th $attrib><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sort=$opt&rev=$r$i'>" . "$title</a></th>";
    } else {
        $tool_content .= "
                  <th $attrib><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sort=$opt$i'>$title</a></th>";
    }
}


/** 
 * @brief show assignment - prof view only
 * @brief the optional message appears instead of assignment details
 * @global type $tool_content
 * @global type $m
 * @global type $langNoSubmissions
 * @global type $langSubmissions
 * @global type $langWorkOnlineText
 * @global type $langGradeOk
 * @global type $course_code
 * @global type $langGraphResults
 * @global type $m
 * @global type $course_code
 * @global array $works_url
 * @global type $course_id
 * @global type $langDelWarnUserAssignment
 * @global type $langQuestionView
 * @global type $langDelete
 * @global type $langEditChange
 * @global type $langAutoJudgeShowWorkResultRpt
 * @global type $langGroupName
 * @param type $id
 * @param type $display_graph_results
 */
function show_assignment($id, $display_graph_results = false) {
    global $tool_content, $m, $langNoSubmissions, $langSubmissions,
    $langWorkOnlineText, $langGradeOk, $course_code, 
    $langGraphResults, $m, $course_code, $works_url, $course_id,
    $langDelWarnUserAssignment, $langQuestionView, $langDelete, $langEditChange,
    $langAutoJudgeShowWorkResultRpt, $langGroupName, $langGroups;

    $assign = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                FROM assignment
                                WHERE course_id = ?d AND id = ?d", $course_id, $id);

    $nav[] = $works_url;
    assignment_details($id, $assign);

    $rev = (@($_REQUEST['rev'] == 1)) ? 'DESC' : 'ASC';
    if (isset($_REQUEST['sort'])) {
        if ($_REQUEST['sort'] == 'am') {
            $order = 'am';
        } elseif ($_REQUEST['sort'] == 'date') {
            $order = 'submission_date';
        } elseif ($_REQUEST['sort'] == 'grade') {
            $order = 'grade';
        } elseif ($_REQUEST['sort'] == 'filename') {
            $order = 'file_name';
        } else {
            $order = 'surname';
        }
    } else {
        $order = 'surname';
    }
   
    $count_of_assignments = Database::get()->querySingle("SELECT COUNT(*) AS count_of_assignments FROM assignment_submit 
                                 WHERE assignment_id = ?d ", $id)->count_of_assignments;    
    if ($count_of_assignments > 0) {
        if ($count_of_assignments == 1) {
            $num_of_submissions = $m['one_submission'];
        } else {
            $num_of_submissions = sprintf("$m[more_submissions]", $count_of_assignments);
        }
        
        if (!$display_graph_results) {
            $group_id = 0;
            $extra_sql = '';
            $sql_vars[] = $id;
            if(isset($_POST['group_id']) && $_POST['group_id']) {
                $group_id = $_POST['group_id'];
                if ($assign->group_submissions) {
                    $extra_sql .= " AND assign.group_id = ?d";
                    $sql_vars[] = $group_id;
                } else {
                    $users = Database::get()->queryArray("SELECT `user_id` FROM `group_members` WHERE `group_id` = ?d", $group_id);
                    $users_sql_ready = '';
                    if($users) {                                          
                         foreach ($users as $user) {
                             $user_ids[] = $user->user_id;
                         }
                         $users_sql_ready .= implode(', ',$user_ids);
                    }
                    $extra_sql .= " AND user.id IN ($users_sql_ready)";
                }
            }           

            $result = Database::get()->queryArray("SELECT assign.id id, assign.file_name file_name,
                                                   assign.uid uid, assign.group_id group_id,
                                                   assign.submission_date submission_date,
                                                   assign.grade_submission_date grade_submission_date,
                                                   assign.grade grade, assign.comments comments,
                                                   assign.grade_comments grade_comments,
                                                   assignment.grading_scale_id grading_scale_id,
                                                   assignment.deadline deadline
                                                   FROM assignment_submit AS assign, user, assignment
                                                   WHERE assign.assignment_id = ?d 
                                                   AND assign.assignment_id = assignment.id 
                                                   AND user.id = assign.uid$extra_sql
                                                   ORDER BY $order $rev", $sql_vars);
            $groups = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d", $course_id);
            $group_options = "";
            foreach ($groups as $group) {
                $group_options .= "<option value='$group->id'>$group->name</option>";
            }
            $tool_content .= "                        
                        <form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' class='form-inline'>
                        <input type='hidden' name='grades_id' value='$id' />
                         <br>
                        <div class='margin-bottom-thin'>
                            <b>$langSubmissions:</b>&nbsp; $count_of_assignments
                        </div>
                        <div class='table-responsive'>
                        <table class='table-default'>
                        <tbody>
                        <tr class='list-header'>
                      <th width='3'>&nbsp;</th>";
            sort_link($m['username'].' / '.$langGroupName, 'username');
            sort_link($m['am'], 'am');
            $assign->submission_type ? $tool_content .= "<th>$langWorkOnlineText</th>" : sort_link($m['filename'], 'filename');
            sort_link($m['sub_date'], 'date');
            sort_link($m['grade'], 'grade');
            $tool_content .= "<th width='5%' class='text-center'><i class='fa fa-cogs'></i></th></tr>";

            $i = 1;
            foreach ($result as $row) {
                //is it a group assignment?
                if (!empty($row->group_id)) {
                    $subContentGroup = "$m[groupsubmit] " .
                            "<a href='../group/group_space.php?course=$course_code&amp;group_id=$row->group_id'>" .
                            "$m[ofgroup] " . gid_to_name($row->group_id) . "</a>";
                } else {
                    $subContentGroup = '';
                }
                $name = empty($row->group_id) ? display_user($row->uid) : display_group($row->group_id);
                $stud_am = Database::get()->querySingle("SELECT am FROM user WHERE id = ?d", $row->uid)->am;
                if ($assign->submission_type) {
                    $filelink = "<a href='#' class='onlineText btn btn-xs btn-default' data-id='$row->id'>$langQuestionView</a>";
                } else {
                    if (empty($row->file_name)) {
                        $filelink = '&nbsp;';
                    } else {
                        $namelen = mb_strlen($row->file_name);
                        if ($namelen > 30) {
                            $extlen = mb_strlen(get_file_extension($row->file_name));
                            $basename = mb_substr($row->file_name, 0, $namelen - $extlen - 3);
                            $ext = mb_substr($row->file_name, $namelen - $extlen - 3);
                            $filename = ellipsize($basename, 30, '...' . $ext);
                        } else {
                            $filename = $row->file_name;
                        }
                        $filelink = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$row->id'>" .
                            q($filename) . "</a>";
                    }
                }
                if (Session::has("grades")) {
                    $grades = Session::get('grades');
                    $grade = $grades[$row->id]['grade'];
                } else {
                    $grade = $row->grade;
                }
                if ($row->grading_scale_id) {
                    $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $row->grading_scale_id, $course_id)->scales;
                    $scales = unserialize($serialized_scale_data);
                    $scale_options = "<option value> - </option>";
                    $scale_values = array_value_recursive('scale_item_value', $scales);
                    if (!in_array($grade, $scale_values) && !is_null($grade)) {
                        $grade = closest($grade, $scale_values)['value'];
                    }
                    foreach ($scales as $scale) {
                        $scale_options .= "<option value='$scale[scale_item_value]'".($scale['scale_item_value'] == $grade ? " selected" : "").">$scale[scale_item_name]</option>";
                    }
                    $grade_field = "
                            <select name='grades[$row->id][grade]' class='form-control' id='scales'>
                                $scale_options
                            </select>";
                } else {
                    $grade_field = "<input class='form-control' type='text' value='$grade' name='grades[$row->id][grade]' maxlength='4' size='3'>";
                }
                $late_sub_text = $row->deadline && $row->submission_date > $row->deadline ?  "<div style='color:red;'><small>$m[late_submission]</small></div>" : '';
                $tool_content .= "
                                <tr>
                                <td align='right' width='4' rowspan='2' valign='top'>$i.</td>
                                <td>$name";
                if (trim($row->comments != '')) {
                    $tool_content .= "<div style='margin-top: .5em;'><small>" .
                            q($row->comments) . '</small></div>';
                }
                $tool_content .= "</td>
                                <td width='85'>" . q($stud_am) . "</td>
                                <td class='text-center' width='180'>
                                        $filelink
                                </td>
                                <td width='100'>" . nice_format($row->submission_date, TRUE) .$late_sub_text. "</td>
                                <td width='5'>
                                    <div class='form-group ".(Session::getError("grade.$row->id") ? "has-error" : "")."'>
                                        $grade_field
                                        <span class='help-block'>".Session::getError("grade.$row->id")."</span>
                                    </div>
                                </td>
                                <td class='option-btn-cell'>".
                                    action_button(array(
                                        array(
                                            'title' => $langEditChange,
                                            'url' => "grade_edit.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id",
                                            'level' => 'primary',
                                            'icon' => 'fa-edit'
                                        ),
                                        array(
                                            'title' => $langDelete,
                                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;as_id=$row->id",
                                            'class' => 'delete',
                                            'icon' => 'fa-times',
                                            'confirm' => $langDelWarnUserAssignment
                                        )
                                    ))."
                                </td>
                                </tr>
                                <tr>
                                <td colspan='6'>";

                //professor comments
                if ($row->grade_comments || $row->grade != '') {
                    $comments = "<br><div class='label label-primary'>" .
                            nice_format($row->grade_submission_date) . "</div>";
                }
                if (trim($row->grade_comments)) {
                    $label = '<b>'.$m['gradecomments'] . '</b>:';
                    $comments .= "&nbsp;<span>" . q_math($row->grade_comments) . "</span>";
                } else {
                    $label = '';
                    $comments = '';
                }
                $tool_content .= "<div style='padding-top: .5em;'>$label
				  $comments
                                ";
                if(AutojudgeApp::getAutojudge()->isEnabled()) {
                    $reportlink = "work_result_rpt.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
                    $tool_content .= "<a href='$reportlink'><b>$langAutoJudgeShowWorkResultRpt</b></a>";
                }
                $tool_content .= "
                                </td>
                                </tr>";
                $i++;
            } //END of Foreach

            $tool_content .= "
                    </tbody>
                </table>
            </div>
            <div class='form-group'>
                <div class='col-xs-12'>
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' value='1' name='email'> $m[email_users]
                      </label>
                    </div>
                </div>
            </div>
            <div class='pull-right'>
                <button class='btn btn-primary' type='submit' name='submit_grades'>$langGradeOk</button>
            </div>
            </form>";
        } else {            
            $result1 = Database::get()->queryArray("SELECT grade FROM assignment_submit WHERE assignment_id = ?d ORDER BY grade ASC", $id);
            $gradeOccurances = array(); // Named array to hold grade occurances/stats
            $gradesExists = 0;
            foreach ($result1 as $row) {
                $theGrade = $row->grade;
                if ($theGrade) {
                    $gradesExists = 1;
                    if (!isset($gradeOccurances[$theGrade])) {
                        $gradeOccurances[$theGrade] = 1;
                    } else {
                        if ($gradesExists) {
                            ++$gradeOccurances[$theGrade];
                        }
                    }
                }
            }
            
            // display pie chart with grades results
            if ($gradesExists) {
                // Used to display grades distribution chart
                $graded_submissions_count = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit AS assign
                                                             WHERE assign.assignment_id = ?d AND
                                                             assign.grade <> ''", $id)->count;                                                           
                if ($assign->grading_scale_id) {
                    $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assign->grading_scale_id, $course_id)->scales;
                    $scales = unserialize($serialized_scale_data);
                    $scale_values = array_value_recursive('scale_item_value', $scales);
                }                
                foreach ($gradeOccurances as $gradeValue => $gradeOccurance) {
                    $percentage = round((100.0 * $gradeOccurance / $graded_submissions_count),2);
                    if ($assign->grading_scale_id) {
                        $key = closest($gradeValue, $scale_values, true)['key'];
                        $gradeValue = $scales[$key]['scale_item_name'];
                    }                    
                    $this_chart_data['grade'][] = "$gradeValue";
                    $this_chart_data['percentage'][] = $percentage;
                }                                
                $tool_content .= "<script type = 'text/javascript'>gradesChartData = ".json_encode($this_chart_data).";</script>";
                /****   C3 plot   ****/
                $tool_content .= "<div class='row plotscontainer'>";
                $tool_content .= "<div class='col-lg-12'>";
                $tool_content .= plot_placeholder("grades_chart", $langGraphResults);
                $tool_content .= "</div></div>";
            }
        }
    } else { // no submissions
        $tool_content .= "
                      <p class='sub_title1'>$langSubmissions:</p>
                      <div class='alert alert-warning'>$langNoSubmissions</div>";
    }
}

function show_non_submitted($id) {
    global $tool_content, $works_url, $course_id, $m, $langSubmissions,
            $langGroup, $course_code;
    $row = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                FROM assignment
                                WHERE course_id = ?d AND id = ?d", $course_id, $id);

    $nav[] = $works_url;
    assignment_details($id, $row);
    if ($row->group_submissions) {
        $groups = groups_with_no_submissions($id);
        $num_results = count($groups);
        if ($num_results > 0) {
            if ($num_results == 1) {
                $num_of_submissions = $m['one_submission'];
            } else {
                $num_of_submissions = sprintf("$m[more_submissions]", $num_results);
            }
                $tool_content .= "
                            <p><div class='sub_title1'>$m[WorkGroupNoSubmission]:</div><p>
                            <p>$num_of_submissions</p>
                            <div class='row'><div class='col-sm-12'>
                            <div class='table-responsive'>
                            <table class='table-default sortable'>
                            <tr class='list-header'>
                          <th width='3'>&nbsp;</th>";
                sort_link($langGroup, 'username');
                $tool_content .= "</tr>";
                $i=1;
                foreach ($groups as $row => $value){

                    $tool_content .= "<tr>
                            <td>$i.</td>
                            <td><a href='../group/group_space.php?course=$course_code&amp;group_id=$row'>$value</a></td>
                            </tr>";
                    $i++;
                }
                $tool_content .= "</table></div></div></div>";
        } else {
            $tool_content .= "
                      <p class='sub_title1'>$m[WorkGroupNoSubmission]:</p>
                      <div class='alert alert-warning'>$m[NoneWorkGroupNoSubmission]</div>";
        }

    } else {
        $users = users_with_no_submissions($id);
        $num_results = count($users);
        if ($num_results > 0) {
            if ($num_results == 1) {
                $num_of_submissions = $m['one_non_submission'];
            } else {
                $num_of_submissions = sprintf("$m[more_non_submissions]", $num_results);
            }
                $tool_content .= "
                            <p><div class='sub_title1'>$m[WorkUserNoSubmission]:</div><p>
                            <p>$num_of_submissions</p>
                            <div class='row'><div class='col-sm-12'>
                            <div class='table-responsive'>
                            <table class='table-default'>
                            <tr class='list-header'>
                          <th width='3'>&nbsp;</th>";
                sort_link($m['username'], 'username');
                sort_link($m['am'], 'am');
                $tool_content .= "</tr>";
                $i=1;
                foreach ($users as $row => $value){
                    $tool_content .= "<tr>
                    <td>$i.</td>
                    <td>".display_user($row)."</td>
                    <td>".  uid_to_am($row) ."</td>
                    </tr>";

                    $i++;
                }
                $tool_content .= "</table></div></div></div>";
        } else {
            $tool_content .= "
                      <p class='sub_title1'>$m[WorkUserNoSubmission]:</p>
                      <div class='alert alert-warning'>$m[NoneWorkUserNoSubmission]</div>";
        }
    }
}

/**
 * @brief display all assignments - student view only
 * @global type $tool_content
 * @global type $m
 * @global type $uid
 * @global type $course_id
 * @global type $course_code
 * @global type $langDaysLeft
 * @global type $langNoAssign
 * @global type $course_code
 * @global type $langHasExpiredS
 * @global type $langTitle
 */
function show_student_assignments() {


    global $tool_content, $m, $uid, $course_id, $course_code, $urlServer,
    $langDaysLeft, $langNoAssign, $langTitle, $langHasExpiredS, $langAddResePortfolio, $langAddGroupWorkSubePortfolio;
    
    $add_eportfolio_res_td = "";
    
    $gids = user_group_info($uid, $course_id);
    if (!empty($gids)) {
        $gids_sql_ready = implode(',',array_keys($gids));
    } else {
        $gids_sql_ready = "''";
    }
    $result = Database::get()->queryArray("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                 FROM assignment WHERE course_id = ?d AND active = '1' AND
                                 (assign_to_specific = '0' OR assign_to_specific = '1' AND id IN
                                    (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d UNION SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                                 )
                                 ORDER BY CASE WHEN CAST(deadline AS UNSIGNED) = '0' THEN 1 ELSE 0 END, deadline", $course_id, $uid);

    if (count($result)>0) {
        if(get_config('eportfolio_enable')) {
            $add_eportfolio_res_th = "<th class='text-center'>".icon('fa-gears')."</th>";
        } else {
            $add_eportfolio_res_th = "";
        }
        
        $tool_content .= "
            <div class='row'><div class='col-sm-12'>
            <div class='table-responsive'><table class='table-default'>
                                  <tr class='list-header'>
                                      <th style='width:45%'>$langTitle</th>
                                      <th class='text-center' style='width:25%'>$m[deadline]</th>
                                      <th class='text-center'>$m[submitted]</th>
                                      <th class='text-center'>$m[grade]</th>
                                      $add_eportfolio_res_th
                                  </tr>";
        $k = 0;
        foreach ($result as $row) {
            $title_temp = q($row->title);
            if($row->deadline){
                $deadline = nice_format($row->deadline, true);
            }else{
                $deadline = $m['no_deadline'];
            }
            $tool_content .= "
                                <tr>
                                    <td><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row->id'>$title_temp</a></td>
                                    <td class='text-center'>" . $deadline ;
            if ($row->time > 0) {
                $tool_content .= "<br>(<small>$langDaysLeft" . format_time_duration($row->time) . "</small>)";
            } else if($row->deadline){
                $tool_content .= "<br> (<small><span class='expired'>$langHasExpiredS</span></small>)";
            }
            $tool_content .= "</td><td class='text-center'>";

            if ($submission = find_submissions(is_group_assignment($row->id), $uid, $row->id, $gids)) {
                $eportfolio_action_array = array();
                foreach ($submission as $sub) {
                    if (isset($sub->group_id)) { // if is a group assignment
                        $tool_content .= "<div style='padding-bottom: 5px;padding-top:5px;font-size:9px;'>($m[groupsubmit] " .
                                "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>" .
                                "$m[ofgroup] " . gid_to_name($sub->group_id) . "</a>)</div>";
                        
                        $eportfolio_action_array[] = array('title' => sprintf($langAddGroupWorkSubePortfolio, gid_to_name($sub->group_id)),
                                'url' => "$urlServer"."main/eportfolio/resources.php?token=".token_generate('eportfolio' . $uid)."&amp;action=add&amp;type=work_submission&amp;rid=".$sub->id,
                                'icon' => 'fa-star');
                        
                    } else {
                        $eportfolio_action_array[] = array('title' => $langAddResePortfolio,
                                                           'url' => "$urlServer"."main/eportfolio/resources.php?token=".token_generate('eportfolio' . $uid)."&amp;action=add&amp;type=work_submission&amp;rid=".$sub->id,
                                                           'icon' => 'fa-star');
                    }
                    $tool_content .= "<i class='fa fa-check-square-o'></i><br>";
                }
                } else {
                    $tool_content .= "<i class='fa fa-square-o'></i><br>";
                }
                $tool_content .= "</td>
                                    <td width='30' align='center'>";
            foreach ($submission as $sub) {
                $grade = submission_grade($sub->id);
                if (!$grade) {
                    $grade = "<div style='padding-bottom: 5px;padding-top:5px;'> - </div>";
                }
                $tool_content .= "<div style='padding-bottom: 5px;padding-top:5px;'>$grade</div>";
            }

            if(get_config('eportfolio_enable') && !empty($submission)) {
                $add_eportfolio_res_td = "<td class='option-btn-cell'>".
                                            action_button($eportfolio_action_array)."</td>";
            }
            
            $tool_content .= "</td>
                                $add_eportfolio_res_td
                                  </tr>";
            $k++;
        }
        $tool_content .= '
                                  </table></div></div></div>';
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoAssign</div>";
    }
}

/**
 * @brief display all assignments
 * @global type $tool_content
 * @global type $m
 * @global type $langEditChange
 * @global type $langDelete
 * @global type $langNoAssign
 * @global type $langNewAssign
 * @global type $course_code
 * @global type $course_id
 * @global type $langWorksDelConfirm
 * @global type $langDaysLeft
 * @global type $m
 * @global type $langWarnForSubmissions
 * @global type $langDelSure
 * @global type $langGradeScales
 * @global type $langTitle
 */
function show_assignments() {
    global $tool_content, $m, $langEditChange, $langDelete, $langNoAssign, $langNewAssign,
           $course_code, $course_id, $langWorksDelConfirm, $langDaysLeft, $m, $langHasExpiredS,
           $langWarnForSubmissions, $langDelSure, $langGradeScales, $langTitle;


    $result = Database::get()->queryArray("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
              FROM assignment WHERE course_id = ?d ORDER BY CASE WHEN CAST(deadline AS UNSIGNED) = '0' THEN 1 ELSE 0 END, deadline", $course_id);
 $tool_content .= action_bar(array(
            array('title' => $langNewAssign,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=1",
                  'button-class' => 'btn-success',
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label'),
            array('title' => $langGradeScales,
                  'url' => "grading_scales.php?course=$course_code",
                  'icon' => 'fa-sort-alpha-asc',
                  'level' => 'primary-label'),
            ),false);

    if (count($result)>0) {

        $tool_content .= "
            <div class='row'>
            <div class='col-xs-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='inner-heading'>
                            <div class='row'>
                                <div class='col-sm-12'>
                                    <strong>Διαθέσιμες Ασκήσεις</strong>
                                </div>
                            </div>
                        </div>
                        <div class='res-table-wrapper'>
                            <div class='row res-table-header'>
                                <div class='col-sm-5'>
                                    $langTitle
                                </div>
                                <div class='col-sm-2'>
                                    $m[subm]
                                </div>
                                <div class='col-sm-2'>
                                    $m[nogr]
                                </div>
                                <div class='col-sm-2'>
                                    $m[deadline]
                                </div>
                                <div class='col-sm-1 text-center'>
                                    <i class='fa fa-cogs'></i>
                                </div>
                            </div>
        ";
        foreach ($result as $row) {
            // Check if assignement contains submissions
            $num_submitted = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit WHERE assignment_id = ?d", $row->id)->count;
            $num_ungraded = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit WHERE assignment_id = ?d AND grade IS NULL", $row->id)->count;
            if (!$num_ungraded) {
                if ($num_submitted > 0) {
                    $num_ungraded = '0';
                } else {
                    $num_ungraded = '-';
                }
            }

            $deadline = (int)$row->deadline ? nice_format($row->deadline, true) : $m['no_deadline'];

            $tool_content .= "
                <div class='row res-table-row ".(!$row->active ? "not_visible":"")."'>
                    <div class='col-xs-5'>
                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id={$row->id}'>" . q($row->title) . "</a>
                        <br><small class='text-muted'>".($row->group_submissions? $m['group_work'] : $m['user_work'])."</small>
                    </div>
                    <div class='col-xs-2'>$num_submitted</div>
                    <div class='col-xs-2'>$num_ungraded</div>
                    <div class='col-xs-2'>$deadline";
            if ($row->time > 0) {
                $tool_content .= " <br><span class='label label-warning'><small>$langDaysLeft" . format_time_duration($row->time) . "</small></span>";
            } else if((int)$row->deadline){
                $tool_content .= " <br><span class='label label-danger'><small>$langHasExpiredS</small></span>";
            }
                $tool_content .="</div>  
                    <div class='col-xs-1'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row->id&amp;choice=edit",
                            'icon' => 'fa-edit'),

                        array('title' => $row->active == 1 ? $m['deactivate']: $m['activate'],
                            'url' => $row->active == 1 ? "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=disable&amp;id=$row->id" : "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=enable&amp;id=$row->id",
                            'icon' => $row->active == 1 ? 'fa-eye-slash': 'fa-eye'),
                        array('title' => $m['WorkSubsDelete'],
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row->id&amp;choice=do_purge",
                            'icon' => 'fa-eraser',
                            'confirm' => "$langWarnForSubmissions $langDelSure",
                            'show' => $num_submitted > 0),
                        array('title' => $langDelete,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row->id&amp;choice=do_delete",
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langWorksDelConfirm))).
                    "
                    </div>
                </div>
                </div>
                </div>
                </div>
                </div>
                </div>
            ";
        }

    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoAssign</div>";
    }
}

/**
 * @brief submit grade and comment for student submission
 * @global type $langGrades
 * @global type $course_id
 * @global type $langTheField
 * @global type $m
 * @global type $course_code
 * @global type $langFormErrors
 * @param type $args
 */
function submit_grade_comments($args) {
    global $langGrades, $course_id, $langTheField, $m, $course_code, $langFormErrors;

    $id = $args['assignment'];
    $sid = $args['submission'];
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);

    $v = new Valitron\Validator($args);
    $v->addRule('emptyOrNumeric', function($field, $value, array $params) {
        if(is_numeric($value) || empty($value)) return true;
    });
    $v->rule('numeric', array('assignment', 'submission'));
    $v->rule('emptyOrNumeric', array('grade'));
    $v->rule('min', array('grade'), 0);
    $v->rule('max', array('grade'), $assignment->max_grade);
    $v->labels(array(
        'grade' => "$langTheField $m[grade]"
    ));
    if($v->validate()) {
        $grade = $args['grade'];
        $comment = $args['comments'];
        $grade = is_numeric($grade) ? $grade : null;
        if(isset($args['auto_judge_scenarios_output'])){
            Database::get()->query("UPDATE assignment_submit SET auto_judge_scenarios_output = ?s
                                    WHERE id = ?d",serialize($args['auto_judge_scenarios_output']), $sid);
        }
        if (Database::get()->query("UPDATE assignment_submit
                                    SET grade = ?f, grade_comments = ?s,
                                    grade_submission_date = NOW(), grade_submission_ip = ?s
                                    WHERE id = ?d", $grade, $comment, $_SERVER['REMOTE_ADDR'], $sid)->affectedRows>0) {
            $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
            triggerGame($course_id, $quserid, $id);
            Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $sid,
                    'title' => $assignment->title,
                    'grade' => $grade,
                    'comments' => $comment));
            if ($assignment->group_submissions) {
                $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE id = ?d", $sid)->group_id;
                $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                foreach ($user_ids as $user_id) {
                    update_gradebook_book($user_id, $id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                }
            } else {
                //update gradebook if needed
                $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                update_gradebook_book($quserid, $id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
            }
        }
        if (isset($args['email'])) {
            grade_email_notify($id, $sid, $grade, $comment);
        }
        Session::Messages($langGrades, 'alert-success');
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/grade_edit.php?course=$course_code&assignment=$id&submission=$sid");
    }

}

/**
 * @brief submit grades to students
 * @global type $langGrades
 * @global type $course_id
 * @global type $course_code
 * @global type $langFormErrors
 * @global type $langTheField
 * @global type $m
 * @param type $grades_id
 * @param type $grades
 * @param type $email
 */
function submit_grades($grades_id, $grades, $email = false) {
    global $langGrades, $course_id, $course_code, $langFormErrors, $langTheField, $m;
    
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $grades_id);
    $errors = [];

    foreach ($grades as $key => $grade) {
        $v = new Valitron\Validator($grade);
        $v->addRule('emptyOrNumeric', function($field, $value, array $params) {
            if(is_numeric($value) || empty($value)) return true;
        });
        $v->rule('emptyOrNumeric', array('grade'));
        $v->rule('min', array('grade'), 0);
        $v->rule('max', array('grade'), $assignment->max_grade);
        $v->labels(array(
            'grade' => "$langTheField $m[grade]"
        ));
        if(!$v->validate()) {
            $valitron_errors = $v->errors();
            $errors["grade.$key"] = $valitron_errors['grade'];
        }
    }
    if(empty($errors)) {
        foreach ($grades as $sid => $grade) {
            $sid = intval($sid);
            $val = Database::get()->querySingle("SELECT grade from assignment_submit WHERE id = ?d", $sid)->grade;

            $grade = is_numeric($grade['grade']) ? $grade['grade'] : null;

            if ($val !== $grade) {
                if (Database::get()->query("UPDATE assignment_submit
                                            SET grade = ?f, grade_submission_date = NOW(), grade_submission_ip = ?s
                                            WHERE id = ?d", $grade, $_SERVER['REMOTE_ADDR'], $sid)->affectedRows > 0) {
                    $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                    triggerGame($course_id, $quserid, $assignment->id);
                    Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $sid,
                            'title' => $assignment->title,
                            'grade' => $grade));

                    //update gradebook if needed
                    if ($assignment->group_submissions) {
                        $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE id = ?d", $sid)->group_id;
                        $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                        foreach ($user_ids as $user_id) {
                            update_gradebook_book($user_id, $assignment->id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                        }
                    } else {
                        $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                        update_gradebook_book($quserid, $assignment->id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                    }

                    if ($email) {
                        grade_email_notify($grades_id, $sid, $grade, '');
                    }
                    Session::Messages($langGrades, 'alert-success');
                }
            }
        }
        Session::Messages($langGrades, 'alert-success');
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($errors);
    }
    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$grades_id");

}



/**
 * @brief download function
 * @global type $uid
 * @global type $is_editor
 * @param type $id
 * @param type $file_type
 * @return boolean
 */
function send_file($id, $file_type) {
    global $uid, $is_editor;
    
    if (isset($file_type)) {
        $info = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
        // don't show file if: assignment nonexistent, not editor, not active assignment, module not visible
        if (count($info) == 0 or
            !($is_editor or
              ($info->active and visible_module(MODULE_ID_ASSIGN)))) {
            return false;
        }
        send_file_to_client("$GLOBALS[workPath]/admin_files/$info->file_path", $info->file_name, null, true);
    } else {
        $info = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $id);
        if (count($info)==0) {
            return false;
        }
        if ($info->group_id) {
            initialize_group_info($info->group_id);
        }
        if (!($is_editor or $info->uid == $uid or $GLOBALS['is_member'])) {
            return false;
        }
        send_file_to_client("$GLOBALS[workPath]/$info->file_path", $info->file_name, null, true);
    }
    exit;
}

/**
 * @brief Zip submissions to assignment $id and send it to user
 * @global string $workPath
 * @global type $course_code
 * @param type $id
 * @return boolean
 */
function download_assignments($id) {
    global $workPath, $course_code;
    
    $counter = Database::get()->querySingle('SELECT COUNT(*) AS count FROM assignment_submit WHERE assignment_id = ?d', $id)->count;
    if ($counter>0) {
        $secret = work_secret($id);
        $filename = "{$course_code}_work_$id.zip";
        chdir($workPath);
        create_zip_index("$secret/index.html", $id);
        $zip = new PclZip($filename);
        $flag = $zip->create($secret, "work_$id", $secret);
        header("Content-Type: application/x-zip");
        header("Content-Disposition: attachment; filename=$filename");
        stop_output_buffering();
        @readfile($filename);
        @unlink($filename);
        exit;
    } else {
        return false;
    }
}

// Create an index.html file for assignment $id listing user submissions
// Set $online to TRUE to get an online view (on the web) - else the
// index.html works for the zip file
function create_zip_index($path, $id, $online = FALSE) {
    global $charset, $m, $course_id;

    $fp = fopen($path, "w");
    if (!$fp) {
        die("Unable to create assignment index file - aborting");
    }
    fputs($fp, '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">
                <style type="text/css">
                .sep td, th { border: 1px solid; }
                td { border: none; }
                table { border-collapse: collapse; border: 2px solid; }
                .sep { border-top: 2px solid black; }
                </style>
	</head>
	<body>
		<table class="table-default">
			<tr>
				<th>' . $m['username'] . '</th>
				<th>' . $m['am'] . '</th>
				<th>' . $m['filename'] . '</th>
				<th>' . $m['sub_date'] . '</th>
				<th>' . $m['grade'] . '</th>
			</tr>');

    $assign = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
    if ($assign->grading_scale_id) {
        $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assign->grading_scale_id, $course_id)->scales;
        $scales = unserialize($serialized_scale_data);
        $scale_values = array_value_recursive('scale_item_value', $scales);
    }
    $result = Database::get()->queryArray("SELECT a.uid, a.file_path, a.submission_date, a.grade, a.comments, a.grade_comments, a.group_id, b.deadline FROM assignment_submit a, assignment b WHERE a.assignment_id = ?d AND a.assignment_id = b.id ORDER BY a.id", $id);
    foreach ($result as $row) {
        $filename = basename($row->file_path);
        $filelink = empty($filename) ? '&nbsp;' :
                ("<a href='$filename'>" . htmlspecialchars($filename) . '</a>');
        $late_sub_text = ((int) $row->deadline && $row->submission_date > $row->deadline) ?  "<div style='color:red;'>$m[late_submission]</div>" : '';
        if ($assign->grading_scale_id) {
            $key = closest($row->grade, $scale_values, true)['key'];
            $row->grade = $scales[$key]['scale_item_name'];
        }
        fputs($fp, '
			<tr class="sep">
				<td>' . q(uid_to_name($row->uid)) . '</td>
				<td>' . q(uid_to_am($row->uid)) . '</td>
				<td align="center">' . $filelink . '</td>
				<td align="center">' . $row->submission_date .$late_sub_text. '</td>
				<td align="center">' . $row->grade . '</td>
			</tr>');
        if (trim($row->comments != '')) {
            fputs($fp, "
			<tr><td colspan='6'><b>$m[comments]: " .
                    "</b>$row->comments</td></tr>");
        }
        if (trim($row->grade_comments != '')) {
            fputs($fp, "
			<tr><td colspan='6'><b>$m[gradecomments]: " .
                    "</b>$row->grade_comments</td></tr>");
        }
        if (!empty($row->group_id)) {
            fputs($fp, "<tr><td colspan='6'>$m[groupsubmit] " .
                    "$m[ofgroup] $row->group_id</td></tr>\n");
        }
    }
    fputs($fp, ' </table></body></html>');
    fclose($fp);
}

// Show a simple html page with grades and submissions
function show_plain_view($id) {
    global $workPath, $charset;

    $secret = work_secret($id);
    create_zip_index("$secret/index.html", $id, TRUE);
    header("Content-Type: text/html; charset=$charset");
    readfile("$workPath/$secret/index.html");
    exit;
}

// Notify students by email about grade/comment submission
// Send to single user for individual submissions or group members for group
// submissions
function grade_email_notify($assignment_id, $submission_id, $grade, $comments) {
    global $m, $currentCourseName, $urlServer, $course_code;
    static $title, $group;

    if (!isset($title)) {
        $res = Database::get()->querySingle("SELECT title, group_submissions FROM assignment WHERE id = ?d", $assignment_id);
        $title = $res->title;
        $group = $res->group_submissions;
    }
    $info = Database::get()->querySingle("SELECT uid, group_id
                                         FROM assignment_submit WHERE id= ?d", $submission_id);

    $subject = sprintf($m['work_email_subject'], $title);
    $body = sprintf($m['work_email_message'], $title, $currentCourseName) . "\n\n";
    if ($grade != '') {
        $body .= ": $m[grade]$grade\n";
    }
    if ($comments) {
        $body .= "$m[gradecomments]: $comments\n";
    }

    $header_html_topic_notify = "<!-- Header Section -->
    <div id='mail-header'>
        <br>
        <div>
            <div id='header-title'>".sprintf($m['work_email_message'], $title, $currentCourseName)."</a>.</div>
        </div>
    </div>";

    $body_html_topic_notify = "<!-- Body Section -->
    <div id='mail-body'>
        <br>
        <div><b>$m[grade]: </b> <span class='left-space'>$grade</span></div><br>
        <div><b>$m[gradecomments]: </b></div>
        <div id='mail-body-inner'>
            $comments<br><br>
        </div>
        $m[link_follows] <a href='{$urlServer}modules/work/index.php?course=$course_code&id=$assignment_id'>{$urlServer}modules/work/index.php?course=$course_code&id=$assignment_id</a>
    </div>";

    $body = $header_html_topic_notify.$body_html_topic_notify;

    $plainBody = html2text($body);
    if (!$group or !$info->group_id) {
        send_mail_to_user_id($info->uid, $subject, $plainBody, $body);
    } else {
        send_mail_to_group_id($info->group_id, $subject, $plainBody, $body);
    }
}

function send_mail_to_group_id($gid, $subject, $plainBody, $body) {
    global $charset;
    $res = Database::get()->queryArray("SELECT surname, givenname, email
                                 FROM user, group_members AS members
                                 WHERE members.group_id = ?d
                                 AND user.id = members.user_id", $gid);
    foreach ($res as $info) {
        send_mail_multipart('', '', "$info->givenname $info->surname", $info->email, $subject, $plainBody, $body, $charset);
    }
}

function send_mail_to_user_id($uid, $subject, $plainBody, $body) {
    global $charset;
    $user = Database::get()->querySingle("SELECT surname, givenname, email FROM user WHERE id = ?d", $uid);
    send_mail_multipart('', '', "$user->givenname $user->surname", $user->email, $subject, $plainBody, $body, $charset);
}

// Return a list of users with no submissions for assignment $id
function users_with_no_submissions($id) {
    global $course_id;
    if (Database::get()->querySingle("SELECT assign_to_specific FROM assignment WHERE id = ?d", $id)->assign_to_specific) {
        $q = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                FROM user, course_user
                                WHERE user.id = course_user.user_id
                                AND course_user.course_id = ?d 
                                AND course_user.status = " .USER_STUDENT . "
                                AND user.id NOT IN (SELECT uid FROM assignment_submit WHERE assignment_id = ?d) 
                                AND user.id IN (SELECT user_id FROM assignment_to_specific WHERE assignment_id = ?d) ORDER BY surname, givenname", $course_id, $id, $id);
    } else {
        $q = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                FROM user, course_user
                                WHERE user.id = course_user.user_id
                                AND course_user.course_id = ?d 
                                AND course_user.status = " . USER_STUDENT . "
                                AND user.id NOT IN (SELECT uid FROM assignment_submit
                                                    WHERE assignment_id = ?d) ORDER BY surname, givenname", $course_id, $id);
    }
    $users = array();
    foreach ($q as $row) {
        $users[$row->id] = "$row->surname $row->givenname";
    }
    return $users;
}

// Return a list of groups with no submissions for assignment $id
function groups_with_no_submissions($id) {
    global $course_id;

    $q = Database::get()->queryArray('SELECT group_id FROM assignment_submit WHERE assignment_id = ?d', $id);
    $groups = user_group_info(null, $course_id, $id);
    if (count($q)>0) {
        foreach ($q as $row) {
            unset($groups[$row->group_id]);
        }
    }
    return $groups;
}

function max_grade_from_scale($scale_id) {
    global $course_id;
    $scale_data = Database::get()->querySingle("SELECT * FROM grading_scale WHERE id = ?d AND course_id = ?d", $scale_id, $course_id);
    $unserialized_scale_items = unserialize($scale_data->scales);
    $max_scale_item_value = 0;
    foreach ($unserialized_scale_items as $item) {
        if ($item['scale_item_value'] > $max_scale_item_value) {
            $max_scale_item_value = $item['scale_item_value'];
        }
    }
    return $max_scale_item_value;
}

function doScenarioAssertion($scenarionAssertion, $scenarioInputResult, $scenarioOutputExpectation) {
    switch($scenarionAssertion) {
        case 'eq':
            $assertionResult = ($scenarioInputResult == $scenarioOutputExpectation);
            break;
        case 'same':
            $assertionResult = ($scenarioInputResult === $scenarioOutputExpectation);
            break;
        case 'notEq':
            $assertionResult = ($scenarioInputResult != $scenarioOutputExpectation);
            break;
        case 'notSame':
            $assertionResult = ($scenarioInputResult !== $scenarioOutputExpectation);
            break;
        case 'integer':
            $assertionResult = (is_int($scenarioInputResult));
            break;
        case 'float':
            $assertionResult = (is_float($scenarioInputResult));
            break;
        case 'digit':
            $assertionResult = (ctype_digit($scenarioInputResult));
            break;
        case 'boolean':
            $assertionResult = (is_bool($scenarioInputResult));
            break;
        case 'notEmpty':
            $assertionResult = (empty($scenarioInputResult) === false);
            break;
        case 'notNull':
            $assertionResult = ($scenarioInputResult !== null);
            break;
        case 'string':
            $assertionResult = (is_string($scenarioInputResult));
            break;
        case 'startsWith':
            $assertionResult = (mb_strpos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8') === 0);
            break;
        case 'endsWith':
            $stringPosition  = mb_strlen($scenarioInputResult, 'utf8') - mb_strlen($scenarioOutputExpectation, 'utf8');
            $assertionResult = (mb_strripos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8') === $stringPosition);
            break;
        case 'contains':
            $assertionResult = (mb_strpos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8'));
            break;
        case 'numeric':
            $assertionResult = (is_numeric($scenarioInputResult));
            break;
        case 'isArray':
            $assertionResult = (is_array($scenarioInputResult));
            break;
        case 'true':
            $assertionResult = ($scenarioInputResult === true);
            break;
        case 'false':
            $assertionResult = ($scenarioInputResult === false);
            break;
        case 'isJsonString':
            $assertionResult = (json_decode($value) !== null && JSON_ERROR_NONE === json_last_error());
            break;
        case 'isObject':
            $assertionResult = (is_object($scenarioInputResult));
            break;
    }

    return $assertionResult;
}
