<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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

/*
 * Authors: Giannis Kapetanakis <bilias@edu.physics.uoc.gr>
 */

namespace Hybridauth\Provider;

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

use Hybridauth\Provider\Keycloak;
use Hybridauth\Exception;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Exception\HttpRequestFailedException;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

final class CustomProvider extends Keycloak
{
    /**
     * Defaults scope to requests
     */
    public $scope = 'openid profile email';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('userinfo');

        $data = new Data\Collection($response);

        if (!$data->exists('sub')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('sub');
        $userProfile->displayName = $data->get('preferred_username');
        $userProfile->email = $data->get('email');
        $userProfile->firstName = $data->get('given_name');
        $userProfile->lastName = $data->get('family_name');
        $userProfile->emailVerified = $data->get('email_verified');

        // Store all Keycloak attributes in $userProfile->data
        $userProfile->data = json_decode(json_encode($response), true);

        // Collect organization claim if provided in the IDToken
        if ($data->exists('organization')) {
            $kc_orgs = array_keys((array) $data->get('organization'));
            $userProfile->data['organization'] = array_shift($kc_orgs); //Get the first key
        }

        return $userProfile;
    }
}

$auth_settings = get_auth_settings(16);
$adapter = new CustomProvider([
    'callback' => $urlServer . 'modules/auth/keycloak.php',
    'url' => $auth_settings['apiBaseUrl'],
    'realm' => $auth_settings['realm'],
    'keys' => [
        'id' => $auth_settings['id'],
        'secret' => $auth_settings['secret'],
    ],
]);

try {
    $adapter->authenticate();
    $userProfile = $adapter->getUserProfile();
} catch (HttpRequestFailedException $e) {
    // Token is invalid logout and retry
    $adapter->disconnect(); // clears stored tokens

    $adapter->authenticate(); // fresh login
    $userProfile = $adapter->getUserProfile();

}

if (isset($_SESSION['keycloak_test'])) {
    unset($_SESSION['keycloak_test']);
    $_SESSION['auth_user_info'] = (array) $userProfile;
    redirect_to_home_page('modules/admin/auth_test.php?auth=16');
} else {
    $profile_array = (array) $userProfile;
    if (isset($profile_array['data']) && is_array($profile_array['data'])) {
        $profile_array = array_merge($profile_array, $profile_array['data']);
    }
    $_SESSION['auth_attributes'] = $profile_array;

    if ($auth_settings['uid_attr']) {
        $_SESSION['keycloak_uname'] = $_SESSION['auth_attributes'][$auth_settings['uid_attr']];
    } else {
        $_SESSION['keycloak_uname'] = $userProfile->displayName; // preferred_username
    }
    if ($auth_settings['userstudentid']) {
        $_SESSION['auth_studentid'] = $_SESSION['auth_attributes'][$auth_settings['userstudentid']];
    }
    $_SESSION['auth_email'] = $userProfile->email;
    $_SESSION['auth_surname'] = $userProfile->lastName;
    $_SESSION['auth_givenname'] = $userProfile->firstName;
    $_SESSION['auth_verified_mail'] = $userProfile->emailVerified;

    if (isset($_GET['next'])) {
        header("Location: $urlServer?next=" . urlencode($_GET['next']));
    } else {
        header("Location: $urlServer");
    }
}
