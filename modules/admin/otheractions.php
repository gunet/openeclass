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


/**
 * @file otheractions.php
 * @brief display other actions
 */

$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'modules/usage/usage.lib.php';

load_js('tools.js');
load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#log_results_table').DataTable ({                                
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'iDisplayLength': 25,
                'searchDelay': 1000,
                'order': [[0, 'desc']],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '" . $langSearch . "',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input ms-0 mb-3').attr('placeholder', '$langDetail');
        });
        </script>";

$toolName = $langPlatformGenStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$data['action_bar'] = action_bar(array(
                    array('title' => $langBack,
                        'url' => "../usage/index.php?t=a",
                        'icon' => 'fa-reply',
                        'level' => 'primary')
                    ));

// ---------------------
// actions
// ---------------------
if (isset($_GET['stats'])) {
    switch ($_GET['stats']) {
        case 'failurelogin':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $date_start = date("Y-m-d", strtotime("-15 days"));
            $date_end = date("Y-m-d", strtotime("+1 days"));
            $page_link = "&amp;stats=failurelogin";
            $log = new Log();
            $log->display(0, 0, 0, LOG_LOGIN_FAILURE, $date_start, $date_end);
            $data['extra_info'] = $tool_content;
            break;
        case 'unregusers':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $date_start = date("Y-m-d", strtotime("-1 month"));
            $date_end = date("Y-m-d", strtotime("+1 days"));
            $page_link = "&amp;stats=unregusers";
            $log = new Log();
            $log->display(0, -1, 0, LOG_DELETE_USER, $date_start, $date_end);
            $data['extra_info'] = $tool_content;
            break;
        case 'musers':
            $data['loginDouble'] = list_ManyResult("SELECT DISTINCT BINARY(username) AS username, COUNT(*) AS nb
				FROM user GROUP BY username HAVING nb > 1 ORDER BY nb DESC", 'username');
            break;
        case 'memail':
            $sqlLoginDouble = "SELECT DISTINCT email, COUNT(*) AS nb FROM user GROUP BY email
				HAVING nb > 1 ORDER BY nb DESC";
            $data['loginDouble'] = list_ManyResult($sqlLoginDouble, 'email');
            break;
        case 'mlogins':
            $sqlLoginDouble = "SELECT DISTINCT BINARY(CONCAT(username, \"-- \", password)) AS pair,
                COUNT(*) AS nb FROM user GROUP BY pair HAVING nb > 1 ORDER BY nb DESC";
            $data['loginDouble'] = list_ManyResult($sqlLoginDouble, 'pair');
            break;
        case 'vmusers':
            $data['verifiedEmailUserCnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFIED . ";")->cnt;
            $data['unverifiedEmailUserCnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_UNVERIFIED . ";")->cnt;
            $data['verificationRequiredEmailUserCnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFICATION_REQUIRED . ";")->cnt;
            $data['totalUserCnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user;")->cnt;
            break;
        case 'cusers':
            $data['q'] = $q = Database::get()->queryArray("SELECT surname, givenname, username, user_id, count(course_id) AS num_of_courses 
                                FROM course_user 
                                    JOIN user 
                                ON course_user.user_id = user.id 
                                WHERE course_user.status=5 
                                    GROUP BY user_id 
                                    ORDER BY num_of_courses 
                                    DESC 
                                    LIMIT 0,30");
            break;
        case 'popularcourses':
            $data['popularcourses'] = Database::get()->queryArray("SELECT code, public_code, title, prof_names, visible, COUNT(*) AS num_of_users 
                                FROM course 
                                    JOIN course_user 
                                ON course_id = course.id 
                                GROUP BY course_id 
                                ORDER BY COUNT(*) 
                                DESC 
                                LIMIT 30");
            break;
        default:
            break;
    }
}

view('admin.other.otheractions', $data);

/**
 * @brief output a <tr> with an array
 * @return string
 */
function tablize($table, $search) {

    global $urlServer;

    $ret = "";
    if (is_array($table)) {
        foreach ($table as $key => $thevalue) {
            $ret .= "<tr>";
            switch($search) {
                case 'email' : $link = $urlServer . "modules/admin/listusers.php?uname=&fname=&lname=&email=" . urlencode($key) . "&am=&user_type=0"
                                        . "&auth_type=0&reg_flag=1&user_registered_at=&verified_mail=3"
                                        . "&department=0&search_type=contains";
                    break;
                case 'username':
                case 'pair': $link = $urlServer . "modules/admin/listusers.php?uname=" . urlencode($key) . "&fname=&lname=&email=&am=&user_type=0"
                                        . "&auth_type=0&reg_flag=1&user_registered_at=&verified_mail=3"
                                        . "&department=0&search_type=contains";
                    break;
                default : $link = '';
            }

            $ret .= "<td style='font-size: 90%'><a href='$link'>" . $key . "</a></td>";
            $ret .= "<td class='right'><strong>" . $thevalue . "</strong></td></tr>";
        }
    }
    return $ret;
}

/**
 *
 * @param type $sql
 * @param type $fieldname
 * @return array
 */
function list_ManyResult($sql, $fieldname) {

    $resu = array();
    $res = Database::get()->queryArray($sql);
    foreach ($res as $resA) {
        $name = $resA->$fieldname;
        $resu[$name] = $resA->nb;
    }
    return $resu;
}
