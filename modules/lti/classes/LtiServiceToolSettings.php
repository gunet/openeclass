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

require_once 'modules/lti/lib.php';
require_once 'modules/lti/classes/LtiServiceBase.php';
require_once 'modules/lti/classes/LtiResourceBase.php';

/**
 * A service implementing Tool Settings.
 */
class LtiServiceToolSettings extends LtiServiceBase {

    // Scope for managing tool settings
    const SCOPE_TOOL_SETTINGS = 'https://purl.imsglobal.org/spec/lti-ts/scope/toolsetting';

    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->id = 'toolsettings';
        $this->name = 'Tool Settings';
    }

    /**
     * Get the resources for this service.
     *
     * @return array
     */
    public function getResources(): array {
        // placeholder service
        if (empty($this->resources)) {
            $this->resources = [];
        }
        return $this->resources;
    }

    /**
     * Get the scope(s) permitted for this service.
     *
     * @return array|null
     */
    public function getPermittedScopes(): ?array {
        return [self::SCOPE_TOOL_SETTINGS];
    }

    /**
     * Get the scopes defined by this service.
     *
     * @return array|null
     */
    public function getScopes(): ?array {
        return [self::SCOPE_TOOL_SETTINGS];
    }

    /**
     * Return an array of key/values to add to the launch parameters.
     *
     * @param string      $messagetype 'basic-lti-launch-request' or 'ContentItemSelectionRequest'.
     * @param string      $courseid    The course id.
     * @param string      $userid      The user id.
     * @param string      $ltiAppId    The tool lti app id.
     * @param string|null $resourceid  The id of the lti activity.
     *
     * The type is passed to check the configuration and not return parameters for services not used.
     *
     * @return array of key/value pairs to add as launch parameters.
     */
    public function getLaunchParameters(string $messagetype, string $courseid, string $userid, string $ltiAppId, string $resourceid = null): array {
        // global $urlServer;
        // $launchparameters = [];
        // placeholder tool settings endpoints
        // if ($this->isUsedInContext($ltiAppId, $courseid)) {
            // $launchparameters['system_setting_url'] = '$ToolProxy.custom.url';
            // $launchparameters['context_setting_url'] = '$ToolProxyBinding.custom.url';
            // $launchparameters['system_setting_url'] = $urlServer . "modules/lti/services.php/tool/" . $ltiAppId . "/custom";
            // $launchparameters['context_setting_url'] = $urlServer . "modules/lti/services.php/CourseSection/" . $courseid . "/bindings/tool/" . $ltiAppId . "/custom";
            // if ($messagetype === 'basic-lti-launch-request') {
                // $launchparameters['link_setting_url'] = '$LtiLink.custom.url';
                // $launchparameters['link_setting_url'] = $urlServer . "modules/lti/services.php/links/{link_id}/custom";
            // }
        // }

        // return $launchparameters;
        return [];
    }

}