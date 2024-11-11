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

define('INDEX_START', 1);

require_once '../../include/baseTheme.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'main/portfolio_functions.php';

$require_help = true;
$helpTopic = 'portfolio';
$helpSubTopic = 'my_announcements';

$toolName = $langMyPersoAnnouncements;

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    if (isset($_SESSION['courses'])) {
        foreach ($_SESSION['courses'] as $user_course_code => $user_course_status) {
            $lesson_ids[] = course_code_to_id($user_course_code);
        }
    } else {
        $lesson_ids = array();
    }
    $announcements = getUserAnnouncements($lesson_ids, 'more', 'to_ajax', $_GET['sSearch']);
    $data['iTotalDisplayRecords'] =  count($announcements);
    if (!isset($_GET['sSearch']) or $_GET['sSearch'] === '') {
        $data['iTotalRecords'] = count(getUserAnnouncements($lesson_ids, 'more', 'to_ajax'));
    } else {
        $data['iTotalRecords'] = $data['iTotalDisplayRecords'];
    }

    if ($limit > 0) {
        $announcements = array_slice($announcements, $offset, $limit);
    }

    $data['aaData'] = array();
    foreach ($announcements as $myrow) {
        if ($myrow->code != '') {
            $data['aaData'][] = array(
                '0' => "<div class='table_td'>
                        <div class='table_td_header clearfix'>
                            <a href='" . $urlAppend . "modules/announcements/index.php?course=" . $myrow->code . "&an_id=" . $myrow->id . "'>" . standard_text_escape($myrow->title) . "</a>
                        </div>
                        <small class='text-grey'>" . q(ellipsize($myrow->course_title, 80)) . "</small>
                        <div class='table_td_body' data-id='$myrow->id' data-course-code='$myrow->code'>" . standard_text_escape($myrow->content) . "</div>
                        </div>",
                '1' => format_locale_date(strtotime($myrow->an_date))
            );
        } else {
            $data['aaData'][] = array(
                '0' => "<div class='table_td'>
                        <div class='table_td_header clearfix'>
                            <a href='" . $urlAppend . "main/system_announcements.php?an_id=" . $myrow->id . "'>" . standard_text_escape($myrow->title) . "</a>
                        </div>
                        <small class='text-grey'>$langAdminAn&nbsp; <span class='fa-solid fa-user'></span></small>
                        <div class='table_td_body' data-id='$myrow->id'>" . standard_text_escape($myrow->content) . "</div>
                        </div>",
                '1' => format_locale_date(strtotime($myrow->an_date))
            );
        }
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

load_js('datatables');
load_js('trunk8');

$head_content .= "
<script type='text/javascript'>
    $(document).ready(function() {

       var oTable = $('#ann_table_my_ann').DataTable ({
            'bStateSave': true,
            'bProcessing': true,
            'bServerSide': true,
            'sScrollX': true,
            'responsive': true,
            'searchDelay': 1000,
            'sAjaxSource': '$_SERVER[REQUEST_URI]',
            'aLengthMenu': [
               [10, 15, 20 , -1],
               [10, 15, 20, '$langAllOfThem'] // change per page values here
           ],
            'fnDrawCallback': function( oSettings ) {
                $('.table_td_body').each(function() {
                    $(this).trunk8({
                        lines: '3',
                        fill: '&hellip;<div class=\"clearfix\"></div><a class=\"float-end\" href=\"$_SERVER[SCRIPT_NAME]?more=yes&course='+$(this).data('course-code')+'&an_id='+$(this).data('id')+'\">$langMore...</div>'
                    })
                });
                $('#ann_table_admin_logout_filter label input').attr({
                      'class' : 'form-control input-sm ms-0 mb-3',
                      'placeholder' : '$langSearch...'
                });
                $('#ann_table_admin_logout_filter label').attr('aria-label', '$langSearch');
                $('#ann_table_my_ann_filter label').attr('aria-label', '$langSearch');
             },
             'sPaginationType': 'full_numbers',
            'bSort': false,
            'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '".js_escape($langNoResult)."',
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
    });
    </script>
";

if (isset($_GET['more']) and $_GET['more'] == 'yes') {
    $data['action_bar'] = action_bar([
        ['title' => $langBack,
            'url' => $_SERVER['SCRIPT_NAME'],
            'icon' => 'fa-reply',
            'level' => 'primary',
            'button-class' => 'btn-secondary']
    ],false);

   $lesson_ids[] = course_code_to_id($_GET['course']);
   $all_announcements = getUserAnnouncements($lesson_ids, 'more', 'to_ajax');
   foreach($all_announcements as $myann){
        if($myann->id == $_GET['an_id']){
            $data['announcement'] = $myann;
        }
   }
   view('modules.announcements.more_announce',$data);
}else{
   view('modules.announcements.myann_index');
}
