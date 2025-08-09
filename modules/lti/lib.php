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

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

require_once 'modules/lti/classes/JwksHelper.php';
require_once 'modules/lti/classes/LtiServiceBasicOutcomes.php';
require_once 'modules/lti/classes/LtiServiceToolSettings.php';
require_once 'modules/lti/classes/LtiServiceMemberships.php';
require_once 'modules/lti/classes/LtiServiceGradebookServices.php';
require_once 'include/lib/curlutil.class.php';
require_once 'modules/lti_consumer/lti-functions.php';

// Scope for accessing the service
const SCOPE_BASIC_OUTCOMES = 'https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome';
// Scope for managing tool settings
const SCOPE_TOOL_SETTINGS = 'https://purl.imsglobal.org/spec/lti-ts/scope/toolsetting';
// Scope for reading membership data
const SCOPE_MEMBERSHIPS_READ = 'https://purl.imsglobal.org/spec/lti-nrps/scope/contextmembership.readonly';
// Scope for full access to Lineitem service
const SCOPE_GRADEBOOKSERVICES_LINEITEM = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem';
// Scope for full access to Lineitem service
const SCOPE_GRADEBOOKSERVICES_LINEITEM_READ = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly';
// Scope for access to Result service
const SCOPE_GRADEBOOKSERVICES_RESULT_READ = 'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly';
// Scope for access to Score service
const SCOPE_GRADEBOOKSERVICES_SCORE = 'https://purl.imsglobal.org/spec/lti-ags/scope/score';

const LTI_ACCESS_TOKEN_LIFE = 3600;
const LTI_JWT_CLAIM_PREFIX = 'https://purl.imsglobal.org/spec/lti';

/**
 * Verifies the JWT signature using a JWK keyset.
 *
 * @param string $jwtparam JWT parameter value.
 * @param string $keyseturl The tool keyseturl.
 * @param string $clientid The tool client id.
 *
 * @return object The JWT's payload as a PHP object
 * @throws Exception
 * @throws UnexpectedValueException     Provided JWT was invalid
 * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed
 * @throws BeforeValidException         Provided JWT is trying to be used before it's eligible as defined by 'nbf'
 * @throws BeforeValidException         Provided JWT is trying to be used before it's been created as defined by 'iat'
 * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim
 */
function ltiVerifyWithKeyset(string $jwtparam, string $keyseturl, string $clientid): stdClass {
    // Attempts to retrieve cached keyset.
    $cache = new FileCache('lticache', 300);
    $lticache = $cache->get();

    try {
        if (empty($lticache) || empty($lticache[$clientid])) {
            throw new Exception('LTI Error: no cached keyset found.');
        }

        $keyset = $lticache[$clientid];
        $keysetarr = json_decode($keyset, true);

        // JWK::parseKeySet uses RS256 algorithm by default.
        $keys = JWK::parseKeySet($keysetarr);
        $jwt = JWT::decode($jwtparam, $keys);
    } catch (Exception) {
        // Something went wrong, so attempt to update cached keyset and then try again.
        $keyset = CurlUtil::downloadFileContent($keyseturl);
        if (!$keyset) {
            throw new Exception('LTI Error: no keyset could be downloaded.');
        }
        $keysetarr = json_decode($keyset, true);
        if (empty($keysetarr)) {
            throw new Exception('LTI Error: downloaded keyset could not be decoded.');
        }

        // Fix for firebase/php-jwt's dependency on the optional 'alg' property in the JWK.
        $keysetarr = JwksHelper::fixJwksAlg($keysetarr, $jwtparam);

        // JWK::parseKeySet uses RS256 algorithm by default.
        $keys = JWK::parseKeySet($keysetarr);
        $jwt = JWT::decode($jwtparam, $keys);

        // If successful, update the cached keyset.
        $lticache[$clientid] = $keyset;
        $cache->store($lticache);
    }

    return $jwt;
}

/**
 * Get the permitted scopes defined by this service.
 *
 * @param stdClass $ltiApp The LTI app.
 *
 * @return array
 */
function ltiGetPermittedServiceScopes(stdClass $ltiApp): array {

    $services = ltiGetServices();
    $scopes = [];
    foreach ($services as $service) {
        $service->setLtiApp($ltiApp);
        $servicescopes = $service->getPermittedScopes();
        if (!empty($servicescopes)) {
            $scopes = array_merge($scopes, $servicescopes);
        }
    }

    return $scopes;
}

/**
 * Create a new access token.
 *
 * @param int $ltiAppId LTI App Id
 * @param string[] $scopes Scopes permitted for new token
 *
 * @return stdClass Access token
 * @throws Exception
 */
function ltiNewAccessToken(int $ltiAppId, array $scopes): stdClass {
    // Make sure the token doesn't exist.
    $numtries = 0;
    do {
        $numtries ++;
        $generatedtoken = md5(uniqid(rand(), 1));
        if ($numtries > 5) {
            throw new Exception('LTI Error: Failed to generate LTI access token');
        }
    } while (Database::get()->querySingle("SELECT count(id) AS count FROM lti_access_tokens WHERE token = ?s", $generatedtoken)->count > 1);

    $newtoken = new stdClass();
    $newtoken->lti_app = $ltiAppId;
    $newtoken->scope = json_encode(array_values($scopes));
    $newtoken->token = $generatedtoken;
    $newtoken->time_created = time();
    $newtoken->valid_until = $newtoken->time_created + LTI_ACCESS_TOKEN_LIFE;
    $newtoken->last_access = null;

    Database::get()->query("INSERT INTO lti_access_tokens (lti_app, scope, token, time_created, valid_until) VALUES (?d, ?s, ?s, ?d, ?d)",
        $newtoken->lti_app,
        $newtoken->scope,
        $newtoken->token,
        $newtoken->time_created,
        $newtoken->valid_until
    );

    return $newtoken;
}

/**
 * Builds a standard LTI Content-Item selection request.
 *
 * @param stdClass  $ltiApp     The ltiApp object.
 * @param stdClass  $course     The course object.
 * @param stdClass  $stat       The course_user object for access check.
 * @param string    $returnurl  The return URL in the tool consumer (TC) that the tool provider (TP) will use to return the Content-Item message.
 * @param string    $title      The tool's title.
 * @param string    $text       The text to display to represent the content item. This value may be a long description of the content item.
 * @param string    $nonce
 * @return stdClass            The object containing the signed request parameters and the URL to the TP's Content-Item selection interface.
 * @throws Exception
 */
