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

$require_usermanage_user = TRUE;
$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'mass_user_account_deletion';

require_once '../../include/baseTheme.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'hierarchy_validations.php';
require_once 'modules/auth/auth.inc.php';

$tree = new Hierarchy();
$user = new User();

$toolName = $langAdmin;

if (isset($_POST['activate_submit'])) {
    $pageName = $langAddSixMonths;
} elseif (isset($_POST['move_submit'])) {
    $pageName = $langChangeDepartment;
} else {
    $pageName = $langMultiDelUser;
}
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
load_js('tools.js');
$usernames = '';
$users = array();

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_POST['months'])) {
        $months = intval($_POST['months']);
    } elseif (isset($_POST['department'])) {
        $dest_dep = $_POST['department'][0];
        $old_dep = $_POST['old_dep'];
    }

    $count = 0;
    $line = strtok($_POST['user_names'], "\n");

    while ($line !== false) {
        // strip comments
        $line = preg_replace('/#.*/', '', trim($line));

        if (!empty($line)) {
            $u = usernameToUid($line); // fetch uid
            $line = q($line); // escape for messages below
            if (!$u) {
                $error_mgs[] = "$langErrorDelete: " . q($line);
            } else {
                if (isset($_POST['delete'])) {
                    // for uids with no admin rights
                    if (get_admin_rights($u) < 0) {
                        // delete user progress report
                        if (deleteUser($u, true)) {
                            $success_mgs[] = "$langWithUsername $line $langWasDeleted";
                            $count++;
                        } else {
                            $error_mgs[] = "$langErrorDelete: " . $line;
                        }
                    } else {
                        $error_mgs[] = "$langDeleteAdmin $line $langNotFeasible";
                    }
                } elseif (isset($months)) {
                    $q = Database::get()->query('UPDATE user
                        SET expires_at = expires_at + INTERVAL ?d MONTH
                        WHERE id = ?d', $months, $u);
                    if ($q) {
                        $success_mgs[] = sprintf($langUserDurationExtended, $line, $months);
                        $count++;
                    } else {
                        $error_mgs[] = sprintf($langUserDurationError, $line);
                    }
                } elseif (isset($dest_dep)) {
                    $q = Database::get()->query('UPDATE user_department
                        SET department = ?d WHERE user = ?d AND department = ?d',
                        $dest_dep, $u, $old_dep);
                    if ($q and $q->affectedRows) {
                        $success_mgs[] = sprintf($langUserMoved, $line);
                        $count++;
                    } else {
                        $error_mgs[] = sprintf($langUserMoveError, $line);
                    }
                }
            }
            $line = strtok("\n");
        }
    }

    if (isset($success_mgs)) {
        Session::flash('message',$success_mgs);
        Session::flash('alert-class', 'alert-success');
    }
    if (isset($error_mgs)) {
        Session::flash('message',$error_mgs);
        Session::flash('alert-class', 'alert-danger');
    }
   redirect_to_home_page('modules/admin/index.php');
} else {
    if (isset($_POST['dellall_submit']) or isset($_POST['activate_submit']) or isset($_POST['move_submit'])) {

        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        // get the incoming values
        $search = $_POST['search'] ?? '';
        $c = isset($_POST['c']) ? intval($_POST['c']) : '';
        $lname = $_POST['lname'] ?? '';
        $fname = $_POST['fname'] ?? '';
        $uname = isset($_POST['uname']) ? canonicalize_whitespace($_POST['uname']) : '';
        $am = $_POST['am'] ?? '';
        $verified_mail = isset($_POST['verified_mail']) ? intval($_POST['verified_mail']) : 3;
        $user_type = $_POST['user_type'] ?? '';
        $auth_type = $_POST['auth_type'] ?? '';
        $email = isset($_POST['email']) ? mb_strtolower(trim($_POST['email'])) : '';
        $reg_flag = isset($_POST['reg_flag']) ? intval($_POST['reg_flag']) : '';
        $user_registered_at = $_POST['user_registered_at'] ?? '';
        $user_expires_until = $_POST['user_expires_until'] ?? '';
        // Criteria/Filters
        $criteria = array();
        $terms = array();

        if (!empty($user_registered_at)) {
            $criteria[] = 'registered_at ' . (($reg_flag === 1) ? '>=' : '<=') . ' ?s';
            $date_user_registered_at = DateTime::createFromFormat("d-m-Y", $user_registered_at);
            $terms[] = $date_user_registered_at->format("Y-m-d");
        }

        if (!empty($_user_expires_until)) {
            $criteria[] = 'expires_at > CURRENT_DATE() AND expires_at < ?s';
            $date_user_expires_until = DateTime::createFromFormat("d-m-Y", $user_expires_until);
            $terms[] = $date_user_expires_until->format("Y-m-d");
        }

        if (!empty($lname)) {
            $criteria[] = 'surname LIKE ?s';
            $terms[] = '%' . $lname . '%';
        }

        if (!empty($fname)) {
            $criteria[] = 'givenname LIKE ?s';
            $terms[] = '%' . $fname . '%';
        }

        if (!empty($uname)) {
            $criteria[] = 'username LIKE ?s';
            $terms[] = '%' . $uname . '%';
        }

        if ($verified_mail === EMAIL_VERIFICATION_REQUIRED or $verified_mail === EMAIL_VERIFIED or $verified_mail === EMAIL_UNVERIFIED) {
            $criteria[] = 'verified_mail = ?d';
            $terms[] = $verified_mail;
        }

        if (!empty($am)) {
            $criteria[] = 'am LIKE ?d';
            $terms[] = '%' . $am . '%';
        }

        if (!empty($user_type)) {
            $criteria[] = 'status = ?d';
            $terms[] = $user_type;
        }

        if (!empty($auth_type)) {
            if ($auth_type >= 2) {
                $criteria[] = 'password = ?s';
                $terms[] = $auth_ids[$auth_type];
            } elseif ($auth_type == 1) {
                $criteria[] = 'password NOT IN (' . implode(', ', array_fill(0, count($auth_ids), '?s')) . ')';
                $terms = array_merge($terms, $auth_ids);
            }
        }

        if (!empty($email)) {
            $criteria[] = 'email LIKE ?s';
            $terms[] = '%' . $email . '%';
        }
        if ($search == 'inactive') {
            $criteria[] = 'expires_at < ' . DBHelper::timeAfter();
        }

        // search for users with their account being expired in one month
        if ($search == 'wexpire') {
            $criteria[] = 'expires_at between CURRENT_DATE() and date_add(CURRENT_DATE(), INTERVAL 1 MONTH)';
        }

        // Department search
        $depqryadd = '';
        $dep = (isset($_POST['department'])) ? intval($_POST['department']) : 0;
        if ($dep || isDepartmentAdmin()) {
            $depqryadd = ', user_department';

            $subs = array();
            if ($dep) {
                $subs = $tree->buildSubtrees(array($dep));
            } else if (isDepartmentAdmin()) {
                $subs = $user->getDepartmentIds($uid);
            }

            $count = 0;
            foreach ($subs as $key => $id) {
                $terms[] = $id;
                validateNode($id, isDepartmentAdmin());
                $count++;
            }

            $pref = ($c) ? 'a' : 'user';
            $criteria[] = $pref . '.id = user_department.user';
            $criteria[] = 'department IN (' . implode(', ', array_fill(0, $count, '?s')) . ')';
        }

        if (isset($_POST['move_submit'])) {
            $criteria[] = 'department = ?d';
            $terms[] = $dep;
        }

        $qry_criteria = (count($criteria)) ? implode(' AND ', $criteria) : '';
        // end filter/criteria

        if (!empty($c)) {
            $qry_base = " FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id $depqryadd WHERE b.course_id = ?d ";
            array_unshift($terms, $c);
            if ($qry_criteria) {
                $qry_base .= ' AND ' . $qry_criteria;
            }
            $qry = "SELECT DISTINCT a.username " . $qry_base . " ORDER BY a.username ASC";
        } elseif ($search == 'no_login') {
            $qry_base = " FROM user LEFT JOIN loginout ON user.id = loginout.id_user $depqryadd WHERE loginout.id_user IS NULL ";
            if ($qry_criteria) {
                $qry_base .= ' AND ' . $qry_criteria;
            }
            $qry = "SELECT DISTINCT username " . $qry_base . ' ORDER BY username ASC';
        } else {
            $qry_base = ' FROM user' . $depqryadd;
            if ($qry_criteria) {
                $qry_base .= ' WHERE ' . $qry_criteria;
            }
            $qry = 'SELECT DISTINCT username ' . $qry_base . ' ORDER BY username ASC';
        }

        $users = Database::get()->queryArray($qry, $terms);
    }

    $data['users'] = $users;
    if (isset($_POST['move_submit'])) {
        $nodePickerParams = array(
            'defaults' => $dep,
            'multiple' => false);
        if (isDepartmentAdmin()) {
            $nodePickerParams['allowables'] = $user->getDepartmentIds($uid);
        }
        load_js('jstree3');
        $data['currentDepartment'] = $tree->getNodeName($dep);
        list($js, $html) = $tree->buildUserNodePicker($nodePickerParams);
        $head_content .= $js;
        $data['html'] = $html;
        $data['dep'] = $dep;
    }
}
$data['usernames'] = $usernames;

view('admin.users.multiedituser', $data);

/**
 * @brief Translate username to uid
 * @param type $uname
 * @return boolean
 */
function usernameToUid($uname) {
    $r = Database::get()->querySingle("SELECT id FROM user WHERE username = ?s", $uname);
    if ($r) {
        return $r->id;
    } else {
        return false;
    }
}
