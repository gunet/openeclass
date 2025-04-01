<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
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
 * ========================================================================
 */

use Firebase\JWT\JWT;

require_once '../../include/baseTheme.php';
require_once 'modules/lti/lib.php';
require_once 'modules/lti/classes/LtiServiceResponse.php';

// initialize
$response = new LtiServiceResponse();
$contenttype = isset($_SERVER['CONTENT_TYPE']) ? explode(';', $_SERVER['CONTENT_TYPE'], 2)[0] : '';
$ok = ($_SERVER['REQUEST_METHOD'] === 'POST') && ($contenttype === 'application/x-www-form-urlencoded');
$error = 'invalid_request';

// request parameters
$clientassertion = (isset($_REQUEST['client_assertion'])) ? $_REQUEST['client_assertion'] : '';
$clientassertiontype = (isset($_REQUEST['client_assertion_type'])) ? $_REQUEST['client_assertion_type'] : '';
$granttype = (isset($_REQUEST['grant_type'])) ? $_REQUEST['grant_type'] : '';
$scope = (isset($_REQUEST['scope'])) ? $_REQUEST['scope'] : '';

// validate parameters
if ($ok) {
    $ok = !empty($clientassertion) && !empty($clientassertiontype) && !empty($granttype) && !empty($scope);
}

// validate types
if ($ok) {
    $ok = ($clientassertiontype === 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer') && ($granttype === 'client_credentials');
    $error = 'unsupported_grant_type';
}

// validate assertion
if ($ok) {
    $parts = explode('.', $clientassertion);
    $ok = (count($parts) === 3);
    if ($ok) {
        $payload = JWT::urlsafeB64Decode($parts[1]);
        $claims = json_decode($payload, true);
        $ok = !is_null($claims) && !empty($claims['sub']);
    }
    $error = 'invalid_request';
}

// validate client (us)
if ($ok) {
    $ltiApp = Database::get()->querySingle("SELECT * FROM lti_apps WHERE client_id = ?s ", $claims['sub']);
    if ($ltiApp) {
        try {
            ltiVerifyWithKeyset($clientassertion, $ltiApp->lti_provider_public_keyset_url, $claims['sub']);
            $ok = true;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $ok = false;
        }
    } else {
        $error = 'invalid_client';
        $ok = false;
    }
}

// validate scope
if ($ok) {
    $scopes = array();
    $requestedscopes = explode(' ', $scope);
    $permittedscopes = ltiGetPermittedServiceScopes($ltiApp);
    $scopes = array_intersect($requestedscopes, $permittedscopes);
    $ok = !empty($scopes);
    $error = 'invalid_scope';
}

if ($ok) {
    $token = ltiNewAccessToken($ltiApp->id, $scopes);
    $expiry = LTI_ACCESS_TOKEN_LIFE;
    $permittedscopes = implode(' ', $scopes);
    $body = <<< EOF
{
  "access_token" : "{$token->token}",
  "token_type" : "Bearer",
  "expires_in" : {$expiry},
  "scope" : "{$permittedscopes}"
}
EOF;
} else {
    $response->setCode(400);
    $body = <<< EOF
{
  "error" : "$error"
}
EOF;
}

$response->setBody($body);
$response->send();