function ltiBuildContentItemSelectionRequest(stdClass $ltiApp, stdClass $course, stdClass $stat, string $returnurl, string $title = '', string $text = '', string $nonce = ''): stdClass {

    // Check title. If empty, use the tool's name.
    if (empty($title)) {
        $title = $ltiApp->title;
    }

    $key = $ltiApp->client_id;
    $islti13 = ($ltiApp->lti_version === LTI_VERSION_1_3);
    if (!$islti13) {
        throw new Exception('LTI Error: do not call ltiBuildContentItemSelectionRequest() function for non LTI 1.3 versions.');
    }

    // Set the tool URL.
    $toolurl = $ltiApp->lti_provider_url;

    // Get base request parameters.
    $requestparams = ltiBuildRequest($ltiApp, $course, $stat, null, null);

    // Get standard request parameters and merge to the request parameters.
    $standardparams = ltiBuildStandardMessage($ltiApp->lti_version, 'ContentItemSelectionRequest');
    $requestparams = array_merge($requestparams, $standardparams);

    // Only LTI links are currently supported.
    $requestparams['accept_types'] = 'ltiResourceLink';

    // Presentation targets. Supports window, iframe by default if empty.
    $presentationtargets = [
        'window',
        'iframe',
    ];
    $requestparams['accept_presentation_document_targets'] = implode(',', $presentationtargets);

    // Other request parameters.
    $requestparams['accept_copy_advice'] = 'false'; // Indicates whether the TC is able and willing to make a local copy of a content item.
    $requestparams['accept_multiple'] = 'true';     // Indicates whether the user should be permitted to select more than one item.
    $requestparams['accept_unsigned'] = 'false';    // Indicates whether the TC is willing to accept an unsigned return message, or not. A signed message should always be required when the content item is being created automatically in the TC without further interaction from the user.
    $requestparams['auto_create'] = 'false';        // Indicates whether any content items returned by the TP would be automatically persisted without any option for the user to cancel the operation.
    $requestparams['can_confirm'] = 'false';
    $requestparams['content_item_return_url'] = $returnurl;
    $requestparams['title'] = $title;
    $requestparams['text'] = $text;
    $signedparams = ltiSignJwt($requestparams, $toolurl, $key, $ltiApp->id, $nonce);

    // Check for params that should not be passed. Unset if they are set.
    $unwantedparams = [
        'resource_link_id',
        'resource_link_title',
        'resource_link_description',
        'launch_presentation_return_url',
        'lis_result_sourcedid',
    ];
    foreach ($unwantedparams as $param) {
        if (isset($signedparams[$param])) {
            unset($signedparams[$param]);
        }
    }

    // Prepare result object.
    $result = new stdClass();
    $result->params = $signedparams;
    $result->url = $toolurl;

    return $result;
}

/**
 * This function builds the request that must be sent to the LTI tool provider.
 *
 * @param  stdClass       $ltiApp        Basic LTI app object;
 * @param  stdClass       $course        Course object.
 * @param  stdClass       $stat          The course_user object for access check.
 * @param  string|null    $resourceType  The resource type.
 * @param  stdClass|null  $resource      The resource object.
 *
 * @return array               Request details
 */
function ltiBuildRequest(stdClass $ltiApp, stdClass $course, stdClass $stat, string $resourceType = null, stdClass $resource = null): array {
    global $uid, $urlServer;

    $is_assignment = !is_null($resourceType) && $resourceType == RESOURCE_LINK_TYPE_ASSIGNMENT && !is_null($resource);

    $requestparams = array(
        'user_id' => "$uid",
        'lis_person_sourcedid' => "$uid",
        'roles' => ltiGetImsRole($stat),
        'context_id' => "$course->id",
        'context_label' => trim($course->title),
        'context_title' => trim($course->title)
    );
    $requestparams['lti_message_type'] = 'basic-lti-launch-request';

    // resource link
    $requestparams['resource_link_title'] = trim($ltiApp->title);
    //$requestparams['resource_link_description'] = trim($ltiApp->description);
    $requestparams['resource_link_id'] = "$ltiApp->id";
    if ($is_assignment) {
        $requestparams['resource_link_title'] = trim($resource->title);
        //$requestparams['resource_link_description'] = trim($ltiApp->description);
        $requestparams['resource_link_id'] = "$resource->id";
    }

    $requestparams['context_type'] = 'CourseSection';
    $requestparams['lis_course_section_sourcedid'] = "$course->id";

    // result sourcedid
    if ($is_assignment) {
        $assignmentSecret = Database::get()->querySingle("SELECT secret_directory FROM assignment WHERE id = ?d", intval($resource->id))->secret_directory;
        $requestparams['lis_result_sourcedid'] = json_encode(ltiBuildSourcedid($resource->id, $uid, $assignmentSecret, $ltiApp->id));
        // placeholder outcome endpoint
        // $requestparams['lis_outcome_service_url'] = $urlServer . "modules/lti/service.php";
        // alternative with token
        // $token = token_generate($assignmentSecret, true);
        // $requestparams['lis_result_sourcedid'] = $token . "-" . $resourceId . "-" . $uid;
    }

    // Send user's data.
    $user = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d ", $uid);
    $requestparams['lis_person_name_given'] = $user->givenname;
    $requestparams['lis_person_name_family'] = $user->surname;
    $requestparams['lis_person_name_full'] = $user->givenname . " " . $user->surname;
    $requestparams['ext_user_username'] = $user->username;
    $requestparams['lis_person_contact_email_primary'] = $user->email;

    return $requestparams;
}

/**
 * This function builds the request custom parameters that must be sent to the LTI tool provider.
 *
 * @param  string    $resourceType  The resource type.
 * @param  stdClass  $resource      The resource object.
 *
 * @return array                    Request details.
 */
