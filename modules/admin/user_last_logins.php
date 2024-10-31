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
require_once '../../include/baseTheme.php';

if (isset($_GET['u'])) {
    $u = intval($_GET['u']);
}

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listusers.php', 'name' => $langListUsersActions);

$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "edituser.php?u=$u",
        'icon' => 'fa-reply',
        'level' => 'primary-label')
));

$toolName = "$langUserLastLogins: " . uid_to_name($u);

load_js('datatables');

// fetch user last year login / logout
$result = Database::get()->queryArray("SELECT * FROM loginout WHERE id_user = ?d  
                                        AND `when` >= (NOW() - INTERVAL 1 YEAR)
                                    ORDER by idLog DESC", $u);

$data['result'] = $result;

view('admin.users.user_last_logins', $data);

/**
 * @brief legend
 * @param $action
 * @return string
 */
function action_text($action) {
    global $langLogin, $langLogout;

    switch ($action) {
        case 'LOGIN': $text = $langLogin;
            break;
        case 'LOGOUT': $text = $langLogout;
            break;
    }
    return $text;
}


