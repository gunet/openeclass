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

require_once 'modules/lti/classes/LtiOAuthBody.php';
require_once 'modules/lti/classes/LtiResourceBase.php';

/**
 * An abstract definition of an LTI service.
 */
abstract class LtiServiceBase {

    // Label representing an LTI 2 message type
    const LTI_VERSION2P0 = 'LTI-2p0';
    // Service enabled
    const SERVICE_ENABLED = 1;

    // ID for the service.
    protected ?string $id;
    // Human readable name for the service.
    protected ?string $name;
    // true if requests for this service do not need to be signed.
    protected bool $unsigned;
    // Tool proxy object for the current service request.
    private ?stdClass $toolproxy;
    private ?stdClass $ltiApp;
    // Instances of the resources associated with this service.
    protected ?array $resources;

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->id = null;
        $this->name = null;
        $this->unsigned = false;
        $this->toolproxy = null;
        $this->ltiApp = null;
        $this->resources = null;
    }

    /**
     * Get the service ID.
     *
     * @return ?string
     */
    public function getId(): ?string {
        return $this->id;
    }

    /**
     * Get the service compoent ID.
     *
     * @return string
     */
    public function getComponentId(): string {
        return 'ltiservice_' . $this->id;
    }

    /**
     * Get the service name.
     *
     * @return ?string
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Get whether the service requests need to be signed.
     *
     * @return boolean
     */
    public function isUnsigned(): bool {
        return $this->unsigned;
    }

    /**
     * Get the tool proxy object.
     *
     * @return ?stdClass
     */
    public function getToolProxy(): ?stdClass {
        return $this->toolproxy;
    }

    /**
     * Set the tool proxy object.
     *
     * @param stdClass $toolproxy The tool proxy for this service request
     */
    public function setToolProxy(stdClass $toolproxy): void {
        $this->toolproxy = $toolproxy;
    }

    /**
     * Get the app object.
     *
     * @return ?stdClass
     */
    public function getLtiApp(): ?stdClass {
        return $this->ltiApp;
    }

    /**
     * Set the LTI app object.
     *
     * @param stdClass $ltiApp The LTI app for this service request
     */
    public function setLtiApp(stdClass $ltiApp): void {
        $this->ltiApp = $ltiApp;
    }

    /**
     * Get the resources for this service.
     *
     * @return LtiResourceBase[]
     */
    abstract public function getResources(): array;

    /**
     * Get the scope(s) permitted for this service in the context of a particular tool type.
     *
     * A null value indicates that no scopes are required to access the service.
     *
     * @return array|null
     */
    public function getPermittedScopes(): ?array {
        return null;
    }

    /**
     * Get the scope(s) permitted for this service.
     *
     * A null value indicates that no scopes are required to access the service.
     *
     * @return array|null
     */
    public function getScopes(): ?array {
        return null;
    }

    /**
     * Called when the launch data is created, offering a possibility to alter the target link URI.
     *
     * @param string $messagetype message type for this launch
     * @param string $targetlinkuri current target link uri
     * @param null|string $customstr concatenated list of custom parameters
     * @param int $courseid
     * @param null|object $lti LTI Instance.
     *
     * @return array containing the target link URL and the custom params string to use.
     */
    public function overrideEndpoint(string $messagetype, string $targetlinkuri, ?string $customstr, int $courseid, ?object $lti = null): array {
        return [$targetlinkuri, $customstr];
    }

    /**
     * Default implementation will check for the existence of at least one lti_app entry for that context.
     *
     * It may be overridden if other inferences can be done.
     *
     * @param int $ltiAppId The lti app id.
     * @param int $courseid The course id.
     * @return bool returns True if app is used in context, false otherwise.
     */
    public function isUsedInContext(int $ltiAppId, int $courseid): bool {
        $cnt = Database::get()->querySingle("SELECT COUNT(id) AS count FROM lti_apps WHERE (id = ?d AND course_id = ?d) OR (id = ?d AND all_courses = 1)", $ltiAppId, $courseid, $ltiAppId)->count;
        return $cnt > 0;
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
     * @return array Key/value pairs to add as launch parameters.
     */
    public function getLaunchParameters(string $messagetype, string $courseid, string $userid, string $ltiAppId, string $resourceid = null): array {
        return [];
    }

    /**
     * Return an array of key/claim mapping allowing LTI 1.1 custom parameters to be transformed to LTI 1.3 claims.
     *
     * @return array Key/value pairs of params to claim mapping.
     */
    public function getJwtClaimMappings(): array {
        return [];
    }

    /**
     * Get the path for service requests.
     *
     * @return string
     */
    public static function getServicePath(): string {
        global $urlServer;
        return $urlServer . "modules/lti/services.php";
    }

    /**
     * Parse a string for custom substitution parameter variables supported by this service's resources.
     *
     * @param string $value  Value to be parsed
     *
     * @return string
     */
    public function parseValue(string $value): string {
        if (empty($this->resources)) {
            $this->resources = $this->getResources();
        }
        if (!empty($this->resources)) {
            foreach ($this->resources as $resource) {
                $value = $resource->parseValue($value);
            }
        }

        return $value;
    }

    /**
     * Check that the request has been properly signed and is permitted.
     *
     * @param string $ltiAppId LTI app ID
     * @param string|null $body Request body (null if none)
     * @param string[]|null $scopes Array of required scope(s) for incoming request
     *
     * @return boolean
     * @throws Exception
     */
    public function checkTool(string $ltiAppId, string $body = null, array $scopes = null): bool {
        $consumerkey = LtiOAuthBody::getOAuthKeyFromHeaders($ltiAppId, $scopes);
        if ($consumerkey === false) {
            $ok = $this->isUnsigned();
        } else {
            if (empty($ltiAppId) && is_int($consumerkey)) {
                $ltiAppId = $consumerkey;
            }
            if (!empty($ltiAppId)) {
                $this->ltiApp = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $ltiAppId);
                if (!$this->ltiApp) {
                    throw new Exception('LTI Error: Lti App not found during LtiServiceBase checkTool.');
                }
                $ok = !empty($this->ltiApp->id);
            } else {
                $ok = false;
            }
        }
        if ($ok && is_string($consumerkey)) {
            $ok = false;
        }

        return $ok;
    }

}