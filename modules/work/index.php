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

$require_current_course = true;
$require_login = true;
$require_user_registration = true;
$require_help = true;
$helpTopic = 'assignments';

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'functions.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/gradebook/functions.php';
require_once 'modules/attendance/functions.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/plagiarism/plagiarism.php';
require_once 'modules/progress/AssignmentEvent.php';
require_once 'modules/progress/AssignmentSubmitEvent.php';
require_once 'modules/analytics/AssignmentAnalyticsEvent.php';
require_once 'modules/lti_consumer/lti-functions.php';
require_once 'modules/admin/extconfig/turnitinapp.php';

// For colorbox, fancybox, shadowbox use
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();
/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_ASSIGN);
/* * *********************************** */

$unit = isset($unit)? $unit: null;

load_js('datatables');
load_js('tools.js');

// D3 / C3 used to display assignment grade graph
if ($is_editor and isset($_GET['id']) and isset($_GET['disp_results'])) {
    require_once 'modules/usage/usage.lib.php';
    $head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.4.10/c3.css' />";
    load_js('d3/d3.min.js');
    load_js('c3-0.4.10/c3.min.js');
}

$workPath = $webDir . "/courses/" . $course_code . "/work";
$works_url = array('url' => "{$urlAppend}modules/work/index.php?course=$course_code", 'name' => $langWorks);
$toolName = $langWorks;

// Auto Judge settings (if any)
$autojudge = new AutojudgeApp();
if ($autojudge->isConfigured()) {
    $autojudge = AutojudgeApp::getAutojudge();
}

//-------------------------------------------
// main program
//-------------------------------------------
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if ($is_course_reviewer) {
        // info about assignments assigned to users and groups
        if (isset($_GET['ass_info_assigned_to'])) {
            echo "<ul>";
            $q = Database::get()->queryArray("SELECT user_id, group_id FROM assignment_to_specific WHERE assignment_id = ?d", $_GET['ass_id']);
            foreach ($q as $user_data) {
                if ($user_data->user_id == 0) { // assigned to group
                    $group_name = Database::get()->querySingle("SELECT name FROM `group` WHERE id = ?d", $user_data->group_id)->name;
                    echo "<li>$group_name</li>";
                } else { // assigned to user
                    echo "<li>" . q(uid_to_name($user_data->user_id)) . "</li>";
                }
            }
            echo "</ul>";
            exit;
        }
    }
    if (isset($_POST['sid'])) {
        $sid = $_POST['sid'];
        $data['submission_text'] = Database::get()->querySingle("SELECT submission_text FROM assignment_submit WHERE id = ?d", $sid)->submission_text;
    } elseif (isset($_POST['assign_type']) or (isset($_POST['assign_g_type']) and $_POST['assign_g_type'] == 2)) {
        $data = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
    } else {
        $data = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                FROM user, course_user
                                WHERE user.id = course_user.user_id
                                AND course_user.course_id = ?d
                                AND course_user.status = " . USER_STUDENT . "
                                AND user.id
                                ORDER BY surname", $course_id);

    }
    echo json_encode($data);
    exit;
}

// Data Tables
if ($is_editor) {
    //  default ordering is deadline
    $order = "[3, 'asc']";
    // disable ordering for action button column
    $columns = 'null, null, null, null, { orderable: false }';
} else {
    //  default ordering is deadline
    $order = "[1, 'asc']";
    if (get_config('eportfolio_enable')) {
        $columns = 'null, null, null, null, { orderable: false }';
    } else {
        $columns = 'null, null, null, null';
    }
}

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#assignment_table').DataTable ({
                'columns': [ $columns ],
                'fnDrawCallback': function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
                'aLengthMenu': [
                   [10, 20, 30 , -1],
                   [10, 20, 30, '$langAllOfThem'] // change per page values here
                ],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [ $order ],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
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
            $('.dataTables_filter input').attr({
                  'class' : 'form-control input-sm ms-0 mb-3',
                  'placeholder' : '$langSearch...'
            });
            $('.dataTables_filter label').attr('aria-label', '$langSearch');

            $(document).on('click', '.assigned_to', function(e) {
                  e.preventDefault();
                  var ass_id = $(this).data('ass_id');
                  url = '$urlAppend' + 'modules/work/index.php?ass_info_assigned_to=true&ass_id='+ass_id;
                  $.ajax({
                    url: url,
                    success: function(data) {
                        var dialog = bootbox.dialog({
                            message: data,
                            title : '$m[WorkAssignTo]',
                            onEscape: true,
                            backdrop: true,
                            buttons: {
                                success: {
                                    label: '$langClose',
                                    className: 'cancelAdminBtn',
                                }
                            }
                        });
                        dialog.init(function() {
                            typeof MathJax !== 'undefined' && MathJax.typeset();
                        });
                    }
                  });
              });
        });
        </script>";

//Gets the student's assignment file ($file_type=NULL)
//or the teacher's assignment ($file_type=1)
if (isset($_GET['get']) or isset($_GET['getcomment'])) {
    if (isset($_GET['getcomment'])) {
        $file_type = 2;
        $get = $_GET['getcomment'];
    } else {
        if (isset($_GET['file_type']) && $_GET['file_type']==1) {
            $file_type = intval($_GET['file_type']);
        } else {
            $file_type = NULL;
        }
        $get = $_GET['get'];
    }
    if (!send_file(intval($get), $file_type)) {
        Session::flash('message',$langFileNotFound);
        Session::flash('alert-class', 'alert-danger');
    }
}

if (isset($_GET['chk'])) { // plagiarism check
    $file_id = $_GET['chk'];
    $file_details = Database::get()->querySingle("SELECT assignment_id, file_path, file_name FROM assignment_submit WHERE id = ?d", $file_id);
    if ($file_details) {
        $assign_id = $file_details->assignment_id;
        $true_file_name = $file_details->file_name;
        $secret = work_secret($file_details->assignment_id);
        $true_file_path = $workPath . "/" . $file_details->file_path;
    } else {
        Session::flash('message',$langFileNotFound);
        Session::flash('alert-class', 'alert-danger');
    }
    send_file_for_plagiarism($assign_id, $file_id, $true_file_path, $true_file_name);
}


// Only course admins can download all assignments in a zip file
if ($is_editor) {
    if (isset($_GET['download'])) {
        $as_id = intval($_GET['download']);
        // Allow unlimited time for creating the archive
        set_time_limit(0);
        if (!download_assignments($as_id)) {
            Session::flash('message',$langNoAssignmentsExist);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$as_id);
        }
    }
}

// Whether to include AutoJudge JavaScript code
$need_autojudge_js = isset($_GET['add']) || (isset($_GET['choice']) && $_GET['choice'] == 'edit');

if ($is_course_reviewer) {
    $head_content .= "<script type='text/javascript'>
    $(function () {
        $('.onlineText') . click(function (e){
            e.preventDefault();
            var sid = $(this) . data('id');
            var assignment_title = $('#assignment_title') . text();
            $.ajax({
                  type: 'POST',
                  url: '',
                  datatype: 'json',
                  data: {
                    sid: sid
                  },
                  success: function (data){
                  data = $.parseJSON(data);
                  bootbox . alert({
                        title: assignment_title,
                        size: 'large',
                        message: data . submission_text ? data . submission_text : '',
                    });
                  },
                  error: function (xhr, textStatus, error) {
                       console . log(xhr . statusText);
                       console . log(textStatus);
                       console . log(error);
                  }
                });
            });
        })
    </script>";
}


