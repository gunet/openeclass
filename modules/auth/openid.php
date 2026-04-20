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

namespace Hybridauth\Provider;

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

use Hybridauth\Provider\Keycloak;
use Hybridauth\Exception;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Data;
use Hybridauth\User;

final class CustomProvider extends Keycloak
{
    /**
     * Defaults scope to requests
     */
    public $scope = 'openid profile email';

}

$auth_settings = get_auth_settings(16);
$adapter = new CustomProvider([
    'callback' => $urlServer . 'modules/auth/openid.php',
    'url' => $auth_settings['apiBaseUrl'],
    'realm' => $auth_settings['realm'],
    'keys' => [
        'id' => $auth_settings['id'],
        'secret' => $auth_settings['secret'],
    ],
]);
$adapter->authenticate();
$userProfile = $adapter->getUserProfile();

if (isset($_SESSION['openid_test'])) {
    unset($_SESSION['openid_test']);
    $_SESSION['auth_user_info'] = (array) $userProfile;
    redirect_to_home_page('modules/admin/auth_test.php?auth=16');
} else {
    $_SESSION['auth_attributes'] = (array) $userProfile;
    $_SESSION['openid_uname'] = $userProfile->displayName; // preferred_username
    $_SESSION['auth_email'] = $userProfile->email;
    $_SESSION['auth_surname'] = $userProfile->lastName;
    $_SESSION['auth_givenname'] = $userProfile->firstName;

    if (isset($_GET['next'])) {
        header("Location: $urlServer?next=" . urlencode($_GET['next']));
    } else {
        header("Location: $urlServer");
    }
}