function ltiBuildCustomParameters(string $resourceType, stdClass $resource): array {
    $requestparams = [];
    // assignment instructor custom parameters
    if ($resourceType == RESOURCE_LINK_TYPE_ASSIGNMENT && $resource->tii_instructorcustomparameters) {
        $requestparams = ltiSplitCustomParameters($resource->tii_instructorcustomparameters);
    }
    if ($resourceType == RESOURCE_LINK_TYPE_ASSIGNMENT && $resource->submission_date) {
        $startdate = DateTime::createFromFormat('Y-m-d H:i:s', $resource->submission_date, new DateTimeZone('Europe/Athens'));
        $startdate->setTimezone(new DateTimeZone('UTC'));
        $requestparams['custom_startdate'] = $startdate->format('Y-m-d\TH:i:s\.v\Z');
    }
    if ($resourceType == RESOURCE_LINK_TYPE_ASSIGNMENT && $resource->deadline) {
        $duedate = DateTime::createFromFormat('Y-m-d H:i:s', $resource->deadline, new DateTimeZone('Europe/Athens'));
        $duedate->setTimezone(new DateTimeZone('UTC'));
        $requestparams['custom_duedate'] = $duedate->format('Y-m-d\TH:i:s\.v\Z');
    }
    return $requestparams;
}

/**
 * Gets the IMS role string for the specified user and LTI course module.
 *
 * @param stdClass  $stat    The course_user object for access check.
 *
 * @return string            A role string suitable for passing with an LTI launch
 */
function ltiGetImsRole(stdClass $stat): string {
    global $uid;

    $roles = array();

    if ($stat->status == USER_TEACHER || $stat->editor == 1 || $stat->tutor == 1) {
        $roles[] = 'Instructor';
    } else {
        $roles[] = 'Learner';
    }

    $admin_rights = get_admin_rights($uid);
    if ($admin_rights == ADMIN_USER) {
        // Make sure admins do not have the Learner role, then set admin role.
        $roles = array_diff($roles, array('Learner'));
        array_push($roles, 'urn:lti:sysrole:ims/lis/Administrator', 'urn:lti:instrole:ims/lis/Administrator');
    }

    return join(',', $roles);
}

/**
 * This function builds the standard parameters for an LTI message that must be sent to the tool producer
 *
 * @param string $ltiversion     LTI version to be used for tool messages
 * @param string $messagetype    The request message type. Defaults to basic-lti-launch-request if empty.
 *
 * @return array                    Message parameters
 */
function ltiBuildStandardMessage(string $ltiversion, string $messagetype = 'basic-lti-launch-request'): array {
    global $language, $urlServer;

    $requestparams = array();
    $requestparams['launch_presentation_locale'] = $language;

    // Make sure we let the tool know what LMS they are being called from.
    $requestparams['ext_lms'] = 'openeclass';
    $requestparams['tool_consumer_info_product_family_code'] = 'openeclass';
    $requestparams['tool_consumer_info_version'] = ECLASS_VERSION;

    // Add oauth_callback to be compliant with the 1.0A spec.
    $requestparams['oauth_callback'] = 'about:blank';

    $requestparams['lti_version'] = $ltiversion;
    $requestparams['lti_message_type'] = $messagetype;

    $requestparams["tool_consumer_instance_guid"] = md5($urlServer);
    $requestparams['tool_consumer_instance_name'] = $GLOBALS['siteName'];
    $requestparams['tool_consumer_instance_description'] = $GLOBALS['Institution'];

    return $requestparams;
}

/**
 * Converts the message parameters to their equivalent JWT claims and signs the payload to launch the external tool using JWT.
 *
 * @param array  $parms        Parameters to be passed for signing
 * @param string $endpoint     url of the external tool
 * @param string $oauthconsumerkey
 * @param int    $ltiAppId     Id of LTI app
 * @param string $nonce        Nonce value to use
 *
 * @return array
 */
function ltiSignJwt(array $parms, string $endpoint, string $oauthconsumerkey, int $ltiAppId = 0, string $nonce = ''): array {

    if (empty($ltiAppId)) {
        $ltiAppId = 0;
    }
    $messagetypemapping = ltiGetJwtMessageTypeMapping();
    if (isset($parms['lti_message_type']) && array_key_exists($parms['lti_message_type'], $messagetypemapping)) {
        $parms['lti_message_type'] = $messagetypemapping[$parms['lti_message_type']];
    }
    if (isset($parms['roles'])) {
        $roles = explode(',', $parms['roles']);
        $newroles = array();
        foreach ($roles as $role) {
            if (strpos($role, 'urn:lti:role:ims/lis/') === 0) {
                $role = 'http://purl.imsglobal.org/vocab/lis/v2/membership#' . substr($role, 21);
            } else if (strpos($role, 'urn:lti:instrole:ims/lis/') === 0) {
                $role = 'http://purl.imsglobal.org/vocab/lis/v2/institution/person#' . substr($role, 25);
            } else if (strpos($role, 'urn:lti:sysrole:ims/lis/') === 0) {
                $role = 'http://purl.imsglobal.org/vocab/lis/v2/system/person#' . substr($role, 24);
            } else if ((strpos($role, '://') === false) && (strpos($role, 'urn:') !== 0)) {
                $role = "http://purl.imsglobal.org/vocab/lis/v2/membership#{$role}";
            }
            $newroles[] = $role;
        }
        $parms['roles'] = implode(',', $newroles);
    }

    $now = time();
    if (empty($nonce)) {
        $nonce = bin2hex(openssl_random_pseudo_bytes(10));
    }
    $claimmapping = ltiGetJwtClaimMapping();
    $payload = array(
        'nonce' => $nonce,
        'iat' => $now,
        'exp' => $now + 60,
    );
    $payload['iss'] = ltiGetIssuer();
    $payload['aud'] = $oauthconsumerkey;
    $payload[LTI_JWT_CLAIM_PREFIX . '/claim/deployment_id'] = strval($ltiAppId);
    $payload[LTI_JWT_CLAIM_PREFIX . '/claim/target_link_uri'] = $endpoint;

    foreach ($parms as $key => $value) {
        $claim = LTI_JWT_CLAIM_PREFIX;
        if (array_key_exists($key, $claimmapping)) {
            $mapping = $claimmapping[$key];
            $type = $mapping["type"] ?? "string";
            if ($mapping['isarray']) {
                $value = explode(',', $value);
                sort($value);
            } else if ($type == 'boolean') {
                $value = isset($value) && ($value == 'true');
            }
            if (!empty($mapping['suffix'])) {
                $claim .= "-{$mapping['suffix']}";
            }
            $claim .= '/claim/';
            if (is_null($mapping['group'])) {
                $payload[$mapping['claim']] = $value;
            } else if (empty($mapping['group'])) {
                $payload["{$claim}{$mapping['claim']}"] = $value;
            } else {
                $claim .= $mapping['group'];
                $payload[$claim][$mapping['claim']] = $value;
            }
        } else if (strpos($key, 'custom_') === 0) {
            $payload["{$claim}/claim/custom"][substr($key, 7)] = $value;
        } else if (strpos($key, 'ext_') === 0) {
            $payload["{$claim}/claim/ext"][substr($key, 4)] = $value;
        }
    }

    $privatekey = JwksHelper::getPrivateKey();
    $jwt = JWT::encode($payload, $privatekey['key'], 'RS256', $privatekey['kid']);

    $newparms = array();
    $newparms['id_token'] = $jwt;

    return $newparms;
}

