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


/**
 * @file otheractions.php
 * @brief display other actions
 */

$require_departmentmanage_user = true;
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'modules/usage/usage.lib.php';

global $langLoginFailure;

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
                   'sInfoEmpty':    '',
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
            $('.dt-search input ms-0 mb-3').attr('placeholder', '$langDetail');
        });
        </script>";

$toolName = $langAdmin;
$pageName = $langPlatformGenStats;

$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../usage/index.php?t=a", "name" => $langUsage);

// ---------------------
// actions
// ---------------------
if (isset($_GET['stats'])) {
    switch ($_GET['stats']) {
        case 'failurelogin':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $date_start = date("Y-m-d", strtotime("-1 month"));
            $date_end = date("Y-m-d", strtotime("+1 days"));
            $page_link = "&amp;stats=failurelogin";
            if ($is_departmentmanage_user && !$is_admin) {
                $logs = getTenantFailureLoginData($date_start, $date_end);

                if (!empty($logs)) {

                    $tool_content .= '<div class="col-12"><div class="table-responsive">';
                    $tool_content .= '<table id="log_results_table" class="table-default table-logs dataTable">';
                    $tool_content .= '<thead>
                    <tr class="list-header">
                    <th>Ημερομηνία</th>
                    <th>Χρήστης</th>
                    <th>Διεύθυνση IP</th>
                    <th>Ενέργεια</th>
                    <th>Λεπτομέρειες</th>
                    </tr></thead><tbody>';

                    foreach ($logs as $r) {
                        $tool_content .= '<tr>';
                        $tool_content .= '<td><span style="display:none;">' . $r->ts . '</span>' . format_locale_date(strtotime($r->ts), 'short') . '</td>';
                        $tool_content .= '<td>&nbsp;&nbsp;———</td>';
                        $tool_content .= '<td class="text-nowrap">' . $r->ip . '</td>';
                        $tool_content .= '<td class="text-nowrap">Αποτυχημένες προσπάθειες εισόδου</td>';
                        $tool_content .= '<td>Όνομα χρήστη «' . $r->details['uname'] . '»</td>';
                        $tool_content .= '</tr>';
                    }

                    $tool_content .= '</tbody></table></div></div>';
                } else {
                    $tool_content .= "<div class='alert alert-warning'>$langNoUsersLog</div>";
                }
            } else {
                $log = new Log();
                $log->display(0, 0, 0, LOG_LOGIN_FAILURE, $date_start, $date_end);
            }
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
        case 'memail':
            $result = [];
            if ($is_departmentmanage_user && !$is_admin) {
                $users = getTenantUsers();
                $emails = [];
                foreach ($users as $u) {
                    $emails[] = $u->email;
                }
                $emailCounts = array_count_values($emails);
                foreach ($emailCounts as $email => $nb) {
                    if ($nb > 1) {
                        $result[$email] = $nb;
                    }
                }
            } else {
                $sql = Database::get()->queryArray("SELECT DISTINCT email, COUNT(*) AS nb FROM user GROUP BY email HAVING nb > 1 ORDER BY nb DESC");
                foreach ($sql as $d) {
                    $result[$d->email] = $d->nb;
                }
            }
            $data['loginDouble'] = $result;
            break;
        case 'vmusers':
            if ($is_departmentmanage_user && !$is_admin) {
                $users = getTenantUsers();
                $data['totalUserCnt'] = count($users);
                $data['verifiedEmailUserCnt'] = count(array_filter($users, fn($u) => $u->verified_mail == EMAIL_VERIFIED));
                $data['unverifiedEmailUserCnt'] = count(array_filter($users, fn($u) => $u->verified_mail == EMAIL_UNVERIFIED));
                $data['verificationRequiredEmailUserCnt'] = count(array_filter($users, fn($u) => $u->verified_mail == EMAIL_VERIFICATION_REQUIRED));
            } else {
                $data['verifiedEmailUserCnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFIED . ";")->cnt;
                $data['unverifiedEmailUserCnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_UNVERIFIED . ";")->cnt;
                $data['verificationRequiredEmailUserCnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFICATION_REQUIRED . ";")->cnt;
                $data['totalUserCnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user;")->cnt;
            }
        case 'cusers':
            $data['q'] = [];

            if ($is_admin) {
                $data['q'] = Database::get()->queryArray("
                        SELECT surname, givenname, username, user_id, COUNT(course_id) AS num_of_courses 
                        FROM course_user 
                        JOIN user ON course_user.user_id = user.id 
                        WHERE course_user.status=5 
                        GROUP BY user_id, surname, givenname, username
                        ORDER BY num_of_courses DESC 
                        LIMIT 0,30
                    ");
                break;
            } 
            
            if ($is_departmentmanage_user) {
                $users = getTenantUsers();
                $tenant = getCurrentTenant();

                if (count($users) === 0 || !$tenant) {
                    break;
                }

                $tenant_hierarchy_id = $tenant->hierarchy_id ?? null;

                if (!$tenant_hierarchy_id) {
                    break;
                }

                $user_ids = implode(",", array_map(fn($user) => intval($user->id), $users));

                $data['q'] = Database::get()->queryArray("
                        SELECT u.surname, u.givenname, u.username, cu.user_id, COUNT(course_id) AS num_of_courses 
                        FROM course_user cu
                        JOIN user u ON cu.user_id = u.id 
                        JOIN course_department cd ON cd.course = cu.course_id
                        JOIN hierarchy h ON h.id = ?d
                        JOIN hierarchy h_cd ON h_cd.id = cd.department
                        WHERE cu.status=5 
                        AND cu.user_id IN ($user_ids)
                        AND h_cd.lft BETWEEN h.lft AND h.rgt
                        GROUP BY user_id, surname, givenname, username
                        ORDER BY num_of_courses DESC 
                        LIMIT 0,30
                    ", $tenant_hierarchy_id);
                break;
            }
            break;

        case 'popularcourses':
            $data['popularcourses'] = [];

            if ($is_admin) {
                $data['popularcourses'] = Database::get()->queryArray("
                    SELECT code, public_code, title, prof_names, visible, COUNT(*) AS num_of_users 
                    FROM course 
                    JOIN course_user ON course_id = course.id 
                    GROUP BY course_id, code, public_code, title, prof_names, visible
                    ORDER BY COUNT(*) DESC 
                    LIMIT 30
                ");
                break;
            }
        
            if ($is_departmentmanage_user) {
                $tenant = getCurrentTenant();

                if (!$tenant) {
                    break;
                }
        
                $tenant_hierarchy_id = $tenant->hierarchy_id ?? null;

                if (!$tenant_hierarchy_id) {
                    break;
                }
    
                $data['popularcourses'] = Database::get()->queryArray("
                    SELECT c.code, c.public_code, c.title, c.prof_names, c.visible, COUNT(cu.user_id) AS num_of_users
                    FROM course c
                    JOIN course_user cu ON cu.course_id = c.id
                    JOIN course_department cd ON cd.course = c.id
                    JOIN hierarchy h_cd ON h_cd.id = cd.department
                    JOIN hierarchy h_tenant ON h_tenant.id = ?d
                    WHERE h_cd.lft BETWEEN h_tenant.lft AND h_tenant.rgt
                    GROUP BY c.id, c.code, c.public_code, c.title, c.prof_names, c.visible
                    ORDER BY num_of_users DESC
                    LIMIT 30
                ", $tenant_hierarchy_id);
    
                break;
            }
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
function tablize($table, $search)
{

    global $urlServer;

    $ret = "";
    if (is_array($table)) {
        foreach ($table as $key => $thevalue) {
            $ret .= "<tr>";
            switch ($search) {
                case 'email':
                    $link = $urlServer . "modules/admin/listusers.php?uname=&fname=&lname=&email=" . urlencode($key) . "&am=&user_type=0"
                        . "&auth_type=0&reg_flag=1&user_registered_at=&verified_mail=3"
                        . "&department=0&search_type=contains";
                    break;
                case 'username':
                case 'pair':
                    $link = $urlServer . "modules/admin/listusers.php?uname=" . urlencode($key) . "&fname=&lname=&email=&am=&user_type=0"
                        . "&auth_type=0&reg_flag=1&user_registered_at=&verified_mail=3"
                        . "&department=0&search_type=contains";
                    break;
                default:
                    $link = '';
            }

            $ret .= "<td style='font-size: 90%'><a href='$link'>" . $key . "</a></td>";
            $ret .= "<td class='right'><strong>" . $thevalue . "</strong></td></tr>";
        }
    }
    return $ret;
}
