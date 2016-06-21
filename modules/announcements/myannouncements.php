<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

define('INDEX_START', 1);
require_once '../../include/baseTheme.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'main/perso.php';

$pageName = $langMyPersoAnnouncements;

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);

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
                            <a href='" . $_SERVER['SCRIPT_NAME'] . "?course=" . $myrow->code . "&an_id=" . $myrow->id . "'>" . standard_text_escape($myrow->title) . "</a>
                        </div>
                        <div class='table_td_body' data-id='$myrow->id'>" . standard_text_escape($myrow->content) . "</div>
                        </div>",
                '1' => claro_format_locale_date($dateFormatLong, strtotime($myrow->an_date))
            );
        } elseif ($myrow->code == '') {
            $data['aaData'][] = array(
                '0' => "<div class='table_td'>
                        <div class='table_td_header clearfix'>
                            <a href='" . $_SERVER['SCRIPT_NAME'] . "?an_id=" . $myrow->id . "'>" . standard_text_escape($myrow->title) . "</a>
                        </div>
                        <div class='table_td_body' data-id='$myrow->id'>" . standard_text_escape($myrow->content) . "</div>
                        </div>",
                '1' => claro_format_locale_date($dateFormatLong, strtotime($myrow->an_date))
            );
        }
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

load_js('datatables');
load_js('trunk8');

if (!getUserAnnouncements($lesson_ids)) {
    $tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlAppend,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
    $tool_content .= "<div class='alert alert-warning'>$langNoAnnounce</div>";
} else {
    $tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlAppend,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);

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
                            fill: '&hellip;<div class=\"clearfix\"></div><a style=\"float:right;\" href=\"$_SERVER[SCRIPT_NAME] ? an_id = '+ $(this).data('id')+'\">$langMore</div>'
                        })
                    });
                    $('#ann_table_admin_logout_filter label input').attr({
                          class : 'form-control input-sm',
                          placeholder : '$langSearch...'
                        });
                 },
                 'sPaginationType': 'full_numbers',
                'bSort': false,
                'oLanguage': {
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '\".$langNoResult.\"',
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

    $tool_content .= "
        <div class='table-responsive'>
            <table id='ann_table_my_ann' class='table-default'>
                <thead>
                    <tr class='list-header'>
                        <th>$langAnnouncement</th>
                        <th>$langDate</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    ";
}

draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