/**
 * Return the mapping for standard message types to JWT message_type claim.
 *
 * @return array
 */
function ltiGetJwtMessageTypeMapping(): array {
    return array(
        'basic-lti-launch-request' => 'LtiResourceLinkRequest',
        'ContentItemSelectionRequest' => 'LtiDeepLinkingRequest',
        'LtiDeepLinkingResponse' => 'ContentItemSelection',
        'LtiSubmissionReviewRequest' => 'LtiSubmissionReviewRequest',
    );
}

/**
 * Return the mapping for standard message parameters to JWT claim.
 *
 * @return array
 */
function ltiGetJwtClaimMapping(): array {
    $mapping = [];
    $services = ltiGetServices();
    foreach ($services as $service) {
        $mapping = array_merge($mapping, $service->getJwtClaimMappings());
    }
    $mapping = array_merge($mapping, [
        'accept_copy_advice' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'accept_copy_advice',
            'isarray' => false,
            'type' => 'boolean'
        ],
        'accept_media_types' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'accept_media_types',
            'isarray' => true
        ],
        'accept_multiple' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'accept_multiple',
            'isarray' => false,
            'type' => 'boolean'
        ],
        'accept_presentation_document_targets' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'accept_presentation_document_targets',
            'isarray' => true
        ],
        'accept_types' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'accept_types',
            'isarray' => true
        ],
        'accept_unsigned' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'accept_unsigned',
            'isarray' => false,
            'type' => 'boolean'
        ],
        'auto_create' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'auto_create',
            'isarray' => false,
            'type' => 'boolean'
        ],
        'can_confirm' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'can_confirm',
            'isarray' => false,
            'type' => 'boolean'
        ],
        'content_item_return_url' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'deep_link_return_url',
            'isarray' => false
        ],
        'content_items' => [
            'suffix' => 'dl',
            'group' => '',
            'claim' => 'content_items',
            'isarray' => true
        ],
        'data' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'data',
            'isarray' => false
        ],
        'text' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'text',
            'isarray' => false
        ],
        'title' => [
            'suffix' => 'dl',
            'group' => 'deep_linking_settings',
            'claim' => 'title',
            'isarray' => false
        ],
        'lti_msg' => [
            'suffix' => 'dl',
            'group' => '',
            'claim' => 'msg',
            'isarray' => false
        ],
        'lti_log' => [
            'suffix' => 'dl',
            'group' => '',
            'claim' => 'log',
            'isarray' => false
        ],
        'lti_errormsg' => [
            'suffix' => 'dl',
            'group' => '',
            'claim' => 'errormsg',
            'isarray' => false
        ],
        'lti_errorlog' => [
            'suffix' => 'dl',
            'group' => '',
            'claim' => 'errorlog',
            'isarray' => false
        ],
        'context_id' => [
            'suffix' => '',
            'group' => 'context',
            'claim' => 'id',
            'isarray' => false
        ],
        'context_label' => [
            'suffix' => '',
            'group' => 'context',
            'claim' => 'label',
            'isarray' => false
        ],
        'context_title' => [
            'suffix' => '',
            'group' => 'context',
            'claim' => 'title',
            'isarray' => false
        ],
        'context_type' => [
            'suffix' => '',
            'group' => 'context',
            'claim' => 'type',
            'isarray' => true
        ],
        'for_user_id' => [
            'suffix' => '',
            'group' => 'for_user',
            'claim' => 'user_id',
            'isarray' => false
        ],
        'lis_course_offering_sourcedid' => [
            'suffix' => '',
            'group' => 'lis',
            'claim' => 'course_offering_sourcedid',
            'isarray' => false
        ],
        'lis_course_section_sourcedid' => [
            'suffix' => '',
            'group' => 'lis',
            'claim' => 'course_section_sourcedid',
            'isarray' => false
        ],
        'launch_presentation_css_url' => [
            'suffix' => '',
            'group' => 'launch_presentation',
            'claim' => 'css_url',
            'isarray' => false
        ],
        'launch_presentation_document_target' => [
            'suffix' => '',
            'group' => 'launch_presentation',
            'claim' => 'document_target',
            'isarray' => false
        ],
        'launch_presentation_height' => [
            'suffix' => '',
            'group' => 'launch_presentation',
            'claim' => 'height',
            'isarray' => false
        ],
        'launch_presentation_locale' => [
            'suffix' => '',
            'group' => 'launch_presentation',
            'claim' => 'locale',
            'isarray' => false
        ],
        'launch_presentation_return_url' => [
            'suffix' => '',
            'group' => 'launch_presentation',
            'claim' => 'return_url',
            'isarray' => false
        ],
        'launch_presentation_width' => [
            'suffix' => '',
            'group' => 'launch_presentation',
            'claim' => 'width',
            'isarray' => false
        ],
        'lis_person_contact_email_primary' => [
            'suffix' => '',
            'group' => null,
            'claim' => 'email',
            'isarray' => false
        ],
        'lis_person_name_family' => [
            'suffix' => '',
            'group' => null,
            'claim' => 'family_name',
            'isarray' => false
        ],
        'lis_person_name_full' => [
            'suffix' => '',
            'group' => null,
            'claim' => 'name',
            'isarray' => false
        ],
        'lis_person_name_given' => [
            'suffix' => '',
            'group' => null,
            'claim' => 'given_name',
            'isarray' => false
        ],
        'lis_person_sourcedid' => [
            'suffix' => '',
            'group' => 'lis',
            'claim' => 'person_sourcedid',
            'isarray' => false
        ],
        'user_id' => [
            'suffix' => '',
            'group' => null,
            'claim' => 'sub',
            'isarray' => false
        ],
        'user_image' => [
            'suffix' => '',
            'group' => null,
            'claim' => 'picture',
            'isarray' => false
        ],
        'roles' => [
            'suffix' => '',
            'group' => '',
            'claim' => 'roles',
            'isarray' => true
        ],
        'role_scope_mentor' => [
            'suffix' => '',
            'group' => '',
            'claim' => 'role_scope_mentor',
            'isarray' => false
        ],
        'deployment_id' => [
            'suffix' => '',
            'group' => '',
            'claim' => 'deployment_id',
            'isarray' => false
        ],
        'lti_message_type' => [
            'suffix' => '',
            'group' => '',
            'claim' => 'message_type',
            'isarray' => false
        ],
        'lti_version' => [
            'suffix' => '',
            'group' => '',
            'claim' => 'version',
            'isarray' => false
        ],
        'resource_link_description' => [
            'suffix' => '',
            'group' => 'resource_link',
            'claim' => 'description',
            'isarray' => false
        ],
        'resource_link_id' => [
            'suffix' => '',
            'group' => 'resource_link',
            'claim' => 'id',
            'isarray' => false
        ],
        'resource_link_title' => [
            'suffix' => '',
            'group' => 'resource_link',
            'claim' => 'title',
            'isarray' => false
        ],
        'tool_consumer_info_product_family_code' => [
            'suffix' => '',
            'group' => 'tool_platform',
            'claim' => 'product_family_code',
            'isarray' => false
        ],
        'tool_consumer_info_version' => [
            'suffix' => '',
            'group' => 'tool_platform',
            'claim' => 'version',
            'isarray' => false
        ],
        'tool_consumer_instance_contact_email' => [
            'suffix' => '',
            'group' => 'tool_platform',
            'claim' => 'contact_email',
            'isarray' => false
        ],
        'tool_consumer_instance_description' => [
            'suffix' => '',
            'group' => 'tool_platform',
            'claim' => 'description',
            'isarray' => false
        ],
        'tool_consumer_instance_guid' => [
            'suffix' => '',
            'group' => 'tool_platform',
            'claim' => 'guid',
            'isarray' => false
        ],
        'tool_consumer_instance_name' => [
            'suffix' => '',
            'group' => 'tool_platform',
            'claim' => 'name',
            'isarray' => false
        ],
        'tool_consumer_instance_url' => [
            'suffix' => '',
            'group' => 'tool_platform',
            'claim' => 'url',
            'isarray' => false
        ]
    ]);
    return $mapping;
}