if ($is_editor) {
    $head_content .= "<script type='text/javascript'>
        $(function () {
            initialize_filemodal({
                download: '$GLOBALS[langDownload]',
                print: '$GLOBALS[langPrint]',
                fullScreen: '$GLOBALS[langFullScreen]',
                newTab: '$GLOBALS[langNewTab]',
                cancel: '$GLOBALS[langCancel]'
            });

            // assignment delete confirmation
            $(document).on('click', '.linkdelete', function(e) {
                var link = $(this).attr('href');
                e.preventDefault();


                // bootbox.confirm('$langDelWarnUserAssignment', function(result) {
                //     if (result) {
                //         document.location.href = link;
                //     }
                // });


                bootbox.confirm({
                    closeButton: false,
                    title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><div class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</div>',
                    message: '<p class=\'text-center\'>".js_escape($langDelWarnUserAssignment)."</p>',
                    buttons: {
                        cancel: {
                            label: '".js_escape($langCancel)."',
                            className: 'cancelAdminBtn position-center'
                        },
                        confirm: {
                            label: '".js_escape($langDelete)."',
                            className: 'deleteAdminBtn position-center',
                        }
                    },
                    callback: function (result) {
                        if(result) {
                            document.location.href = link;
                        }
                    }
                });


            });
        });";

    if ($need_autojudge_js and $autojudge->isEnabled() and !isset($_GET['disp_results'])) {
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
        $('input[name=group_submissions]').click(changeAssignLabel);
        $('input[id=assign_button_some]').click(ajaxAssignees);
        $('input[id=assign_button_group]').click(ajaxAssignees);
        $('input[id=assign_button_all]').click(hideAssignees);
        ";

        if ($need_autojudge_js and $autojudge->isEnabled()) {
            $head_content .= "
            $('input[name=auto_judge]').click(changeAutojudgeScenariosVisibility);
            $(document).ready(function() { changeAutojudgeScenariosVisibility.apply($('input[name=auto_judge]')); });
            ";
        }

        $head_content .= "
        function hideAssignees()
        {
            $('#assignees_tbl').addClass('hide');
            $('#assignee_box').find('option').remove();
        }
        function changeAssignLabel()
        {
            var assign_to_specific = $('input:radio[name=assign_to_specific]:checked').val();
            if ((assign_to_specific==1) || (assign_to_specific==2)) {
               ajaxAssignees();
            }
            if (this.id=='group_button') {
               $('#assign_button_all_text').text('$m[WorkToAllGroups]');
               $('#assign_button_some_text').text('$m[WorkToGroup]');
               $('#assignees').text('$langGroups');
               $('#assign_group_div').hide();
            } else {
               $('#assign_button_all_text').text('$m[WorkToAllUsers]');
               $('#assign_button_some_text').text('$m[WorkToUser]');
               $('#assign_button_group_text').text('$m[WorkToGroup]');
               $('#assignees').text('$langStudents');
               $('#assign_group_div').show();
            }
        }
        function ajaxAssignees()
        {
            $('#assignees_tbl').removeClass('hide');
            var type = $('input:radio[name=group_submissions]:checked').val();
            var g_type = $('input:radio[name=assign_to_specific]:checked').val();
            $.post('$works_url[url]',
            {
              assign_type: type,
              assign_g_type: g_type,
            },
            function(data,status) {
                var index;
                var parsed_data = JSON.parse(data);
                var select_content = '';
                if (type == 0) {
                    if (g_type == 1) {
                        for (index = 0; index < parsed_data.length; ++index) {
                            select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['surname'] + ' ' + parsed_data[index]['givenname'] + '<\/option>';
                        }
                    } else if (g_type == 2) {
                        for (index = 0; index < parsed_data.length; ++index) {
                            select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['name'] + '<\/option>';
                        }
                    }
                } else {
                   for (index = 0; index < parsed_data.length; ++index) {
                        select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['name'] + '<\/option>';
                    }
                }
                $('#assignee_box').find('option').remove();
                $('#assign_box').find('option').remove().end().append(select_content);
            });
        }";
        if ($need_autojudge_js and $autojudge->isEnabled()) {
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

    $email_notify = (isset($_POST['send_email']) && $_POST['send_email']);
    if (isset($_POST['grade_comments'])) {
        $work_title = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", intval($_POST['assignment']))->title;
        $pageName = $work_title;
        $navigation[] = $works_url;
        submit_grade_comments($_POST);
    } elseif (isset($_POST['ass_review'])) {
        $id = intval($_POST['assign']);
        submit_review_per_ass($id);
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
            Session::flash('message',$langDeleted);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message',$langDelError);
            Session::flash('alert-class', 'alert-danger');
        }
        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
    } elseif (isset($_POST['grades']) || isset($_POST['grade_rubric']) ) {
        $navigation[] = $works_url;
        submit_grades(intval($_POST['grades_id']), $_POST, $email_notify);

    } elseif (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        $work_title_raw = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $id)->title;
        $work_title = q($work_title_raw);
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
                        Session::flash('message',$langAssignmentDeactivated);
                        Session::flash('alert-class', 'alert-success');
                    }
                } else {
                    Session::flash('message',$langResourceBelongsToCert);
                    Session::flash('alert-class', 'alert-warning');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'enable') {
                if (Database::get()->query("UPDATE assignment SET active = '1' WHERE id = ?d", $id)->affectedRows > 0) {
                    Session::flash('message',$langAssignmentActivated);
                    Session::flash('alert-class', 'alert-success');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'do_delete') {
                if (!resource_belongs_to_progress_data(MODULE_ID_ASSIGN, $id)) {
                    if(delete_assignment($id)) {
                        Session::flash('message',$langDeleted);
                        Session::flash('alert-class', 'alert-success');
                    } else {
                        Session::flash('message',$langDelError);
                        Session::flash('alert-class', 'alert-danger');
                    }
                } else {
                    Session::flash('message',$langResourceBelongsToCert);
                    Session::flash('alert-class', 'alert-warning');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'do_delete_file') {
                if(delete_teacher_assignment_file($id)){
                    Session::flash('message',$langDelF);
                    Session::flash('alert-class', 'alert-success');
                } else {
                    Session::flash('message',$langDelF);
                    Session::flash('alert-class', 'alert-danger');
                }
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id&choice=edit");
            } elseif ($choice == 'do_purge') {
                if (!resource_belongs_to_progress_data(MODULE_ID_ASSIGN, $id)) {
                    if (purge_assignment_subs($id)) {
                        Session::flash('message',$langAssignmentSubsDeleted);
                        Session::flash('alert-class', 'alert-success');
                    }
                } else {
                    Session::flash('message',$langResourceBelongsToCert);
                    Session::flash('alert-class', 'alert-warning');
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
            $pageName = $work_title_raw;
            $navigation[] = $works_url;
            if (isset($_GET['disp_results'])) {
                display_assignment_submissions_graph_results($id);
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
}  elseif ($is_course_reviewer) { // course reviewer view
    if (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        $work_title_raw = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $id)->title;
        $pageName = q($work_title_raw);
        $navigation[] = $works_url;
        show_assignment($id);
    } else {
        $pageName = $langWorks;
        show_assignments();
    }
} else {
    if (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        if (isset($_POST['work_submit'])) {
            $pageName = $m['SubmissionStatusWorkInfo'];
            if (!$unit) {
                $navigation[] = $works_url;
            }
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id", 'name' => $langWorks);
            submit_work($id);
        } else {
            $work_title_raw = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $id)->title;
            $pageName = q($work_title_raw);
            if (!isset($unit)) {
                $navigation[] = $works_url;
            }
            show_student_assignment($id);
        }
    } else {
        show_student_assignments();
    }
    //call  submit_grade_reviews
    if (isset($_POST['grade_comments_review'])) {
        $work_title_raw = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", intval($_POST['assignment']))->title;
        $pageName = q($work_title_raw);
        if (!isset($unit)) {
            $navigation[] = $works_url;
        }
        submit_grade_reviews($_POST);
    }
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);

//-------------------------------------
// end of main program
//-------------------------------------

/**
 * @brief Valitron rule to match IPv4/6 and IPv4/6 CIDR ranges
 * @param string $field field name (ignored)
 * @param array $value array of IPs
 * @param array $params ignored
 */
function ipORcidr($field, $value, array $params) {
    foreach ($value as $ip){
        $valid = isIPv4($ip) || isIPv4cidr($ip) || isIPv6($ip) || isIPv6cidr($ip);
        if (!$valid) {
            return false;
        }
    }
    return true;
};

/**
 * @brief insert the assignment into the database
 * @return type
 */
function add_assignment() {
    global $workPath, $course_id, $uid, $langTheField, $m, $langTitle,
        $langErrorCreatingDirectory, $langGeneralError, $langPeerReviewPerUserCompulsory,
        $course_code, $langFormErrors, $langNewAssignSuccess, $langIPInvalid,
        $langPeerReviewStartDateCompulsory, $langPeerReviewEndDateCompulsory,
        $langPeerReviewDeadlineCompulsory, $langPeerReviewStartDateError,
        $langPeerReviewStartDateError2;

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->rule('integer', array('group_submissions', 'assign_to_specific'));
    $v->addRule('ipORcidr', 'ipORcidr', $langIPInvalid);
    $v->rule('ipORcidr', array('assignmentIPLock'));
    if (isset($_POST['max_grade'])) {
        $v->rule('required', array('max_grade'));
        $v->rule('numeric', array('max_grade'));
        $v->labels(array('max_grade' => "$langTheField $m[max_grade]"));
    }
    //upoxrewtika pedia sthn epilogh aksiologhsh apo omotimous
    elseif (isset($_POST['reviews_per_user'])){
        $v->rule('required', array('reviews_per_user'));
        $v->rule('numeric', array('reviews_per_user'));
        $v->rule('min', array('reviews_per_user'), 3);
        $v->rule('max', array('reviews_per_user'), 5);
        $v->labels(array('reviews_per_user' => "$langPeerReviewPerUserCompulsory"));

        $v->rule('required', array('WorkStart_review'));
        $v->labels(array('WorkStart_review' => "$langPeerReviewStartDateCompulsory"));

        $v->rule('required', array('WorkEnd_review'));
        $v->labels(array('WorkEnd_review' => "$langPeerReviewEndDateCompulsory"));

        $v->rule('required', array('WorkEnd'));
        $v->labels(array('WorkEnd' => "$langPeerReviewDeadlineCompulsory"));

        if ( isset($_POST['WorkStart_review'] ) < isset($_POST['WorkEnd']) )  {
            /*$v->addRule('error', 'error', $langrevnvalid);
            $v->rule('error', array('WorkStart_review'));*/
            $v->rule('min',array('WorkStart_review'), "$langPeerReviewStartDateError2");
            $v->labels(array('WorkStart_review' => "$langPeerReviewStartDateError"));
        }
    }
    $v->labels(array('title' => "$langTheField $langTitle"));
    if ($v->validate()) {
        $title = $_POST['title'];
        $desc =$_POST['desc'];
        $submission_date = isset($_POST['WorkStart']) && !empty($_POST['WorkStart']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkStart'])->format('Y-m-d H:i:s') : (new DateTime('NOW'))->format('Y-m-d H:i:s');
        $deadline = isset($_POST['WorkEnd']) && !empty($_POST['WorkEnd']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkEnd'])->format('Y-m-d H:i:s') : NULL;
        //aksiologhseis ana xrhsth
        $reviews_per_user = isset($_POST['reviews_per_user']) && !empty($_POST['reviews_per_user']) ? $_POST['reviews_per_user']: NULL;
        //hmeromhnia enarkshs ths aksiologhshs apo omotimous
        $submission_date_review = isset($_POST['WorkStart_review']) && !empty($_POST['WorkStart_review']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkStart_review'])->format('Y-m-d H:i:s') : NULL;
        //deadline aksiologhshs apo omotimous
        $deadline_review = isset($_POST['WorkEnd_review']) && !empty($_POST['WorkEnd_review']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkEnd_review'])->format('Y-m-d H:i:s') :NULL;
        $submission_type = isset($_POST['submission_type']) ? intval($_POST['submission_type']) : 0;
        $late_submission = isset($_POST['late_submission']) ? 1 : 0;
        $group_submissions = $_POST['group_submissions'];
        $notify_submission = isset($_POST['notify_submission']) ? 1 : 0;

        if (isset($_POST['grading_type'])) {
            $grade_type = $_POST['grading_type'];
        } else {
            $grade_type = ASSIGNMENT_STANDARD_GRADE;
        }

        if (isset($_POST['scale'])) {
            $max_grade = max_grade_from_scale($_POST['scale']);
            $grading_scale_id = $_POST['scale'];
        } elseif (isset($_POST['rubric'])) {
            $max_grade = max_grade_from_rubric($_POST['rubric']);
            $grading_scale_id = $_POST['rubric'];
        } elseif (isset($_POST['max_grade'])) {
            $max_grade = $_POST['max_grade'];
            $grading_scale_id = 0;
        } elseif (isset($_POST['reviews_per_user'])) { // peer review
            $max_grade = max_grade_from_rubric($_POST['rubric_review']);
            $grading_scale_id = $_POST['rubric_review'];
        }

        if (!isset($max_grade)) {
            $max_grade = isset($_POST['max_grade'])? $_POST['max_grade']: 0;
        }
        $assign_to_specific = $_POST['assign_to_specific'];
        $assigned_to = filter_input(INPUT_POST, 'ingroup', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
        $auto_judge           = isset($_POST['auto_judge']) ? filter_input(INPUT_POST, 'auto_judge', FILTER_VALIDATE_INT) : 0;
        $auto_judge_scenarios = isset($_POST['auto_judge_scenarios']) ? serialize($_POST['auto_judge_scenarios']) : "";
        $lang                 = isset($_POST['lang']) ? filter_input(INPUT_POST, 'lang') : '';
        $secret = uniqid('');
        $password_lock = $_POST['assignmentPasswordLock'];
        if (isset($_POST['assignmentIPLock'])) {
            $ip_lock = implode(',', $_POST['assignmentIPLock']);
        } else {
            $ip_lock = '';
        }

        if ($assign_to_specific == 1 && empty($assigned_to)) {
            $assign_to_specific = 0;
        }
        $assignment_type = intval($_POST['assignment_type']);

        $lti_template = isset($_POST['lti_template']) ? $_POST['lti_template'] : NULL;
        $launchcontainer = isset($_POST['lti_launchcontainer']) ? $_POST['lti_launchcontainer'] : NULL;
        $tii_feedbackreleasedate = isset($_POST['tii_feedbackreleasedate']) && !empty($_POST['tii_feedbackreleasedate']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['tii_feedbackreleasedate'])->format('Y-m-d H:i:s') : NULL;
        $tii_internetcheck = isset($_POST['tii_internetcheck']) ? 1 : 0;
        $tii_institutioncheck = isset($_POST['tii_institutioncheck']) ? 1 : 0;
        $tii_journalcheck = isset($_POST['tii_journalcheck']) ? 1 : 0;
        $tii_s_view_reports = isset($_POST['tii_s_view_reports']) ? 1 : 0;;
        $tii_studentpapercheck = isset($_POST['tii_studentpapercheck']) ? 1 : 0;;
        $tii_use_biblio_exclusion = isset($_POST['tii_use_biblio_exclusion']) ? 1 : 0;;
        $tii_use_quoted_exclusion = isset($_POST['tii_use_quoted_exclusion']) ? 1 : 0;;
        $tii_report_gen_speed = 0;
        if (isset($_POST['tii_report_gen_speed']) && intval($_POST['tii_report_gen_speed']) == 1) {
            $tii_report_gen_speed = 1;
        } else if (isset($_POST['tii_report_gen_speed']) && intval($_POST['tii_report_gen_speed']) == 2) {
            $tii_report_gen_speed = 2;
        }
        $tii_submit_papers_to = 1;
        if (isset($_POST['tii_submit_papers_to']) && intval($_POST['tii_submit_papers_to']) == 0) {
            $tii_submit_papers_to = 0;
        } else if (isset($_POST['tii_submit_papers_to']) && intval($_POST['tii_submit_papers_to']) == 2) {
            $tii_submit_papers_to = 2;
        }
        $tii_exclude_type = "none";
        $tii_exclude_value = 0;
        if (isset($_POST['tii_use_small_exclusion'])) {
            $tii_exclude_type = $_POST['tii_exclude_type'];
            $tii_exclude_value = intval($_POST['tii_exclude_value']);
            if ($tii_exclude_type == "percentage" && $tii_exclude_value > 100) {
                $tii_exclude_value = 100;
            }
        }

        $fileCount = isset($_POST['fileCount'])? $_POST['fileCount']: 0;

        if (make_dir("$workPath/$secret") and make_dir("$workPath/admin_files/$secret")) {
            $id = Database::get()->query("INSERT INTO assignment
                    (course_id, title, description, deadline, late_submission,
                    comments, submission_type, submission_date, active, secret_directory,
                    group_submissions, grading_type, max_grade, grading_scale_id,
                    assign_to_specific, auto_judge, auto_judge_scenarios, lang,
                    notification, password_lock, ip_lock, assignment_type, lti_template,
                    launchcontainer, tii_feedbackreleasedate, tii_internetcheck, tii_institutioncheck,
                    tii_journalcheck, tii_report_gen_speed, tii_s_view_reports, tii_studentpapercheck,
                    tii_submit_papers_to, tii_use_biblio_exclusion, tii_use_quoted_exclusion,
                    tii_exclude_type, tii_exclude_value, reviews_per_assignment,
                    start_date_review, due_date_review, max_submissions)
                VALUES (?d, ?s, ?s, ?t, ?d, ?s, ?d, ?t, 1, ?s, ?d, ?d, ?f, ?d, ?d, ?d, ?s, ?s, ?d, ?s, ?s, ?d, ?d, ?d, ?t,
                ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s, ?d, ?d, ?t, ?t, ?d)",
                $course_id, $title, $desc, $deadline, $late_submission, '',
                $submission_type, $submission_date, $secret, $group_submissions, $grade_type,
                $max_grade, $grading_scale_id, $assign_to_specific, $auto_judge,
                $auto_judge_scenarios, $lang, $notify_submission, $password_lock,
                $ip_lock, $assignment_type, $lti_template, $launchcontainer, $tii_feedbackreleasedate,
                $tii_internetcheck, $tii_institutioncheck, $tii_journalcheck, $tii_report_gen_speed,
                $tii_s_view_reports, $tii_studentpapercheck, $tii_submit_papers_to, $tii_use_biblio_exclusion,
                $tii_use_quoted_exclusion, $tii_exclude_type, $tii_exclude_value, $reviews_per_user,
                $submission_date_review, $deadline_review, $fileCount)->lastInsertID;

            if ($id) {
                // tags
                $moduleTag = new ModuleElement($id);
                if (isset($_POST['tags'])) {
                    $moduleTag->syncTags($_POST['tags']);
                } else {
                    $moduleTag->syncTags(array());
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
                    $ext = get_file_extension($_FILES['userfile']['name']);
                    $filename = "$secret/$local_name" . (empty($ext) ? '' : '.' . $ext);
                    if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/admin_files/$filename")) {
                        @chmod("$workPath/admin_files/$filename", 0644);
                        $file_name = $_FILES['userfile']['name'];
                        Database::get()->query("UPDATE assignment SET file_path = ?s, file_name = ?s WHERE id = ?d", $filename, $file_name, $id);
                    }
                }
                if ($assign_to_specific && !empty($assigned_to)) {
                    if (($group_submissions == 1) or ($assign_to_specific == 2)) {
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
                Session::flash('message',$langNewAssignSuccess);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
            } else {
                @rmdir("$workPath/$secret");
                Session::flash('message',$langGeneralError);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
            }
        } else {
            Session::flash('message',$langErrorCreatingDirectory);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
    }
}

/**
 * @brief edit assignment
 * @param type $id
 * @return type
 */
function edit_assignment($id) {
    global $langEditSuccess, $m, $langTheField, $course_code,
        $course_id, $uid, $workPath, $langFormErrors, $langTitle,
        $langIPInvalid, $langPeerReviewPerUserCompulsory,
        $langPeerReviewStartDateCompulsory, $langPeerReviewEndDateCompulsory,
        $langPeerReviewDeadlineCompulsory, $langPeerReviewStartDateError2,
        $langPeerReviewStartDateError;

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->rule('integer', array('group_submissions', 'assign_to_specific'));
    $v->addRule('ipORcidr', 'ipORcidr', $langIPInvalid);
    $v->rule('ipORcidr', array('assignmentIPLock'));

    if (isset($_POST['max_grade'])) {
        $v->rule('required', array('max_grade'));
        $v->rule('numeric', array('max_grade'));
        $v->labels(array('max_grade' => "$langTheField $m[max_grade]"));
    }
    //upoxrewtika pedia sthn epilogh aksiologhsh apo omotimous
    if (isset($_POST['reviews_per_user']) and !empty($_POST['reviews_per_user'])) {
        $v->rule('required', array('reviews_per_user'));
        $v->rule('numeric', array('reviews_per_user'));
        $v->rule('min', array('reviews_per_user'), 3);
        $v->rule('max', array('reviews_per_user'), 5);
        $v->labels(array('reviews_per_user' => "$langPeerReviewPerUserCompulsory"));

        $v->rule('required', array('WorkStart_review'));
        $v->labels(array('WorkStart_review' => "$langPeerReviewStartDateCompulsory"));
        $v->rule('required', array('WorkEnd_review'));
        $v->labels(array('WorkEnd_review' => "$langPeerReviewEndDateCompulsory"));

        $v->rule('required', array('WorkEnd'));
        $v->labels(array('WorkEnd' => "$langPeerReviewDeadlineCompulsory"));

        if ($_POST['WorkStart_review'] < $_POST['WorkEnd']) {
            $v->rule('min',array('WorkStart_review'), "$langPeerReviewStartDateError2");
            $v->labels(array('WorkStart_review' => "$langPeerReviewStartDateError"));
        }
    }

    $v->labels(array('title' => "$langTheField $langTitle"));
    if ($v->validate()) {
        $row = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
        $title = $_POST['title'];
        $desc = purify($_POST['desc']);
        if (isset($_POST['reviews_per_user'])) {
            $reviews_per_user = $_POST['reviews_per_user'];
        }
        $submission_type = isset($_POST['submission_type']) ? intval($_POST['submission_type']) : 0;
        $submission_date = isset($_POST['WorkStart']) && !empty($_POST['WorkStart']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkStart'])->format('Y-m-d H:i:s') : (new DateTime('NOW'))->format('Y-m-d H:i:s');
        $deadline = isset($_POST['WorkEnd']) && !empty($_POST['WorkEnd']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkEnd'])->format('Y-m-d H:i:s') : NULL;
        //hmeromhnia enarkshs ths aksiologhshs apo omotimous
        $submission_date_review = isset($_POST['WorkStart_review']) && !empty($_POST['WorkStart_review']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkStart_review'])->format('Y-m-d H:i:s') : NULL;
        //deadline aksiologhshs apo omotimous
        $deadline_review = isset($_POST['WorkEnd_review']) && !empty($_POST['WorkEnd_review']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['WorkEnd_review'])->format('Y-m-d H:i:s') : NULL;
        $late_submission = isset($_POST['late_submission']) ? 1 : 0;
        $group_submissions = $_POST['group_submissions'];
        $grade_type = $_POST['grading_type'];

        if (isset($_POST['rubric_review']) && isset($_POST['reviews_per_user']) && ($grade_type == ASSIGNMENT_PEER_REVIEW_GRADE)) {
            $max_grade = max_grade_from_rubric($_POST['rubric_review']);
            $grading_scale_id = $_POST['rubric_review'];
        } elseif (isset($_POST['scale']) && ($grade_type == ASSIGNMENT_SCALING_GRADE)) {
            $max_grade = max_grade_from_scale($_POST['scale']);
            $grading_scale_id = $_POST['scale'];
        } elseif (isset($_POST['rubric']) && ($grade_type == ASSIGNMENT_RUBRIC_GRADE)) {
            $max_grade = max_grade_from_rubric($_POST['rubric']);
            $grading_scale_id = $_POST['rubric'];
        } elseif (isset($_POST['max_grade']) && ($grade_type == ASSIGNMENT_STANDARD_GRADE)) {
            $max_grade = $_POST['max_grade'];
            $grading_scale_id = 0;
        }

        $assign_to_specific = filter_input(INPUT_POST, 'assign_to_specific', FILTER_VALIDATE_INT);
        $assigned_to = filter_input(INPUT_POST, 'ingroup', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
        $auto_judge           = isset($_POST['auto_judge']) ? filter_input(INPUT_POST, 'auto_judge', FILTER_VALIDATE_INT) : 0;
        $auto_judge_scenarios = isset($_POST['auto_judge_scenarios']) ? serialize($_POST['auto_judge_scenarios']) : "";
        $lang                 = isset($_POST['lang']) ? filter_input(INPUT_POST, 'lang') : '';

        $fileCount = isset($_POST['fileCount'])? $_POST['fileCount']: 0;

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
            $student_name = trim(uid_to_name($uid));
            $local_name = !empty($student_name)? $student_name : uid_to_name($uid, 'username');
            $am = Database::get()->querySingle("SELECT am FROM user WHERE id = ?d", $uid)->am;
            if (!empty($am)) {
                $local_name .= $am;
            }
            $local_name = greek_to_latin($local_name);
            $local_name = replace_dangerous_char($local_name);
            $secret = $row->secret_directory;
            $ext = get_file_extension($_FILES['userfile']['name']);
            $filename = "$secret/$local_name" . (empty($ext) ? '' : '.' . $ext);
            make_dir("$workPath/admin_files/$secret");
            if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/admin_files/$filename")) {
                @chmod("$workPath/admin_files/$filename", 0644);
                $file_name = $_FILES['userfile']['name'];
            }
        }
        $notify_submission = isset($_POST['notify_submission']) ? 1 : 0;
        $assignment_type = intval($_POST['assignment_type']);
        $lti_template = isset($_POST['lti_template']) ? $_POST['lti_template'] : NULL;
        $launchcontainer = isset($_POST['lti_launchcontainer']) ? $_POST['lti_launchcontainer'] : NULL;
        $tii_feedbackreleasedate = isset($_POST['tii_feedbackreleasedate']) && !empty($_POST['tii_feedbackreleasedate']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['tii_feedbackreleasedate'])->format('Y-m-d H:i:s') : NULL;
        $tii_internetcheck = isset($_POST['tii_internetcheck']) ? 1 : 0;
        $tii_institutioncheck = isset($_POST['tii_institutioncheck']) ? 1 : 0;
        $tii_journalcheck = isset($_POST['tii_journalcheck']) ? 1 : 0;
        $tii_s_view_reports = isset($_POST['tii_s_view_reports']) ? 1 : 0;
        $tii_studentpapercheck = isset($_POST['tii_studentpapercheck']) ? 1 : 0;
        $tii_use_biblio_exclusion = isset($_POST['tii_use_biblio_exclusion']) ? 1 : 0;
        $tii_use_quoted_exclusion = isset($_POST['tii_use_quoted_exclusion']) ? 1 : 0;
        $tii_report_gen_speed = 0;
        if (isset($_POST['tii_report_gen_speed']) && intval($_POST['tii_report_gen_speed']) == 1) {
            $tii_report_gen_speed = 1;
        } else if (isset($_POST['tii_report_gen_speed']) && intval($_POST['tii_report_gen_speed']) == 2) {
            $tii_report_gen_speed = 2;
        }
        $tii_submit_papers_to = 1;
        if (isset($_POST['tii_submit_papers_to']) && intval($_POST['tii_submit_papers_to']) == 0) {
            $tii_submit_papers_to = 0;
        } else if (isset($_POST['tii_submit_papers_to']) && intval($_POST['tii_submit_papers_to']) == 2) {
            $tii_submit_papers_to = 2;
        }
        $tii_exclude_type = "none";
        $tii_exclude_value = 0;
        if (isset($_POST['tii_use_small_exclusion'])) {
            $tii_exclude_type = $_POST['tii_exclude_type'];
            $tii_exclude_value = intval($_POST['tii_exclude_value']);
            if ($tii_exclude_type == "percentage" && $tii_exclude_value > 100) {
                $tii_exclude_value = 100;
            }
        }

        Database::get()->query("UPDATE assignment SET title = ?s, description = ?s,
                group_submissions = ?d, comments = ?s, submission_type = ?d,
                deadline = ?t, late_submission = ?d, submission_date = ?t, grading_type = ?d, max_grade = ?f,
                grading_scale_id = ?d, assign_to_specific = ?d, file_path = ?s, file_name = ?s,
                auto_judge = ?d, auto_judge_scenarios = ?s, lang = ?s, notification = ?d,
                password_lock = ?s, ip_lock = ?s, assignment_type = ?d, lti_template = ?d, launchcontainer = ?d,
                tii_feedbackreleasedate = ?t, tii_internetcheck = ?d, tii_institutioncheck = ?d,
                tii_journalcheck = ?d, tii_report_gen_speed = ?d, tii_s_view_reports = ?d, tii_studentpapercheck = ?d,
                tii_submit_papers_to = ?d, tii_use_biblio_exclusion = ?d, tii_use_quoted_exclusion = ?d,
                tii_exclude_type = ?s, tii_exclude_value = ?d, reviews_per_assignment = ?d,
                start_date_review = ?t, due_date_review = ?t,
                max_submissions = ?d
            WHERE course_id = ?d AND id = ?d",
            $title, $desc, $group_submissions, $comments, $submission_type,
            $deadline, $late_submission, $submission_date, $grade_type, $max_grade,
            $grading_scale_id, $assign_to_specific, $filename, $file_name,
            $auto_judge, $auto_judge_scenarios, $lang, $notify_submission,
            $_POST['assignmentPasswordLock'],
            isset($_POST['assignmentIPLock'])? implode(',', $_POST['assignmentIPLock']): '',
            $assignment_type, $lti_template, $launchcontainer, $tii_feedbackreleasedate,
            $tii_internetcheck, $tii_institutioncheck, $tii_journalcheck, $tii_report_gen_speed,
            $tii_s_view_reports, $tii_studentpapercheck, $tii_submit_papers_to, $tii_use_biblio_exclusion,
            $tii_use_quoted_exclusion, $tii_exclude_type, $tii_exclude_value, $reviews_per_user,
            $submission_date_review, $deadline_review, $fileCount, $course_id, $id);

        // purge old entries (if any)
        Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);
        // tags
        $moduleTag = new ModuleElement($id);
        if (isset($_POST['tags'])) {
            $moduleTag->syncTags($_POST['tags']);
        } else {
            $moduleTag->syncTags(array());
        }
        if ($assign_to_specific && !empty($assigned_to)) {
            if (($group_submissions == 1) or ($assign_to_specific == 2)) {
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
        Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY,
            array('id' => $id,
                  'title' => $title,
                  'description' => $desc,
                  'deadline' => $deadline,
                  'group' => $group_submissions));

        Session::flash('message',$langEditSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id&choice=edit");
    }
}


/**
 * @brief submit assignment
 * @param type $id
 * @param type $on_behalf_of
 */
function submit_work($id, $on_behalf_of = null) {
    global $course_id, $uid, $unit, $langOnBehalfOfGroupComment,
           $works_url, $langOnBehalfOfUserComment, $workPath,
           $langUploadSuccess, $langUploadError, $course_code,
           $langAutoJudgeInvalidFileType, $langExerciseNotPermit, $langNoFileUploaded,
           $langAutoJudgeScenariosPassed, $autojudge, $langEmptyFaculte;


    $row = Database::get()->querySingle("SELECT id, title, group_submissions, submission_type, submission_date,
                            deadline, late_submission, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                            auto_judge, auto_judge_scenarios, lang, max_grade, notification, max_submissions
                            FROM assignment
                            WHERE course_id = ?d AND id = ?d",
                            $course_id, $id);

    $notification = $row->notification;
    $auto_judge = $row->auto_judge;
    $auto_judge_scenarios = $auto_judge ? unserialize($row->auto_judge_scenarios) : null;
    $lang = $row->lang;
    $max_grade = $row->max_grade;

    if ($autojudge->isEnabled() && $auto_judge) {
        $langExt = $autojudge->getSupportedLanguages();
    }

    $nav[] = $works_url;
    $nav[] = array('url' => "$_SERVER[SCRIPT_NAME]?id=$id", 'name' => q($row->title));

    $submit_ok = FALSE; // Default do not allow submission
    if (isset($uid) && $uid) { // check if logged-in
        if ($GLOBALS['status'] == USER_GUEST) { // user is guest
            $submit_ok = FALSE;
        } else { // user NOT guest
            if (isset($_SESSION['courses'][$_SESSION['dbname']])) { // user is registered to this lesson
                $WorkStart = new DateTime($row->submission_date);
                $current_date = new DateTime('NOW');
                $interval = $WorkStart->diff($current_date);
                if ($WorkStart > $current_date) {
                    $submit_ok = FALSE; // before assignment
                } else if (($row->time < 0 && intval($row->deadline) && !$row->late_submission) and !$on_behalf_of) {
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
        //Preparing variables
        $user_id = isset($on_behalf_of) ? $on_behalf_of : $uid;
        if ($row->group_submissions) {
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : -1;
            $gids = user_group_info($on_behalf_of ? null : $user_id, $course_id);
        } else {
            $group_id = 0;
        }
        // If submission type is Online Text
        if ($row->submission_type == 1) {
            $filename = '';
            $file_name = '';
            $files_to_keep = [];
            if (isset($_POST['submission_text']) and !empty($_POST['submission_text'])) {
                $submission_text = purify($_POST['submission_text']);
                $success_msgs[] = $langUploadSuccess;
            } else {
                Session::flash('message',$langEmptyFaculte);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
            }
        } else { // If submission type is one or multiple files
            if ($row->group_submissions) {
                $local_name = isset($gids[$group_id]) ? greek_to_latin($gids[$group_id]) : '';
            } else {
                $student_name = trim(uid_to_name($user_id));
                $local_name = !empty($student_name)? $student_name : uid_to_name($user_id, 'username');
                $am = uid_to_am($user_id);
                if (!empty($am)) {
                    $local_name .= ' ' . $am;
                }
                $local_name = greek_to_latin($local_name);
            }
            $local_name .= ' (' . uid_to_name($user_id, 'username') . ')';
            $local_name = replace_dangerous_char($local_name);
            $local_name = work_secret($row->id) . '/' . $local_name;

            $files_to_keep = [];
            $file_name = $filename = $submission_text = '';
            $no_files = isset($on_behalf_of) && !isset($_FILES);

            if (!$no_files) {
                // Multiple files
                if ($row->submission_type == 2) {
                    $maxFiles = $row->max_submissions;
                    $totalFiles = 0;
                    $fileInfo = [];
                    foreach ($_FILES['userfile']['name'] as $i => $name) {
                        $status = $_FILES['userfile']['error'][$i];
                        if (!in_array($status, [UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE])) {
                            Session::flash('message',$langUploadError);
                            Session::flash('alert-class', 'alert-danger');
                            redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                        }
                        if ($status == UPLOAD_ERR_OK) {
                            $totalFiles++;
                        }
                    }
                    $fileCount = count($_FILES['userfile']['name']);
                    if ($totalFiles > $maxFiles) {
                        Session::flash('message',$GLOBALS['langWorkFilesCountExceeded']);
                        Session::flash('alert-class', 'alert-danger');
                        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                    }
                    if ($totalFiles == 1) {
                        $format = '';
                    } else {
                        $destDir = $workPath . '/' . $local_name;
                        if (!is_dir($destDir)) {
                            mkdir($destDir, 0755);
                        }
                        $format = '/%0' . strlen($totalFiles) . 'd';
                    }
                    $j = 1;
                    foreach ($_FILES['userfile']['name'] as $i => $file_name) {
                        if ($_FILES['userfile']['error'][$i] == UPLOAD_ERR_NO_FILE) {
                            continue;
                        }
                        validateUploadedFile($file_name, 2);
                        $ext = get_file_extension($file_name);
                        $filename = $local_name . sprintf($format, $j) . (empty($ext) ? '' : '.' . $ext);
                        $file_moved = move_uploaded_file($_FILES['userfile']['tmp_name'][$i], $workPath . '/' . $filename);
                        if (!$file_moved) {
                            break;
                        }
                        $fileInfo[] = [$filename, $file_name];
                        $files_to_keep[] = $filename;
                        $j++;
                    }
                    // keep details of first file for insert into DB
                    list($filename, $file_name) = $fileInfo[0];
                } else {
                    // Single file
                    if ($_FILES['userfile']['error'] == UPLOAD_ERR_NO_FILE) {
                        Session::flash('message', $langNoFileUploaded);
                        Session::flash('alert-class', 'alert-warning');
                        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                    }
                    if ($_FILES['userfile']['error'] == UPLOAD_ERR_CANT_WRITE) {
                        Session::flash('message', $langUploadError);
                        Session::flash('alert-class', 'alert-danger');
                        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                    }
                    $file_name = $_FILES['userfile']['name'];
                    validateUploadedFile($file_name, 2);
                    $ext = get_file_extension($file_name);
                    $filename = $local_name . (empty($ext) ? '' : '.' . $ext);
                    $file_moved = move_uploaded_file($_FILES['userfile']['tmp_name'], $workPath . '/' . $filename);
                    $files_to_keep = [$filename];
                }
                if (!$file_moved) {
                    Session::flash('message', $langUploadError);
                    Session::flash('alert-class', 'alert-danger');
                    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                }
            }
            $success_msgs[] = $langUploadSuccess;
        }

        $submit_ip = Log::get_client_ip();

        $grade_comments = $grade_ip = '';
        $grade = null;
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
                    $del_submission_msg = delete_submissions_by_uid(-1, $group_id, $row->id, $files_to_keep);
                    if (!empty($del_submission_msg)) {
                        $success_msgs[] = $del_submission_msg;
                    }
                }
            } else {
                $del_submission_msg = delete_submissions_by_uid($user_id, -1, $row->id, $files_to_keep);
                if (!empty($del_submission_msg)) {
                    $success_msgs[] = $del_submission_msg;
                }
            }
            $stud_comments = $_POST['stud_comments'];
        }
        if (isset($_POST['grade_rubric'])){
            $grade_rubric = serialize($_POST['grade_rubric']);
        } else {
            $grade_rubric = '';
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
                $grade_rubric,
                $grade_comments,
                $grade_ip,
                $group_id
            );
            $sid = Database::get()->query("INSERT INTO assignment_submit
                                    (uid, assignment_id, submission_date, submission_ip, file_path,
                                     file_name, submission_text, comments, grade, grade_rubric, grade_comments, grade_submission_ip,
                                     grade_submission_date, group_id)
                                     VALUES (?d, ?d, ". DBHelper::timeAfter() . ", ?s, ?s, ?s, ?s, ?s, ?f, ?s, ?s, ?s, " . DBHelper::timeAfter() . ", ?d)", $data)->lastInsertID;

            // for multifile submissions, add more records for files 2-n
            if ($row->submission_type == 2 && $totalFiles > 1) {
                array_shift($fileInfo); // first file has been inserted, so discard it
                foreach ($fileInfo as $file) {
                    $data = [$user_id, $row->id, $submit_ip, $file[0], $file[1], '', '', 0, '', '', '', $group_id];
                    Database::get()->query("INSERT INTO assignment_submit
                        (uid, assignment_id, submission_date, submission_ip, file_path,
                         file_name, submission_text, comments, grade, grade_rubric, grade_comments, grade_submission_ip,
                         grade_submission_date, group_id)
                         VALUES (?d, ?d, ". DBHelper::timeAfter() . ", ?s, ?s, ?s, ?s, ?s, ?f, ?s, ?s, ?s, " .
                         DBHelper::timeAfter() . ", ?d)", $data)->lastInsertID;
                }
            }

            triggerGame($course_id, $user_id, $row->id);
            triggerAssignmentSubmit($course_id, $user_id, $row->id);
            triggerAssignmentAnalytics($course_id, $user_id, $row->id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
            triggerAssignmentAnalytics($course_id, $user_id, $row->id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
            Log::record($course_id, MODULE_ID_ASSIGN, LOG_INSERT, array('id' => $sid,
                'title' => $row->title,
                'assignment_id' => $row->id,
                'filepath' => $filename,
                'filename' => $file_name,
                'comments' => $stud_comments,
                'group_id' => $group_id));

            // notify course admin (if requested)
            if ($notification) {
                notify_for_assignment_submission($row->title);
            }

            if ($row->group_submissions) {
                $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE id = ?d", $sid)->group_id;
                $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                foreach ($user_ids as $user_id) {
                    update_attendance_book($user_id->user_id, $row->id, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                    update_gradebook_book($user_id->user_id, $row->id, $grade/$row->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                }
            } else {
                $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                // update attendance book as well
                update_attendance_book($quserid, $row->id, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                //update gradebook if needed
                $book_grade = is_null($grade)? null: $grade / $row->max_grade;
                update_gradebook_book($quserid, $id, $book_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
            }
            if ($on_behalf_of and isset($_POST['send_email'])) {
                $email_grade = $_POST['grade'];
                $email_comments = $_POST['stud_comments'];
                grade_email_notify($row->id, $sid, $email_grade, $email_comments);
            }
        }

        // Send file to AutoJudge service
        if($autojudge->isEnabled()) {
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
                        $result = $autojudge->compile($input);
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
                    submit_grade_comments([
                        'assignment' => $id,
                        'submission' => $sid,
                        'grade' => $grade,
                        'comments' => $comment,
                        'send_email' => false,
                        'auto_judge_scenarios_output' => $auto_judge_scenarios_output,
                        'preventUiAlterations' => true,
                    ]);

            } else if ($auto_judge && $ext !== $langExt[$lang]) {
                if($lang == null) { die('Auto Judge is enabled but no language is selected'); }
                if($langExt[$lang] == null) { die('An unsupported language was selected. Perhaps platform-wide auto judge settings have been changed?'); }
                submit_grade_comments([
                    'assignment' => $id,
                    'submission' => $sid,
                    'grade' => 0,
                    'comments' => sprintf($langAutoJudgeInvalidFileType, $langExt[$lang], $ext),
                    'send_email' => false,
                    'auto_judge_scenarios_output' => null,
                    'preventUiAlterations' => true,
                ]);
            }
        }
        // End Auto-judge
        Session::flash('message', $success_msgs);
        Session::flash('alert-class', 'alert-success');
        if (isset($unit)) {
            redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit");
        } else {
            redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
        }

    } else { // not submit_ok
        Session::flash('message',$langExerciseNotPermit);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/work/index.php?course=$course_code");
    }
}


/**
 * @brief assignment - new assignment - prof view only
 */
function new_assignment() {
    global $tool_content, $m, $course_code, $course_id, $langAssignmentStartHelpBlock,
           $desc, $language, $head_content, $langGradeRubrics,
           $langSubmit, $langStudents, $langMove, $langWorkFile, $langWorkMultipleFiles,
           $langAssignmentEndHelpBlock, $langWorkSubType, $langWorkOnlineText, $langStartDate,
           $langGradeNumbers, $langGradeType, $langGradeScales,
           $langAutoJudgeInputNotSupported, $langAutoJudgeSum, $langAutoJudgeNewScenario,
           $langAutoJudgeEnable, $langAutoJudgeInput, $langAutoJudgeExpectedOutput,
           $langOperator, $langAutoJudgeWeight, $langAutoJudgeProgrammingLanguage,
           $langAutoJudgeAssertions, $langDescription, $langTitle, $langNotifyAssignmentSubmission,
           $langPassCode, $langIPUnlock, $langDelete, $langAssignmentType, $langAssignmentTypeEclass,
           $langAssignmentTypeTurnitin, $langTiiApp, $langLTILaunchContainer, $langTurnitinNewAssignNotice,
           $langTiiFeedbackReleaseDate, $langAssignmentFeedbackReleaseHelpBlock, $langTiiSubmissionSettings,
           $langTiiSubmissionNoStore, $langTiiSubmissionStandard, $langTiiSubmissionInstitutional, $langTiiCompareAgainst,
           $langTiiStudentPaperCheck, $langTiiInternetCheck, $langTiiJournalCheck, $langTiiInstitutionCheck,
           $langTiiSimilarityReport, $langTiiReportGenImmediatelyNoResubmit, $langTiiReportGenImmediatelyWithResubmit,
           $langTiiReportGenOnDue, $langTiiSViewReports, $langTiiExcludeBiblio, $langTiiExcludeQuoted,
           $langTiiExcludeSmall, $langTiiExcludeType, $langTiiExcludeTypeWords, $langPercentage,
           $langTiiExcludeValue, $langLTIOptions, $langGradeReviews, $langReviewsPerUser, $autojudge,
           $langAllowableReviewValues, $langReviewStart, $langReviewEnd, $langReviewDateHelpBlock,
           $langNoGradeRubrics, $langNoGradeScales, $langGroupWorkDeadline_of_Submission, $langImgFormsDes,
           $langSelect, $langForm;

    load_js('bootstrap-datetimepicker');
    load_js('select2');

    $scales = Database::get()->queryArray('SELECT * FROM grading_scale WHERE course_id = ?d', $course_id);
    $scale_options = "";
    foreach ($scales as $scale) {
        $scale_options .= "<option value='$scale->id'>$scale->title</option>";
    }
    $rubrics = Database::get()->queryArray('SELECT * FROM rubric WHERE course_id = ?d', $course_id);
    $rubric_options = "";
    foreach ($rubrics as $rubric) {
        $rubric_options .= "<option value='$rubric->id'>$rubric->name</option>";
    }
    $lti_templates = Database::get()->queryArray('SELECT * FROM lti_apps WHERE enabled = true AND is_template = true AND type = ?s', TURNITIN_LTI_TYPE);
    $lti_template_options = "";
    foreach ($lti_templates as $lti) {
        $lti_template_options .= "<option value='$lti->id'>$lti->title</option>";
    }
    $turnitinapp = ExtAppManager::getApp(strtolower(TurnitinApp::NAME));

    $interval = new DateInterval('P1M');
    $tii_fwddate = (new DateTime('NOW'))->add($interval)->format('d-m-Y H:i');

    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#scales').select2({ width: '100%' });
            $('#rubrics').select2({ width: '100%' }); //plaisio anazhthshs
            $('#reviews').select2({ width: '100%' });
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
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                } else if (choice == 1) {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                } else if (choice == 2) {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#rubrics')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                }
                else  {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                }
            });
            $('input[name=assignment_type]').on('change', function(e) {
                var choice = $(this).val();
                if (choice == 0) {
                    // lti fields
                    $('#lti_label')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#lti_templates')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#lti_launchcontainer')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_feedbackreleasedate')
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_internetcheck')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    /*$('#tii_institutioncheck')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');*/
                    $('#tii_journalcheck')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_report_gen_speed')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_s_view_reports')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_studentpapercheck')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    /*$('#tii_submit_papers_to')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');*/
                    $('#tii_use_biblio_exclusion')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_use_quoted_exclusion')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_use_small_exclusion')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');

                    // user groups
                    $('#group_button')
                        .prop('disabled', false);

                    // grading type
                    $('#scales_button')
                        .prop('disabled', false);
                    $('#rubrics_button')
                        .prop('disabled', false);
                    $('#reviews_button')
                        .prop('disabled', false);

                    // submission type
                    $('#file_button')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#online_button')
                        .prop('disabled', false);

                } else if (choice == 1) {
                    // lti fields
                    $('#lti_label')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#lti_templates')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#lti_launchcontainer')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_feedbackreleasedate')
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_internetcheck')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    /*$('#tii_institutioncheck')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');*/
                    $('#tii_journalcheck')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_report_gen_speed')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_s_view_reports')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_studentpapercheck')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    /*$('#tii_submit_papers_to')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');*/
                    $('#tii_use_biblio_exclusion')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_use_quoted_exclusion')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_use_small_exclusion')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');

                    // user groups
                    $('#user_button')
                        .prop('checked', true)
                        .trigger('click')
                        .trigger('change');
                    $('#group_button')
                        .prop('disabled', true);

                    // grading type
                    $('#numbers_button')
                        .prop('checked', true)
                        .trigger('click')
                        .trigger('change');
                    $('#scales_button')
                        .prop('disabled', true);
                    $('#rubrics_button')
                        .prop('disabled', true);
                    $('#reviews_button')
                        .prop('disabled',true);

                    // submission type
                    $('#file_button')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#online_button')
                        .prop('disabled', true);

                    // dates
                    $('#enableWorkStart').trigger('click');
                    $('#enableWorkEnd').trigger('click');
                    $('#enableWorkFeedbackRelease').trigger('click');
                    $('#WorkEnd').val('$tii_fwddate');
                    $('#tii_feedbackreleasedate').val('$tii_fwddate');
                    $('#enableWorkStart_review').trigger('click');
                    $('#enableWorkEnd_review').trigger('click');
                }
            });
            $('#WorkEnd, #WorkStart,#WorkStart_review, #WorkEnd_review,#tii_feedbackreleasedate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '$language',
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
            $('#enableWorkFeedbackRelease').change(function() {
                if($(this).prop('checked')) {
                    $('input#tii_feedbackreleasedate').prop('disabled', false);
                    $('#late_sub_row').removeClass('hide');
                } else {
                    $('input#tii_feedbackreleasedate').prop('disabled', true);
                }
            });
            $('#enableWorkEnd_review, #enableWorkStart_review').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#'+dateType).prop('disabled', false);
                } else {
                    $('input#'+dateType).prop('disabled', true);
                }
            });

            $('input[name=grading_type]').on('change', function(e){
                var choice = $(this).val();
                if (choice == 3 ){
                    $('#late_submission').prop('disabled', true)
                }
                else{
                    $('#late_submission').prop('disabled', false)
                }
            });

            $('#tii_use_small_exclusion').change(function() {
                if($(this).prop('checked')) {
                    $('#tii_exclude_type_words')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_exclude_type_percentage')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_exclude_value')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                } else {
                    $('#tii_exclude_type_words')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_exclude_type_percentage')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_exclude_value')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                }
            });
            $('#assignmentIPLock').select2({
                minimumResultsForSearch: Infinity,
                tags: true,
                tokenSeparators: [',', ' '],
                width: '100%'
            });
        });

    </script>";

    $title_error = Session::getError('title');
    $title = q(Session::has('title') ? Session::get('title') : '');
    $desc = Session::has('desc') ? Session::get('desc') : '';
    $reviews_per_user = Session::has('reviews_per_user') ? Session::get('reviews_per_user') : 3;
    $max_grade_error = Session::getError('max_grade');
    $scale_error = Session::getError('scale');
    $rubric_error = Session::getError('rubric');
    $review_error_user= Session::getError('reviews_per_user');
    $review_error_rubric = Session::getError('rubric_review');
    $max_grade = Session::has('max_grade') ? Session::get('max_grade') : 10;
    $scale = Session::getError('scale');
    $rubric = Session::getError('rubric');
    $rubric_review = Session::getError('rubric_review');
    $submission_type = Session::has('submission_type') ? Session::get('submission_type') : 0;
    $assignment_type = Session::has('assignment_type') ? Session::get('assignment_type') : 0;
    $grading_type = Session::has('grading_type') ? Session::get('grading_type') : 0;
    $WorkStart = Session::has('WorkStart') ? Session::get('WorkStart') : (new DateTime('NOW'))->format('d-m-Y H:i');
    $WorkEnd = Session::has('WorkEnd') ? Session::get('WorkEnd') : "";
    //hmeromhnia enarkshs ths aksiologhshs apo omotimous
    $WorkStart_review = Session::has('WorkStart_review') ? Session::get('WorkStart_review') : (new DateTime('NOW'))->format('d-m-Y H:i');
    //deadline aksiologhshs apo omotimous
    $WorkEnd_review = Session::has('WorkEnd_review') ? Session::get('WorkEnd_review') : null;
    $WorkFeedbackRelease = Session::has('WorkFeedbackRelease') ? Session::get('WorkFeedbackRelease') : null;
    $enableWorkStart = Session::has('enableWorkStart') ? Session::get('enableWorkStart') : null;
    $enableWorkEnd = Session::has('enableWorkEnd') ? Session::get('enableWorkEnd') : ($WorkEnd ? 1 : 0);
    $enableWorkStart_review = Session::has('enableWorkStart_review') ? Session::get('enableWorkStart_review') : null;
    $enableWorkEnd_review = Session::has('enableWorkEnd_review') ? Session::get('enableWorkEnd_review') : ($WorkEnd_review ? 1 : 0);
    $enableWorkFeedbackRelease = Session::has('enableWorkFeedbackRelease') ? Session::get('enableWorkFeedbackRelease') : ($WorkFeedbackRelease ? 1 : 0);
    $assignmentPasswordLock = Session::has('assignmentPasswordLock') ? Session::get('assignmentPasswordLock') : '';
    $assignmentIPLock = Session::has('assignmentIPLock') ? Session::get('assignmentIPLock') : array();
    $assignmentIPLockOptions = implode('', array_map(
        function ($item) {
            $item = trim($item);
            return $item ? ('<option selected>' . q($item) . '</option>') : '';
        }, $assignmentIPLock));
    enableCheckFileSize();
    $fileCount = Session::has('fileCount')? Session::get('fileCount') : 2;

    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
        <div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' enctype='multipart/form-data' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <fieldset>
            <legend class='mb-0' aria-label='$langForm'></legend>
            <div class='row form-group " . ($title_error ? "has-error" : "") . "'>
                <label for='title' class='col-12 control-label-notes mb-1'>$langTitle <span class='asterisk Accent-200-cl'>(*)</span></label>
                <div class='col-12'>
                  <input name='title' type='text' class='form-control' id='title' placeholder='$langTitle' value='" . q($title) . "'>
                  <span class='help-block Accent-200-cl'>$title_error</span>
                </div>
            </div>
            <div class='row form-group mt-4'>
                <label for='desc' class='col-12 control-label-notes mb-1'>$langDescription</label>
                <div class='col-12'>
                " . rich_text_editor('desc', 4, 20, $desc) . "
                </div>
            </div>
            <div class='row form-group mt-4'>
                <label for='userfile' class='col-12 control-label-notes mb-1'>$langWorkFile</label>
                <div class='col-12'>" .
        fileSizeHidenInput() . "
                  <input type='file' id='userfile' name='userfile'>
                </div>
            </div>";
    if (is_active_external_lti_app($turnitinapp, TURNITIN_LTI_TYPE, $course_id)) { // lti options
        $tool_content .= "
            <div class='row form-group mt-4'>
                <div class='col-12 control-label-notes mb-1'>$langAssignmentType</div>
                <div class='col-12'>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' name='assignment_type' value='0'" . ($assignment_type == 0 ? " checked" : "") . ">
                         $langAssignmentTypeEclass
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='assignment_type' value='1'" . ($assignment_type == 1 ? " checked" : "") . ">
                        $langAssignmentTypeTurnitin
                      </label>
                    </div>
                </div>
                <div class='col-12'>
                    <span class='help-block'>$langTurnitinNewAssignNotice</span>
                </div>
            </div>

            <div class='col-12 form-group hidden mt-4 mb-4 p-3' id='lti_label' style='box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1); padding-top:10px; padding-bottom:10px;'>
                <div class='TextBold large-text'>$langLTIOptions</div>
                <div class='form-group hidden mt-4'>
                    <label for='lti_templates' class='col-sm-12 control-label-notes'>$langTiiApp</label>
                    <div class='col-12'>
                      <select name='lti_template' class='form-select' id='lti_templates' disabled>
                            $lti_template_options
                      </select>
                    </div>
                </div>
            <div class='form-group hidden mt-3'>
                <label for='lti_launchcontainer' class='col-sm-12 control-label-notes'>$langLTILaunchContainer</label>
                <div class='col-sm-12'>" . selection(lti_get_containers_selection(), 'lti_launchcontainer', LTI_LAUNCHCONTAINER_EMBED, 'id="lti_launchcontainer" disabled') . "</div>
            </div>";
        $tool_content .= "
            <!-- <div class='form-group hidden mt-4'>
                <label for='tii_submit_papers_to' class='col-sm-12 control-label-notes mb-1'>$langTiiSubmissionSettings:</label>
                <div class='col-sm-12'>
                  <select name='tii_submit_papers_to' class='form-select' id='tii_submit_papers_to' disabled>
                        <option value='0'>$langTiiSubmissionNoStore</option>
                        <option value='1' selected>$langTiiSubmissionStandard</option>
                        <option value='2'>$langTiiSubmissionInstitutional</option>
                  </select>
                </div>
            </div> -->
            <div class='form-group hidden mt-4'>
                <div class='col-sm-12 control-label-notes mb-1'>$langTiiCompareAgainst</div>
                <div class='col-sm-12'>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='tii_studentpapercheck' id='tii_studentpapercheck' value='1' checked disabled>
                        <span class='checkmark'></span>
                        $langTiiStudentPaperCheck
                      </label>
                    </div>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='tii_internetcheck' id='tii_internetcheck' value='1' checked disabled>
                        <span class='checkmark'></span>
                        $langTiiInternetCheck
                      </label>
                    </div>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='tii_journalcheck' id='tii_journalcheck' value='1' checked disabled>
                        <span class='checkmark'></span>
                        $langTiiJournalCheck
                      </label>
                    </div>
                    <!--<div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='tii_institutioncheck' id='tii_institutioncheck' value='1' checked disabled>
                        <span class='checkmark'></span>
                        $langTiiInstitutionCheck
                      </label>
                    </div>-->
                </div>
            </div>
            <div class='form-group hidden mt-4'>
                <label for='tii_report_gen_speed' class='col-sm-12 control-label-notes mb-1'>$langTiiSimilarityReport</label>
                <div class='col-sm-12'>
                  <select name='tii_report_gen_speed' class='form-select' id='tii_report_gen_speed' disabled>
                        <option value='0' selected>$langTiiReportGenImmediatelyNoResubmit</option>
                        <option value='1'>$langTiiReportGenImmediatelyWithResubmit</option>
                        <option value='2'>$langTiiReportGenOnDue</option>
                  </select>
                </div>
                <div class='col-sm-12 mt-4'>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='tii_s_view_reports' id='tii_s_view_reports' value='1' disabled>
                        <span class='checkmark'></span>
                        $langTiiSViewReports
                      </label>
                    </div>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='tii_use_biblio_exclusion' id='tii_use_biblio_exclusion' value='1' disabled>
                        <span class='checkmark'></span>
                        $langTiiExcludeBiblio
                      </label>
                    </div>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='tii_use_quoted_exclusion' id='tii_use_quoted_exclusion' value='1' disabled>
                        <span class='checkmark'></span>
                        $langTiiExcludeQuoted
                      </label>
                    </div>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='tii_use_small_exclusion' id='tii_use_small_exclusion' value='1' disabled>
                        <span class='checkmark'></span>
                        $langTiiExcludeSmall
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group hidden mt-4'>
                <div class='col-sm-12 control-label-notes mb-1'>$langTiiExcludeType</div>
                <div class='col-sm-12'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='tii_exclude_type' id='tii_exclude_type_words' value='words' checked disabled>
                        $langTiiExcludeTypeWords
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='tii_exclude_type' id='tii_exclude_type_percentage' value='percentage' disabled>
                        $langPercentage
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group hidden mt-4'>
                <label for='tii_exclude_value' class='col-sm-6 control-label-notes'>$langTiiExcludeValue:</label>
                <div class='col-sm-12'>
                    <input name='tii_exclude_value' type='text' class='form-control' id='tii_exclude_value' value='0' disabled>
                </div>
            </div>
            </div>";
    } else {
        $tool_content .= "
            <input type='hidden' name='assignment_type' value='0' />";
    }
    $tool_content .= "
            <div class='row form-group mt-4'>
                <div class='col-12 control-label-notes mb-1'>$langGradeType</div>
                <div class='col-12'>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='numbers_button' name='grading_type' value='0'". ($grading_type==ASSIGNMENT_STANDARD_GRADE ? " checked" : "") .">
                         $langGradeNumbers
                      </label>
                    </div>";
                    if (!grading_scales_exist()) {
                        $addon = "disabled";
                        $class_not_visible = 'not_visible';
                        $label = "data-bs-toggle='tooltip' data-bs-placement='top' title='$langNoGradeScales'";
                    } else {
                        $addon = $class_not_visible = $label = '';
                    }
                    $tool_content .= "<div class='radio $class_not_visible'>
                      <label $label>
                        <input type='radio' id='scales_button' name='grading_type' value='1'". ($grading_type==ASSIGNMENT_SCALING_GRADE ? " checked" : "") ." $addon>
                        $langGradeScales
                      </label>
                    </div>";
                    if (!rubrics_exist()) {
                        $addon = "disabled";
                        $class_not_visible = 'not_visible';
                        $label = "data-bs-toggle='tooltip' data-bs-placement='top' title='$langNoGradeRubrics'";
                    } else {
                        $addon = $class_not_visible = $label = '';
                    }
                    $tool_content .= "<div class='radio $class_not_visible'>
                      <label $label>
                        <input type='radio' id='rubrics_button' name='grading_type' value='2'". ($grading_type==ASSIGNMENT_RUBRIC_GRADE ? " checked" : "") ." $addon>
                        $langGradeRubrics
                      </label>
                    </div>";

                    $tool_content .= "<div class='radio $class_not_visible'>
                      <label $label>
                        <input type='radio' id='reviews_button' name='grading_type' value='3'". ($grading_type==ASSIGNMENT_PEER_REVIEW_GRADE ? " checked" : "") ." $addon>
                        $langGradeReviews
                      </label>
                    </div>";
                $tool_content .= "</div>
            </div>
            <div class='row form-group".($max_grade_error ? " has-error" : "").($grading_type==ASSIGNMENT_STANDARD_GRADE ? "" : " hidden")." mt-4'>
                <label for='max_grade' class='col-12 control-label-notes mb-1'>$m[max_grade]</label>
                <div class='col-12'>
                  <input name='max_grade' type='text' class='form-control' id='max_grade' placeholder='$m[max_grade]' value='$max_grade'>
                  <span class='help-block'>$max_grade_error</span>
                </div>
            </div>
            <div class='row form-group".($scale_error ? " has-error" : "").($grading_type==ASSIGNMENT_SCALING_GRADE ? "" : " hidden")." mt-4'>
                <label for='scales' class='col-12 control-label-notes mb-1'>$langGradeScales</label>
                <div class='col-12'>
                  <select name='scale' class='form-select' id='scales' disabled>
                        $scale_options
                  </select>
                  <span class='help-block'>$scale_error</span>
                </div>
            </div>
            <div class='row form-group".($rubric_error ? " has-error" : "").($grading_type==ASSIGNMENT_RUBRIC_GRADE ? "" : " hidden")." mt-4'>
                <label for='rubrics' class='col-12 control-label-notes mb-1'>$langGradeRubrics</label>
                <div class='col-12'>
                  <select name='rubric' class='form-select' id='rubrics' disabled>
                        $rubric_options
                  </select>
                  <span class='help-block'>$rubric_error</span>
                </div>
            </div>

            <div class='row form-group" .($review_error_user ? " has-error" : " ").($grading_type==ASSIGNMENT_PEER_REVIEW_GRADE ? "" : " hidden")." mt-4'>
                <label for='reviews_per_user' class='col-12 control-label-notes mb-1'>$langReviewsPerUser</label>
                <div class='col-12'>
                    <input name='reviews_per_user' type='text' class='form-control' id = 'reviews_per_user'  disabled>
                    <span class='help-block'>$langAllowableReviewValues $review_error_user</span>
                </div>
            </div>
            <div class='row form-group" .($review_error_rubric ? " has-error" : "").($grading_type==3 ? "" : " hidden")." mt-4'>
                <label for='reviews' class='col-12 control-label-notes mb-1'>$langGradeRubrics</label>
                <div class='col-12'>
                  <select name='rubric_review' class='form-select' id='reviews' disabled>
                        $rubric_options
                  </select>
                  <span class='help-block'>&nbsp;$review_error_rubric</span>
                </div>

                <div class='row input-append date".(Session::getError('WorkStart_review') ? " has-error" : "")." mt-4' id='startdatepicker' data-date='$WorkStart_review' data-date-format='dd-mm-yyyy'>
                    <label for='WorkStart_review' class='col-12 control-label-notes mb-1'>$langReviewStart</label>
                    <div class='col-12'>
                       <div class='input-group'>
                           <span class='input-group-addon'>
                            <label class='label-container' aria-label='$langSelect'>
                             <input class='mt-0' type='checkbox' id='enableWorkStart_review' name='enableWorkStart_review' value='1'".($enableWorkStart_review ? ' checked' : '').">
                             <span class='checkmark'></span></label>
                             </span>
                           <span class='add-on1 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                           <input class='form-control mt-0 border-start-0' name='WorkStart_review' id='WorkStart_review' type='text' value='$WorkStart_review'".($enableWorkStart_review ? '' : ' disabled').">
                       </div>
                       <span class='help-block'>".(Session::hasError('WorkStart_review') ? Session::getError('WorkStart_review') : "<i class='fa fa-share fa-rotate-270'></i> $langReviewDateHelpBlock")." </span>
                        &nbsp
                    </div>
                </div>

                <div class='row input-append date".(Session::getError('WorkEnd_review') ? " has-error" : "")." mt-4' id='enddatepicker' data-date='$WorkEnd_review' data-date-format='dd-mm-yyyy'>
                    <label for='WorkEnd_review' class='col-12 control-label-notes mb-1'>$langReviewEnd</label>
                    <div class='col-12'>
                       <div class='input-group'>
                           <span class='input-group-addon'>
                           <label class='label-container' aria-label='$langSelect'>
                             <input class='mt-0' type='checkbox' id='enableWorkEnd_review' name='enableWorkEnd_review' value='1'".($enableWorkEnd_review ? ' checked' : '').">
                             <span class='checkmark'></span></label>
                             </span>
                           <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                           <input class='form-control mt-0 border-start-0' name='WorkEnd_review' id='WorkEnd_review' type='text' value='$WorkEnd_review'".($enableWorkEnd_review ? '' : ' disabled').">
                       </div>
                       <span class='help-block'>".(Session::hasError('WorkEnd_review') ? Session::getError('WorkEnd_review') : "<i class='fa fa-share fa-rotate-270'></i> $langAssignmentEndHelpBlock")."</span>
                    </div>
                </div>
            </div>

            <div class='row form-group mt-4'>
                <div class='col-12 control-label-notes mb-1'>$langWorkSubType</div>
                <div class='col-12'>
                    <div class='radio'>
                      <label>
                        <input aria-label='$langWorkFile' type='radio' id='file_button' name='submission_type' value='0'" .
                        ($submission_type == 0 ? ' checked' : '') .">
                         $langWorkFile
                      </label>
                    </div>
                    <div class='radio'>
                      <label class='radio'>
                        <input aria-label='$langWorkMultipleFiles' type='radio' id='online_button' name='submission_type' value='2'" .
                        ($submission_type == 2 ? ' checked' : '') .">
                        <div class='me-2'>$langWorkMultipleFiles</div><div>" . selection(fileCountOptions(), 'fileCount', $fileCount) . "</div>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input aria-label='$langWorkOnlineText' type='radio' id='online_button' name='submission_type' value='1'" .
                        ($submission_type == 1 ? ' checked' : '') .">
                        $langWorkOnlineText
                      </label>
                    </div>
                </div>
            </div>
            <div class='row input-append date form-group".(Session::getError('WorkStart') ? " has-error" : "")." mt-4' id='startdatepicker' data-date='$WorkStart' data-date-format='dd-mm-yyyy'>
                <label for='WorkStart' class='col-12 control-label-notes mb-1'>$langStartDate</label>
                <div class='col-12'>
                   <div class='input-group'>
                       <span class='input-group-addon'>
                       <label class='label-container' aria-label='$langSelect'>
                         <input class='mt-0' type='checkbox' id='enableWorkStart' name='enableWorkStart' value='1'".($enableWorkStart ? ' checked' : '').">
                         <span class='checkmark'></span></label>
                         </span>
                       <span class='add-on1 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                       <input class='form-control mt-0 border-start-0' name='WorkStart' id='WorkStart' type='text' value='$WorkStart'".($enableWorkStart ? '' : ' disabled').">
                   </div>
                   <span class='help-block'>".(Session::hasError('WorkStart') ? Session::getError('WorkStart') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentStartHelpBlock")."</span>
                </div>
            </div>
            <div class='row input-append date form-group".(Session::getError('WorkEnd') ? " has-error" : "")." mt-4' id='enddatepicker' data-date='$WorkEnd' data-date-format='dd-mm-yyyy'>
                <label for='WorkEnd' class='col-12 control-label-notes mb-1'>$langGroupWorkDeadline_of_Submission</label>
                <div class='col-12'>
                   <div class='input-group'>
                       <span class='input-group-addon'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input class='mt-0' type='checkbox' id='enableWorkEnd' name='enableWorkEnd' value='1'".($enableWorkEnd ? ' checked' : '').">
                        <span class='checkmark'></span></label>
                       </span>
                       <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                       <input class='form-control mt-0 border-start-0' name='WorkEnd' id='WorkEnd' type='text' value='$WorkEnd'".($enableWorkEnd ? '' : ' disabled').">
                   </div>
                   <span class='help-block'>".(Session::hasError('WorkEnd') ? Session::getError('WorkEnd') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentEndHelpBlock")."</span>
                </div>
            </div>";
    if (is_active_external_lti_app($turnitinapp, TURNITIN_LTI_TYPE, $course_id)) {
        $tool_content .= "
            <div class='row input-append date form-group hidden".(Session::getError('WorkFeedbackRelease') ? " has-error" : "")." mt-4' id='feedbackreleasedatepicker' data-date='$WorkFeedbackRelease' data-date-format='dd-mm-yyyy'>
                <label for='tii_feedbackreleasedate' class='col-12 control-label-notes mb-1'>$langTiiFeedbackReleaseDate</label>
                <div class='col-12'>
                   <div class='input-group'>
                       <span class='input-group-addon'>
                       <label class='label-container' aria-label='$langSelect'>
                         <input class='mt-0' type='checkbox' id='enableWorkFeedbackRelease' name='enableWorkFeedbackRelease' value='1'".($enableWorkFeedbackRelease ? ' checked' : '').">
                         <span class='checkmark'></span></label>
                         </span>
                       <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                       <input class='form-control mt-0 border-start-0' name='tii_feedbackreleasedate' id='tii_feedbackreleasedate' type='text' value='$WorkFeedbackRelease'".($enableWorkFeedbackRelease ? '' : ' disabled').">
                   </div>
                   <span class='help-block'>".(Session::hasError('WorkFeedbackRelease') ? Session::getError('WorkFeedbackRelease') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentFeedbackReleaseHelpBlock")."</span>
                </div>
            </div>";
    }
    $tool_content .= "
            <div class='mt-4 form-group ". ($WorkEnd ? "" : "hide")." mt-4' id='late_sub_row'>
                <div class='col-12'>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' id='late_submission' name='late_submission' value='1'>
                        <span class='checkmark'></span>
                        $m[late_submission_enable]
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group mt-4'>
                <div class='col-12'>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='notify_submission' value='1'>
                        <span class='checkmark'></span>
                        $langNotifyAssignmentSubmission
                      </label>
                    </div>
                </div>
            </div>
            <div class='row form-group mt-4'>
                <div class='col-12 control-label-notes mb-1'>$m[group_or_user]</div>
                <div class='col-12'>
                    <div class='radio'>
                    <label class='label-container' aria-label='$langSelect'>
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
            <div class='row form-group mt-4'>
                <div class='col-12 control-label-notes mb-1'>$m[WorkAssignTo]</div>
                <div class='col-12'>
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
                    <div class='radio' id='assign_group_div'>
                      <label>
                        <input type='radio' id='assign_button_group' name='assign_to_specific' value='2'>
                        <span id='assign_button_group_text'>$m[WorkToGroup]</span>
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group mt-4'>
                <div class=col-12 offset-md-3'>
                    <div class='table-responsive'>
                        <table id='assignees_tbl' class='table-default hide'>
                            <thead><tr class='title1 list-header'>
                              <td id='assignees' class='form-label'>$langStudents</td>
                              <td class='text-center form-label'>$langMove</td>
                              <td class='form-label'>$m[WorkAssignTo]</td>
                            </tr></thead>
                            <tr>
                              <td>
                                <select aria-label='$langStudents' class='form-select h-100 rounded-0' id='assign_box' size='10' multiple></select>
                              </td>
                              <td class='text-center'>
                                <input class='btn btn-outline-primary btn-sm rounded-2 h-40px'type='button' onClick=\"move('assign_box','assignee_box')\" value='   &gt;&gt;   ' /><br /><input class='mt-2 btn btn-outline-primary btn-sm h-40px rounded-2' type='button' onClick=\"move('assignee_box','assign_box')\" value='   &lt;&lt;   ' />
                              </td>
                              <td width='40%'>
                                <select aria-label='$m[WorkAssignTo]' class='form-select h-100 rounded-0' id='assignee_box' name='ingroup[]' size='10' multiple></select>
                              </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>";
    // Auto Judge Options
    if ($autojudge->isEnabled()) {
        $supported_languages = $autojudge->getSupportedLanguages();
        if (!isset($supported_languages['error'])) {
            $supported_languages = "<select id='lang' name='lang'>" .
                implode(array_map(function ($lang) {
                    $lang = q($lang);
                    return "<option value='$lang'>$lang</option>\n";
                }, array_keys($supported_languages))) .
                "</select>";
            $tool_content .= "
                <div class='row form-group mt-4'>
                    <div class='col-12 control-label-notes mb-1'>$langAutoJudgeEnable</div>
                    <div class='col-12'>
                        <div class='radio'><label class='label-container' aria-label='$langSelect'><input type='checkbox' id='auto_judge' name='auto_judge' value='1'><span class='checkmark'></span></label></div>
                        <div class='table-responsive'>
                            <table style='display: none;'>
                                <thead>
                                    <tr>
                                      <th>$langAutoJudgeInput</th>
                                      <th>$langOperator</th>
                                      <th>$langAutoJudgeExpectedOutput</th>
                                      <th>$langAutoJudgeWeight</th>
                                      <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                      <td><input type='text' name='auto_judge_scenarios[0][input]' ".($autojudge->supportsInput() ? '' : 'readonly="readonly" placeholder="'.$langAutoJudgeInputNotSupported.'"')." /></td>
                                      <td>
                                        <select name='auto_judge_scenarios[0][assertion]' class='auto_judge_assertion' aria-label='$langSelect'>
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
                                      <td><input type='text' name='auto_judge_scenarios[0][output]' class='auto_judge_output'></td>
                                      <td><input type='text' name='auto_judge_scenarios[0][weight]' class='auto_judge_weight'></td>
                                      <td>
                                          <a href='#' class='autojudge_remove_scenario' style='display: none;' aria-label='$langDelete'>
                                            <span class='fa fa-fw fa-xmark text-danger' data-bs-original-title='$langDelete' data-bs-toggle='tooltip'></span>
                                          </a>
                                      </td>
                                    </tr>

                                    <tr>
                                        <td colspan='5' style='text-align: right;'> $langAutoJudgeSum: <span id='weights-sum'>0</span></td>
                                    </tr>
                                    <tr>
                                        <td colspan='5' style='text-align: left;'><input type='submit' value='$langAutoJudgeNewScenario' id='autojudge_new_scenario' /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class='row form-group mt-4'>
                  <div class='col-12 control-label-notes mb-1'>$langAutoJudgeProgrammingLanguage</div>
                  <div class='col-12'>
                    $supported_languages
                  </div>
                </div>";
        }
    }
    $tool_content .= "
            <div class='row form-group mt-4'>
                <label for='assignmentPasswordLock' class='col-12 control-label-notes mb-1'>$langPassCode</label>
                <div class='col-12'>
                    <input name='assignmentPasswordLock' type='text' class='form-control' id='assignmentPasswordLock' value='".q($assignmentPasswordLock)."'>
                </div>
            </div>
            <div class='row form-group ".(Session::getError('assignmentIPLock') ? 'has-error' : '')." mt-4'>
                <label for='assignmentIPLock' class='col-12 control-label-notes mb-1'>$langIPUnlock</label>
                <div class='col-12'>
                    <select name='assignmentIPLock[]' class='form-select' id='assignmentIPLock' multiple>
                        $assignmentIPLockOptions
                    </select>
                </div>
            </div>" .
            eClassTag::tagInput();
    $tool_content .= "
        <div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-end align-items-center'>

                    "
                    .
                form_buttons(array(
                    array(
                        'class'         => 'submitAdminBtn',
                        'name'          => 'new_assign',
                        'value'         => $langSubmit,
                        'javascript'    => "selectAll('assignee_box',true)"
                    ),
                    array(
                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                        'class' => 'cancelAdminBtn ms-1'
                    )
                ))
                .
                    "

                </div>
            </div>
        </fieldset>
        </form></div></div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
    </div>";
}

/**
 * @brief display form for editing assignment
 * @param type $id
 *
 */
function show_edit_assignment($id) {

    global $tool_content, $m, $course_code,
        $langModify, $course_id, $head_content, $language, $langAssignmentStartHelpBlock,
        $langAssignmentEndHelpBlock, $langStudents, $langMove, $langWorkFile, $langStartDate,
        $langWorkOnlineText, $langWorkSubType, $langGradeRubrics, $langWorkMultipleFiles,
        $langGradeType, $langGradeNumbers, $langGradeScales, $langNoGradeScales, $langNoGradeRubrics,
        $langAutoJudgeInputNotSupported, $langTitle, $autojudge, $langGroupWorkDeadline_of_Submission,
        $langAutoJudgeSum, $langAutoJudgeNewScenario, $langAutoJudgeEnable, $langDescription,
        $langAutoJudgeInput, $langAutoJudgeExpectedOutput, $langOperator, $langNotifyAssignmentSubmission,
        $langAutoJudgeWeight, $langAutoJudgeProgrammingLanguage, $langAutoJudgeAssertions,
        $langPassCode, $langIPUnlock, $langDelete, $langAssignmentType, $langAssignmentTypeEclass,
        $langAssignmentTypeTurnitin, $langTiiApp, $langLTILaunchContainer, $langTurnitinNewAssignNotice,
        $langTiiFeedbackReleaseDate, $langAssignmentFeedbackReleaseHelpBlock, $langTiiSubmissionSettings,
        $langTiiSubmissionNoStore, $langTiiSubmissionStandard, $langTiiSubmissionInstitutional, $langTiiCompareAgainst,
        $langTiiStudentPaperCheck, $langTiiInternetCheck, $langTiiJournalCheck, $langTiiInstitutionCheck,
        $langTiiSimilarityReport, $langTiiReportGenImmediatelyNoResubmit, $langTiiReportGenImmediatelyWithResubmit,
        $langTiiReportGenOnDue, $langTiiSViewReports, $langTiiExcludeBiblio, $langTiiExcludeQuoted,
        $langTiiExcludeSmall, $langTiiExcludeType, $langTiiExcludeTypeWords, $langPercentage,
        $langTiiExcludeValue, $langGradeReviews, $langReviewsPerUser, $langAllowableReviewValues,
        $langReviewStart, $langReviewEnd, $langReviewDateHelpBlock, $langLTIOptions, $langImgFormsDes,
        $langSelect, $langForm;

    load_js('bootstrap-datetimepicker');
    load_js('select2');

    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#scales').select2({ width: '100%' });
            $('#rubrics').select2({ width: '100%' });
            $('#reviews').select2({ width: '100%' });
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
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                } else if (choice == 1) {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                } else if (choice == 2) {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#rubrics')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                }
                else
                {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                }});
            $('input[name=assignment_type]').on('change', function(e) {
                var choice = $(this).val();
                if (choice == 0) {
                    // lti fields
                    $('#lti_label')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#lti_templates')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#lti_launchcontainer')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_feedbackreleasedate')
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_internetcheck')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    /*$('#tii_institutioncheck')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');*/
                    $('#tii_journalcheck')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_report_gen_speed')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_s_view_reports')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_studentpapercheck')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    /*$('#tii_submit_papers_to')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');*/
                    $('#tii_use_biblio_exclusion')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_use_quoted_exclusion')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_use_small_exclusion')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');

                    // user groups
                    $('#group_button')
                        .prop('disabled', false);

                    // grading type
                    $('#scales_button')
                        .prop('disabled', false);
                    $('#rubrics_button')
                        .prop('disabled', false);
                    $('#reviews_button')
                        .prop('disabled', false);

                    // submission type
                    $('#file_button')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#online_button')
                        .prop('disabled', false);

                } else if (choice == 1) {
                    // lti fields
                    $('#lti_label')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#lti_templates')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#lti_launchcontainer')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_feedbackreleasedate')
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_internetcheck')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    /*$('#tii_institutioncheck')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');*/
                    $('#tii_journalcheck')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_report_gen_speed')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_s_view_reports')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_studentpapercheck')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    /*$('#tii_submit_papers_to')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');*/
                    $('#tii_use_biblio_exclusion')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_use_quoted_exclusion')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_use_small_exclusion')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');

                    // user groups
                    $('#user_button')
                        .prop('checked', true)
                        .trigger('click')
                        .trigger('change');
                    $('#group_button')
                        .prop('disabled', true);

                    // grading type
                    $('#numbers_button')
                        .prop('checked', true)
                        .trigger('click')
                        .trigger('change');
                    $('#scales_button')
                        .prop('disabled', true);
                    $('#rubrics_button')
                        .prop('disabled', true);
                    $('#reviews_button')
                        .prop('disabled', true);

                    // submission type
                    $('#file_button')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#online_button')
                        .prop('disabled', true);
                }
            });
            $('#WorkEnd, #WorkStart, #WorkStart_review, #WorkEnd_review, #tii_feedbackreleasedate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '$language',
                autoclose: true
            });
            $('#enableWorkEnd, #enableWorkStart').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#'+dateType).prop('disabled', false);
                    //if (dateType == 'WorkEnd') $('#late_submission').prop('disabled', false);
                    $('#late_sub_row').removeClass('hide');
                } else {
                    $('input#'+dateType).prop('disabled', true);
                    //if (dateType == 'WorkEnd') $('#late_submission').prop('disabled', true);
                    $('#late_sub_row').addClass('hide');

                }
            });

            $('#enableWorkFeedbackRelease').change(function() {
                if($(this).prop('checked')) {
                    $('input#tii_feedbackreleasedate').prop('disabled', false);
                    $('#late_sub_row').removeClass('hide');
                } else {
                    $('input#tii_feedbackreleasedate').prop('disabled', true);
                }
            });
            $('#enableWorkEnd_review, #enableWorkStart_review').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#'+dateType).prop('disabled', false);
                } else {
                    $('input#'+dateType).prop('disabled', true);
                }
            });

            $('input[name=grading_type]').on('change', function(e){
                var choice = $(this).val();
                if (choice == 3 ){
                    $('#late_submission').prop('disabled', true)
                }
                else{
                    $('#late_submission').prop('disabled', false)
                }
            });

            $('#tii_use_small_exclusion').change(function() {
                if($(this).prop('checked')) {
                    $('#tii_exclude_type_words')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_exclude_type_percentage')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_exclude_value')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                } else {
                    $('#tii_exclude_type_words')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_exclude_type_percentage')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_exclude_value')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                }
            });
            $('#assignmentIPLock').select2({
                minimumResultsForSearch: Infinity,
                tags: true,
                tokenSeparators: [',', ' '],
                width: '100%'
            });
        });
    </script>";

    $row = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);

    $assignmentPasswordLock = Session::has('assignmentPasswordLock') ? Session::get('assignmentPasswordLock') : $row->password_lock;
    $assignmentIPLock = Session::has('assignmentIPLock') ?
        Session::get('assignmentIPLock') :
        explode(',', $row->ip_lock);
    $assignmentIPLockOptions = implode('', array_map(
        function ($item) {
            $item = trim($item);
            return $item? ('<option selected>' . q($item) . '</option>'): '';
        }, $assignmentIPLock));
    $assignment_type = ($row->assignment_type ? $row->assignment_type : 0);
    $lti_hidden = ($assignment_type == 1) ? '' : ' hidden';
    $subtype_hidden = ($assignment_type == 1) ?  ' hidden' : '';
    $lti_disabled = ($assignment_type == 1) ? '' : ' disabled';
    $lti_group_disabled = ($assignment_type == 1) ? ' disabled' : '';
    $grading_type = ($row->grading_type ? $row->grading_type : 0);
    $scales = Database::get()->queryArray('SELECT * FROM grading_scale WHERE course_id = ?d', $course_id);
    $scale_options = '';
    foreach ($scales as $scale) {
        $scale_options .= "<option value='$scale->id'".(($row->grading_scale_id == $scale->id && $grading_type==1)? " selected" : "").">$scale->title</option>";
    }
    $rubrics = Database::get()->queryArray('SELECT * FROM rubric WHERE course_id = ?d', $course_id);
    $rubric_options = '';
    foreach ($rubrics as $rubric) {
        $rubric_options .= "<option value='$rubric->id'".(($row->grading_scale_id == $rubric->id && $grading_type==2) ? " selected" : "").">$rubric->name</option>";
    }
    $rubric_option_review = '';
    foreach ($rubrics as $rub) {
        $rubric_option_review .= "<option value='$rub->id'".(($row->grading_scale_id == $rub->id && $grading_type==3) ? " selected" : "").">$rub->name</option>";
    }
    $lti_templates = Database::get()->queryArray('SELECT * FROM lti_apps WHERE enabled = true AND is_template = true AND type = ?s', TURNITIN_LTI_TYPE);
    $lti_template_options = "";
    foreach ($lti_templates as $lti) {
        $lti_template_options .= "<option value='$lti->id'" . (($row->lti_template == $lti->id && $assignment_type == 1) ? " selected" : "") . ">$lti->title</option>";
    }
    $turnitinapp = ExtAppManager::getApp(strtolower(TurnitinApp::NAME));
    if ($row->assign_to_specific) {
        //preparing options in select boxes for assigning to specific users/groups
        $assignee_options = '';
        $unassigned_options = '';
        if (($row->group_submissions) or ($row->assign_to_specific == 2)) {
            $assignees = Database::get()->queryArray("SELECT `group`.id AS id, `group`.name
                                   FROM assignment_to_specific, `group`
                                    WHERE course_id = ?d
                                    AND `group`.id = assignment_to_specific.group_id
                                    AND assignment_to_specific.assignment_id = ?d", $course_id, $id);
            $all_groups = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d AND visible = 1", $course_id);
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
                                   WHERE user.id = assignment_to_specific.user_id AND assignment_to_specific.assignment_id = ?d
                                   ORDER BY surname, givenname, am", $id);
            $all_users = Database::get()->queryArray("SELECT user.id AS id, user.givenname, user.surname
                                    FROM user, course_user
                                    WHERE user.id = course_user.user_id
                                      AND course_user.course_id = ?d AND course_user.status = " . USER_STUDENT . "
                                      AND user.id
                                    ORDER BY user.surname, user.givenname, user.am", $course_id);
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

    $WorkStart_review = $row->start_date_review ? DateTime::createFromFormat('Y-m-d H:i:s', $row->start_date_review)->format('d-m-Y H:i') : NULL;
    $WorkEnd_review = $row->due_date_review ? DateTime::createFromFormat('Y-m-d H:i:s', $row->due_date_review)->format('d-m-Y H:i') : NULL;

    $WorkFeedbackRelease = $row->tii_feedbackreleasedate ? DateTime::createFromFormat('Y-m-d H:i:s', $row->tii_feedbackreleasedate)->format('d-m-Y H:i') : NULL;
    $max_grade = Session::has('max_grade') ? Session::get('max_grade') : ($row->max_grade ? $row->max_grade : 10);

    $reviews_per_user = Session::has('reviews_per_user') ? Session::get('reviews_per_user') : ($row->reviews_per_assignment ? $row->reviews_per_assignment : 5);

    $enableWorkStart = Session::has('enableWorkStart') ? Session::get('enableWorkStart') : ($WorkStart ? 1 : 0);
    $enableWorkEnd = Session::has('enableWorkEnd') ? Session::get('enableWorkEnd') : ($WorkEnd ? 1 : 0);

    $enableWorkStart_review = Session::has('enableWorkStart_review') ? Session::get('enableWorkStart_review') : ($WorkStart_review ? 1 : 0);
    $enableWorkEnd_review = Session::has('enableWorkEnd_review') ? Session::get('enableWorkEnd_review') : ($WorkEnd_review ? 1 : 0);

    $enableWorkFeedbackRelease = Session::has('enableWorkFeedbackRelease') ? Session::get('enableWorkFeedbackRelease') : ($WorkFeedbackRelease ? 1 : 0);
    $checked = $row->notification ? 'checked' : '';
    $comments = trim($row->comments);


    //Get possible validation errors
    $title_error = Session::getError('title');
    $max_grade_error = Session::getError('max_grade');
    $scale_error = Session::getError('scale');
    $rubric_error = Session::getError('rubric');
    $review_error_user= Session::getError('reviews_per_user');
    $review_error_rubric = Session::getError('rubric_review');

    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
    <div class='form-wrapper form-edit rounded'>
    <form class='form-horizontal' enctype='multipart/form-data' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
    <input type='hidden' name='id' value='$id' />
    <input type='hidden' name='choice' value='do_edit' />
    <fieldset>
            <legend class='mb-0' aria-label='$langForm'></legend>
            <div class='row form-group ".($title_error ? "has-error" : "")."'>
                <label for='title' class='col-12 control-label-notes'>$langTitle <span class='asterisk Accent-200-cl'>(*)</span></label>
                <div class='col-12'>
                  <input name='title' type='text' class='form-control' id='title' value='".q($row->title)."' placeholder='$langTitle'>
                  <span class='help-block Accent-200-cl'>$title_error</span>
                </div>
            </div>
            <div class='row form-group mt-4'>
                <label for='desc' class='col-12 control-label-notes'>$langDescription</label>
                <div class='col-12'>
                " . rich_text_editor('desc', 4, 20, $row->description) . "
                </div>
            </div>";
    if (!empty($comments)) {
        $tool_content .= "<div class='row form-group mt-4'>
                <label for='comments' class='col-12 control-label-notes'>$m[comments]</label>
                <div class='col-12'>
                " . rich_text_editor('comments', 5, 65, $comments) . "
                </div>
            </div>";
    }

    $tool_content .= "
                <div class='row form-group mt-4'>
                    <label for='userfile' class='col-12 control-label-notes'>$langWorkFile</label>
                    <div class='col-12'>
                      ".(($row->file_name)? "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$row->id&amp;file_type=1'>".q($row->file_name)."</a>"
                      . "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=do_delete_file' onClick='return confirmation(\"$m[WorkDeleteAssignmentFileConfirm]\");'>
                             <span class='fa-solid fa-xmark fa-lg Accent-200-cl' title='$m[WorkDeleteAssignmentFile]'></span></a>" : "<input type='file' id='userfile' name='userfile' />")."
                    </div>
                </div>";
    if (is_active_external_lti_app($turnitinapp, TURNITIN_LTI_TYPE, $course_id)) {
        $tool_content .= "
                <div class='row form-group mt-4'>
                    <div class='col-12 control-label-notes mb-1'>$langAssignmentType</div>
                    <div class='col-12 d-inline-flex'>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='assignment_type' value='0'" . ($assignment_type == 0 ? " checked" : "") . ">
                             $langAssignmentTypeEclass
                          </label>
                        </div>
                        <div class='radio ms-3'>
                          <label>
                            <input type='radio' name='assignment_type' value='1'" . ($assignment_type == 1 ? " checked" : "") . ">
                            $langAssignmentTypeTurnitin
                          </label>
                        </div>
                    </div>
                    <div class='col-12 mt-1 mb-1'>
                        <span class='help-block'>$langTurnitinNewAssignNotice</span>
                    </div>
                </div>

                <div class='col-12'>
                <div class='container-fluid form-group " . ($assignment_type == 0 ? " hidden" : "") . " p-3' id='lti_label' style='margin-top: 30px; margin-bottom:30px; box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1); padding-top:10px; padding-bottom:10px;'>
                    <div class='TextBold large-text col-sm-offset-1'>$langLTIOptions</div>
                    <div class='form-group $lti_hidden mt-4'>
                        <label for='lti_templates' class='col-sm-6 control-label-notes'>$langTiiApp</label>
                        <div class='col-sm-12'>
                          <select name='lti_template' class='form-select' id='lti_templates' $lti_disabled>
                                $lti_template_options
                          </select>
                        </div>
                    </div>
                    <div class='form-group $lti_hidden mt-4'>
                        <label for='lti_launchcontainer' class='col-sm-6 control-label-notes'>$langLTILaunchContainer</label>
                        <div class='col-sm-12'>" . selection(lti_get_containers_selection(), 'lti_launchcontainer', $row->launchcontainer, 'id="lti_launchcontainer"' . $lti_disabled) . "</div>
                    </div>";

                $tool_content .= "
                <!--<div class='form-group $lti_hidden mt-3'>
                    <label for='tii_submit_papers_to' class='col-sm-6 control-label-notes'>$langTiiSubmissionSettings</label>
                    <div class='col-sm-12'>
                      <select name='tii_submit_papers_to' class='form-select' id='tii_submit_papers_to' $lti_disabled>
                            <option value='0' " . (($row->tii_submit_papers_to == 0) ? 'selected' : '') . ">$langTiiSubmissionNoStore</option>
                            <option value='1' " . (($row->tii_submit_papers_to == 1) ? 'selected' : '') . ">$langTiiSubmissionStandard</option>
                            <option value='2' " . (($row->tii_submit_papers_to == 2) ? 'selected' : '') . ">$langTiiSubmissionInstitutional</option>
                      </select>
                    </div>
                </div>-->
                <div class='form-group $lti_hidden mt-4'>
                    <div class='col-sm-12 control-label-notes'>$langTiiCompareAgainst</div>
                    <div class='col-sm-12'>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='tii_studentpapercheck' id='tii_studentpapercheck' value='1' " . ((($row->tii_studentpapercheck == 1) or ($assignment_type == 0)) ? 'checked' : '') . " $lti_disabled>
                            <span class='checkmark'></span>
                            $langTiiStudentPaperCheck
                          </label>
                        </div>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='tii_internetcheck' id='tii_internetcheck' value='1' " . ((($row->tii_internetcheck == 1)  or ($assignment_type == 0)) ? 'checked' : '') . " $lti_disabled>
                            <span class='checkmark'></span>
                            $langTiiInternetCheck
                          </label>
                        </div>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='tii_journalcheck' id='tii_journalcheck' value='1' " . ((($row->tii_journalcheck == 1) or ($assignment_type == 0)) ? 'checked' : '') . " $lti_disabled>
                            <span class='checkmark'></span>
                            $langTiiJournalCheck
                          </label>
                        </div>
                        <!--<div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='tii_institutioncheck' id='tii_institutioncheck' value='1' " . (($row->tii_institutioncheck == 1) ? 'checked' : '') . " $lti_disabled>
                            <span class='checkmark'></span>
                            $langTiiInstitutionCheck
                          </label>
                        </div>-->
                    </div>
                </div>
                <div class='form-group $lti_hidden mt-4'>
                    <label for='tii_report_gen_speed' class='col-sm-12 control-label-notes'>$langTiiSimilarityReport</label>
                    <div class='col-sm-12'>
                      <select name='tii_report_gen_speed' class='form-select' id='tii_report_gen_speed' $lti_disabled>
                            <option value='0' " . (($row->tii_report_gen_speed == 0) ? 'selected' : '') . ">$langTiiReportGenImmediatelyNoResubmit</option>
                            <option value='1' " . (($row->tii_report_gen_speed == 1) ? 'selected' : '') . ">$langTiiReportGenImmediatelyWithResubmit</option>
                            <option value='2' " . (($row->tii_report_gen_speed == 2) ? 'selected' : '') . ">$langTiiReportGenOnDue</option>
                      </select>
                    </div>
                    <div class='col-sm-12 mt-3'>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='tii_s_view_reports' id='tii_s_view_reports' value='1' " . (($row->tii_s_view_reports == 1) ? 'checked' : '') . " $lti_disabled>
                            <span class='checkmark'></span>
                            $langTiiSViewReports
                        </label>
                        </div>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='tii_use_biblio_exclusion' id='tii_use_biblio_exclusion' value='1' " . (($row->tii_use_biblio_exclusion == 1) ? 'checked' : '') . " $lti_disabled>
                            <span class='checkmark'></span>
                            $langTiiExcludeBiblio
                        </label>
                        </div>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='tii_use_quoted_exclusion' id='tii_use_quoted_exclusion' value='1' " . (($row->tii_use_quoted_exclusion == 1) ? 'checked' : '') . " $lti_disabled>
                            <span class='checkmark'></span>
                            $langTiiExcludeQuoted
                        </label>
                        </div>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='tii_use_small_exclusion' id='tii_use_small_exclusion' value='1' " . (($row->tii_exclude_type != 'none') ? 'checked' : '') . " $lti_disabled>
                            <span class='checkmark'></span>
                            $langTiiExcludeSmall
                        </label>
                        </div>
                    </div>
                </div>
                </div>
                    <div class='row form-group " . (($row->tii_exclude_type == 'none') ? 'hidden' : '') . " mt-4'>
                        <div class='col-12 control-label-notes'>$langTiiExcludeType</div>
                        <div class='col-12'>
                            <div class='radio'>
                              <label>
                                <input type='radio' name='tii_exclude_type' id='tii_exclude_type_words' value='words' " . (($row->tii_exclude_type == 'words' || $row->tii_exclude_type == 'none') ? 'checked' : '') . " $lti_disabled>
                                $langTiiExcludeTypeWords
                              </label>
                            </div>
                            <div class='radio'>
                              <label>
                                <input type='radio' name='tii_exclude_type' id='tii_exclude_type_percentage' value='percentage' " . (($row->tii_exclude_type == 'percentage') ? 'checked' : '') . " $lti_disabled>
                                $langPercentage
                              </label>
                            </div>
                        </div>
                </div>
                <div class='row form-group " . (($row->tii_exclude_type == 'none') ? 'hidden' : '') . " mt-4'>
                    <label for='tii_exclude_value' class='col-12 control-label-notes'>$langTiiExcludeValue</label>
                    <div class='col-12'>
                        <input name='tii_exclude_value' type='text' class='form-control' id='tii_exclude_value' value='" . intval($row->tii_exclude_value) . "' $lti_disabled>
                    </div>
                </div>
            </div>";
    } else {
        $tool_content .= "<input type='hidden' name='assignment_type' value='0' />";
    }
    $tool_content .= "
                <div class='row form-group mt-4'>
                    <div class='col-12 control-label-notes mb-1'>$langGradeType</div>
                    <div class='col-12'>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='numbers_button' name='grading_type' value='0'". ($grading_type==0 ? " checked" : "") .">
                             $langGradeNumbers
                          </label>
                        </div>";
                        if (!grading_scales_exist()) {
                            $addon = "disabled";
                            $class_not_visible = 'not_visible';
                            $label = "data-bs-toggle='tooltip' data-bs-placement='top' title='$langNoGradeScales'";
                        } else {
                            $addon = $class_not_visible = $label = '';
                        }
                        $tool_content .= "<div class='radio $class_not_visible'>
                                          <label $label>
                                            <input type='radio' id='scales_button' name='grading_type' value='1'". ($grading_type==ASSIGNMENT_SCALING_GRADE ? " checked" : "") ." $lti_group_disabled $addon>
                                            $langGradeScales
                                          </label>
                                        </div>";
                        if (!rubrics_exist()) {
                            $addon = "disabled";
                            $class_not_visible = 'not_visible';
                            $label = "data-bs-toggle='tooltip' data-bs-placement='top' title='$langNoGradeRubrics'";
                        } else {
                            $addon = $class_not_visible = $label = '';
                        }
                        $tool_content .= "<div class='radio $class_not_visible'>
                          <label $label>
                            <input type='radio' id='rubrics_button' name='grading_type' value='2'". ($grading_type==ASSIGNMENT_RUBRIC_GRADE ? " checked" : "") ." $lti_group_disabled $addon>
                            $langGradeRubrics
                          </label>
                        </div>
                        <div class='radio $class_not_visible'>
                          <label $label>
                            <input type='radio' id='reviews_button' name='grading_type' value='3'". ($grading_type==ASSIGNMENT_PEER_REVIEW_GRADE ? " checked" : "") ." $lti_group_disabled $addon>
                            $langGradeReviews
                          </label>
                        </div>
                    </div>
                </div>
                <div class='row form-group".($max_grade_error ? " has-error" : "").($grading_type==0 ? "" : " hidden")." mt-4'>
                    <label for='max_grade' class='col-12 control-label-notes'>$m[max_grade]</label>
                    <div class='col-12'>
                      <input name='max_grade' type='text' class='form-control' id='max_grade' placeholder='$m[max_grade]' value='$max_grade'>
                      <span class='help-block'>$max_grade_error</span>
                    </div>
                </div>
                <div class='row form-group".($scale_error ? " has-error" : "").($grading_type==1 ? "" : " hidden")." mt-4'>
                    <label for='scales' class='col-12 control-label-notes'>$langGradeScales</label>
                    <div class='col-12'>
                      <select name='scale' class='form-select' id='scales'".(!$grading_type ? " disabled" : "").">
                            $scale_options
                      </select>
                      <span class='help-block'>$scale_error</span>
                    </div>
                </div>
                <div class='row form-group".($rubric_error ? " has-error" : "").($grading_type==2 ? "" : " hidden")." mt-4'>
                    <label for='rubrics' class='col-12 control-label-notes'>$langGradeRubrics</label>
                    <div class='col-12'>
                      <select name='rubric' class='form-select' id='rubrics'".(!$grading_type ? " disabled" : "").">
                            $rubric_options
                      </select>
                      <span class='help-block'>$rubric_error</span>
                    </div>
                </div>


				<div class='row form-group" .($review_error_user ? " has-error" : " ").($grading_type==3 ? "" : " hidden")." mt-4'>
					<label for='reviews_per_user' class='col-12 control-label-notes'>$langReviewsPerUser</label>
					<div class='col-12'>
						<input name='reviews_per_user' id = 'reviews_per_user' type='text' class='form-control' value='".q($row->reviews_per_assignment)."'>
						<span class='help-block'>$langAllowableReviewValues $review_error_user</span>
					</div>
				</div>

                <div class='row form-group".($review_error_rubric ? " has-error" : "").($grading_type==3 ? "" : " hidden")." mt-4' >
				   <label for='reviews' class='col-12 control-label-notes'>$langGradeRubrics</label>
                    <div class='col-12'>
                     <select name='rubric_review' class='form-select' id='reviews'".(!$grading_type ? " disabled" : "").">
                            $rubric_option_review
                      </select>
                      <span class='help-block'>&nbsp;$review_error_rubric</span>
                    </div>
                    <div class='input-append date".(Session::getError('WorkStart_review') ? " has-error" : "")."' id='startdatepicker' data-date='$WorkStart_review' data-date-format='dd-mm-yyyy'>
                        <label for='WorkStart_review' class='col-sm-6 control-label-notes'>$langReviewStart</label>
                        <div class='col-sm-12'>
                           <div class='input-group'>
                               <span class='input-group-addon'>
                               <label class='label-container' aria-label='$langSelect'>
                                 <input class='mt-0' type='checkbox' id='enableWorkStart_review' name='enableWorkStart_review' value='1'".($enableWorkStart_review ? ' checked' : '').">
                                 <span class='checkmark'></span></label>
                                 </span>
                               <span class='add-on1 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                               <input class='form-control mt-0 border-start-0' name='WorkStart_review' id='WorkStart_review' type='text' value='$WorkStart_review'".($enableWorkStart_review ? '' : ' disabled').">
                            </div>
                            <span class='help-block'>".(Session::hasError('WorkStart_review') ? Session::getError('WorkStart_review') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langReviewDateHelpBlock")."</span>
							&nbsp
						</div>
                    </div>
                    <div class='input-append date".(Session::getError('WorkEnd_review') ? " has-error" : "")."' id='enddatepicker' data-date='$WorkEnd_review' data-date-format='dd-mm-yyyy'>
                        <label for='WorkEnd_review' class='col-sm-6 control-label-notes'>$langReviewEnd:</label>
                        <div class='col-sm-12'>
                           <div class='input-group'>
                               <span class='input-group-addon'>
                               <label class='label-container' aria-label='$langSelect'>
                                 <input class='mt-0' type='checkbox' id='enableWorkEnd_review' name='enableWorkEnd_review' value='1'".($enableWorkEnd_review ? ' checked' : '').">
                                 <span class='checkmark'></span></label>
                                 </span>
                               <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                               <input class='form-control mt-0 border-start-0' name='WorkEnd_review' id='WorkEnd_review' type='text' value='$WorkEnd_review'".($enableWorkEnd_review ? '' : ' disabled').">
                            </div>
                            <span class='help-block'>".(Session::hasError('WorkEnd_review') ? Session::getError('WorkEnd_review') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentEndHelpBlock")."</span>
                        </div>
                    </div>
                </div>

                <div class='row form-group mt-4'>
                    <div class='col-12 control-label-notes mb-1'>$langWorkSubType</div>
                    <div class='col-12'>
                        <div class='radio mb-2'>
                          <label>
                            <input type='radio' id='file_button' name='submission_type' value='0'" .
                            ($row->submission_type == 0 ? ' checked' : '') .">
                            $langWorkFile
                          </label>
                        </div>
                        <div class='radio form-inline mb-2'>
                          <label>
                            <input aria-label='$langWorkMultipleFiles' type='radio' id='online_button' name='submission_type' value='2'" .
                            ($row->submission_type == 2 ? ' checked' : '') .">
                            $langWorkMultipleFiles " . selection(fileCountOptions(), 'fileCount', $row->max_submissions) . "
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input aria-label='$langWorkOnlineText' type='radio' id='online_button' name='submission_type' value='1'" .
                            ($row->submission_type == 1 ? ' checked' : '') .">
                            $langWorkOnlineText
                          </label>
                        </div>
                    </div>
                </div>



                        <div class='row input-append date form-group".(Session::getError('WorkStart') ? " has-error" : "")." mt-4' id='startdatepicker' data-date='$WorkStart' data-date-format='dd-mm-yyyy'>
                            <label for='WorkStart' class='col-12 control-label-notes'>$langStartDate</label>
                            <div class='col-12'>
                            <div class='input-group'>
                                <span class='input-group-addon'>
                                    <label class='label-container' aria-label='$langSelect'>
                                    <input class='mt-0' type='checkbox' id='enableWorkStart' name='enableWorkStart' value='1'".($enableWorkStart ? ' checked' : '').">
                                    <span class='checkmark'></span></label>
                                </span>
                                <span class='add-on1 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                <input class='form-control mt-0 border-start-0' name='WorkStart' id='WorkStart' type='text' value='$WorkStart'".($enableWorkStart ? '' : ' disabled').">
                            </div>
                            <span class='help-block'>".(Session::hasError('WorkStart') ? Session::getError('WorkStart') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentStartHelpBlock")."</span>
                            </div>
                        </div>

                        <div class='row input-append date form-group".(Session::getError('WorkEnd') ? " has-error" : "")." mt-4' id='enddatepicker' data-date='$WorkEnd' data-date-format='dd-mm-yyyy'>
                            <label for='WorkEnd' class='col-12 control-label-notes'>$langGroupWorkDeadline_of_Submission</label>
                            <div class='col-12'>
                            <div class='input-group'>
                                <span class='input-group-addon'>
                                <label class='label-container' aria-label='$langSelect'>
                                    <input class='mt-0' type='checkbox' id='enableWorkEnd' name='enableWorkEnd' value='1'".($enableWorkEnd ? ' checked' : '').">
                                    <span class='checkmark'></span></label>
                                    </span>
                                <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                <input class='form-control mt-0 border-start-0' name='WorkEnd' id='WorkEnd' type='text' value='$WorkEnd'".($enableWorkEnd ? '' : ' disabled').">
                            </div>
                            <span class='help-block'>".(Session::hasError('WorkEnd') ? Session::getError('WorkEnd') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentEndHelpBlock")."</span>
                            </div>
                        </div>

                ";
    if (is_active_external_lti_app($turnitinapp, TURNITIN_LTI_TYPE, $course_id)) {
        $tool_content .= "
                <div class='row input-append date form-group $lti_hidden".(Session::getError('WorkFeedbackRelease') ? " has-error" : "")." mt-4' id='feedbackreleasedatepicker' data-date='$WorkFeedbackRelease' data-date-format='dd-mm-yyyy'>
                    <label for='tii_feedbackreleasedate' class='col-12 control-label-notes'>$langTiiFeedbackReleaseDate</label>
                    <div class='col-12'>
                       <div class='input-group'>
                           <span class='input-group-addon'>
                           <label class='label-container' aria-label='$langSelect'>
                             <input class='mt-0' type='checkbox' id='enableWorkFeedbackRelease' name='enableWorkFeedbackRelease' value='1'".($enableWorkFeedbackRelease ? ' checked' : '').">
                             <span class='checkmark'></span></label>
                             </span>
                           <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                           <input class='form-control mt-0 border-start-0' name='tii_feedbackreleasedate' id='tii_feedbackreleasedate' type='text' value='$WorkFeedbackRelease'".($enableWorkFeedbackRelease ? '' : ' disabled').">
                       </div>
                       <span class='help-block'>".(Session::hasError('WorkFeedbackRelease') ? Session::getError('WorkFeedbackRelease') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langAssignmentFeedbackReleaseHelpBlock")."</span>
                    </div>
                </div>
        ";
    }
    $tool_content .= "
                <div class='form-group ". ($WorkEnd ? "" : "hide") ." mt-4' id='late_sub_row'>
                    <div class='col-12'>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' id='late_submission' name='late_submission' value='1' ".(($row->late_submission)? 'checked' : '')."".($grading_type == 3 ? " disabled" : "").">
                            <span class='checkmark'></span>
                            $m[late_submission_enable]
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <div class='col-12'>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='notify_submission' value='1' $checked>
                            <span class='checkmark'></span>
                            $langNotifyAssignmentSubmission
                          </label>
                        </div>
                    </div>
                </div>
                <div class='row form-group mt-4'>
                    <div class='col-12 control-label-notes'>$m[group_or_user]</div>
                    <div class='col-12'>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='user_button' name='group_submissions' value='0' ".(($row->group_submissions==1) ? '' : 'checked').">
                            $m[user_work]
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='group_button' name='group_submissions' value='1' ".(($row->group_submissions==1) ? 'checked' : '')." $lti_group_disabled>
                            $m[group_work]
                          </label>
                        </div>
                    </div>
                </div>
                <div class='row form-group mt-4'>
                    <div class='col-12 control-label-notes'>$m[WorkAssignTo]</div>
                    <div class='col-12'>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='assign_button_all' name='assign_to_specific' value='0' ".(($row->assign_to_specific==1) ? '' : 'checked').">
                            <span id='assign_button_all_text'>$m[WorkToAllUsers]</span>
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='assign_button_some' name='assign_to_specific' value='1' ".(($row->assign_to_specific==1) ? 'checked' : '').">
                            <span id='assign_button_some_text'>$m[WorkToUser]</span>
                          </label>
                        </div>
                        <div class='radio' id='assign_group_div'>
                          <label>
                            <input type='radio' id='assign_button_group' name='assign_to_specific' value='2' ".(($row->assign_to_specific==2) ? 'checked' : '').">
                            <span id='assign_button_group_text'>$m[WorkToGroup]</span>
                          </label>
                        </div>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table id='assignees_tbl' class='table-default ".((($row->assign_to_specific==1) or ($row->assign_to_specific==2))? '' : 'hide')."'>
                            <thead><tr class='title1 list-headr'>
                              <td id='assignees' class='form-label'>$langStudents</td>
                              <td class='text-center form-label'>$langMove</td>
                              <td class='form-label'>$m[WorkAssignTo]</td>
                            </tr></thead>
                            <tr>
                              <td>
                                <select aria-label='$langStudents' class='form-select h-100 rounded-0' id='assign_box' size='10' multiple>
                                ".((isset($unassigned_options)) ? $unassigned_options : '')."
                                </select>
                              </td>
                              <td class='text-center'>
                                <input class='btn btn-outline-primary btn-sm rounded-2 h-40px' type='button' onClick=\"move('assign_box','assignee_box')\" value='   &gt;&gt;   ' /><br /><input class='btn btn-outline-primary btn-sm h-40px rounded-2 mt-2' type='button' onClick=\"move('assignee_box','assign_box')\" value='   &lt;&lt;   ' />
                              </td>
                              <td>
                                <select aria-label='$m[WorkAssignTo]' class='form-select h-100 rounded-0' id='assignee_box' name='ingroup[]' size='10' multiple>
                                ".((isset($assignee_options)) ? $assignee_options : '')."
                                </select>
                              </td>
                            </tr>
                            </table>
                        </div>
                    </div>
                </div>";
                    // `auto judge` assignment
                if ($autojudge->isEnabled()) {
                    $auto_judge = $row->auto_judge;
                    $lang = $row->lang;
                    $tool_content .= "
                    <div class='row form-group mt-4'>
                    <div class='col-12 control-label-notes'>$langAutoJudgeEnable</div>
                    <div class='col-12'>
                        <div class='radio'><label class='label-container' aria-label='$langSelect'><input type='checkbox' id='auto_judge' name='auto_judge' value='1' ".($auto_judge == true ? "checked='1'" : '')." /><span class='checkmark'></span></label></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>$langAutoJudgeInput</th>
                                    <th>$langOperator</th>
                                    <th>$langAutoJudgeExpectedOutput</th>
                                    <th>$langAutoJudgeWeight</th>
                                    <th aria-label='$langSelect'>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>";
                    $auto_judge_scenarios = $auto_judge == true ? unserialize($row->auto_judge_scenarios) : null;
                    $rows    = 0;
                    $display = 'visible';
                    if ($auto_judge_scenarios != null) {
                        $scenariosCount = count($auto_judge_scenarios);
                        foreach ($auto_judge_scenarios as $aajudge) {
                            $tool_content .= "
                                <tr>
                                    <td><input type='text' value='".htmlspecialchars($aajudge['input'], ENT_QUOTES)."' name='auto_judge_scenarios[$rows][input]' ".($autojudge->supportsInput() ? '' : 'readonly="readonly" placeholder="'.$langAutoJudgeInputNotSupported.'"')." /></td>
                                    <td>
                                        <select name='auto_judge_scenarios[$rows][assertion]' class='auto_judge_assertion' aria-label='$langSelect'>
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

                            $tool_content .= "
                                    <td><input type='text' value='$aajudge[weight]' name='auto_judge_scenarios[$rows][weight]' class='auto_judge_weight'/></td>
                                    <td><a href='#' aria-label='$langDelete' class='autojudge_remove_scenario' style='display: ".($rows <= 0 ? 'none': 'visible').";'>
                                    <span class='fa fa-fw fa-xmark text-danger' data-bs-original-title='$langDelete' data-bs-toggle='tooltip'></span>
                                    </a>
                                    </td>
                                </tr>";

                            $rows++;
                        }
                    } else {
                        $tool_content .= "
                                <tr>
                                    <td><input type='text' name='auto_judge_scenarios[$rows][input]' /></td>
                                    <td>
                                        <select name='auto_judge_scenarios[$rows][assertion]' class='auto_judge_assertion' aria-label='$langSelect'>
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
                                    <td><a href='#' class='autojudge_remove_scenario' style='display: none;' aria-label='$langDelete'>
                                        <span class='fa fa-fw fa-xmark text-danger' data-bs-original-title='$langDelete' data-bs-toggle='tooltip'></span>
                                    </a></td>
                                </tr>";
                    }
                    $tool_content .= "<tr>
                                        <td colspan='4' style='text-align: right;'> $langAutoJudgeSum: <span id='weights-sum'>0</span></td>
                                      </tr>
                                    <tr>
                                        <td colspan='4' style='text-align: left;'>
                                            <input type='submit' value='$langAutoJudgeNewScenario' id='autojudge_new_scenario'>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class='row form-group mt-4'>
                      <label for='lang' class='col-12 control-label-notes'>$langAutoJudgeProgrammingLanguage</label>
                      <div class='col-12'>
                        <select id='lang' name='lang'>";
                        foreach($autojudge->getSupportedLanguages() as $llang => $ext) {
                            $tool_content .= "<option value='$llang' ".($llang === $lang ? "selected='selected'" : "").">$llang</option>\n";
                        }
                        $tool_content .= "</select>
                      </div>
                    </div>";
                }
                // end of `auto judge` assignment

                $tool_content .= "
                <div class='row form-group mt-4'>
                    <label for='assignmentPasswordLock' class='col-12 control-label-notes'>$langPassCode</label>
                    <div class='col-12'>
                        <input name='assignmentPasswordLock' type='text' class='form-control' id='assignmentPasswordLock' value='".q($assignmentPasswordLock)."'>
                    </div>
                </div>
                <div class='row form-group ".(Session::getError('assignmentIPLock') ? 'has-error' : '')." mt-4'>
                    <label for='assignmentIPLock' class='col-12 control-label-notes'>$langIPUnlock</label>
                    <div class='col-12'>
                        <select name='assignmentIPLock[]' class='form-select' id='assignmentIPLock' multiple>
                            $assignmentIPLockOptions
                        </select>
                    </div>
                </div>" .
                eClassTag::tagInput($id);
        $tool_content .= "
            <div class='form-group mt-5'>
                <div class='col-12 d-inline-flex justify-content-end align-items-center'>


                        ".
                        form_buttons(array(
                            array(
                                'class'         => 'submitAdminBtn',
                                'name'          => 'do_edit',
                                'value'         => $langModify,
                                'javascript'    => "selectAll('assignee_box',true)"
                            ),
                            array(
                                'class' => 'cancelAdminBtn ms-1',
                                'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                            )
                        ))
                        ."



                </div>
            </div>
    </fieldset>
    </form></div></div><div class='d-none d-lg-block'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div>
</div>";
}



