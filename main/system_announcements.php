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

/**
 * @file about.php
 * @brief Displays general platform information.
 * @author original developed by Ophelia Neofytou.
 */

require_once '../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
$pageName = $langAdminAn;

load_js('datatables');
load_js('trunk8');

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    $keyword = '%' . $_GET['sSearch'] . '%';


    $all_announc = Database::get()->querySingle("SELECT COUNT(*) AS total FROM admin_announcement");
    $filtered_announc = Database::get()->querySingle("SELECT COUNT(*) AS total FROM admin_announcement WHERE title LIKE ?s", $keyword);
    if ($limit>0) {
        $extra_sql = 'LIMIT ?d, ?d';
        $extra_terms = array($offset, $limit);
    } else {
        $extra_sql = '';
        $extra_terms = array();
    }

    $student_sql = 'AND visible = 1 AND (`begin` <= NOW() OR `begin` IS NULL) AND (`end` >= NOW() OR `end` IS NULL)';
    $result = Database::get()->queryArray("SELECT * FROM admin_announcement WHERE title LIKE ?s $student_sql ORDER BY `order` DESC , `date` DESC  $extra_sql", $keyword, $extra_terms);

    $data['iTotalRecords'] = $all_announc->total;
    $data['iTotalDisplayRecords'] = $filtered_announc->total;
    $data['aaData'] = array();
    foreach ($result as $myrow) {

        if ($myrow->order != 0) {
            $pinned = "<span class='fa fa-thumb-tack pull-right text-danger'></span>";
        } else {
            $pinned = "";
        }

        $data['aaData'][] = array(
            '0' => "<div class='table_td'>
                        <div class='table_td_header clearfix'>
                            <a href='".$_SERVER['SCRIPT_NAME']."?an_id=".$myrow->id."'>".standard_text_escape($myrow->title)."</a>
                            $pinned
                        </div>
                        <div class='table_td_body' data-id='$myrow->id'>".standard_text_escape($myrow->body)."</div>
                        </div>",
            '1' => claro_format_locale_date($dateFormatLong, strtotime($myrow->date))
        );
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}



$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {

           var oTable = $('#ann_table_admin_logout').DataTable ({
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
                            fill: '&hellip;<div class=\"clearfix\"></div><a style=\"float:right;\" href=\"$_SERVER[SCRIPT_NAME]?an_id='+ $(this).data('id')+'\">$langMore</div>'
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
        });
        </script>";

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => $urlServer,
        'icon' => 'fa-reply',
        'level' => 'primary-label',
        'button-class' => 'btn-default')
),false);

if (isset($_GET['an_id'])) {

    $row = Database::get()->querySingle("SELECT * FROM admin_announcement WHERE id = ". intval($_GET['an_id']));
    if(empty($row)){
        redirect_to_home_page("main/system_announcements/");
    }
}



if (isset($_GET['an_id'])) {
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langAnnouncements);
    $tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel'>";
    $tool_content .= "<div class='panel-body'>";
    $tool_content .= "
                        <div class='single_announcement'>
                            <div class='announcement-title'>
                                ".standard_text_escape($row->title)."
                            </div>
                            <span class='announcement-date'>
                                - ".claro_format_locale_date($dateFormatLong, strtotime($row->date))." -
                            </span>
                            <div class='announcement-main'>
                                ".standard_text_escape($row->body)."
                            </div>
                        </div>";

    $tool_content .= "
                    </div>
                </div></div></div>";
} else {
    // display admin announcements
    $tool_content .= "
        <table id='ann_table_admin_logout' class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th>$langAnnouncement</th>
                    <th>$langDate</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>";
}




if (isset($uid) and $uid) {
    draw($tool_content, 1, null, $head_content);
} else {
    draw($tool_content, 0, null, $head_content);
}