/**
 * Return the launch data required for opening the external tool.
 *
 * @param  stdClass     $ltiApp       The ltiApp object.
 * @param stdClass      $course       Course object.
 * @param  stdClass     $stat         The course_user object for access check.
 * @param  string       $nonce        The nonce value to use (applies to LTI 1.3 only).
 * @param  string       $messagetype  The request message type. Defaults to basic-lti-launch-request if empty.
 * @param  string|null  $resourceType The resource type.
 * @param  string|null  $resourceId   The resource type id.
 * @return array                      The endpoint URL and parameters (including the signature).
 */
function ltiGetLaunchData(stdClass $ltiApp, stdClass $course, stdClass $stat, ?string $resourceType, ?string $resourceId, string $messagetype = 'basic-lti-launch-request', string $nonce = ''): array {
    global $urlServer, $uid;

    $ltiAppId = $ltiApp->id;
    $key = $ltiApp->client_id;
    $endpoint = trim($ltiApp->lti_provider_url);

    $is_assignment = false;
    $assignment = null;

    if (!is_null($resourceType) && !is_null($resourceId) && $resourceType == RESOURCE_LINK_TYPE_ASSIGNMENT) {
        $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $resourceId);
        if (!empty($assignment)) {
            $is_assignment = true;
        }
    }

    $allparams = ltiBuildRequest($ltiApp, $course, $stat, $resourceType, $assignment);
    $requestparams = array_merge($allparams, ltiBuildStandardMessage($ltiApp->lti_version, $messagetype));

    $launchcontainer = $ltiApp->launchcontainer;
    // placeholder for a separate return step
    // $returnurlparams = array('course' => $course->id,
    //    'launch_container' => $launchcontainer,
    //    'id' => $ltiAppId,
    //    'sesskey' => randomkeys(10));
    // $returnurl = $urlServer . "modules/lti/return.php?" . getQueryString($returnurlparams);
    $returnurl = $urlServer . "courses/" . $course->code . "/";

    $target = '';
    switch($launchcontainer) {
        case LTI_LAUNCHCONTAINER_EMBED:
            $target = 'iframe';
            break;
        case LTI_LAUNCHCONTAINER_NEWWINDOW:
        case LTI_LAUNCHCONTAINER_EXISTINGWINDOW:
            $target = 'window';
            break;
    }
    if (!empty($target)) {
        $requestparams['launch_presentation_document_target'] = $target;
    }

    $requestparams['launch_presentation_return_url'] = $returnurl;

    // custom params
    if ($is_assignment) {
        $requestparams = array_merge($requestparams, ltiBuildCustomParameters($resourceType, $assignment));
        $services = ltiGetServices();
        foreach ($services as $service) {
            $serviceparameters = $service->getLaunchParameters($messagetype, $course->id, $uid, $ltiAppId, $assignment->id);
            foreach ($serviceparameters as $paramkey => $paramvalue) {
                $requestparams['custom_' . $paramkey] = $paramvalue;
            }
        }
    }

    $parms = ltiSignJwt($requestparams, $endpoint, $key, $ltiAppId, $nonce);

    return array($endpoint, $parms);
}

