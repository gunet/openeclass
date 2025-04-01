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

require_once '../../include/baseTheme.php';
require_once 'modules/lti/lib.php';
require_once 'modules/lti/classes/LtiResourceBase.php';
require_once 'modules/lti/classes/LtiServiceResponse.php';

// Controller for receiving LTI service requests.

$response = new LtiServiceResponse();

$isget = $response->getRequestMethod() === LtiResourceBase::HTTP_GET;
$isdelete = $response->getRequestMethod() === LtiResourceBase::HTTP_DELETE;

if ($isget) {
    $response->setAccept($_SERVER['HTTP_ACCEPT'] ?? '');
} else {
    $response->setContentType(isset($_SERVER['CONTENT_TYPE']) ? explode(';', $_SERVER['CONTENT_TYPE'], 2)[0] : '');
}

$ok = false;
$path = $_SERVER['PATH_INFO'] ?? '';

$accept = $response->getAccept();
$contenttype = $response->getContentType();

$services = ltiGetServices();
foreach ($services as $service) {
    $resources = $service->getResources();
    foreach ($resources as $resource) {
        if (($isget && !empty($accept) && (strpos($accept, '*/*') === false) &&
             !in_array($accept, $resource->getFormats())) ||
            ((!$isget && !$isdelete) && !in_array($contenttype, $resource->getFormats()))) {
            continue;
        }
        $template = $resource->getTemplate();
        $template = preg_replace('/{config_type}/', '(toolproxy|tool)', $template);
        $template = preg_replace('/\{[a-zA-Z_]+\}/', '[^/]+', $template);
        $template = preg_replace('/\(([0-9a-zA-Z_\-,\/]+)\)/', '(\\1|)', $template);
        $template = str_replace('/', '\/', $template);
        if (preg_match("/^{$template}$/", $path) === 1) {
            $ok = true;
            break 2;
        }
    }
}
if (!$ok) {
    $response->setCode(400);
    $response->setReason("No handler found for {$path} {$accept} {$contenttype}");
} else {
    $body = file_get_contents('php://input');
    $response->setRequestData($body);
    if (in_array($response->getRequestMethod(), $resource->getMethods())) {
        $resource->execute($response);
    } else {
        $response->setCode(405);
    }
}
$response->send();