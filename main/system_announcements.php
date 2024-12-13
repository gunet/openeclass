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

require_once '../include/baseTheme.php';
$pageName = $langAdminAn;

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    if (isset($_GET['sSearch']) and $_GET['sSearch'] != '') {
        $keyword = '%' . $_GET['sSearch'] . '%';
    } else {
        $keyword = [];
    }

    $announcement_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM admin_announcement WHERE visible = 1 AND important = 0")->total;
    if ($limit > 0) {
        $extra_sql = 'LIMIT ?d, ?d';
        $extra_terms = [$offset, $limit];
    } else {
        $extra_sql = '';
        $extra_terms = [];
    }

    $student_sql = ($keyword? 'title LIKE ?s AND': '') .
        ' visible = 1 AND important = 0 AND (`begin` <= NOW() OR `begin` IS NULL) AND (`end` >= NOW() OR `end` IS NULL)';
    $result = Database::get()->queryArray("SELECT * FROM admin_announcement WHERE $student_sql
        ORDER BY `order` DESC , `date` DESC $extra_sql", $keyword, $extra_terms);

    if ($keyword) {
        $filtered_total = Database::get()->querySingle("SELECT COUNT(*) AS total
            FROM admin_announcement WHERE $student_sql", $keyword)->total;
    } else {
        $filtered_total = $announcement_total;
    }

    $data['iTotalRecords'] = $announcement_total;
    $data['iTotalDisplayRecords'] = $filtered_total;
    $data['aaData'] = [];
    foreach ($result as $myrow) {

        if ($myrow->order != 0) {
            $pinned = "<span class='fa fa-thumb-tack float-end text-danger'></span>";
        } else {
            $pinned = '';
        }

        $data['aaData'][] = array(
            '0' => "<div class='table_td'>
                        <div class='table_td_header clearfix'>
                            <a href='$_SERVER[SCRIPT_NAME]?an_id={$myrow->id}'>" . q($myrow->title) . "</a>
                            $pinned
                        </div>
                        <div class='table_td_body' data-id='{$myrow->id}'>" . standard_text_escape($myrow->body) . "</div>
                    </div>",
            '1' => format_locale_date(strtotime($myrow->date))
        );
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

if (isset($_GET['an_id'])) {

    $row = Database::get()->querySingle("SELECT * FROM admin_announcement WHERE id = ?d", $_GET['an_id']);
    if (!$row) {
        redirect_to_home_page("main/system_announcements/");
    }

    $navigation[] = array("url" => $_SERVER['SCRIPT_NAME'], "name" => $langAnnouncements);

    $data['title'] = $row->title;
    $data['date'] = format_locale_date(strtotime($row->date));
    $data['body'] = standard_text_escape($row->body);

    view('info.single_system_announcement', $data);
} else {
    // display admin announcements

    load_js('datatables');
    load_js('trunk8');

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
                        'class' : 'form-control input-sm ms-0 mb-3',
                        'placeholder' : '$langSearch...'
                    });
                    $('#ann_table_admin_logout_filter label').attr('aria-label', '$langSearch');
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

    view('info.system_announcements');
}