/**
 * Prepares an LTI 1.3 login request
 *
 * @param  stdClass       $course         Course Object.
 * @param  stdClass       $lti            LTI App Object.
 * @param  string         $messagetype    LTI message type.
 * @param  string         $resourceType   Resource type.
 * @param  stdClass|null  $resource       The Resource object.
 * @return array                          Login request parameters.
 */
function ltiBuildLoginRequest(stdClass $course, stdClass $lti, string $messagetype, string $resourceType, ?stdClass $resource): array {
    global $uid;

    // ContentItemSelectionRequest comes with null resource, as it is not yet created, so we handle things a bit differently

    $ltihint = [];
    $resourceid = "";
    $launch_part = $messagetype;
    if (!empty($resource)) {
        $launch_part = $lti->id;
        if ($resourceType == RESOURCE_LINK_TYPE_ASSIGNMENT) {
            $resourceid = $resource->id;
            $ltihint['resourceid'] = $resourceid;
        }
    }
    $launchid = 'ltilaunch_' . $launch_part . '_' . rand();
    $_SESSION[$launchid] = "{$course->id},{$lti->id},{$messagetype},{$resourceType},{$resourceid},,";

    $ltihint['launchid'] = $launchid;

    $params = array();
    $params['iss'] = ltiGetIssuer();
    $params['target_link_uri'] = trim($lti->lti_provider_url);
    $params['login_hint'] = $uid;
    $params['lti_message_hint'] = json_encode($ltihint);
    $params['client_id'] = $lti->client_id;
    $params['lti_deployment_id'] = $lti->id;

    return $params;
}

/**
 * Build source ID
 *
 * @param int $resourceid
 * @param int $userid
 * @param string $servicesalt
 * @param int $ltiAppId
 * @param int|null $launchid
 * @return stdClass
 */
function ltiBuildSourcedid(int $resourceid, int $userid, string $servicesalt, int $ltiAppId, int $launchid = null): stdClass {
    $data = new stdClass();

    $data->instanceid = "$resourceid";
    $data->userid = "$userid";
    $data->typeid = "$ltiAppId";
    if (!is_null($launchid)) {
        $data->launchid = $launchid;
    } else {
        $data->launchid = mt_rand();
    }

    $json = json_encode($data);

    $hash = hash('sha256', $json . $servicesalt, false);

    $container = new stdClass();
    $container->data = $data;
    $container->hash = $hash;

    return $container;
}

/**
 * Generate the form for initiating a login request for an LTI 1.3 message
 *
 * @param  stdClass  $course        Course Object.
 * @param  stdClass  $ltiApp        LTI App Object.
 * @param  string    $messagetype   LTI message type.
 * @param  string    $resourceType  Resource type.
 * @return string
 */
function ltiInitiateLogin(stdClass $course, stdClass $ltiApp, string $resourceType, string $messagetype = 'basic-lti-launch-request'): string {

    $params = ltiBuildLoginRequest($course, $ltiApp, $messagetype, $resourceType, null);

    $r = "<form action=\"" . $ltiApp->lti_provider_initiate_login_url .
        "\" name=\"ltiInitiateLoginForm\" id=\"ltiInitiateLoginForm\" method=\"post\" " .
        "encType=\"application/x-www-form-urlencoded\">\n";

    foreach ($params as $key => $value) {
        $key = htmlspecialchars($key, ENT_COMPAT);
        $value = htmlspecialchars($value, ENT_COMPAT);
        $r .= "  <input type=\"hidden\" name=\"{$key}\" value=\"{$value}\"/>\n";
    }
    $r .= "</form>\n";

    $r .= "<script type=\"text/javascript\">\n" .
        "//<![CDATA[\n" .
        "document.ltiInitiateLoginForm.submit();\n" .
        "//]]>\n" .
        "</script>\n";

    return $r;
}

function ltiGetIssuer(): string {
    $iss = 'https://';
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        $iss = 'http://';
    }
    $iss .= $_SERVER['SERVER_NAME'];
    return $iss;
}

/**
 * Verifies the JWT signature of an incoming message.
 *
 * @param int    $ltiAppId      LTI App Object id.
 * @param string $consumerkey   The consumer key.
 * @param string $jwtparam      JWT parameter value.
 *
 * @return stdClass LTI App Object.
 * @throws Exception
 * @throws UnexpectedValueException     Provided JWT was invalid
 * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed
 * @throws BeforeValidException         Provided JWT is trying to be used before it's eligible as defined by 'nbf'
 * @throws BeforeValidException         Provided JWT is trying to be used before it's been created as defined by 'iat'
 * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim
 */
function ltiVerifyJwtSignature(int $ltiAppId, string $consumerkey, string $jwtparam): stdClass {
    $ltiApp = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $ltiAppId);

    // Validate parameters.
    if (!$ltiApp) {
        throw new Exception('LTI Error: Lti App not found during verifying JWT signature.');
    }

    $key = $ltiApp->client_id ?? '';

    if ($consumerkey !== $key) {
        throw new Exception('LTI Error: Incorrect consumerkey during verifying JWT signature.');
    }

    $keyseturl = $ltiApp->lti_provider_public_keyset_url ?? '';
    if (empty($keyseturl)) {
        throw new Exception('LTI Error: No public keyset configured during verifying JWT signature.');
    }

    // Attempt to verify jwt with jwk keyset.
    ltiVerifyWithKeyset($jwtparam, $keyseturl, $ltiApp->client_id);

    return $ltiApp;
}

/**
 * Converts the new Deep-Linking format for Content-Items to the old format.
 *
 * @param string $param JSON string representing new Deep-Linking format
 * @return string  JSON representation of content-items
 */