/**
 * @brief delete assignment
 * @param type $id
 */
function delete_assignment($id) {

    global $workPath, $course_code, $webDir, $course_id;

    $secret = work_secret($id);
    $row = Database::get()->querySingle("SELECT title, assign_to_specific FROM assignment WHERE course_id = ?d
                                        AND id = ?d", $course_id, $id);
    if ($row != null) {
        $uids = Database::get()->queryArray("SELECT uid FROM assignment_submit WHERE assignment_id = ?d", $id);
        foreach ($uids as $user_id) {
            triggerGame($course_id, $user_id->uid, $id);
            triggerAssignmentSubmit($course_id, $user_id->uid, $id);
            triggerAssignmentAnalytics($course_id, $user_id->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
            triggerAssignmentAnalytics($course_id, $user_id->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
        }
        if (Database::get()->query("DELETE FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id)->affectedRows > 0){
            Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $id);
            Database::get()->query("DELETE FROM assignment_grading_review WHERE assignment_id = ?d", $id);

            if ($row->assign_to_specific) {
                Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);
            }

            $admin_files_directory = $webDir . "/courses/" . $course_code . "/work/admin_files/" . $secret;
            removeDir($admin_files_directory);

            move_dir("$workPath/$secret", "$webDir/courses/garbage/{$course_code}_work_{$id}_$secret");

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
 * @param type $id
 */
function purge_assignment_subs($id) {

    global $workPath, $webDir, $course_code, $course_id;

    $secret = work_secret($id);
    $row = Database::get()->querySingle("SELECT title, assign_to_specific FROM assignment WHERE course_id = ?d
                                    AND id = ?d", $course_id, $id);
    $uids = Database::get()->queryArray("SELECT uid FROM assignment_submit WHERE assignment_id = ?d", $id);

    foreach ($uids as $user_id) {
        triggerGame($course_id, $user_id->uid, $id);
        triggerAssignmentSubmit($course_id, $user_id->uid, $id);
        triggerAssignmentAnalytics($course_id, $user_id->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
        triggerAssignmentAnalytics($course_id, $user_id->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
    }
    if (Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $id)->affectedRows > 0) {
        if ($row->assign_to_specific) {
            Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);
        }
        move_dir("$workPath/$secret", "$webDir/courses/garbage/{$course_code}_work_{$id}_$secret");
        return true;
    }
    return false;
}
/**
 * @brief delete user assignment
 * @param type $id
 */
function delete_user_assignment($id) {
    global $course_code, $webDir, $course_id;

    $return = true;
    $info = Database::get()->querySingle('SELECT uid, group_id, assignment_id
        FROM assignment_submit WHERE id = ?d', $id);
    if (is_null($info->group_id)) {
        $records = Database::get()->queryArray('SELECT id, file_path FROM assignment_submit
            WHERE assignment_id = ?d AND uid = ?d AND group_id IS NULL',
            $info->assignment_id, $info->uid);
    } else {
        $records = Database::get()->queryArray('SELECT id, file_path FROM assignment_submit
            WHERE assignment_id = ?d AND uid = ?d AND group_id = ?d',
            $info->assignment_id, $info->uid, $info->group_id);
    }
    foreach ($records as $record) {
        if (Database::get()->query("DELETE FROM assignment_submit WHERE id = ?d", $record->id)->affectedRows > 0) {
            if ($record->file_path) {
                $file = $webDir . "/courses/" . $course_code . "/work/" . $record->file_path;
                if (!my_delete($file)) {
                    $return = false;
                }
            }
        }
    }
    if ($return) {
        if (count($records) > 1) {
            $userdir = preg_replace('|/[^/]+$|', '', $file);
            rmdir($userdir);
        }
        triggerGame($course_id, $info->uid, $id);
        triggerAssignmentSubmit($course_id, $info->uid, $id);
        triggerAssignmentAnalytics($course_id, $info->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
        triggerAssignmentAnalytics($course_id, $info->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
    }
    return $return;
}
/**
 * @brief delete teacher assignment file
 * @param type $id
 */
function delete_teacher_assignment_file($id) {
    global $course_code, $webDir;

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
 * @param type $id
 */
function show_student_assignment($id) {

    global $tool_content, $m, $uid, $langUserOnly, $langBack,
        $course_id, $course_code, $langAssignmentWillBeActive,
        $langWrongPassword, $langIPHasNoAccess, $langNoPeerReview,
        $langPendingPeerSubmissions;

    $_SESSION['has_unlocked'] = array();

    $cdate = date('Y-m-d H:i:s');
    $user_group_info = user_group_info($uid, $course_id);
    if (!empty($user_group_info)) {
        $gids_sql_ready = implode(',',array_keys($user_group_info));
    } else {
        $gids_sql_ready = "''";
    }

    $row = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                                                         CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
                                                         CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due
                                                     FROM assignment
                                                     WHERE course_id = ?d
                                                        AND id = ?d
                                                        AND active = 1
                                                        AND (assign_to_specific = 0 OR
                                                             id IN
                                                               (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                                                UNION
                                                                SELECT assignment_id FROM assignment_to_specific
                                                                   WHERE group_id != 0 AND group_id IN ($gids_sql_ready)))",
                                                    $course_id, $id, $uid);

    $count_of_assign = countSubmissions($id);
    $_SESSION['has_unlocked'][$id] = true;
    if ($row) {
        if ($row->password_lock !== '' and (!isset($_POST['password']) or $_POST['password'] !== $row->password_lock)) {
            $_SESSION['has_unlocked'][$id] = false;
            Session::flash('message',$langWrongPassword);
            Session::flash('alert-class', 'alert-warning');
            if (isset($unit)) {
                redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit");
            } else {
                redirect_to_home_page("modules/work/index.php?course=" . $course_code);
            }
        }

        if ($row->ip_lock) {
            $user_ip = Log::get_client_ip();
            if (!match_ip_to_ip_or_cidr($user_ip, explode(',', $row->ip_lock))) {
                Session::flash('message', $langIPHasNoAccess);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page('modules/work/index.php?course=' . $course_code);
            }
        }

        $WorkStart = new DateTime($row->submission_date);
        $current_date = new DateTime('NOW');
        $interval = $WorkStart->diff($current_date);
        if ($WorkStart > $current_date) {
            Session::flash('message',$langAssignmentWillBeActive . ' ' . $WorkStart->format('d-m-Y H:i'));
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/work/index.php?course=$course_code");
        }

        if (isset($_GET['unit'])) {
            $back_url = "../units/index.php?course=$course_code&amp;id=$_GET[unit]";
        } else {
            $back_url = "$_SERVER[SCRIPT_NAME]?course=$course_code";
        }
        $tool_content .= action_bar(array(
           array(
               'title' => $langBack,
               'icon' => 'fa-reply',
               'url' => "$back_url",
               'level' => "primary"
           )
        ));

        $user = Database::get()->querySingle("SELECT * FROM assignment_submit
            WHERE assignment_id = ?d AND uid = ?d
            ORDER BY id LIMIT 1", $id, $uid);
        if ($user) {
            assignment_details($id, $row, false); // emfanizodai hmeromhnies start, due otan uparxei peer review
        } else {
            assignment_details($id, $row, true); // den emfanizontai oi hmeromhnies start, due otan o foithths den exei upovalei parolo pou uparxei peer review
        }

        $submit_ok = ($row->time > 0 || !(int) $row->deadline || $row->time <= 0 && $row->late_submission);
        $submissions_exist = false;

        if (!$uid) {
            $tool_content .= "<p>$langUserOnly</p>";
            $submit_ok = FALSE;
        } elseif ($GLOBALS['status'] == USER_GUEST) {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$m[noguest]</span></div></div>";
            $submit_ok = FALSE;
        } else {
            foreach (find_submissions($row->group_submissions, $uid, $id, $user_group_info) as $sub) {
                // if $submissions_exist is numeric > 1 displays different message
                if ($row->submission_type = 2) {
                    $submissions_exist = submission_count($sub->id);
                } else {
                    $submissions_exist = true;
                }
                if ($sub->grade != '' && $row->assignment_type != ASSIGNMENT_TYPE_TURNITIN) {
                    $submit_ok = false;
                }
                show_submission_details($sub->id);
            }
        }
        if ($submit_ok) {
            if ($row->assignment_type == ASSIGNMENT_TYPE_TURNITIN) {
                show_turnitin_integration($id);
            } else {
                //emfanizei mono thn forma ypovolhs
                show_submission_form($id, $user_group_info, false, $submissions_exist);
            }
        }
        // h sunarthhsh theloume na kaleitai an einai peer review kai an exei
        // upovalei ergasia o foithths dhladh an einai true h $submissions_exist
        $ass = Database::get()->querySingle("SELECT * FROM assignment_submit
                                 WHERE assignment_id = ?d AND uid = ?d ", $id, $uid);
        $rows = Database::get()->queryArray("SELECT * FROM assignment_grading_review
                                 WHERE assignment_id = ?d ", $id);
        if ($row->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $submissions_exist && $ass) {
            if ($row->start_date_review < $cdate) {
                if ($row->reviews_per_assignment < $count_of_assign && $rows) {
                    show_assignment_review($id);
                } elseif ($row->reviews_per_assignment < $count_of_assign && empty($rows)) {
                    Session::flash('message', $langPendingPeerSubmissions);
                    Session::flash('alert-class', 'alert-warning');
                } elseif ($row->reviews_per_assignment > $count_of_assign) {
                    Session::flash('message', $langNoPeerReview);
                    Session::flash('alert-class', 'alert-warning');
                }
            } else {
                //auto to mnm emfanizetai mexri kai thn hmeromhnia kai wra tou start_date_review
                Session::flash('message', $langPendingPeerSubmissions);
                Session::flash('alert-class', 'alert-warning');
            }
        }
    } else {
        redirect_to_home_page("modules/work/index.php?course=$course_code");
    }
}


/**
 * Count number of submitted files for a submission where submission_type = multiple files
 * @param integer $sub_id - a database id of a submission
 * @return integer $count
 */
function submission_count($sub_id) {
    $sub = Database::get()->querySingle('SELECT assignment_id, uid, group_id
        FROM assignment_submit WHERE id = ?d', $sub_id);
    return Database::get()->querySingle('SELECT COUNT(*) AS cnt
        FROM assignment_submit
        WHERE assignment_id = ?d AND
              (uid = ?d OR group_id = ?d)',
        $sub->assignment_id, $sub->uid, $sub->group_id)->cnt;
}


/**
 *sunarthsh foithth
 * @param type $id
 * @param type $display_graph_results
 */
function show_assignment_review($id, $display_graph_results = false) {
    global $tool_content, $head_content, $course_id, $works_url, $course_code, $uid, $langProgress,
        $langWorkOnlineText, $m, $langGradebookGrade, $langDownloadToPDF, $langPlagiarismCheck, $langPlagiarismResult,
        $langQuestionView, $langSGradebookBook, $langEdit, $langPeerSubmissions, $langSelect;

    $assign = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
        FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $nav[] = $works_url;
    $review_per_assignment = Database::get()->querySingle("SELECT reviews_per_assignment FROM assignment WHERE id = ?d", $id)->reviews_per_assignment;
    if (!$display_graph_results) {

        $head_content .= "
          <style>
            .table-responsive { width: 100%; }
            .table-responsive td { word-break: break-word; }
          </style>";

        $tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' class='form-inline'>
            <input type='hidden' name='grades_id' value='$id'>
            <div class='margin-bottom-thin'>
              <strong>$langPeerSubmissions:</strong>&nbsp; $review_per_assignment
            </div>
            <div class='table-responsive'>
              <table class='table-default'>
                <tbody>
                  <tr class='list-header'>
                    <th width='3%'>&nbsp;</th>";
        //auta einai ta onomata panw sto pedio tou pinaka bathmos hmeromhnia...
        $assign->submission_type ? $tool_content .= "<th>$langWorkOnlineText</th>" : sort_link($m['filename'], 'filename');
        sort_link($langGradebookGrade, 'grade');
        $tool_content .= "<th width='10%'><i class='fa fa-cogs'></i></th></tr>";
        $result = Database :: get()->queryArray("SELECT * from assignment_grading_review WHERE assignment_id = ?d && users_id = ?d",$id, $uid);
        $i = 1;
        $plagiarismlink = '';

        foreach ($result as $row) {
            $tool_content .="<input type='hidden' name='assignment' value='$row->id'>";
            if ($assign->submission_type) {
                $filelink = "<a href='#' class='onlineText btn btn-sm btn-default' data-id='$row->id'>$langQuestionView</a>";
            } else {
                if (empty($row->file_name)) {
                    $filelink = '&nbsp;';
                } else {
                    if (isset($_GET['unit'])) {
                        $unit = intval($_GET['unit']);
                        $fileUrl = "{$urlAppend}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;id=$unit&amp;get=$row->user_submit_id";
                    } else {
                        $fileUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$row->user_submit_id";
                    }
                    $namelen = mb_strlen($row->file_name);
                    if ($namelen > 30) {
                        $extlen = mb_strlen(get_file_extension($row->file_name));
                        $basename = mb_substr($row->file_name, 0, $namelen - $extlen - 3);
                        $ext = mb_substr($row->file_name, $namelen - $extlen - 3);
                        $filename = ellipsize($basename, 27, '...' . $ext);
                    } else {
                        $filename = $row->file_name;
                    }
                    $filelink = MultimediaHelper::chooseMediaAhrefRaw($fileUrl, $fileUrl, $filename, $row->file_name);

                }
            }
            if (isset($_GET['unit'])) {
                $edit_grade_link = "../units/view.php?course=$course_code&amp;res_type=assignment_grading&amp;unit=$unit&amp;assignment=$id&amp;submission=$row->id";
            } else {
                $edit_grade_link = "grade_edit_review.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
            }
            $icon_field = "<a class='link' href='$edit_grade_link' aria-label='$langEdit'><span class='fa fa-fw fa-edit' data-bs-original-title='$langEdit' title='' data-bs-toggle='tooltip'></span></a>";

            $grade = Database::get()->querySingle("SELECT grade FROM assignment_grading_review WHERE id = ?d ", $row->id )->grade;
            if (!empty($grade)) {
                $grade_field = "<input class='form-control' type='text' value='$grade' name='grade' maxlength='4' size='3' disabled>";
            } else {
                $icon_field = '';
                if (isset($_GET['unit'])) {
                    $grade_link = "../units/view.php?course=$course_code&amp;res_type=assignment_grading&amp;unit=$unit&amp;assignment=$id&amp;submission=$row->id";
                } else {
                    $grade_link = "grade_edit_review.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
                }
                $grade_field = "<a class='link' href='$grade_link' aria-label='$langSGradebookBook'><span class='fa fa-fw fa-plus' data-bs-original-title='$langSGradebookBook' title='' data-bs-toggle='tooltip'></span></a>";
            }
            $tool_content .= "<tr><td class='text-end' width='4'>$i.</td>";
            // check for plagiarism via unicheck (aka 'unplag') tool (http://www.unicheck.com)
            if (get_config('ext_unicheck_enabled') and valid_plagiarism_file_type($row->id)) {
                $results = Plagiarism::get()->getResults($row->id);
                if ($results) {
                    if ($results->ready) {
                        $plagiarismlink = "<small><a href='$results->resultURL' target=_blank>$langPlagiarismResult</a><br>(<a href='$results->pdfURL' target=_blank>$langDownloadToPDF</a>)</small>";
                    } else {
                        $plagiarismlink = "<small>$langProgress: ". $results->progress*100 . "%</small>";
                    }
                } else {
                    $plagiarismlink = "<span class='small'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;chk=$row->id'>$langPlagiarismCheck</a></span>";
                }
            }
            // ---------------------------------
            $tool_content .= "
                <td class='text-start'>
                  $filelink <br> $plagiarismlink
                </td>
                <td width='5'>
                  <div class='form-group ".(Session::getError("grade.$row->id") ? "has-error" : "")."'>
                    $grade_field
                    <span class='help-block Accent-200-cl'>".Session::getError("grade.$row->id")."</span>
                  </div>
                </td>
                <td>
                  $icon_field
                </td></tr>";
            $i++;
        }
        //end foreach
        $tool_content .= "
                    </tbody>
                </table>
            </div>
            <div class='form-group'>
              <div class='col-12'>
                 <div class='checkbox'>
                 <label class='label-container' aria-label='$langSelect'>
                     <input type='checkbox' value='1' name='send_email' checked><span class='checkmark'></span> $m[email_users]
                   </label>
                 </div>
              </div>
            </div>
          </form>";
    }
}


/**
 * @brief display submission assignment form
 * @param type $id
 * @param type $user_group_info
 * @param type $on_behalf_of
 * @param type $submissions_exist
 * @return type
 */
function show_submission_form($id, $user_group_info, $on_behalf_of=false, $submissions_exist=false) {
    global $tool_content, $m, $langWorkFile, $langSubmit, $langWorkFileLimit,
    $langNotice3, $langNotice3Multiple, $urlAppend, $langGroupSpaceLink, $langOnBehalfOf,
    $course_code, $course_id, $langBack, $is_editor, $langWorkOnlineText,
    $langGradebookGrade, $langComments, $langImgFormsDes, $langSelect, $langForm;

    if (!$_SESSION['courses'][$course_code]) {
        return;
    }

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
    /*if ($assignment->late_submission) {
        $tool_content .= "<div class='alert alert-warning'>$langWarnAboutDeadLine</div>";
    }*/
    $cdate = date('Y-m-d H:i:s');

    $group_select_hidden_input = $group_select_form = $course_unit_hidden_input = '';
    if (isset($_GET['unit'])) {
        $course_unit_hidden_input = "<input type='hidden' name='unit' value='$_GET[unit]'>
                                     <input type='hidden' name='res_type' value='assignment'>";
    }
    $is_group_assignment = is_group_assignment($id);
    if ($is_group_assignment) {
        if (!$on_behalf_of) {
            if (count($user_group_info) == 1) {
                $gids = array_keys($user_group_info);
                $group_link = $urlAppend . '/modules/group/document.php?gid=' . $gids[0];
                $group_select_hidden_input = "<input type='hidden' name='group_id' value='$gids[0]' />";
            } elseif ($user_group_info) {
                $group_select_form = "
                        <div class='form-group mt-4'>
                            <label for='group_id' class='col-sm-6 control-label-notes'>$langGroupSpaceLink:</label>
                            <div class='col-sm-12'>
                              " . selection($user_group_info, 'group_id') . "
                            </div>
                        </div>";
            } else {
                $group_link = $urlAppend . 'modules/group/';
                $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$m[this_is_group_assignment] <br />" .
                        sprintf(count($user_group_info) ?
                                        $m['group_assignment_publish'] :
                                        $m['group_assignment_no_groups'], $group_link) .
                        "</span></div></div>\n";
            }
        } else {
            $groups_with_no_submissions = groups_with_no_submissions($id);
            if (count($groups_with_no_submissions)>0) {
                $group_select_form = "
                        <div class='form-group mt-4'>
                            <label for='group_id' class='col-sm-6 control-label-notes'>$langGroupSpaceLink:</label>
                            <div class='col-sm-12'>
                              " . selection($groups_with_no_submissions, 'group_id') . "
                            </div>
                        </div>";
            } else {
                Session::flash('message',$m['NoneWorkGroupNoSubmission']);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
            }
        }
    } elseif ($on_behalf_of) {
            $users_with_no_submissions = users_with_no_submissions($id);

            if (count($users_with_no_submissions)>0) {
                $group_select_form = "
                        <div class='form-group mt-4'>
                            <label for='user_id' class='col-sm-6 control-label-notes'>$langOnBehalfOf:</label>
                            <div class='col-sm-12'>
                              " .selection($users_with_no_submissions, 'user_id', '', "class='form-control'") . "
                            </div>
                        </div>";
            } else {
                Session::flash('message',$m['NoneWorkUserNoSubmission']);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
            }
    }
    $notice = $submissions_exist > 1? $langNotice3Multiple: $langNotice3;
    $notice = ($submissions_exist)?
    "<div class='col-12 mt-3'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$notice</span></div></div>": '';
    if ($assignment->grading_type == ASSIGNMENT_SCALING_GRADE) {
        $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assignment->grading_scale_id, $course_id)->scales;
        $scales = unserialize($serialized_scale_data);
        $scale_options = "<option value> - </option>";
        foreach ($scales as $scale) {
            $scale_options .= "<option value='$scale[scale_item_value]'>$scale[scale_item_name]</option>";
        }
        $grade_field = "<select name='grade' class='form-select' id='scales'>$scale_options</select>";
    } elseif ($assignment->grading_type == ASSIGNMENT_RUBRIC_GRADE) {
        $valuegrade = (isset($grade)) ? $grade : '';
        $grade_field = "<input class='form-control' type='text' value='$valuegrade' name='grade' maxlength='4' size='3' readonly>";
    } elseif ($assignment->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
        $valuegrade = (isset($grade)) ? $grade : '';
        $grade_field = "<input class='form-control' type='text' value='$valuegrade' name='grade' maxlength='4' size='3' readonly>";
    } else {
        $grade_field = "<input class='form-control' type='text' name='grade' maxlength='4' size='3'> ($m[max_grade]: $assignment->max_grade)";
    }
    $extra = $on_behalf_of ? "
                        <div class='form-group mt-4'>
                            <div class='col-sm-6 control-label-notes'>$langGradebookGrade:</div>
                            <div class='col-sm-12'>
                              $grade_field
                              <input type='hidden' name='on_behalf_of' value='1'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <div class='col-sm-10 col-sm-offset-2'>
                                <div class='checkbox'>
                                <label class='label-container' aria-label='$langSelect'>
                                    <input type='checkbox' name='send_email' id='email_button' value='1' checked>
                                    <span class='checkmark'></span>
                                    $m[email_users]
                                  </label>
                                </div>
                            </div>
                        </div>" : '';
    if (!$is_group_assignment || count($user_group_info) || $on_behalf_of) {
        if (isset($_POST['password'])) {
            $password_input = '<input type="hidden" name="password" value="' . q($_POST['password']) . '">';
        } else {
            $password_input = '';
        }
        if ($assignment->submission_type == 1) {
            // Online text submission
            $submission_form = "
                        <div class='form-group mt-0'>
                            <label for='submission_text' class='col-sm-6 control-label-notes'>$langWorkOnlineText:</label>
                            <div class='col-sm-12'>
                                ". rich_text_editor('submission_text', 10, 20, '') ."
                            </div>
                        </div>";
        } else {
            // Multiple or single file submission
            if ($assignment->submission_type == 2) {
                $label = sprintf($langWorkFileLimit, $assignment->max_submissions);
                $maxFiles = $assignment->max_submissions;
                $inputName = 'userfile[]';
                $moreButton = "<div>
                                 <button class='btn submitAdminBtn btn-sm moreFiles' aria-label='Add'>
                                   <span class='fa fa-plus'></span>
                                 </button>
                               </div>";
                $GLOBALS['head_content'] .=
                    "<script>$(function () { initialize_multifile_submission($maxFiles) });</script>";
            } else {
                $inputName = 'userfile';
                $label = $langWorkFile;
                $moreButton = '';
            }
            $submission_form = "
                        <div class='form-group mt-4'>
                            <label for='userfile' class='col-sm-6 control-label-notes'>$label:</label>
                            <div class='col-sm-10'>$moreButton
                              <input type='file' name='$inputName' id='userfile'>
                            </div>
                        </div>";
        }
        if ($is_editor) {
            $back_link = $form_link = "index.php?course=$course_code&id=$id";
        } else {
            if (isset($_GET['unit'])) {
                $back_link = "../units/index.php?course=$course_code&id=$_GET[unit]";
                $form_link = "../units/view.php?course=$course_code";
            } else {
                $back_link = $form_link = "{$urlAppend}modules/work/index.php?course=$course_code";
            }
        }
        $tool_content .= action_bar(array(
                array(
                    'title' => $langBack,
                    'icon' => 'fa-reply',
                    'level' => 'primary',
                    'url' => "index.php?course=$course_code&id=$id",
                    'show' => $is_editor
                )
            ))."
                    $notice
                    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
                     <form class='form-horizontal' enctype='multipart/form-data' action='$form_link' method='post'>
                        <input type='hidden' name='id' value='$id' />$group_select_hidden_input $course_unit_hidden_input
                        <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        $group_select_form
                        $submission_form
                        <div class='form-group mt-4'>
                            <label for='stud_comments' class='col-sm-6 control-label-notes'>$langComments:</label>
                            <div class='col-sm-12'>
                              <textarea class='form-control' name='stud_comments' id='stud_comments' rows='5'></textarea>
                            </div>
                        </div>
                        $extra
                        <div class='form-group mt-4'>
                            <div class='col-12 d-flex justify-content-end align-items-center'>".
                    form_buttons(array(
                        array(
                            'class'         => 'submitAdminBtn',
                            'text'          => $langSubmit,
                            'name'          => 'work_submit',
                            'value'         => $langSubmit
                        ),
                        array(
                            'class' => 'cancelAdminBtn',
                            'href' => $back_link
                        )
                    ))
                    ."</div>
                        </div>
                        </fieldset>
                     </form>
                     </div></div><div class='d-none d-lg-block'>
                     <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                 </div>
                 </div>
                     <div class='float-end'><small>$GLOBALS[langMaxFileSize] " .
                ini_get('upload_max_filesize') . "</small></div><br>";
    }
}

function show_turnitin_integration($id) {
    global $tool_content, $head_content, $course_code, $langTurnitinIntegration, $urlAppend;

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
    $lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d", $assignment->lti_template);

    if ($assignment->launchcontainer == LTI_LAUNCHCONTAINER_EMBED) {
        /*$head_content .= <<<EOF
<script type='text/javascript'>
//<![CDATA[
$(document).ready(function() {

    var lastHeight;
    var padding = 15;
    var frame = $("#contentframe");

    var resize = function(e) {
        var viewportH = $(window).height();
        var docH = $(document).height();
        var minHeight = Math.min(docH, viewportH);
        if (lastHeight !== minHeight) {
            frame.css("height", viewportH - frame.offset().top - padding + "px");
            lastHeight = minHeight;
        }
    };

    resize();

    $(window).on('resize', function() {
        resize();
    });

});
//]]
</script>
EOF;*/

        $tool_content .= '<div class="col-sm-12 mt-3"><iframe id="contentframe"
            src="' . $urlAppend . "modules/work/post_launch.php?course=" . $course_code . "&amp;id=" . $id . '"
            webkitallowfullscreen=""
            mozallowfullscreen=""
            allowfullscreen=""
            width="100%"
            height="800px"
            style="border: 1px solid #ddd; border-radius: 4px;"></iframe>';
    } else {
        $joinLink = create_join_button(
            $lti->lti_provider_url,
            $lti->lti_provider_key,
            $lti->lti_provider_secret,
            $assignment->id,
            RESOURCE_LINK_TYPE_ASSIGNMENT,
            $assignment->title,
            $assignment->description,
            $assignment->launchcontainer,
            $langTurnitinIntegration . ":&nbsp;&nbsp;",
            $assignment
        );

        $tool_content .= "<div class='form-wrapper'>" . $joinLink . "</div>";
    }
}



/**
 * @brief display assignment details
 * @param type $id
 * @param type $row
 */
function assignment_details($id, $row, $x =false) {
    global $tool_content, $head_content, $is_editor, $course_code, $m, $langDaysLeft, $course_id, $uid,
           $langEndDeadline, $langDelAssign, $langAddGrade, $langZipDownload, $langTags, $langNoDeadline,
           $langGraphResults, $langWorksDelConfirm, $langWorkFile, $langGradeType, $langGradeNumber,
           $langGradeScale, $langGradeRubric, $langCriteria, $langDetail, $urlAppend, $langBack,
           $langEditChange, $langExportGrades, $langDescription, $langTitle, $langWarnAboutDeadLine,
           $langReviewStart, $langReviewEnd, $langGradeReviews, $langImportGrades, $langGroupWorkDeadline_of_Submission;

    load_js('screenfull/screenfull.min.js');
    $head_content .= "<script>$(function () {
            initialize_filemodal({
                download: '$GLOBALS[langDownload]',
                print: '$GLOBALS[langPrint]',
                fullScreen: '$GLOBALS[langFullScreen]',
                newTab: '$GLOBALS[langNewTab]',
                cancel: '$GLOBALS[langCancel]'
            });
        });</script>";

    if ($row->assign_to_specific == 1) {
        $assign_to_users_message = "$m[WorkToUser]";
    } else if ($row->assign_to_specific == 2) {
        $assign_to_users_message = "$m[WorkToGroup]";
    } else {
        $assign_to_users_message = "$m[WorkToAllUsers]";
    }

    $preview_rubric = '';
    $grade_type = $row->grading_type;
    if ($grade_type == 0){
        $g_type = $langGradeNumber;
    }
    elseif ($grade_type == ASSIGNMENT_SCALING_GRADE) {
        $g_type = $langGradeScale;
    } elseif ($grade_type == ASSIGNMENT_RUBRIC_GRADE) {
        $g_type = $langGradeRubric;
        $rubric_id = $row ->grading_scale_id;
        $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $rubric_id);
        if ($rubric) {
            $rubric_name =  $rubric->name;
            $rubric_desc = $rubric -> description;
            $preview_rubric = $rubric -> preview_rubric;
            $points_to_graded = $rubric -> points_to_graded;
            $criteria = unserialize($rubric->scales);
            $criteria_list = "";
            foreach ($criteria as $ci => $criterio) {
                $criteria_list .= "<li><b>$criterio[title_name] ($criterio[crit_weight]%)</b></li>";
                if(is_array($criterio['crit_scales'])) {
                    $criteria_list .= "<li><ul>";
                    foreach ($criterio['crit_scales'] as $si=>$scale) {
                        if ($preview_rubric ==1 AND $points_to_graded == 1) {
                            $criteria_list .= "<li>$scale[scale_item_name] ( $scale[scale_item_value] )</li>";
                        } elseif ($preview_rubric ==1 AND $points_to_graded == 0) {
                            $criteria_list .= "<li>$scale[scale_item_name]</li>";
                        } else {
                            $criteria_list .= "";
                        }
                    }
                    $criteria_list .= "</ul></li>";
                }
            }
        }
    } elseif ($grade_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
        $g_type = $langGradeReviews;
        $rubric_id = $row ->grading_scale_id;
        $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $rubric_id);
        if ($rubric) {
            $rubric_name =  $rubric->name;
            $rubric_desc = $rubric -> description;
            $preview_rubric = $rubric -> preview_rubric;
            $points_to_graded = $rubric -> points_to_graded;
            $criteria = unserialize($rubric->scales);
            $criteria_list = "";
            foreach ($criteria as $ci => $criterio) {
                $criteria_list .= "<li><b>$criterio[title_name] ($criterio[crit_weight]%)</b></li>";
                if(is_array($criterio['crit_scales'])) {
                    $criteria_list .= "<li><ul>";
                    foreach ($criterio['crit_scales'] as $si=>$scale) {
                        if ($preview_rubric ==1 AND $points_to_graded == 1) {
                            $criteria_list .= "<li>$scale[scale_item_name] ( $scale[scale_item_value] )</li>";
                        } elseif ($preview_rubric ==1 AND $points_to_graded == 0) {
                            $criteria_list .= "<li>$scale[scale_item_name]</li>";
                        } else {
                            $criteria_list .= "";
                        }
                    }
                    $criteria_list .= "</ul></li>";
                }
            }
        }
		//metraei tis meres gia start review
		$start_date_review = format_locale_date(strtotime($row->start_date_review));
		if ($row->time_start > 0) {
			$start_date_review_notice = "<br><span>($langDaysLeft " . format_time_duration($row->time_start) . ")</span>";
		} /*elseif ((int)$row->start_date_review) {
			$start_date_review_notice = "<br><span class='text-danger'>$langEndDeadline</span>";
		}*/

		//metraei tis meres gia lhksh review
		$due_date_review = format_locale_date(strtotime($row->due_date_review));
		if ($row->time_due > 0) {
			$due_date_review_notice = "<br><span>($langDaysLeft " . format_time_duration($row->time_due) . ")</span>";
		} elseif ((int)$row->due_date_review) {
			$due_date_review_notice = "<br><span class='text-danger'>$langEndDeadline</span>";
		}
    }
    if ($is_editor) {
        if (isset($_GET['disp_results']) or isset($_GET['disp_non_submitted'])) {
            $tool_content .= action_bar(array(
                array(
                    'title' => $langBack,
                    'icon' => 'fa-reply',
                    'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$id",
                    'level' => 'primary-label'
                )
            ));
        } else {
            $tool_content .= action_bar(array(
            array(
                'title' => $langZipDownload,
                'icon' => 'fa-file-zipper',
                'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;download=$id",
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            ),
            array(
                'title' => $langExportGrades,
                'icon' => 'fa-file-excel',
                'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$id&amp;choice=export",
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            ),
            array(
                'title' => $langAddGrade,
                'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$id&amp;choice=add",
                'icon' => 'fa-plus-circle'
            ),
            array(
                'title' => $langImportGrades,
                'icon' => 'fa-upload',
                'url' => "import.php?course=$course_code&amp;id=$id",
                'show' => ($grade_type == 0)
            ),
            array(
                'title' => $langGraphResults,
                'icon' => 'fa-bar-chart',
                'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$id&amp;disp_results=true"
            ),
            array(
                'title' => $m['WorkUserGroupNoSubmission'],
                'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$id&amp;disp_non_submitted=true",
                'icon' => 'fa-minus-square'
            ),
            array(
                'title' => $langDelAssign,
                'icon' => 'fa-xmark',
                'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$id&amp;choice=do_delete",
                'text-class' => 'text-danger',
                'button-class' => "deleteAdminBtn",
                'confirm' => "$langWorksDelConfirm"
            )
        ));
        }
    }
    $deadline = (int)$row->deadline ? format_locale_date(strtotime($row->deadline)) : $langNoDeadline;
    if ($row->time > 0) {
        $deadline_notice = "<br><span>($langDaysLeft " . format_time_duration($row->time) . ")</span>";
    } elseif ((int)$row->deadline) {
        $deadline_notice = "<br><span class='text-danger'>$langEndDeadline</span>";
    }

    $moduleTag = new ModuleElement($id);
    $tool_content .= "
    <div class='col-12'>
    <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
            <h3 class='mb-0'>
                $m[WorkInfo]
            </h3>
                ". (($is_editor) ?
                "<a href='{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$id&amp;choice=edit' aria-label='$langEditChange'>
                    <span class='fa-solid fa-edit fa-lg' title='' data-bs-toggle='tooltip' data-bs-original-title='$langEditChange'></span>
                </a>" : "")."

        </div>
        <div class='card-body'>
        <ul class='list-group list-group-flush'>
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$langTitle</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height' id='assignment_title'>
                        " . q($row->title) . "
                    </div>
                </div>
            </li>";
        if (!empty($row->description)) {
            $tool_content .= "<li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$langDescription</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        " . mathfilter($row->description, 12 , "../../courses/mathimg/") . "
                    </div>
                </div>
            </li>";
        }
        if (!empty($row->comments)) {
            $tool_content .= "<li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$m[comments]</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height' style='white-space: pre-wrap'>
                        $row->comments
                    </div>
                </div>
            </li>";
        }
        if (isset($_GET['unit'])) {
            $unit = intval($_GET['unit']);
            $fileUrl = "{$urlAppend}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;get=$row->id&amp;file_type=1&amp;id=$unit";
        } else {
            $fileUrl = "{$urlAppend}modules/work/index.php?course=$course_code&amp;get=$row->id&amp;file_type=1";
        }
        if (!empty($row->file_name)) {
            $filelink = MultimediaHelper::chooseMediaAhrefRaw($fileUrl, $fileUrl, $row->file_name, $row->file_name);
            $tool_content .= "
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$langWorkFile</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        $filelink
                    </div>
                </div>
            </li>";
        }
        $tool_content .= "
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$m[max_grade]</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        $row->max_grade
                    </div>
                </div>
            </li>
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default mb-1'>$langGradeType</div>
                    </div>
                <div class='col-md-9 col-12 title-default-line-height'>";
                    if ($preview_rubric == 1) {
                        $tool_content .= "
                            <a class='' role='button' data-bs-toggle='collapse' href='#collapseRubric' aria-expanded='false' aria-controls='collapseRubric'>
                                $g_type
                            </a>
                        </div>
                        </div>
                        <div class='table-responsive collapse' id='collapseRubric'>
                            <table class='table-default'>
                                <thead class='list-header'>
                                    <th>$langDetail</th>
                                    <th>$langCriteria</th>
                                </thead>
                                <tr>
                                    <td><div class='text-heading-h5'>$rubric_name</div><div class='text-heading-h6'>$rubric_desc</div></td>
                                    <td>
                                        <ul class='list-unstyled'>
                                            $criteria_list
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </div>";
                    } else {
                        $tool_content .= "$g_type
                            </div></div>";
                    }
        $tool_content .= "</li>
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$m[start_date]</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        " . format_locale_date(strtotime($row->submission_date)) . "
                    </div>
                </div>
            </li>
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$langGroupWorkDeadline_of_Submission</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        $deadline ".(isset($deadline_notice) ? $deadline_notice : "")."
                    </div>
                </div>
            </li>
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$m[group_or_user]</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        ".(($row->group_submissions == '0') ? $m['user_work'] : $m['group_work'])."
                    </div>
                </div>
            </li>
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$m[WorkAssignTo]</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        $assign_to_users_message
                    </div>
                </div>
            </li>
            ";
        $tags_list = $moduleTag->showTags();
        if ($tags_list) {
            $tool_content .= "
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$langTags</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        $tags_list
                    </div>
                </div>
            </li>";
        }
		$review_per_assignment = Database::get()->querySingle("SELECT reviews_per_assignment FROM assignment WHERE id = ?d", $id)->reviews_per_assignment;
		if ($grade_type == 3 && !$x){
			$tool_content .= "
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>$langReviewStart</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            $start_date_review ".(isset($start_date_review_notice) ? $start_date_review_notice: "")."
                        </div>
                    </div>
                </li>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>$langReviewEnd:</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            $due_date_review ".(isset($due_date_review_notice) ? $due_date_review_notice: "")."
                        </div>
                    </div>
                </li>
				";
		}
    $tool_content .= "</ul>
        </div>
    </div></div>";
    $cdate = date('Y-m-d H:i:s');
    if ($row->deadline < $cdate && $row->late_submission && !$is_editor) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langWarnAboutDeadLine</span></div></div>";
    }
}


/**
 * @brief show assignment - prof view only
 * @brief the optional message appears instead of assignment details
 * @param type $id
 */
function show_assignment($id) {
    global $tool_content, $head_content, $langNoSubmissions, $langSubmissions, $langGradebookGrade, $langEdit,
    $langWorkOnlineText, $langGradeOk, $langPlagiarismResult, $langHasAssignmentPublished, $langMailToUsers,
    $m, $course_code, $works_url, $course_id, $langDownloadToPDF, $langGradedAt,
    $langQuestionView, $langAmShort, $langSGradebookBook, $langDeleteSubmission, $urlAppend, $langTransferGrades,
    $langAutoJudgeShowWorkResultRpt, $langSurnameName, $langFileName,
    $langPeerReviewImpossible, $langPeerReviewGrade, $langPeerReviewCompletedByStudent,
    $autojudge, $langPeerReviewPendingByStudent, $langPeerReviewMissingByStudent, $langAssignmentDistribution,
    $langQuestionCorrectionTitle2, $langFrom2, $langOpenCoursesFiles, $is_editor, $langSelect, $langSettingSelect;

    // transfer grades in peer review assignment
    $head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
           $('a#transfer_grades').click(function(e) {
               e.preventDefault();
               $('input[name=grade_review]').each(function() {
                   if (this.value) {
                       var input_grade_value_name = 'grades[' + this.id + '][grade]';
                       var input_grade = $('input[name=\"' + input_grade_value_name + '\"]');
                       if (!input_grade.val()) {
                           input_grade.val(this.value);
                       }
                   }
              });
           })
        });
    </script>";

    $assign = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                                                        CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
                                                        CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due,
                                                        auto_judge
                                                    FROM assignment
                                                      WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $nav[] = $works_url;
    assignment_details($id, $assign);
    $auto_judge_enabled_assign = $assign->auto_judge;

	$cdate = date('Y-m-d H:i:s');
    $count_of_ass = countSubmissions($id);

    //to button anathesh tha emfanizetai sto xroniko diasthma apo deadline assignment
    if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $cdate > $assign->deadline) {
        if ($assign->reviews_per_assignment < $count_of_ass) {
            $tool_content .= " <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code' enctype='multipart/form-data'>
                           <input type='hidden' name='assign' value='$id'>
                           <div class='form-group'>
                                <div class='text-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='ass_review' value='$langAssignmentDistribution'>
                                </div>
                           </div>
                           </form>";
        } else {
            Session::flash('message', $langPeerReviewImpossible);
            Session::flash('alert-class', 'alert-warning');
        }
    }

    $rev = (@($_REQUEST['rev'] == 1)) ? 'DESC' : 'ASC';
    if (isset($_REQUEST['sort'])) {
        if ($_REQUEST['sort'] == 'date') {
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

    if ($assign->assignment_type == ASSIGNMENT_TYPE_TURNITIN) {
        show_turnitin_integration($id);
    }
    $count_of_assignments = countSubmissions($id);
    if ($count_of_assignments > 0) {
        if ($count_of_assignments == 1) {
            $num_of_submissions = $langHasAssignmentPublished;
        } else {
            $num_of_submissions = sprintf("$m[more_submissions]", $count_of_assignments);
        }

        $result = Database::get()->queryArray("SELECT assign.id id, assign.file_name file_name,
                                                assign.uid uid, assign.group_id group_id,
                                                assign.submission_date submission_date,
                                                assign.grade_submission_date grade_submission_date,
                                                assign.grade grade, assign.comments comments,
                                                assign.grade_comments grade_comments,
                                                assign.grade_comments_filename grade_comments_filename,
                                                assign.grade_comments_filepath grade_comments_filepath,
                                                assignment.grading_scale_id grading_scale_id,
                                                assignment.deadline deadline,
                                                assignment.grading_type
                                               FROM assignment_submit AS assign, user, assignment
                                               WHERE assign.assignment_id = ?d AND assign.assignment_id = assignment.id AND user.id = assign.uid
                                               ORDER BY $order $rev, assign.id", $id);

        $head_content .= "
          <style>
            .table-responsive { width: 100%; }
            .table-responsive td { word-break: break-word; }
          </style>";
        $tool_content .= "<form action='{$urlAppend}modules/work/index.php?course=$course_code' method='post' class='form-inline'>
            <input type='hidden' name='grades_id' value='$id' />
            <br>
            <div class='alert alert-success'>
                <strong>$langSubmissions:</strong>&nbsp; $count_of_assignments";
            // button for transferring student peer review grades to teacher grades
            if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && ($count_of_ass > 0) && $is_editor) {
                $tool_content .= "<div class='text-end' style='margin-bottom: 15px;'><a class='btn submitAdminBtn' href='$_SERVER[SCRIPT_NAME]?course=$course_code' id='transfer_grades'>$langTransferGrades</a></div>";
            }
            $tool_content .= "</div>";

            $tool_content .= "
                <div class='table-responsive mt-3'>
                <table class='table table-default'>

                <thead><tr class='list-header'>
                <th class='count-col'>#</th>";
                sort_link($langSurnameName, 'username', 'class="user-col"');
                if ($assign->submission_type == 1)  {
                    $tool_content .= "<th>$langWorkOnlineText</th>";
                } elseif ($assign->submission_type == 2) {
                    $tool_content .= "<th>$langOpenCoursesFiles</th>";
                } else {
                    $tool_content .= "<th>$langFileName</th>";
                }
                sort_link($m['sub_date'], 'date', 'class="date-col"');
                if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) { //neo pedio vathmos aksiologhshs mono gia peer review
                    sort_link($langPeerReviewGrade, '');
                }
                sort_link($langGradebookGrade, 'grade', 'style="width: 10%;" class="grade-col"');
                if ($is_editor) {
                    $tool_content .= "<th class='tools-col' style='width:10%;' aria-label='$langSettingSelect'></th>";
                }

                $tool_content .= "</tr></thead><tbody>";
                $i = 1;
                $plagiarismlink = '';
                $seen = [];
                foreach ($result as $row) {
                    // is it a group assignment?
                    if (!empty($row->group_id)) {
                        if (isset($seen[$row->group_id])) {
                            continue;
                        }
                        $subContentGroup = "$m[groupsubmit] " .
                                "<a href='{$urlAppend}modules/group/group_space.php?course=$course_code&amp;group_id=$row->group_id'>" .
                                "$m[ofgroup] " . gid_to_name($row->group_id) . "</a>";
                    } else {
                        if (isset($seen[$row->uid])) {
                            continue;
                        }
                        $subContentGroup = '';
                    }
                $mess = '';
                if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
                    $grade_review_field = "<input class='form-control' type='text' value='' name='grade_review' maxlength='4' size='3' disabled>";
                    $condition ='';
                    $rows = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE assignment_id = ?d ", $id);
                    if ($count_of_assignments > $assign->reviews_per_assignment && $rows) {
                        //status aksiologhshs kathe foithth
                        if ( $cdate > $assign->start_date_review){
                            $assigns = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE assignment_id = ?d AND users_id = ?d", $id, $row->uid);
                            $r_count = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_grading_review WHERE assignment_id = ?d AND users_id = ?d", $id, $row->uid)->count;
                            $counter = 0;
                            foreach ($assigns as $ass){
                                if ( empty($ass->grade) ){
                                    $counter++;
                                }
                            }
                            if ($counter == 0) {
                                $mess = "<span style='color: green;'><div class='text-heading-h6'>$langPeerReviewCompletedByStudent</div>&nbsp;</span>";
                            } elseif ($counter < $r_count){
                                $mess = "<span style='color: darkorange;'><div class='text-heading-h6'>$langPeerReviewPendingByStudent<br>($langQuestionCorrectionTitle2 $counter $langFrom2 $r_count)</div></span>";
                            } else {
                                $mess = "<span style='color: red;'><div class='text-heading-h6'>$langPeerReviewMissingByStudent</div></span>";
                            }
                        }
                        // grade_field pedio
                        if ($cdate > $assign->due_date_review){
                            //select tous vathmous ths kathe upovolhs kai vres ton mo kai topothethse ton sto pedio
                            $grades= Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE user_submit_id = ?d", $row->id);
                            $count_grade = 0;
                            $sum = 0;
                            $grade_review = '';
                            foreach ($grades as $as){
                                if ($as->grade){
                                    $count_grade++;
                                }
                                if ($count_grade == $assign->reviews_per_assignment){
                                    $condition = "<span class='fa fa-fw fa-check text-success' data-bs-toggle='tooltip' data-bs-placement='top' title='$count_grade/$assign->reviews_per_assignment'></span>";
                                }else{
                                    $condition = "<span class='fa fa-fw fa-xmark text-danger' data-bs-toggle='tooltip' data-bs-placement='top' title='$count_grade/$assign->reviews_per_assignment'></span>";
                                }
                                $sum = $sum + $as->grade;
                            }
                            if ($sum != 0){
                                $grade = $sum / $count_grade;

                                if (is_float($grade)) {
                                    $grade_review = number_format($grade,1);
                                } else {
                                    $grade_review = $grade;
                                }
                            }
                        $grade_review_field = "<input class='form-control' id='$row->id' type='text' value='$grade_review' name='grade_review' maxlength='4' size='3' disabled>";
                    }
                }
            }
            $name = empty($row->group_id) ? display_user($row->uid) : display_group($row->group_id);
            $stud_am = uid_to_am($row->uid);
            if ($assign->submission_type == 1) {
                $filelink = "<button class='onlineText btn btn-xs btn-default' data-id='$row->id'>$langQuestionView</button>";
            } else {
                if (empty($row->file_name)) {
                    $filelink = '&nbsp;';
                } else {
                    if ($assign->submission_type == 2) {
                        // Get all files by the same user and group
                        $allFiles = array_filter($result, function ($item) use ($row) {
                            return $item->uid == $row->uid && $item->group_id == $row->group_id;
                        });
                    } else {
                        $allFiles = [$row];
                    }
                    $filelink = implode('<br>', array_map(function ($item) {
                        global $urlAppend, $course_code;
                        $url = "{$urlAppend}modules/work/index.php?course=$course_code&amp;get=$item->id";
                        $namelen = mb_strlen($item->file_name);
                        if ($namelen > 30) {
                            $extlen = mb_strlen(get_file_extension($item->file_name));
                            $basename = mb_substr($item->file_name, 0, $namelen - $extlen - 3);
                            $ext = mb_substr($item->file_name, $namelen - $extlen - 3);
                            $filename = ellipsize($basename, 27, '...' . $ext);
                        } else {
                            $filename = $item->file_name;
                        }
                        return MultimediaHelper::chooseMediaAhrefRaw($url, $url, $filename, $item->file_name);
                    }, $allFiles));
                }
            }
            if (Session::has("grades")) {
                $grades = Session::get('grades');
                $grade = $grades[$row->id]['grade'];
            } else {
                $grade = $row->grade;
            }

            if (isset($_GET['unit'])) {
                $grade_edit_link = "../work/grade_edit.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
            } else {
                $grade_edit_link = "grade_edit.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
            }

            $icon_field = "<a class='link' href='$grade_edit_link' aria-label='$langEdit'><span class='fa fa-fw fa-edit' data-bs-original-title='$langEdit' title='' data-bs-toggle='tooltip'></span></a>";
            if ($row->grading_scale_id && $row->grading_type == ASSIGNMENT_SCALING_GRADE) {
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
                $grade_field = "<select name='grades[$row->id][grade]' class='form-control' id='scales'>$scale_options</select>";
            }
            else if ($row->grading_scale_id && $row->grading_type == ASSIGNMENT_RUBRIC_GRADE) {
                $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $row->grading_scale_id);

                $criteria = unserialize($rubric->scales);
                $criteria_list = '';
                foreach ($criteria as $ci => $criterio) {
                    $criteria_list .= "<li class='list-group-item'>$criterio[title_name] <b>($criterio[crit_weight]%)</b></li>";
                    if(is_array($criterio['crit_scales'])) {
                        foreach ($criterio['crit_scales'] as $si=>$scale) {
                            $criteria_list .= "<ul class='list-unstyled'><li  class='list-group-item'>
                            <input type='radio' name='grade_rubric[$row->uid][$ci]' value='$si'>
                            $scale[scale_item_name] ( $scale[scale_item_value] )
                            </li></ul>";
                        }
                    }
                }
                if (!empty($grade)) {
                    $grade_field = "<input aria-label='$langGradebookGrade' class='form-control' type='text' value='$grade' name='grades[$row->id][grade]' maxlength='4' size='3' disabled>";
                } else {
                    $icon_field = '';
                    if ($is_editor) {
                        $grade_field = "<a class='link' href='{$urlAppend}modules/work/grade_edit.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id' aria-label='$langSGradebookBook'>
                                    <span class='fa fa-fw fa-plus' data-bs-original-title='$langSGradebookBook' title='' data-bs-toggle='tooltip'></span></a>";
                    } else {
                        $grade_field = "";
                    }

                }
            } else {
                // disabled grade field if turnitin or user is course reviewer
                $grade_disabled = ($assign->assignment_type == 1 or !$is_editor) ? ' disabled': '';
                $grade_field = "<input aria-label='$langGradebookGrade' class='form-control' type='text' value='$grade' name='grades[$row->id][grade]' maxlength='4' size='3' $grade_disabled>";
            }
            $late_sub_text = $row->deadline && $row->submission_date > $row->deadline ?  "<div class='Accent-200-cl'><small>$m[late_submission]</small></div>" : '';
            $am_field = '';
            if (!is_null($stud_am)) {
                $am_field = "<div class='text-heading-h6'>$langAmShort: " . q($stud_am) . "</div>";
            }
            $tool_content .= "<tr>
                            <td class='count-col'>$i.</td>
                            <td class='user-col' style='width: 45%';>$name $am_field $mess";

            // student comment
            if (trim($row->comments != '')) {
                $tool_content .= "<div style='margin-top: .5em; white-space: pre-wrap;'><small>" .
                        q($row->comments) . '</small></div>';
            }
            $label = '';
            $comments = '';
            //emfanizei pote vathmologhthhke
            if ($row->grade != '') { // grade submission date
                $label = "<div class='text-heading-h6'>($langGradedAt " .format_locale_date(strtotime($row->grade_submission_date), 'short', false) . ")</div>";
            }
            // professor comments
            if ($row->grade_comments or $row->grade_comments_filename) {
                $grade_comments = trim(q_math($row->grade_comments));
                if (preg_match('/[\n\r] +\S/', $grade_comments)) {
                    $grade_comments = "<div style='white-space: pre-wrap'>$grade_comments</div>";
                } else {
                    $grade_comments = "&nbsp;<span>" . nl2br($grade_comments) . "</span>&nbsp;&nbsp;";
                }
                $fileUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;getcomment=" . $row->id;
                $fileLink = MultimediaHelper::chooseMediaAhrefRaw($fileUrl, $fileUrl, $row->grade_comments_filename, $row->grade_comments_filename);
                $comments = '<strong>'.$m['gradecomments'] . '</strong>:' . $grade_comments . "<div>$fileLink</div>";
            }
            $tool_content .= "<div style='padding-top: .5em;'>$comments $label</div>";
            if($autojudge->isEnabled() and $auto_judge_enabled_assign) {
                $reportlink = "{$urlAppend}modules/work/work_result_rpt.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
                $tool_content .= "<a href='$reportlink'><b>$langAutoJudgeShowWorkResultRpt</b></a>";
            }

            $tool_content .= "</td>";

            // check for plagiarism via unicheck (aka 'unplag') tool (http://www.unicheck.com)
            if (get_config('ext_unicheck_enabled') and valid_plagiarism_file_type($row->id)) {
                $results = Plagiarism::get()->getResults($row->id);
                if ($results) {
                    if ($results->ready) {
                        $plagiarismlink = "<small><a href='$results->resultURL' target=_blank>$langPlagiarismResult</a><br>(<a href='$results->pdfURL' target=_blank>$langDownloadToPDF</a>)</small>";
                    } else {
                        $icon_field = '';
                        if ($is_editor) {
                            $grade_field = "<a class='link' href='{$urlAppend}modules/work/grade_edit.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id' aria-label='$langSGradebookBook'>
                                    <span class='fa fa-fw fa-plus' data-bs-original-title='$langSGradebookBook' title='' data-bs-toggle='tooltip'></span></a>";
                        } else {
                            $grade_field = "";
                        }
                    }
                } else {
                    // disabled grade field if turnitin or user is course reviewer
                    $grade_disabled = ($assign->assignment_type == 1 or !$is_editor) ? ' disabled': '';
                    $grade_field = "<input aria-label='$langGradebookGrade' class='form-control' type='text' value='$grade' name='grades[$row->id][grade]' maxlength='4' size='3' $grade_disabled>";
                }
                $late_sub_text = $row->deadline && $row->submission_date > $row->deadline ?  "<div class='Accent-200-cl'><small>$m[late_submission]</small></div>" : '';
                $am_field = '';
                if (trim($stud_am) != '') {
                    $am_field = "<span>$langAmShort: " . q($stud_am) . "</span>";
                }
                $tool_content .= "<tr>
                                <td class='count-col'>$i.</td>
                                <td class='user-col'>$name $am_field $mess";

                // student comment
                if (trim($row->comments != '')) {
                    $tool_content .= "<div style='margin-top: .5em; white-space: pre-wrap;'><small>" .
                            q($row->comments) . '</small></div>';
                }
                $label = '';
                $comments = '';
                //emfanizei pote vathmologhthhke
                if ($row->grade != '') { // grade submission date
                    $label = "<p>($langGradedAt " .format_locale_date(strtotime($row->grade_submission_date), 'short', false) . ")</p>";
                }
                // professor comments
                if ($row->grade_comments or $row->grade_comments_filename) {
                    $grade_comments = trim(q_math($row->grade_comments));
                    if (preg_match('/[\n\r] +\S/', $grade_comments)) {
                        $grade_comments = "<div style='white-space: pre-wrap'>$grade_comments</div>";
                    } else {
                        $grade_comments = "&nbsp;<span>" . nl2br($grade_comments) . "</span>&nbsp;&nbsp;";
                    }
                    $fileUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;getcomment=" . $row->id;
                    $fileLink = MultimediaHelper::chooseMediaAhrefRaw($fileUrl, $fileUrl, $row->grade_comments_filename, $row->grade_comments_filename);
                    $comments = '<strong>'.$m['gradecomments'] . '</strong>:' . $grade_comments . "<span class='small'>$fileLink</span>";
                }
                $tool_content .= "<div style='padding-top: .5em;'>$comments $label</div>";
                if($autojudge->isEnabled() and $auto_judge_enabled_assign) {
                    $reportlink = "{$urlAppend}modules/work/work_result_rpt.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
                    $tool_content .= "<a href='$reportlink'><b>$langAutoJudgeShowWorkResultRpt</b></a>";
                }
            }
            $tool_content .= "<td class='filename-col' class='col-md-2'>$filelink <br> $plagiarismlink</td>";

            $tool_content .= "<td class='col-md-2'><small>" . format_locale_date(strtotime($row->submission_date)) .$late_sub_text. "</small></td>";

            if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
                $tool_content .="<td class='col-md-1' class='text-center'>
                                    <div class='form-group'>
                                        $grade_review_field
                                        $condition
                                    </div>
                                </td>";
            }
            // grade field
            $tool_content.="<td>
                                <div class='form-group ".(Session::getError("grade.$row->id") ? "has-error" : "")."'>
                                    $grade_field
                                    <span class='help-block Accent-200-cl'>".Session::getError("grade.$row->id")."</span>
                                </div>
                            </td>";
            // edit - delete buttons
            if ($is_editor) {
                $tool_content .= "<td class='text-end'>
                                    $icon_field
                                <a class='linkdelete ps-2' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;as_id=$row->id' aria-label='$langDeleteSubmission'>
                                    <span class='fa fa-fw fa-xmark text-danger' data-bs-original-title='$langDeleteSubmission' title='' data-bs-toggle='tooltip'></span>
                                </a>
                            </td>";
                }
                $tool_content .= "</tr>";
                $i++;

                $seen[$row->group_id] = $seen[$row->uid] = true;
            } //END of Foreach

            // disabled grades submit if turnitin
            $disabled_submit = ($assign->assignment_type == 1) ? ' disabled': '';

            $tool_content .= "</tbody></table></div>";
            if ($is_editor) {
                $tool_content .= "
                <div class='form-group'>
                    <div class='col-12'>
                        <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' value='1' name='send_email' checked><span class='checkmark'></span> $langMailToUsers
                          </label>
                        </div>
                    </div>
                </div>
                <div class='mt-4'>
                    <button class='btn submitAdminBtn' type='submit' name='submit_grades' $disabled_submit>$langGradeOk</button>
                </div>";
            }
        $tool_content .= "</form>";
    } else { // no submissions
        $tool_content .= "<div class='col-12 mt-3 bg-transparent'>
                <p class='sub_title1 text-center TextBold mb-0 pt-2'>$langSubmissions:</p>
                <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoSubmissions</span></div></div>";
    }
}

/**
 * @param type $id
 */
function show_non_submitted($id) {
    global $tool_content, $works_url, $course_id, $m,
            $langGroup, $course_code, $langHasAssignmentPublished, $langSettingSelect;

    $row = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
								CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
								CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due
                                FROM assignment
                                WHERE course_id = ?d AND id = ?d", $course_id, $id);

    $nav[] = $works_url;
    assignment_details($id, $row);
    if ($row->assignment_type == ASSIGNMENT_TYPE_TURNITIN) {
        show_turnitin_integration($id);
    }
    if ($row->group_submissions) {
        $groups = groups_with_no_submissions($id);
        $num_results = count($groups);
        if ($num_results > 0) {
            if ($num_results == 1) {
                $num_of_submissions = $langHasAssignmentPublished;
            } else {
                $num_of_submissions = sprintf("$m[more_submissions]", $num_results);
            }
                $tool_content .= "
                            <p class='mt-4'>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>$m[WorkGroupNoSubmission]:</span>
                                </div>
                            <p>
                            <p class='text-start form-label py-3'>$num_of_submissions</p>
                            <div class='row'><div class='col-sm-12'>
                            <div class='table-responsive'>
                            <table class='table-default sortable'>
                            <thead><tr class='list-header'>
                          <th class='count-col' aria-label='$langSettingSelect'>#</th>";
                sort_link($langGroup, 'username');
                $tool_content .= "</tr></thead>";
                $i=1;
                foreach ($groups as $row => $value){

                    $tool_content .= "<tr>
                            <td class='count-col'>$i.</td>
                            <td><a href='../group/group_space.php?course=$course_code&amp;group_id=$row'>$value</a></td>
                            </tr>";
                    $i++;
                }
                $tool_content .= "</table></div></div></div>";
        } else {
            $tool_content .= "
                      <p class='sub_title1 mt-3'>$m[WorkGroupNoSubmission]:</p>
                      <div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$m[NoneWorkGroupNoSubmission]</span></div></div>";
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
                            <p>
                                <div class='alert alert-warning mt-4'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>$m[WorkUserNoSubmission]:</span>
                                </div>
                            <p>
                            <p class='text-start form-label py-3'>$num_of_submissions</p>
                            <div class='row'><div class='col-sm-12'>
                            <div class='table-responsive mt-0'>
                            <table class='table-default'>
                            <thead><tr class='list-header'>
                          <th class='count-col' aria-label='$langSettingSelect'>#</th>";
                sort_link($m['username'], 'username');
                sort_link($m['am'], 'am');
                $tool_content .= "</tr></thead>";
                $i=1;
                foreach ($users as $row => $value){
                    $tool_content .= "<tr>
                    <td class='count-col'>$i.</td>
                    <td>".display_user($row)."</td>
                    <td>".  uid_to_am($row) ."</td>
                    </tr>";

                    $i++;
                }
                $tool_content .= "</table></div></div></div>";
        } else {
            $tool_content .= "
                      <p class='sub_title1 mt-3'>$m[WorkUserNoSubmission]:</p>
                      <div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$m[NoneWorkUserNoSubmission]</span></div></div>";
        }
    }
}


/**
 * @brief display all assignments - student view only
 */
function show_student_assignments() {
    global $tool_content, $head_content, $m, $uid, $course_id, $urlAppend, $langGroupWorkDeadline_of_Submission,
        $langHasExpiredS, $langDaysLeft, $langNoAssign, $course_code, $langNoDeadline,
        $langTitle, $langAddResePortfolio, $langAddGroupWorkSubePortfolio, $langAssignemtTypeTurnitinInfo,
        $langGradebookGrade, $langPassCode, $langIPUnlock, $langWillStartAt, $langAssignmentTypeTurnitin, $langSettingSelect;

    $gids = user_group_info($uid, $course_id);
    if (!empty($gids)) {
        $gids_sql_ready = implode(',',array_keys($gids));
    } else {
        $gids_sql_ready = "''";
    }

    // ordering assignments by deadline, without deadline, expired.
    // query uses pseudo limit in ordering results
    // (see https://dev.mysql.com/doc/refman/5.7/en/union.html)
    $result = Database::get()->queryArray("
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment WHERE course_id = ?d
                        AND UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) > 0
                        AND active = '1' AND
                        (assign_to_specific = 0 OR id IN
                            (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                UNION
                            SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                        )
                    ORDER BY time
                    DESC
                    LIMIT 1000
            )
            UNION
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment WHERE course_id = ?d
                        AND deadline IS NULL
                        AND active = '1' AND
                        (assign_to_specific = 0 OR id IN
                            (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                UNION
                            SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                        )
                    ORDER BY title
                    DESC
                    LIMIT 1000
            )
            UNION
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment WHERE course_id = ?d
                        AND UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) < 0
                        AND active = '1' AND
                        (assign_to_specific = 0 OR id IN
                            (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                UNION
                            SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                        )
                    ORDER BY time
                    DESC
                    LIMIT 1000
            )
            ", $course_id, $uid, $course_id, $uid, $course_id, $uid);

    if (count($result) > 0) {
        if (get_config('eportfolio_enable')) {
            $add_eportfolio_res_th = "<th style='width:10%;' aria-label='$langSettingSelect'>".icon('fa-gears')."</th>";
        } else {
            $add_eportfolio_res_th = "<th aria-label='$langSettingSelect'></th>";
        }


        $tool_content .= "
            <div class='col-sm-12'>
            <div class='table-responsive'>
                <table id='assignment_table' class='table-default'>
                  <thead>
                      <tr class='list-header'>
                          <th style='width:40%'>$langTitle</th>
                          <th style='width:25%'>$langGroupWorkDeadline_of_Submission</th>
                          <th class='text-center'>$m[submitted]</th>
                          <th class='text-center'>$langGradebookGrade</th>
                          $add_eportfolio_res_th
                      </tr>
                  </thead>
                  <tbody>";
        $sort_id = 0;
        foreach ($result as $row) {
            $sort_id++;
            $exclamation_icon = $turnitin_message = '';
            $class = '';
            $not_started = false;

            if (!isset($_REQUEST['unit'])) {
                if ($row->password_lock or $row->ip_lock) {
                    $lock_description = "<ul>";
                    if ($row->password_lock) {
                        $lock_description .= "<li>$langPassCode</li>";
                        enable_password_bootbox();
                        $class = ' class="password_protected"';
                    }
                    if ($row->ip_lock) {
                        $lock_description .= "<li>$langIPUnlock</li>";
                    }
                    $lock_description .= "</ul>";
                    $exclamation_icon = "&nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='$lock_description'></span>";
                }
            }

            if ($row->assignment_type == ASSIGNMENT_TYPE_TURNITIN) {
                $turnitin_message = "&nbsp;&nbsp;<span class='badge' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='$langAssignemtTypeTurnitinInfo'><small>$langAssignmentTypeTurnitin</small></span>";
            }

            $title_temp = q($row->title);
            if ($row->deadline) {
                $deadline = format_locale_date(strtotime($row->deadline));
            } else {
                $deadline = $langNoDeadline;
            }
            if (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date)) { // assignment not starting yet
                $not_started = true;
            }

            if ($not_started) {
                $deadline = '';
                $class_not_started = 'not_visible';
                $link = "$title_temp";
            } else {
                $class_not_started = '';
                $link = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row->id'$class>$title_temp</a>$turnitin_message$exclamation_icon
                <br><small class='text-muted'>".($row->group_submissions? $m['group_work'] : $m['user_work'])."</small>";
            }

            $tool_content .= "<tr class='$class_not_started'>
                                <td>$link</td>
                                <td data-sort='$sort_id'>" . $deadline ;

            if ($not_started) {
                $tool_content .= "<small><span class='Warning-200-cl'>$langWillStartAt: " . format_locale_date(strtotime($row->submission_date)). "</span></small>";
            } else if ($row->time > 0) {
                $tool_content .= "<br>(<small class='Warning-200-cl'>$langDaysLeft " . format_time_duration($row->time) . "</small>)";
            }   else if($row->deadline) {
                $tool_content .= "<br>(<small><span class='text-danger'>$langHasExpiredS</span></small>)";
            }
            $tool_content .= "</td><td class='text-center'>";

            $eportfolio_action_array = [];
            if ($submission = find_submissions(is_group_assignment($row->id), $uid, $row->id, $gids)) {
                foreach ($submission as $sub) {
                    if (isset($sub->group_id)) { // if is a group assignment
                        $tool_content .= "<small><div>($m[groupsubmit] $m[ofgroup] <em>" . gid_to_name($sub->group_id) . "</em>)</div></small>";

                        $eportfolio_action_title = sprintf($langAddGroupWorkSubePortfolio, gid_to_name($sub->group_id));
                    } else {
                        $eportfolio_action_title = $langAddResePortfolio;
                    }
                    $eportfolio_action_array[] = [
                        'title' => $eportfolio_action_title,
                        'url' => $urlAppend . "main/eportfolio/resources.php?token=" .
                            token_generate('eportfolio' . $uid) .
                            "&amp;action=add&amp;type=work_submission&amp;rid=" . $sub->id,
                            'icon' => 'fa-star'
                    ];
                    $tool_content .= "<i class='fa-solid fa-check'></i><br>";
                }
            } else {
                $tool_content .= "<i class='fa-regular fa-hourglass-half'></i><br>";
            }
            $tool_content .= "</td><td class='text-center'>";
            foreach ($submission as $sub) {
                $grade = submission_grade($sub->id);
                $tool_content .= '<div>' . ($grade? $grade: '-') . '</div>';
            }
            $tool_content .= '</td>';

            if (get_config('eportfolio_enable')) {
                if ($eportfolio_action_array) {
                    $tool_content .= "<td class='text-end' style='width:10%;'>" .
                        action_button($eportfolio_action_array) . "</td>";
                } else {
                    $tool_content .= '<td></td>';
                }
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</tbody></table></div></div>";
    } else {
        $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAssign</span></div></div>";
    }
}

/**
 * @brief display all assignments
 */
function show_assignments() {
    global $tool_content, $head_content, $m, $langEditChange, $langDelete, $langNoAssign,
        $langNewAssign, $course_code, $course_id, $langWorksDelConfirm, $is_editor, $action_bar,
        $langDaysLeft, $langHasExpiredS, $langWarnForSubmissions, $langNoDeadline,
        $langDelSure, $langGradeScales, $langTitle, $langGradeRubrics, $langWillStartAt,
        $langPassCode, $langIPUnlock, $langGroupWorkDeadline_of_Submission, $langAssignemtTypeTurnitinInfo,
        $langActivate, $langDeactivate, $urlAppend, $langAssignmentTypeTurnitin, $langSettingSelect;

    // ordering assignments by deadline, without deadline, expired.
    // query uses pseudo limit in ordering results
    // (see https://dev.mysql.com/doc/refman/5.7/en/union.html)
    $result = Database::get()->queryArray("
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment
                WHERE course_id = ?d
                    AND UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) > 0
                ORDER BY time
                DESC
                LIMIT 10000
            )
            UNION
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment
                WHERE course_id = ?d
                    AND deadline IS NULL
                ORDER BY title
                ASC
                LIMIT 10000
            )
            UNION
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment
                WHERE course_id = ?d
                    AND UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) < 0
                ORDER BY time
                DESC
                LIMIT 10000
            )", $course_id, $course_id, $course_id);

    $action_bar = action_bar(array(
            array('title' => $langNewAssign,
                  'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;add=1",
                  'button-class' => 'btn-success',
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label'),
            array('title' => $langGradeScales,
                  'url' => "grading_scales.php?course=$course_code",
                  'icon' => 'fa-sort-alpha-asc'),
            array('title' => $langGradeRubrics,
                  'url' => "rubrics.php?course=$course_code",
                  'icon' => 'fa-brands fa-readme'),
            ),false);
    $tool_content .= $action_bar;

    if (count($result) > 0) {

        $tool_content .= "
            <div class='col-sm-12'>
                    <div class='table-responsive'>
                    <table id='assignment_table' class='table-default'>
                    <thead>
                    <tr class='list-header'>
                      <th style='width:45%;'>$langTitle</th>
                      <th class='text-center'>$m[subm]</th>
                      <th class='text-center'>$m[nogr]</th>
                      <th style='width:20%;'>$langGroupWorkDeadline_of_Submission</th>
                      <th aria-label='$langSettingSelect'>" . icon('fa-gears') . "</th>
                    </tr>
                    </thead>
                    <tbody>";
        $sort_id = 0;
        foreach ($result as $key => $row) {
            $sort_id++;
            $not_started = false;
            $exclamation_icon = $turnitin_message = '';
            if ($row->password_lock or $row->ip_lock) {
                $lock_description = "<ul>";
                if ($row->password_lock) {
                    $lock_description .= "<li>$langPassCode</li>";
                }
                if ($row->ip_lock) {
                    $lock_description .= "<li>$langIPUnlock</li>";
                }
                $lock_description .= "</ul>";
                $exclamation_icon = "&nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='$lock_description'></span>";
            }
            if ($row->assignment_type == ASSIGNMENT_TYPE_TURNITIN) {
                $turnitin_message = "&nbsp;&nbsp;<span class='badge' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='$langAssignemtTypeTurnitinInfo'><small>$langAssignmentTypeTurnitin</small></span>";
            }

            if ($row->assign_to_specific == 1) {
                $assign_to_users_message = "<a class='assigned_to' data-ass_id='$row->id'><small class='help-block link-color'>$m[WorkAssignTo]: $m[WorkToUser]</small></a>";
            } else if ($row->assign_to_specific == 2) {
                $assign_to_users_message = "<a class='assigned_to' data-ass_id='$row->id'><small class='help-block link-color'>$m[WorkAssignTo]: $m[WorkToGroup]</small></a>";
            } else {
                $assign_to_users_message = '';
            }

            // Check if assignment contains submissions
            $num_submitted = countSubmissions($row->id);

            // For multiple file submissions, continuation records have grade=0 by default
            $num_ungraded = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit WHERE assignment_id = ?d AND grade IS NULL", $row->id)->count;
            if (!$num_ungraded) {
                if ($num_submitted > 0) {
                    $num_ungraded = '0';
                } else {
                    $num_ungraded = '-';
                }
            }

            if (isset($row->deadline)) {
                $deadline = format_locale_date(strtotime($row->deadline));
            } else {
                $deadline = $langNoDeadline;
            }
            if (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date)) { // assignment not starting yet
                $not_started = true;
            }
            if ($not_started) {
                $deadline = '';
            }
            $tool_content .= "<tr class='".((!$row->active or $not_started)? "not_visible":"")."'>";
            $tool_content .= "<td style='width:40%;'><a href='{$urlAppend}modules/work/index.php?course=$course_code&amp;id={$row->id}'>" . q($row->title) . "</a>
                                $exclamation_icon
                                $turnitin_message
                                <br><small class='text-muted'>".($row->group_submissions? $m['group_work'] : $m['user_work'])."</small>
                                $assign_to_users_message
                            <td class='text-center'>$num_submitted</td>
                            <td class='text-center'>$num_ungraded</td>
                            <td data-sort='$sort_id' style='width:20%;'>$deadline";

            if ($not_started) {
                $tool_content .= "<small><span class='Warning-200-cl'>$langWillStartAt: " . format_locale_date(strtotime($row->submission_date)). "</span></small>";
            } else if ($row->time > 0) {
                $tool_content .= " <br><span><small class='label label-warning'>$langDaysLeft " . format_time_duration($row->time) . "</small></span>";
            } else if (intval($row->deadline)) {
                $tool_content .= " <br><span><small class='label label-danger'>$langHasExpiredS</small></span>";
            }
           $tool_content .= "</td>
              <td style='width:10%;' class='text-end'>";
              if ($is_editor) {
                  $tool_content .= action_button(array(
                      array('title' => $langEditChange,
                          'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=edit",
                          'icon' => 'fa-edit'),
                      array('title' => $m['WorkUserGroupNoSubmission'],
                          'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;disp_non_submitted=true",
                          'icon' => 'fa-minus-square'),
                      array('title' => $row->active == 1 ? $langDeactivate : $langActivate,
                          'url' => $row->active == 1 ? "{$urlAppend}modules/work/index.php?course=$course_code&amp;choice=disable&amp;id=$row->id" : "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=enable&amp;id=$row->id",
                          'icon' => $row->active == 1 ? 'fa-eye-slash' : 'fa-eye'),
                      array('title' => $m['WorkSubsDelete'],
                          'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=do_purge",
                          'icon' => 'fa-eraser',
                          'confirm' => "$langWarnForSubmissions $langDelSure",
                          'show' => $num_submitted > 0),
                      array('title' => $langDelete,
                          'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=do_delete",
                          'icon' => 'fa-xmark',
                          'class' => 'delete',
                          'confirm' => $langWorksDelConfirm)));
              }
              $tool_content .= "</td></tr>";
        }
        $tool_content .= '</tbody></table></div></div>';
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAssign</span></div></div>";
    }
}

