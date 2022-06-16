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
 * @file password.php
 * @brief change user password
 */

$require_login = true;
$require_valid_uid = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/admin/hierarchy_validations.php';

if (isset($_REQUEST['userid'])) {
    $userid = intval($_REQUEST['userid']);
} else {
    forbidden();
}

if (isDepartmentAdmin()) {
    $tree = new Hierarchy();
    $user = new User();
    validateUserNodes($userid, true);
} elseif (!$is_usermanage_user) {
    forbidden();
}

$backUrl = $urlServer . 'modules/admin/edituser.php?u=' . urlencode($_REQUEST['userid']);
$toolName = $langChangePass;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => $backUrl, 'name' => $langEditUser);

// javascript
load_js('pwstrength.js');
$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

    var lang = {
hContent;
$head_content .= "pwStrengthTooShort: '" . js_escape($langPwStrengthTooShort) . "', ";
$head_content .= "pwStrengthWeak: '" . js_escape($langPwStrengthWeak) . "', ";
$head_content .= "pwStrengthGood: '" . js_escape($langPwStrengthGood) . "', ";
$head_content .= "pwStrengthStrong: '" . js_escape($langPwStrengthStrong) . "'";
$head_content .= <<<hContent
    };

    $(document).ready(function() {
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });
    });

/* ]]> */
</script>
hContent;

check_uid();

$passurl = $urlServer . 'modules/admin/password.php';
$data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                'url' => "{$urlServer}modules/admin/edituser.php?u=" . urlencode(getDirectReference($_REQUEST['userid'])),
                'icon' => 'fa-reply',
                'level' => 'primary-label')
                ));
$data['menuTypeID'] = 3;
view('admin.users.password', $data);                