function ltiConvertContentItems(string $param): string {
    $items = array();
    $json = json_decode($param);
    if (!empty($json) && is_array($json)) {
        foreach ($json as $item) {
            if (isset($item->type)) {
                $newitem = clone $item;
                switch ($item->type) {
                    case 'ltiResourceLink':
                        $newitem->{'@type'} = 'LtiLinkItem';
                        $newitem->mediaType = 'application\/vnd.ims.lti.v1.ltilink';
                        break;
                    case 'link':
                    case 'rich':
                        $newitem->{'@type'} = 'ContentItem';
                        $newitem->mediaType = 'text/html';
                        break;
                    case 'file':
                        $newitem->{'@type'} = 'FileItem';
                        break;
                }
                unset($newitem->type);
                if (isset($item->html)) {
                    $newitem->text = $item->html;
                    unset($newitem->html);
                }
                if (isset($item->iframe)) {
                    // DeepLinking allows multiple options to be declared as supported.
                    // We favor iframe over new window if both are specified.
                    $newitem->placementAdvice = new stdClass();
                    $newitem->placementAdvice->presentationDocumentTarget = 'iframe';
                    if (isset($item->iframe->width)) {
                        $newitem->placementAdvice->displayWidth = $item->iframe->width;
                    }
                    if (isset($item->iframe->height)) {
                        $newitem->placementAdvice->displayHeight = $item->iframe->height;
                    }
                    unset($newitem->iframe);
                    unset($newitem->window);
                } else if (isset($item->window)) {
                    $newitem->placementAdvice = new stdClass();
                    $newitem->placementAdvice->presentationDocumentTarget = 'window';
                    if (isset($item->window->targetName)) {
                        $newitem->placementAdvice->windowTarget = $item->window->targetName;
                    }
                    if (isset($item->window->width)) {
                        $newitem->placementAdvice->displayWidth = $item->window->width;
                    }
                    if (isset($item->window->height)) {
                        $newitem->placementAdvice->displayHeight = $item->window->height;
                    }
                    unset($newitem->window);
                } else if (isset($item->presentation)) {
                    // This may have been part of an early draft but is not in the final spec
                    // so keeping it around for now in case it's actually been used.
                    $newitem->placementAdvice = new stdClass();
                    if (isset($item->presentation->documentTarget)) {
                        $newitem->placementAdvice->presentationDocumentTarget = $item->presentation->documentTarget;
                    }
                    if (isset($item->presentation->windowTarget)) {
                        $newitem->placementAdvice->windowTarget = $item->presentation->windowTarget;
                    }
                    if (isset($item->presentation->width)) {
                        $newitem->placementAdvice->dislayWidth = $item->presentation->width;
                    }
                    if (isset($item->presentation->height)) {
                        $newitem->placementAdvice->dislayHeight = $item->presentation->height;
                    }
                    unset($newitem->presentation);
                }
                if (isset($item->icon) && isset($item->icon->url)) {
                    $newitem->icon->{'@id'} = $item->icon->url;
                    unset($newitem->icon->url);
                }
                if (isset($item->thumbnail) && isset($item->thumbnail->url)) {
                    $newitem->thumbnail->{'@id'} = $item->thumbnail->url;
                    unset($newitem->thumbnail->url);
                }
                if (isset($item->lineItem)) {
                    unset($newitem->lineItem);
                    $newitem->lineItem = new stdClass();
                    $newitem->lineItem->{'@type'} = 'LineItem';
                    $newitem->lineItem->reportingMethod = 'http://purl.imsglobal.org/ctx/lis/v2p1/Result#totalScore';
                    if (isset($item->lineItem->label)) {
                        $newitem->lineItem->label = $item->lineItem->label;
                    }
                    if (isset($item->lineItem->resourceId)) {
                        $newitem->lineItem->assignedActivity = new stdClass();
                        $newitem->lineItem->assignedActivity->activityId = $item->lineItem->resourceId;
                    }
                    if (isset($item->lineItem->tag)) {
                        $newitem->lineItem->tag = $item->lineItem->tag;
                    }
                    if (isset($item->lineItem->scoreMaximum)) {
                        $newitem->lineItem->scoreConstraints = new stdClass();
                        $newitem->lineItem->scoreConstraints->{'@type'} = 'NumericLimits';
                        $newitem->lineItem->scoreConstraints->totalMaximum = $item->lineItem->scoreMaximum;
                    }
                    if (isset($item->lineItem->submissionReview)) {
                        $newitem->lineItem->submissionReview = $item->lineItem->submissionReview;
                    }
                }
                $items[] = $newitem;
            }
        }
    }

    $newitems = new stdClass();
    $newitems->{'@context'} = 'http://purl.imsglobal.org/ctx/lti/v1/ContentItem';
    $newitems->{'@graph'} = $items;

    return json_encode($newitems);
}

/**
 * Verfies the JWT and converts its claims to their equivalent message parameter.
 *
 * @param int    $ltiAppId   LTI App Object id
 * @param string $jwtparam   JWT parameter
 *
 * @return array  message parameters
 * @throws Exception
 */
function ltiConvertFromJwt(int $ltiAppId, string $jwtparam): array {

    $params = array();
    $parts = explode('.', $jwtparam);
    $ok = (count($parts) === 3);

    if ($ok) {
        $payload = JWT::urlsafeB64Decode($parts[1]);
        $claims = json_decode($payload, true);
        $ok = !is_null($claims) && !empty($claims['iss']);
    }

    if ($ok) {
        $ltiApp = ltiVerifyJwtSignature($ltiAppId, $claims['iss'], $jwtparam);
        $params['oauth_consumer_key'] = $claims['iss'];
        foreach (ltiGetJwtClaimMapping() as $key => $mapping) {
            $claim = LTI_JWT_CLAIM_PREFIX;
            if (!empty($mapping['suffix'])) {
                $claim .= "-{$mapping['suffix']}";
            }
            $claim .= '/claim/';
            if (is_null($mapping['group'])) {
                $claim = $mapping['claim'];
            } else if (empty($mapping['group'])) {
                $claim .= $mapping['claim'];
            } else {
                $claim .= $mapping['group'];
            }
            if (isset($claims[$claim])) {
                $value = null;
                if (empty($mapping['group'])) {
                    $value = $claims[$claim];
                } else {
                    $group = $claims[$claim];
                    if (is_array($group) && array_key_exists($mapping['claim'], $group)) {
                        $value = $group[$mapping['claim']];
                    }
                }
                if (!empty($value) && $mapping['isarray']) {
                    if (is_array($value)) {
                        if (is_array($value[0])) {
                            $value = json_encode($value);
                        } else {
                            $value = implode(',', $value);
                        }
                    }
                }
                if (!is_null($value) && is_string($value) && (strlen($value) > 0)) {
                    $params[$key] = $value;
                }
            }
            $claim = LTI_JWT_CLAIM_PREFIX . '/claim/custom';
            if (isset($claims[$claim])) {
                $custom = $claims[$claim];
                if (is_array($custom)) {
                    foreach ($custom as $key => $value) {
                        $params["custom_{$key}"] = $value;
                    }
                }
            }
            $claim = LTI_JWT_CLAIM_PREFIX . '/claim/ext';
            if (isset($claims[$claim])) {
                $ext = $claims[$claim];
                if (is_array($ext)) {
                    foreach ($ext as $key => $value) {
                        $params["ext_{$key}"] = $value;
                    }
                }
            }
        }
    }

    if (isset($params['content_items'])) {
        $params['content_items'] = ltiConvertContentItems($params['content_items']);
    }

    $messagetypemapping = ltiGetJwtMessageTypeMapping();
    if (isset($params['lti_message_type']) && array_key_exists($params['lti_message_type'], $messagetypemapping)) {
        $params['lti_message_type'] = $messagetypemapping[$params['lti_message_type']];
    }

    return $params;
}

