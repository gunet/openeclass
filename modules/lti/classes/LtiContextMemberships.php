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

require_once 'modules/lti/classes/LtiResourceBase.php';
require_once 'modules/lti/classes/LtiServiceMemberships.php';
require_once 'modules/lti/classes/LtiServiceResponse.php';

/**
 * A resource implementing Context Memberships.
 */
class LtiContextMemberships extends LtiResourceBase {

    /**
     * Class constructor.
     *
     * @param LtiServiceMemberships $service Service instance
     */
    public function __construct($service) {
        parent::__construct($service);
        $this->id = 'ToolProxyBindingMemberships';
        $this->template = '/{context_type}/{context_id}/bindings/{tool_code}/memberships';
        $this->variables[] = 'ToolProxyBinding.memberships.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.membershipcontainer+json';
        $this->formats[] = 'application/vnd.ims.lti-nrps.v2.membershipcontainer+json';
        $this->methods[] = self::HTTP_GET;
    }

    /**
     * Execute the request for this resource.
     *
     * @param LtiServiceResponse $response  Response object for this request.
     */
    public function execute(LtiServiceResponse $response): void {
        $params = $this->parseTemplate();

        $role = (isset($_REQUEST['role'])) ? $_REQUEST['role'] : '';
        $limitnum = (isset($_REQUEST['limit'])) ? $_REQUEST['limit'] : 0;
        $limitfrom = (isset($_REQUEST['from'])) ? $_REQUEST['from'] : 0;
        $linkid = (isset($_REQUEST['rlid'])) ? $_REQUEST['rlid'] : '';
        $ltiApp = null;

        if ($limitnum <= 0) {
            $limitfrom = 0;
        }

        try {
            if (!$this->checkTool($params['tool_code'], $response->getRequestData(), array(LtiServiceMemberships::SCOPE_MEMBERSHIPS_READ))) {
                throw new Exception(null, 401);
            }
            $course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d ", $params['context_id']);
            if (empty($course) || empty($course->id) || empty($course->code)) {
                throw new Exception("Not Found: Course {$params['context_id']} doesn't exist", 404);
            }
            if (!$this->getService()->isUsedInContext($params['tool_code'], $course->id)) {
                throw new Exception(null, 404);
            }
            if (!empty($linkid)) {
                $ltiApp = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $linkid);
                if (empty($ltiApp)) {
                    throw new Exception("Not Found: LTI link $linkid doesn't exist", 404);
                }
            }

            $json = $this->getService()->getMembersJson($this, $course, $role, $limitfrom, $limitnum, $ltiApp, $response);

            $response->setBody($json);
        } catch (Exception $e) {
            $response->setCode($e->getCode());
            $response->setReason($e->getMessage());
        }
    }

    /**
     * Parse a value for custom parameter substitution variables.
     *
     * @param string $value String to be parsed
     *
     * @return string
     */
    public function parseValue(string $value): string {
        global $course_id;

        if (strpos($value, '$ToolProxyBinding.memberships.url') !== false) {
            $this->params['context_type'] = 'CourseSection';
            $this->params['context_id'] = $course_id;
            if ($ltiApp = $this->getService()->getLtiApp()) {
                $this->params['tool_code'] = $ltiApp->id;
            }
            $value = str_replace('$ToolProxyBinding.memberships.url', parent::getEndpoint(), $value);
        }

        return $value;
    }

}