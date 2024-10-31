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

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Data;
use Hybridauth\User;

final class CustomProvider extends OAuth2
{
    /**
     * Defaults scope to requests
     */
    protected $scope = '';

    /**
     * Default Base URL to provider API
     */
    protected $apiBaseUrl = '';

    /**
     * Default Authorization Endpoint
     */
    protected $authorizeUrl = '';

    /**
     * Default Access Token Endpoint
     */
    protected $accessTokenUrl = '';

    /* optional: set any extra parameters or settings */
    protected function initialize()
    {
        parent::initialize();

        $url = rtrim($this->config->get('url'), '/');
        $this->apiBaseUrl = $url . '/';
        $this->authorizeUrl = $url . $this->config->get('authorizePath');
        $this->accessTokenUrl = $url . $this->config->get('accessTokenPath');

        /* optional: determine how exchange Authorization Code with an Access Token */
        $this->tokenExchangeParameters = [
            'client_id' => $this->clientId,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->callback
        ];
        $this->tokenExchangeMethod = 'POST';
        $this->tokenExchangeHeaders = ['Authorization' => 'Basic ' . base64_encode($this->clientId .  ':' . $this->clientSecret)];
    }

    function getUserProfile()
    {
        /* Send a signed http request to provider API to request user's profile */
        return $this->apiRequest($this->config->get('profileMethod'));
    }
}

$auth_settings = get_auth_settings(15);
$adapter = new CustomProvider([
    'callback' => $urlServer . 'modules/auth/oauth2.php',
    'url' => $auth_settings['apiBaseUrl'],
    'keys' => [
        'id' => $auth_settings['id'],
        'secret' => $auth_settings['secret'],
    ],
    'authorizePath' => $auth_settings['authorizePath'],
    'accessTokenPath' => $auth_settings['accessTokenPath'],
    'profileMethod' => $auth_settings['profileMethod'],
]);
$adapter->authenticate();
$accessToken = $adapter->getAccessToken();
$userProfile = $adapter->getUserProfile();
if (isset($_SESSION['oauth2_test'])) {
    unset($_SESSION['oauth2_test']);
    $_SESSION['auth_user_info'] = (array) $userProfile->attributes;
    redirect_to_home_page('modules/admin/auth_test.php?auth=15');
} else {
    $_SESSION['auth_attributes'] = (array) $userProfile->attributes;
    $attrs = get_cas_attrs($_SESSION['auth_attributes'], $auth_settings);
    $_SESSION['auth_id'] = $userProfile->id;
    if (!empty($attrs['surname'])) {
        $_SESSION['auth_surname'] = $attrs['surname'];
    }
    if (!empty($attrs['givenname'])) {
        $_SESSION['auth_givenname'] = $attrs['givenname'];
    }
    if (!empty($attrs['email'])) {
        $_SESSION['auth_email'] = $attrs['email'];
    }
    if (!empty($attrs['studentid'])) {
        $_SESSION['auth_studentid'] = $attrs['studentid'];
    }
    if (isset($_GET['next'])) {
        header("Location: $urlServer?next=" . urlencode($_GET['next']));
    } else {
        header("Location: $urlServer");
    }
}