/**
 * Processes the tool provider's response to the ContentItemSelectionRequest and builds the configuration data from the
 * selected content item. This configuration data can be then used when adding a tool into the course.
 *
 * @param int $ltiAppId LTI App Object id.
 * @param string $messagetype The value for the lti_message_type parameter.
 * @param string $ltiversion The value for the lti_version parameter.
 * @param string $consumerkey The consumer key.
 * @param string $contentitemsjson The JSON string for the content_items parameter.
 * @return stdClass The array of module information objects.
 * @throws Exception
 */
function ltiToolConfigurationFromContentItem(int $ltiAppId, string $messagetype, string $ltiversion, string $consumerkey, string $contentitemsjson): stdClass {
    $ltiApp = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $ltiAppId);

    // Validate parameters.
    if (!$ltiApp) {
        throw new Exception('LTI Error: Lti App not found during tool configuration from content item.');
    }

    // Check lti_message_type.
    if ($messagetype !== 'ContentItemSelection') {
        throw new Exception("LTI Error: message_type: {$messagetype} is invalid during tool configuration from content item. It should be set to ContentItemSelection.");
    }

    // Check LTI versions from our side and the response's side.
    $expectedversion = $ltiApp->lti_version;
    if ($ltiversion !== $expectedversion) {
        throw new Exception("LTI Error: version from response does not match the lti app version during tool configuration from content item. App: {$expectedversion}, Response: {$ltiversion}");
    }

    $items = json_decode($contentitemsjson);
    if (empty($items)) {
        throw new Exception('LTI Error: invalid content_items data during tool configuration from content item.');
    }
    if (!isset($items->{'@graph'}) || !is_array($items->{'@graph'})) {
        throw new Exception('LTI Error: invalid content_items response format during tool configuration from content item.');
    }

    $config = new stdClass();
    $config->title = null;
    $config->maxscore = null;
    $config->startdate = null;
    $config->enddate = null;
    $config->feedbackdate = null;
    $config->instructorcustomparameters = null;
    $graph = $items->{'@graph'};
    if (!empty($graph)) {
        $item = $graph[0];

        // title resolve
        if (isset($item->title)) {
            $config->title = $item->title;
        }

        // maxscore resolve
        if (isset($item->lineItem)) {
            $lineitem = $item->lineItem;
            if (isset($lineitem->scoreConstraints)) {
                $sc = $lineitem->scoreConstraints;
                if (isset($sc->totalMaximum)) {
                    $config->maxscore = $sc->totalMaximum;
                } else if (isset($sc->normalMaximum)) {
                    $config->maxscore = $sc->normalMaximum;
                }
            }
        }

        // dates resolve
        if (isset($item->custom)) {
            $custom = $item->custom;
            $config->instructorcustomparameters = paramsToString($custom);
            if (isset($custom->default_startdate)) {
                $config->startdate = $custom->default_startdate;
            }
            if (isset($custom->default_duedate)) {
                $config->enddate = $custom->default_duedate;
            }
            if (isset($custom->default_feedbackreleasedate)) {
                $config->feedbackdate = $custom->default_feedbackreleasedate;
            }
            if (isset($custom->tii_setting_workflow_feedback_release_date)) {
                $config->feedbackdate = $custom->tii_setting_workflow_feedback_release_date;
            }
            if (isset($custom->tii_setting_workflow_description)) {
                $config->description = $custom->tii_setting_workflow_description;
            }
        }
    }
    return $config;
}

/**
 * Converts an array of custom parameters to a new line separated string.
 *
 * @param stdClass $params list of params to concatenate
 * @return string
 */
function paramsToString(stdClass $params): string {
    $customparameters = [];
    foreach ($params as $key => $value) {
        $customparameters[] = "{$key}={$value}";
    }
    return implode("\n", $customparameters);
}

/**
 * Splits the custom parameters
 *
 * @param string $customstr String containing the parameters
 * @return array of custom parameters
 */
function ltiSplitParameters(string $customstr): array {
    $customstr = str_replace("\r\n", "\n", $customstr);
    $customstr = str_replace("\n\r", "\n", $customstr);
    $customstr = str_replace("\r", "\n", $customstr);
    $lines = explode("\n", $customstr);
    $retval = array();
    foreach ($lines as $line) {
        $pos = mb_strpos($line, '=');
        if ( $pos === false || $pos < 1 ) {
            continue;
        }
        $key = trim(mb_substr($line, 0, $pos));
        $val = trim(mb_substr($line, $pos + 1, mb_strlen($line)));
        $retval[$key] = $val;
    }
    return $retval;
}

/**
 * Splits the custom parameters field to the various parameters
 *
 * @param string $customstr String containing the parameters
 * @return array of custom parameters
 */
function ltiSplitCustomParameters(string $customstr): array {
    $splitted = ltiSplitParameters($customstr);
    $retval = array();
    foreach ($splitted as $key => $val) {
        $retval['custom_' . $key] = $val;
    }
    return $retval;
}

/**
 * Initializes an array with the services supported by the LTI module
 *
 * @return array List of services
 */
function ltiGetServices(): array {
    $services = array();
    $services[] = new LtiServiceBasicOutcomes();
    $services[] = new LtiServiceToolSettings();
    $services[] = new LtiServiceMemberships();
    $services[] = new LtiServiceGradebookServices();
    return $services;
}
