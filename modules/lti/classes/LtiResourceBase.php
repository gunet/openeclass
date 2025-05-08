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

require_once 'modules/lti/classes/LtiServiceBase.php';
require_once 'modules/lti/classes/LtiServiceResponse.php';

/**
 * An abstract definition of an LTI resource.
 */
abstract class LtiResourceBase {

    // HTTP Post method
    const HTTP_POST = 'POST';
    // HTTP Get method
    const HTTP_GET = 'GET';
    // HTTP Put method
    const HTTP_PUT = 'PUT';
    // HTTP Delete method
    const HTTP_DELETE = 'DELETE';

    // Service associated with this resource.
    private LtiServiceBase $service;
    // Type for this resource.
    protected string $type;
    // ID for this resource.
    protected ?string $id;
    // Template for this resource.
    protected ?string $template;
    // Custom parameter substitution variables associated with this resource.
    protected array $variables;
    // Media types supported by this resource.
    protected array $formats;
    // HTTP actions supported by this resource.
    protected array $methods;
    // Template variables parsed from the resource template.
    protected ?array $params;

    /**
     * Class constructor.
     *
     * @param LtiServiceBase $service Service instance.
     */
    public function __construct(LtiServiceBase $service) {

        $this->service = $service;
        $this->type = 'RestService';
        $this->id = null;
        $this->template = null;
        $this->variables = array();
        $this->formats = array();
        $this->methods = array();
        $this->params = null;
    }

    /**
     * Get the resource ID.
     *
     * @return string|null
     */
    public function getId(): ?string {
        return $this->id;
    }

    /**
     * Get the resource template.
     *
     * @return string|null
     */
    public function getTemplate(): ?string {
        return $this->template;
    }

    /**
     * Get the resource path.
     *
     * @return string|null
     */
    public function getPath(): ?string {
        return $this->getTemplate();
    }

    /**
     * Get the resource type.
     *
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * Get the resource's service.
     *
     * @return LtiServiceBase
     */
    public function getService(): LtiServiceBase {
        return $this->service;
    }

    /**
     * Get the resource methods.
     *
     * @return array
     */
    public function getMethods(): array {
        return $this->methods;
    }

    /**
     * Get the resource media types.
     *
     * @return array
     */
    public function getFormats(): array {
        return $this->formats;
    }

    /**
     * Get the resource template variables.
     *
     * @return array
     */
    public function getVariables(): array {
        return $this->variables;
    }

    /**
     * Get the resource fully qualified endpoint.
     *
     * @return string
     */
    public function getEndpoint(): string {
        $this->parseTemplate();
        $template = preg_replace('/[\(\)]/', '', $this->getTemplate());
        $url = $this->getService()->getServicePath() . $template;
        foreach ($this->params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        $toolproxy = $this->getService()->getToolProxy();
        if (!empty($toolproxy)) {
            $url = str_replace('{config_type}', 'toolproxy', $url);
            $url = str_replace('{tool_proxy_id}', $toolproxy->guid, $url);
        } else {
            $url = str_replace('{config_type}', 'tool', $url);
            $url = str_replace('{tool_proxy_id}', $this->getService()->getLtiApp()->id, $url);
        }

        return $url;
    }

    /**
     * Execute the request for this resource.
     *
     * @param LtiServiceResponse $response  Response object for this request.
     */
    abstract public function execute(LtiServiceResponse $response);

    /**
     * Check to make sure the request is valid.
     *
     * @param int $ltiAppId The lti app id we want to use
     * @param string|null $body Body of HTTP request message
     * @param string[]|null $scopes Array of scope(s) required for incoming request
     *
     * @return boolean
     * @throws Exception
     */
    public function checkTool(int $ltiAppId, string $body = null, array $scopes = null): bool {
        $ok = $this->getService()->checkTool($ltiAppId, $body, $scopes);
        if ($ok) {
            // Check that the scope required for the service request is included in those granted for the access token being used.
            $permittedscopes = $this->getService()->getPermittedScopes();
            $ok = is_null($permittedscopes) || empty($scopes) || !empty(array_intersect($permittedscopes, $scopes));
        }

        return $ok;
    }

    /**
     * Parse a value for custom parameter substitution variables.
     *
     * @param string $value String to be parsed
     *
     * @return string
     */
    public function parseValue(string $value): string {
        return $value;
    }

    /**
     * Parse the template for variables.
     *
     * @return array
     */
    protected function parseTemplate(): array {
        if (empty($this->params)) {
            $this->params = array();
            if (!empty($_SERVER['PATH_INFO'])) {
                $path = explode('/', $_SERVER['PATH_INFO']);
                $template = preg_replace('/\([0-9a-zA-Z_\-,\/]+\)/', '', $this->getTemplate());
                $parts = explode('/', $template);
                for ($i = 0; $i < count($parts); $i++) {
                    if ((substr($parts[$i], 0, 1) == '{') && (substr($parts[$i], -1) == '}')) {
                        $value = '';
                        if ($i < count($path)) {
                            $value = $path[$i];
                        }
                        $this->params[substr($parts[$i], 1, -1)] = $value;
                    }
                }
            }
        }

        return $this->params;
    }

    protected static function appendGetParam(string $url, string $param, int|string $value): string {
        return $url . (parse_url($url, PHP_URL_QUERY) ? "&" : "?") . $param ."=" . $value;
    }

}