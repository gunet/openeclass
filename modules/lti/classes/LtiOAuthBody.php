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

use IMSGlobal\LTI\OAuth\OAuthUtil;

require_once 'modules/lti/ltiprovider/src/OAuth/OAuthUtil.php';

class LtiOAuthBody {

    /**
     *
     * @param int|null $ltiAppId LTI app ID.
     * @param string[]|null $scopes Array of scopes which give permission for the current request.
     *
     * @return string|int|boolean  The OAuth consumer key, the LTI type ID for the validated bearer token,
    * true for requests not requiring a scope, otherwise false.
     */
    public static function getOAuthKeyFromHeaders(int $ltiAppId = null, array $scopes = null): bool|int|string {
        $now = time();
        $requestheaders = OAuthUtil::get_headers();

        if (isset($requestheaders['Authorization'])) {
            if (substr($requestheaders['Authorization'], 0, 6) == "OAuth ") {
                $headerparameters = OAuthUtil::split_header($requestheaders['Authorization']);

                return $headerparameters['oauth_consumer_key'];
            } else if (empty($scopes)) {
                return true;
            } else if (substr($requestheaders['Authorization'], 0, 7) == 'Bearer ') {
                $tokenvalue = trim(substr($requestheaders['Authorization'], 7));
                if (!empty($ltiAppId)) {
                    $token = Database::get()->querySingle("SELECT * FROM lti_access_tokens WHERE token = ?s AND lti_app = ?d", $tokenvalue, $ltiAppId);
                } else {
                    $token = Database::get()->querySingle("SELECT * FROM lti_access_tokens WHERE token = ?s", $tokenvalue);
                }
                if ($token) {
                    // Log token access.
                    Database::get()->query("UPDATE lti_access_tokens SET last_access = ?d WHERE id = ?d", $now, $token->id);
                    $permittedscopes = json_decode($token->scope);
                    if ((intval($token->valid_until) > $now) && !empty(array_intersect($scopes, $permittedscopes))) {
                        return intval($token->lti_app);
                    }
                }
            }
        }

        return false;
    }

}