/**
 * @brief submit grade and comment for student submission
 * @param type $args
 */
function submit_grade_comments($args) {

    global $langGrades, $course_id, $langTheField, $course_code,
            $langFormErrors, $workPath, $langGradebookGrade;

    if (isset($args['grade'])) {
        $args['grade'] = trim($args['grade']);
        $args['grade'] = $args['grade'] === '' ? null : fix_float($args['grade']);
    }

    $id = $args['assignment']; // assignment=id_ergasias hidden pedio sto grade_edit arxeio
    $sid = $args['submission'];
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
    $grading_type = $assignment->grading_type;

    $v = new Valitron\Validator($args);
    $v->addRule('emptyOrNumeric', function($field, $value, array $params) {
        if(is_numeric($value) || empty($value)) return true;
    });
    $v->rule('numeric', array('assignment', 'submission'));
    $v->rule('emptyOrNumeric', array('grade'));
    $v->rule('min', array('grade'), 0);
    $v->rule('max', array('grade'), $assignment->max_grade);
    $v->labels(array(
        'grade' => "$langTheField $langGradebookGrade"
    ));

    if($v->validate()) {
        $grade_rubric = '';
        if ($grading_type == ASSIGNMENT_SCALING_GRADE) {
            $grade = $args['grade'];
        } else if ($grading_type == ASSIGNMENT_RUBRIC_GRADE) {
            $rubric = Database::get()->querySingle("SELECT * FROM rubric AS a  JOIN assignment AS b
                                                            WHERE b.course_id = ?d
                                                                AND a.id = b.grading_scale_id
                                                                AND b.id = ?d", $course_id, $id);
            $grade_rubric = serialize($args['grade_rubric']);
            $criteria = unserialize($rubric->scales);
            $r_grade = 0;
            foreach ($criteria as $ci => $criterio) {
                if (is_array($criterio['crit_scales'])) {
                    $r_grade += $criterio['crit_scales'][$args['grade_rubric'][$ci]]['scale_item_value'] * $criterio['crit_weight'];
                }
            }
            $grade = $r_grade/100;
        } else if ($grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
           // edw tha kahoristei o telikos bathmos pou tha valei o kathghths
            $sum = 0;
            $count = 0;
            $users= Database::get()->queryArray("SELECT grade FROM assignment_grading_review WHERE user_submit_id = ?d", $sid);
            foreach ($users as $row){
                if ($row->grade){
                    $count = $count + 1;
                }
                $sum = $sum + $row->grade;
            }
            $grad = $sum / $count;
            $grade = number_format($grad,1);
        } else {
            $grade = $args['grade'];
        }
        $comment = $args['comments'];
        if (isset($_FILES['comments_file']) and is_uploaded_file($_FILES['comments_file']['tmp_name'])) { // upload comments file
            $comments_filename = $_FILES['comments_file']['name'];
            validateUploadedFile($comments_filename); // check file type
            $comments_filename = add_ext_on_mime($comments_filename);
            // File name used in file system and path field
            $safe_comments_filename = safe_filename(get_file_extension($comments_filename));
            if (move_uploaded_file($_FILES['comments_file']['tmp_name'], "$workPath/admin_files/$safe_comments_filename")) {
                @chmod("$workPath/admin_files/$safe_comments_filename", 0644);
                $comments_real_filename = $_FILES['comments_file']['name'];
                $comments_filepath = $safe_comments_filename;
            }
        } else {
            $comments_filepath = $comments_real_filename = '';
        }

        $grade = is_numeric($grade) ? $grade : null;
        if(isset($args['auto_judge_scenarios_output'])){
            Database::get()->query("UPDATE assignment_submit SET auto_judge_scenarios_output = ?s
                                    WHERE id = ?d",serialize($args['auto_judge_scenarios_output']), $sid);
        }
        if (Database::get()->query("UPDATE assignment_submit
                                    SET grade = ?f, grade_rubric = ?s, grade_comments = ?s,
                                    grade_comments_filepath = ?s,
                                    grade_comments_filename = ?s,
                                    grade_submission_date = NOW(), grade_submission_ip = ?s
                                    WHERE id = ?d", $grade, $grade_rubric, $comment, $comments_filepath,
                                            $comments_real_filename, Log::get_client_ip(), $sid)->affectedRows>0) {
            $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
            triggerGame($course_id, $quserid, $id);
            triggerAssignmentAnalytics($course_id, $quserid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
            triggerAssignmentAnalytics($course_id, $quserid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
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
        Session::flash('message', $langGrades);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/grade_edit.php?course=$course_code&assignment=$id&submission=$sid");
    }
}


/**
 * @brief submit grade and comment for student submission
 * @param type $args
 */
function submit_grade_reviews($args) {
    global $langGrades, $course_id, $course_code, $unit, $langFormErrors;

    $id = $args['assignment'];//assignment=id_ergasias exei topotheththei ws pedio hidden sto grade_edit_review
    $rubric = Database::get()->querySingle("SELECT * FROM rubric as a JOIN assignment as b WHERE b.course_id = ?d AND a.id = b.grading_scale_id AND b.id = ?d", $course_id, $id);

    $sid = $args['submission'];//asubimision=id_submision exei topotheththei ws pedio hidden sto grade_edit_review
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);

    $v = new Valitron\Validator($args);
    $v->addRule('emptyOrNumeric', function($field, $value, array $params) {
        if(is_numeric($value) || empty($value)) return true;
    });
    $v->rule('numeric', array('assignment', 'submission'));

    if($v->validate()) {
        $grade_rubric = serialize($args['grade_rubric']);
        $criteria = unserialize($rubric->scales);
        $r_grade = 0;
        foreach ($criteria as $ci => $criterio) {
                if(is_array($criterio['crit_scales']))
                    $r_grade += $criterio['crit_scales'][$args['grade_rubric'][$ci]]['scale_item_value'] * $criterio['crit_weight'];
        }
        $grade = $r_grade/100;
        $grade = is_numeric($grade) ? $grade : null;
        $comment = $args['comments'];
		Database::get()->query("UPDATE assignment_grading_review
                                    SET grade = ?f, comments =?s, date_submit = NOW(), rubric_scales = ?s WHERE id = ?d
                                  ", $grade, $comment, $grade_rubric, $sid);

        Session::flash('message', $langGrades);
        Session::flash('alert-class', 'alert-success');

        if ($unit) {
            redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit");
        } else {
            redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
        }

    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/grade_edit.php?course=$course_code&assignment=$id&submission=$sid");
    }
}

/**
 * @brief submit reviews per assignment
 */
function submit_review_per_ass($id) {
	global $course_code, $langNoPeerReviewMultipleFiles;

	$assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d ",$id);
	$assign = Database::get()->queryArray("SELECT * FROM assignment_submit WHERE assignment_id = ?d ",$id);

	$del_submission_msg = delete_submissions($id);
    $success_msgs[] = $del_submission_msg;
	$value = 1;
	$value1 = 0;
	foreach ($assign as $row1) {
		$ass = Database::get()->queryArray("SELECT * FROM assignment_submit WHERE assignment_id = ?d LIMIT $assignment->reviews_per_assignment OFFSET $value", $id);

		$rowcount = count($ass);

		$count = $assignment->reviews_per_assignment - $rowcount;//oi ergasies pou leipoun
		foreach($ass as $row2) {
			if ($assignment->submission_type == 1) { // online text
				Database::get()->query("INSERT INTO assignment_grading_review ( assignment_id, user_submit_id, user_id, submission_text, submission_date, gid, users_id)
				VALUES (?d, ?d, ?d, ?s, ?t, ?d, ?d)", $id, $row1->id, $row1->uid, $row1->submission_text, $row1->submission_date, $row1->group_id, $row2->uid)->lastInsertID;
			} else if ($assignment->submission_type == 0) { // single file submission
				Database::get()->query("INSERT INTO assignment_grading_review ( assignment_id, user_submit_id, user_id, file_path, file_name, submission_date, gid, users_id)
				VALUES (?d, ?d, ?d, ?s, ?s, ?t, ?d, ?d)", $id, $row1->id, $row1->uid, $row1->file_path, $row1->file_name, $row1->submission_date, $row1->group_id, $row2->uid)->lastInsertID;
			} else if ($assignment->submission_type == 2) { // multiple file submission
                Session::flash('message', $langNoPeerReviewMultipleFiles);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
            }
		}
		if ($count != 0)
		{
			$assign1 = Database::get()->queryArray("SELECT * FROM assignment_submit WHERE assignment_id = ?d LIMIT $count OFFSET $value1", $id);
			foreach ($assign1 as $row3)
			{
				if ($assignment->submission_type == 1) { // online text
					Database::get()->query("INSERT INTO assignment_grading_review ( assignment_id, user_submit_id, user_id, submission_text, submission_date, gid, users_id)
					VALUES (?d, ?d, ?d, ?s, ?t, ?d, ?d)", $id, $row1->id, $row1->uid, $row1->submission_text, $row1->submission_date, $row1->group_id, $row3->uid)->lastInsertID;
				} else if ($assignment->submission_type == 0) { // single file submission
					Database::get()->query("INSERT INTO assignment_grading_review ( assignment_id, user_submit_id, user_id, file_path, file_name, submission_date, gid, users_id)
					VALUES (?d, ?d, ?d, ?s, ?s, ?t, ?d, ?d)", $id, $row1->id, $row1->uid, $row1->file_path, $row1->file_name, $row1->submission_date, $row1->group_id, $row3->uid)->lastInsertID;
				} else if ($assignment->submission_type == 2) { // multiple file submission
                    Session::flash('message', $langNoPeerReviewMultipleFiles);
                    Session::flash('alert-class', 'alert-warning');
                    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                }
			}
		}
		$value++;
	}
    Session::flash('message', $success_msgs);
    Session::flash('alert-class', 'alert-success');
	redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
}


/**
 * @brief submit grades to students
 * @param type $grades_id
 * @param type $grades
 * @param type $email
 */
function submit_grades($grades_id, $grades, $email = false) {
    global $langGrades, $course_id, $course_code, $langFormErrors,
            $langTheField, $langGradebookGrade;

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $grades_id);
    $errors = [];

    foreach ($grades['grades'] as $key => $grade) {
        $v = new Valitron\Validator($grade);
        $v->addRule('emptyOrNumeric', function($field, $value, array $params) {
            if(is_numeric($value) || empty($value)) return true;
        });
        $v->rule('emptyOrNumeric', array('grade'));
        $v->rule('min', array('grade'), 0);
        $v->rule('max', array('grade'), $assignment->max_grade);
        $v->labels(array(
            'grade' => "$langTheField $langGradebookGrade"
        ));
        if(!$v->validate()) {
            $valitron_errors = $v->errors();
            $errors["grade.$key"] = $valitron_errors['grade'];
        }
    }

    if(empty($errors)) {
        if(is_array($grades['grades'])) {
            foreach ($grades['grades'] as $sid => $grade) {
                $sid = intval($sid);
                $val = Database::get()->querySingle("SELECT grade from assignment_submit WHERE id = ?d", $sid)->grade;

                $grade = is_numeric($grade['grade']) ? $grade['grade'] : null;

                if ($val !== $grade) {
                    Database::get()->query("UPDATE assignment_submit
                                                SET grade = ?f, grade_submission_date = NOW(), grade_submission_ip = ?s
                                                WHERE id = ?d", $grade, Log::get_client_ip(), $sid);
                        $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                    triggerGame($course_id, $quserid, $assignment->id);
                    triggerAssignmentAnalytics($course_id, $quserid, $assignment->id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
                    triggerAssignmentAnalytics($course_id, $quserid, $assignment->id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
                    Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $sid,
                                'title' => $assignment->title,
                                'grade' => $grade));

                        //update gradebook if needed
                        if ($assignment->group_submissions) {
                            $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE id = ?d", $sid)->group_id;
                            $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                            foreach ($user_ids as $user_id) {
                                update_gradebook_book($user_id->user_id, $assignment->id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                            }
                        } else {
                            $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                            update_gradebook_book($quserid, $assignment->id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                        }
                        if ($email) {
                            grade_email_notify($grades_id, $sid, $grade, '');
                        }
                        Session::flash('message',$langGrades);
                        Session::flash('alert-class', 'alert-success');
                }
            }
        }

        Session::flash('message',$langGrades);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($errors);
    }
    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$grades_id");
}



/**
 * @brief download function
 * @param type $id
 * @param type $file_type
 * @return boolean
 */
function send_file($id, $file_type) {
    global $uid, $is_editor, $is_course_reviewer;

    $files_to_download = [];
    if (!$is_editor and is_module_disable(MODULE_ID_ASSIGN)) {
        return false;
    }

    if (isset($_GET['download']) and $_GET['download']) {
        $disposition = null;
    } else {
        $disposition = 'inline';
    }

    if (isset($file_type)) {
        if ($file_type == 1) {
            $info = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
            if (!$info) { // invalid (not found) assignment
                return false;
            }
            if (!$is_editor) { // don't show file to users if not active and before submission date
                if ((!$info->active) or (date("Y-m-d H:i:s") < $info->submission_date)) {
                    return false;
                }
                // make sure that user entered password and has been accepted
                if ($info->password_lock and (!isset($_SESSION['has_unlocked'][$id]) or !$_SESSION['has_unlocked'][$id])) {
                    return false;
                }
            }
            send_file_to_client("$GLOBALS[workPath]/admin_files/$info->file_path", $info->file_name, $disposition, true);
        } elseif ($file_type == 2) { // download comments file
            $info = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $id);
            if (!$info) {
                return false;
            }
            send_file_to_client("$GLOBALS[workPath]/admin_files/$info->grade_comments_filepath", $info->grade_comments_filename, $disposition, true);
        }
    } else {

        $info = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $id);
        if (!$info) {
            return false;
        }

        $a = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $info->assignment_id);

        if ($a->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
            $result = Database:: get()->queryArray("SELECT * FROM assignment_grading_review
                                        WHERE assignment_id = ?d
                                        AND users_id = ?d", $a->id, $uid);

            foreach ($result as $data) {
                $files_to_download[] = $data->file_path;
            }
            if (in_array($info->file_path, $files_to_download)) {
                send_file_to_client("$GLOBALS[workPath]/$info->file_path", $info->file_name, $disposition, true);
            }
        }

        if ($info->group_id) {
            initialize_group_info($info->group_id);
        }
        if (!($is_course_reviewer or $info->uid == $uid or $GLOBALS['is_member'])) {
            return false;
        }
        send_file_to_client("$GLOBALS[workPath]/$info->file_path", $info->file_name, $disposition, true);

    }
    exit;
}

/**
 * @brief Zip submissions to assignment $id and send it to user
 * @param type $id
 * @return boolean
 */
function download_assignments($id) {
    global $workPath, $course_code, $webDir;

    $sub_type = Database::get()->querySingle("SELECT submission_type FROM assignment WHERE id = ?d", $id)->submission_type;
    $counter = Database::get()->querySingle("SELECT COUNT(*) AS `count` FROM assignment_submit WHERE assignment_id = ?d", $id)->count;
    if ($counter) {
        ignore_user_abort(true); // needed to ensure zip file is deleted
        $secret = work_secret($id);
        $filename = "{$course_code}_work_$id.zip";
        $filepath = "$webDir/courses/temp/$filename";
        $temp_online_text_path = "$webDir/courses/temp/{$course_code}_work_$id";
        $zip = new ZipArchive();
        $zip->open($filepath, ZipArchive::CREATE);
        chdir($workPath);
        if ($sub_type == 1) { // free text assignment
            create_zip_index("$secret/index.html", $id);
            if (!is_dir($temp_online_text_path)) {
                mkdir($temp_online_text_path);
            }
            chdir($temp_online_text_path);
            $sql = Database::get()->queryArray("SELECT uid, submission_text FROM assignment_submit WHERE assignment_id = ?d", $id);
            foreach ($sql as $data) {
                $onlinetext = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => _MPDF_TEMP_PATH,
                ]);
                $onlinetext->WriteHTML($data->submission_text);
                $pdfname = strtr(greek_to_latin(uid_to_name($data->uid)), '\\/:', '___') . ".pdf";
                $onlinetext->Output($pdfname, 'F');
                unset($onlinetext);
            }
            foreach (glob('*.pdf') as $pdfname) {
                $zip->addFile($pdfname);
            }
            $zip->addFile("$workPath/$secret/index.html", "index.html");
        } else { // 'normal' assignment
            foreach (glob("$secret/*") as $file) {
                if (is_dir($file)) {
                    foreach (glob("$file/*") as $subfile) {
                        $zip->addFile($subfile, "work_$id/".substr($subfile, strlen($secret)+1));
                    }
                } elseif (file_exists($file) and is_readable($file)) {
                    $zip->addFile($file, "work_$id/".substr($file, strlen($secret)+1));
                }
            }
        }
        if ($zip->close()) {
            header("Content-Type: application/zip");
            set_content_disposition('attachment', $filename);
            header("Content-Length: " . filesize($filepath));
            stop_output_buffering();
            readfile($filepath);
        }
        if (file_exists($temp_online_text_path)) {
            removeDir($temp_online_text_path);
        }
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        exit;
    } else {
        return false;
    }
}


/**
 * @brief Create an index.html file for assignment $id listing user submissions
         Set $online to TRUE to get an online view (on the web) - else the index.html works for the zip file
 * @param $path
 * @param $id
 * @param bool $online
 *
 */
function create_zip_index($path, $id) {
    global $charset, $m, $course_id, $langGradebookGrade,
           $langAssignment, $langAm, $langSurnameName;

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
                td { border: none; padding: .1em .5em; }
                table { border-collapse: collapse; border: 2px solid; }
                .sep { border-top: 2px solid black; }
                </style>
    </head>
    <body>
        <table class="table-default">
            <tr>
                <th>' . $langSurnameName . '</th>
                <th>' . $langAm .  '</th>
                <th>' . $langAssignment . '</th>
                <th>' . $m['sub_date'] . '</th>
                <th>' . $langGradebookGrade . '</th>
            </tr>');

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
    $assign_type = $assignment->submission_type;
    if ($assignment->grading_type == ASSIGNMENT_SCALING_GRADE) {
        $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assignment->grading_scale_id, $course_id)->scales;
        $scales = unserialize($serialized_scale_data);
        $scale_values = array_value_recursive('scale_item_value', $scales);
    }

    $submissions = Database::get()->queryArray("SELECT a.id, a.uid, a.file_path, a.file_name,
                a.submission_text, a.submission_date, a.grade, a.comments,
                a.grade_comments, a.group_id, b.deadline
            FROM assignment_submit a, assignment b
            WHERE a.assignment_id = ?d AND a.assignment_id = b.id
            ORDER BY a.id", $id);
    $seen = [];
    foreach ($submissions as $row) {
        if (in_array($row->id, $seen)) {
            continue;
        }
        if ($assign_type == 1) {
            $filename = greek_to_latin(uid_to_name($row->uid)) . ".pdf";
        } else {
            $filename = preg_replace('|^[^/]+/|', '', $row->file_path);
        }
        $filelink = empty($filename) ? '&nbsp;' :
                ("<a href='$filename'>" . q($row->file_name) . '</a>');

        // If further files exist for this submission
        if ($assign_type == 2 and strpos($filename, '/') !== false) {
            $otherFiles = Database::get()->queryArray('SELECT id, file_name, file_path
                FROM assignment_submit
                WHERE assignment_id = ?d AND uid = ?d AND group_id = ?d AND id <> ?d
                ORDER BY id', $id, $row->uid, $row->group_id, $row->id);
            foreach ($otherFiles as $file) {
                $seen[] = $file->id;
                $filename = preg_replace('|^[^/]+/|', '', $file->file_path);
                $filelink .= "<br><a href='$filename'>" . q($file->file_name) . '</a>';
            }
        }

        $late_sub_text = ((int) $row->deadline && $row->submission_date > $row->deadline) ?  "<div class='Accent-200-cl'>$m[late_submission]</div>" : '';
        if ($assignment->grading_type == ASSIGNMENT_SCALING_GRADE) {
            if ($assignment->grading_scale_id and !is_null($row->grade)) {
                $key = closest($row->grade, $scale_values)['key'];
                $row->grade = $scales[$key]['scale_item_name'];
            }
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
    create_zip_index("$secret/index.html", $id);
    header("Content-Type: text/html; charset=$charset");
    readfile("$workPath/$secret/index.html");
    exit;
}

// Notify students by email about grade/comment submission
// Send to single user for individual submissions or group members for group
// submissions
function grade_email_notify($assignment_id, $submission_id, $grade, $comments) {

    global $m, $currentCourseName, $urlServer, $course_code, $langGradebookGrade;
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
        $body .= ": $langGradebookGrade$grade\n";
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
        <div><b>$langGradebookGrade: </b> <span class='left-space'>$grade</span></div><br>
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

/**
 * @brief send email to user groups
 * @param $gid
 * @param $subject
 * @param $plainBody
 * @param $body
 */
function send_mail_to_group_id($gid, $subject, $plainBody, $body) {

    $res = Database::get()->queryArray("SELECT surname, givenname, email
                                 FROM user, group_members AS members
                                 WHERE members.group_id = ?d
                                 AND user.id = members.user_id", $gid);
    foreach ($res as $info) {
        send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], "$info->givenname $info->surname", $info->email, $subject, $plainBody, $body);
    }
}

/**
 * @brief send mail to users
 * @param $uid
 * @param $subject
 * @param $plainBody
 * @param $body
 */
function send_mail_to_user_id($uid, $subject, $plainBody, $body) {

    $user = Database::get()->querySingle("SELECT surname, givenname, email FROM user WHERE id = ?d", $uid);
    send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'],"$user->givenname $user->surname", $user->email, $subject, $plainBody, $body);
}

// Return a list of users with no submissions for assignment $id
function users_with_no_submissions($id) {
    global $course_id;
    if (Database::get()->querySingle("SELECT assign_to_specific FROM assignment WHERE id = ?d", $id)->assign_to_specific) {
        $q = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                FROM user, course_user
                                WHERE user.id = course_user.user_id
                                AND course_user.course_id = ?d
                                AND course_user.status = " . USER_STUDENT . "
                                AND user.id NOT IN (SELECT uid FROM assignment_submit WHERE assignment_id = ?d)
                                AND user.id IN (
                                    SELECT user_id FROM assignment_to_specific WHERE assignment_id = ?d
                                    UNION
                                    SELECT group_members.user_id FROM assignment_to_specific, group_members
                                        WHERE assignment_to_specific.group_id = group_members.group_id AND assignment_id = ?d)
                                ORDER BY surname, givenname", $course_id, $id, $id, $id);
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

function max_grade_from_rubric($rubric_id) {
    global $course_id;
    $rubric_data = Database::get()->querySingle("SELECT * FROM rubric WHERE id = ?d AND course_id = ?d", $rubric_id, $course_id);
    $unserialized_scale_items = unserialize($rubric_data->scales);
    $max_grade = 0;
    $max_scale_item_value = 0;
    foreach ($unserialized_scale_items as $CritArrItems) {
        $max_scale_item_value = 0;
        if(is_array($CritArrItems['crit_scales'] ))
        foreach($CritArrItems['crit_scales'] as $scalesArr){
            $max_scale_item_value = $max_scale_item_value<$scalesArr['scale_item_value']?$scalesArr['scale_item_value']:$max_scale_item_value;
        }
        $max_grade = $max_grade + $CritArrItems['crit_weight'] * $max_scale_item_value;
    }
    return $max_grade/100;
}

// Returns an array of numbers like [ 2 => 2, 3 => 3, ... ] to use as file count options
function fileCountOptions() {
    return array_slice(range(0, get_config('max_work_file_count', 10)), 2, null, true);
}
