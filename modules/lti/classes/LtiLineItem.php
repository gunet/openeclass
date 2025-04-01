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
require_once 'modules/lti/classes/LtiServiceGradebookServices.php';
require_once 'modules/lti/classes/LtiServiceResponse.php';

/**
 * A resource implementing LineItem.
 */
class LtiLineItem extends LtiResourceBase {

    /**
     * Class constructor.
     *
     * @param LtiServiceGradebookServices $service Service instance
     */
    public function __construct($service) {
        parent::__construct($service);
        $this->id = 'LineItem.item';
        $this->template = '/{context_id}/lineitems/{resource_id}/lineitem';
        $this->variables[] = 'LineItem.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.lineitem+json';
        $this->methods[] = self::HTTP_GET;
        $this->methods[] = self::HTTP_PUT;
        $this->methods[] = self::HTTP_DELETE;
    }

    /**
     * Execute the request for this resource.
     *
     * @param LtiServiceResponse $response Response object for this request.
     */
    public function execute(LtiServiceResponse $response): void {
        $params = $this->parseTemplate();
        $contextid = $params['context_id'];
        $resourceid = $params['resource_id'];
        $ltiAppId = (isset($_REQUEST['lti_app_id'])) ? $_REQUEST['lti_app_id'] : null;

        $scopes = array(LtiServiceGradebookServices::SCOPE_GRADEBOOKSERVICES_LINEITEM);
        if ($response->getRequestMethod() === self::HTTP_GET) {
            $scopes[] = LtiServiceGradebookServices::SCOPE_GRADEBOOKSERVICES_LINEITEM_READ;
        }

        try {
            if (!$this->checkTool($ltiAppId, $response->getRequestData(), $scopes)) {
                throw new Exception(null, 401);
            }
            $ltiAppId = $this->getService()->getLtiApp()->id;
            $course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d ", $contextid);
            if (empty($course) || empty($course->id) || empty($course->code)) {
                throw new Exception("Not Found: Course $contextid doesn't exist", 404);
            }
            if (!$this->getService()->isUsedInContext($ltiAppId, $course->id)) {
                throw new Exception('Not used in context', 403);
            }
            $item = $this->getService()->getLineitem($contextid, $resourceid, $ltiAppId);
            if ($item === false) {
                throw new Exception("Line item does not exist: Resource assignment $resourceid doesn't exist", 404);
            }
            switch ($response->getRequestMethod()) {
                case self::HTTP_GET:
                    $this->getRequest($response, $item, $ltiAppId);
                    break;
                case self::HTTP_PUT:
                    $json = $this->processPutRequest($response->getRequestData(), $item, $ltiAppId);
                    $response->setBody($json);
                    $response->setCode(200);
                    break;
                case self::HTTP_DELETE:
                    $this->processDeleteRequest($item);
                    $response->setCode(204);
                    break;
            }
        } catch (Exception $e) {
            $response->setCode($e->getCode());
            $response->setReason($e->getMessage());
        }
    }

    /**
     * Process a GET request.
     *
     * @param LtiServiceResponse $response Response object for this request.
     * @param stdClass $item Grade item instance.
     * @param string $ltiAppId Tool Type Id
     */
    private function getRequest(LtiServiceResponse $response, stdClass $item, string $ltiAppId): void {
        $response->setContentType($this->formats[0]);
        $length = strrpos(parent::getEndpoint(), "/", -10);
        $endpoint = substr(parent::getEndpoint(), 0, $length);
        $lineitem = LtiServiceGradebookServices::itemForJson($item, $endpoint, $ltiAppId);
        $response->setBody(json_encode($lineitem));
    }

    /**
     * Process a PUT request.
     *
     * @param string   $body      PUT body
     * @param stdClass $item      Grade item instance
     * @param string   $ltiAppId  Tool Type Id
     *
     * @return string
     * @throws Exception
     */
    private function processPutRequest(string $body, stdClass $item, string $ltiAppId): string {
        $json = json_decode($body);
        if (empty($json) ||
            !isset($json->scoreMaximum) ||
            !isset($json->label)) {
            throw new Exception("LtiLineItem::processPutRequest() error: Bad PUT request json body format.", 400);
        }

        $updategradeitem = false;
        $rescalegrades = false;
        $oldgrademax = floatval($item->max_grade);

        if ($item->title !== $json->label) {
            $updategradeitem = true;
        }

        if (!is_numeric($json->scoreMaximum)) {
            throw new Exception("LtiLineItem::processPutRequest() error: Bad PUT request scoreMaximum format.", 400);
        } else {
            if ($oldgrademax !== floatval($json->scoreMaximum)) {
                $updategradeitem = true;
                $rescalegrades = true;
            }
        }

        // Additional Validations
        if (isset($json->resourceLinkId) && !is_numeric($json->resourceLinkId)) {
            throw new Exception("LtiLineItem::processPutRequest() error: Bad PUT request resourceLinkId format.", 400);
        } else if (isset($json->ltiLinkId) && !is_numeric($json->ltiLinkId)) {
            throw new Exception("LtiLineItem::processPutRequest() error: Bad PUT request ltiLinkId format.", 400);
        }

        if ($updategradeitem) {
            Database::get()->query("UPDATE assignment SET title = ?s, max_grade = ?f WHERE id = ?d", $json->label, $json->scoreMaximum, $item->id);

            if ($rescalegrades) {
                // TODO: implement rescale grades
                // error_log("LtiLineItem::processPutRequest(item_id: {$item->id}) need rescaling");
            }
        }

        $lineitem = new LtiLineItem($this->getService());
        $endpoint = $lineitem->getEndpoint();
        $json->id = "{$endpoint}?lti_app_id={$ltiAppId}";

        return json_encode($json, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Process a DELETE request.
     *
     * @param stdClass $item Grade item instance.
     */
    private function processDeleteRequest(stdClass $item): void {
        // lti requested that we delete this resource
        // TODO: do we really need to run a hard delete on our sql tables or is it safe to ignore ?
        // error_log("LtiLineItem::processDeleteRequest({$item->id})");
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

        if (strpos($value, '$LineItem.url') !== false) {
            $resolved = '';
            $this->params['context_id'] = $course_id;
            if ($ltiApp = $this->getService()->getLtiApp()) {
                $this->params['tool_code'] = $ltiApp->id;
            }
            $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : 0;
            if (empty($id)) {
                $hint = (isset($_REQUEST['lti_message_hint'])) ? $_REQUEST['lti_message_hint'] : "";
                if ($hint) {
                    $hintdec = json_decode($hint);
                    if (isset($hintdec->resourceid)) {
                        $id = $hintdec->resourceid;
                    }
                }
            }
            if (!empty($id)) {
                $grades = Database::get()->queryArray("SELECT s.* FROM assignment a LEFT JOIN assignment_submit s ON (a.id = s.assignment_id) WHERE s.assignment_id = ?d AND a.course_id = ?d", $id, $course_id);
                if (count($grades) > 0) {
                    $this->params['item_id'] = $grades[0]->id;
                    $resolved = parent::getEndpoint();
                    $resolved .= "?lti_app_id={$ltiApp->id}";
                }
            }
            $value = str_replace('$LineItem.url', $resolved, $value);
        }

        return $value;
    }

}