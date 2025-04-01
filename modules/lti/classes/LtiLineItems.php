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
 * A resource implementing LineItem container.
 */
class LtiLineItems extends LtiResourceBase {

    /**
     * Class constructor.
     *
     * @param LtiServiceGradebookServices $service Service instance
     */
    public function __construct($service) {
        parent::__construct($service);
        $this->id = 'LineItem.collection';
        $this->template = '/{context_id}/lineitems';
        $this->variables[] = 'LineItems.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.lineitemcontainer+json';
        $this->formats[] = 'application/vnd.ims.lis.v2.lineitem+json';
        $this->methods[] = self::HTTP_GET;
        $this->methods[] = self::HTTP_POST;
    }

    /**
     * Execute the request for this resource.
     *
     * @param LtiServiceResponse $response  Response object for this request.
     */
    public function execute(LtiServiceResponse $response): void {
        $params = $this->parseTemplate();
        $contextid = $params['context_id'];
        $isget = $response->getRequestMethod() === self::HTTP_GET;
        if ($isget) {
            $contenttype = $response->getAccept();
        } else {
            $contenttype = $response->getContentType();
        }
        $container = empty($contenttype) || ($contenttype === $this->formats[0]);
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
            if (empty($contextid) || !($container ^ ($response->getRequestMethod() === self::HTTP_POST)) ||
                    (!empty($contenttype) && !in_array($contenttype, $this->formats))) {
                throw new Exception('No context or unsupported content type', 400);
            }
            $course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d ", $contextid);
            if (empty($course) || empty($course->id) || empty($course->code)) {
                throw new Exception("Not Found: Course $contextid doesn't exist", 404);
            }
            if (!$this->getService()->isUsedInContext($ltiAppId, $course->id)) {
                throw new Exception('Not used in context', 403);
            }
            if ($response->getRequestMethod() !== self::HTTP_POST) {
                $resourceid = (isset($_REQUEST['resource_id'])) ? $_REQUEST['resource_id'] : null;
                $ltilinkid = (isset($_REQUEST['resource_link_id'])) ? $_REQUEST['resource_link_id'] : null;
                if (is_null($ltilinkid)) {
                    $ltilinkid = (isset($_REQUEST['lti_link_id'])) ? $_REQUEST['lti_link_id'] : null;
                }
                $tag = (isset($_REQUEST['tag'])) ? $_REQUEST['tag'] : null;
                $limitnum = (isset($_REQUEST['limit'])) ? $_REQUEST['limit'] : 0;
                $limitfrom = (isset($_REQUEST['from'])) ? $_REQUEST['from'] : 0;
                $itemsandcount = $this->getService()->getLineitems($contextid, $resourceid, $ltilinkid, $tag, $limitfrom, $limitnum, $ltiAppId);
                $items = $itemsandcount[1];
                $totalcount = $itemsandcount[0];
                $json = $this->getJsonForGetRequest($items, $resourceid, $ltilinkid, $tag, $limitfrom, $limitnum, $totalcount, $ltiAppId, $response);
                $response->setContentType($this->formats[0]);
            } else {
                $json = $this->getJsonForPostRequest($response->getRequestData(), $contextid, $ltiAppId);
                $response->setCode(201);
                $response->setContentType($this->formats[1]);
            }
            $response->setBody($json);
        } catch (Exception $e) {
            $response->setCode($e->getCode());
            $response->setReason($e->getMessage());
        }
    }

    /**
     * Generate the JSON for a GET request.
     *
     * @param array       $items       Array of lineitems
     * @param string|null $resourceid  Resource identifier used for filtering
     * @param string|null $ltilinkid   Resource Link identifier used for filtering
     * @param string|null $tag         Tag identifier used for filtering
     * @param int         $limitfrom   Offset of the first line item to return
     * @param int         $limitnum    Maximum number of line items to return, ignored if zero or less
     * @param int         $totalcount  Number of total lineitems before filtering for paging
     * @param int         $ltiAppId
     * @param LtiServiceResponse $response
     *
     * @return string
     */
    private function getJsonForGetRequest(array $items, ?string $resourceid, ?string $ltilinkid, ?string $tag, int $limitfrom, int $limitnum, int $totalcount, int $ltiAppId, LtiServiceResponse $response): string {
        $firstpage = null;
        $nextpage = null;
        $prevpage = null;
        $lastpage = null;

        if ($limitnum > 0) {
            if ($limitfrom >= $totalcount || $limitfrom < 0) {
                $outofrange = true;
            } else {
                $outofrange = false;
            }

            $limitprev = max($limitfrom - $limitnum, 0);
            $limitcurrent = $limitfrom;
            $limitlast = max($totalcount - $limitnum + 1, 0);
            $limitfrom += $limitnum;

            $baseurl = $this->getEndpoint();
            if (isset($resourceid)) {
                $baseurl = self::appendGetParam($baseurl, "resource_id", $resourceid);
            }
            if (isset($ltilinkid)) {
                $baseurl = self::appendGetParam($baseurl, "resource_link_id", $ltilinkid);
            }
            if (isset($tag)) {
                $baseurl = self::appendGetParam($baseurl, "tag", $tag);
            }

            $baseurl = self::appendGetParam($baseurl, "lti_app_id", $ltiAppId);
            $baseurl = self::appendGetParam($baseurl, "limit", $limitnum);
            if (($limitfrom <= $totalcount - 1) && (!$outofrange)) {
                $nextpage = self::appendGetParam($baseurl, "from", $limitfrom);
            }
            $firstpage = self::appendGetParam($baseurl, "from", 0);
            $canonicalpage = self::appendGetParam($baseurl, "from", $limitcurrent);
            $lastpage = self::appendGetParam($baseurl, "from", $limitlast);
            if (($limitcurrent > 0) && (!$outofrange)) {
                $prevpage = self::appendGetParam($baseurl, "from", $limitprev);
            }
        }

        $jsonitems = [];
        $endpoint = parent::getEndpoint();
        foreach ($items as $item) {
            $jsonitems[] = LtiServiceGradebookServices::itemForJson($item, $endpoint, $ltiAppId);
        }

        if (isset($canonicalpage) && ($canonicalpage)) {
            $links = 'Link: <' . $firstpage . '>; rel=“first”';
            if (!is_null($prevpage)) {
                $links .= ', <' . $prevpage . '>; rel=“prev”';
            }
            $links .= ', <' . $canonicalpage . '>; rel=“canonical”';
            if (!is_null($nextpage)) {
                $links .= ', <' . $nextpage . '>; rel=“next”';
            }
            $links .= ', <' . $lastpage . '>; rel=“last”';
            $response->addAdditionalHeader($links);
        }
        return json_encode($jsonitems);
    }

    /**
     * Generate the JSON for a POST request.
     *
     * @param string $body POST body
     * @param string $contextid Course ID
     * @param string $ltiAppId
     *
     * @return string
     * @throws Exception
     */
    private function getJsonForPostRequest(string $body, string $contextid, string $ltiAppId): string {
        $json = json_decode($body);
        if (empty($json) ||
                !isset($json->scoreMaximum) ||
                !isset($json->label)) {
            throw new Exception('No label or Score Maximum', 400);
        }
        $resourceid = (isset($json->resourceId)) ? $json->resourceId : '';
        $json->id = parent::getEndpoint() . "/{$resourceid}/lineitem?lti_app_id={$ltiAppId}";
        return json_encode($json, JSON_UNESCAPED_SLASHES);
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

        if (strpos($value, '$LineItems.url') !== false) {
            $this->params['context_id'] = $course_id;
            $query = '';
            if ($ltiApp = $this->getService()->getLtiApp()) {
                $query = "?lti_app_id={$ltiApp->id}";
            }
            $value = str_replace('$LineItems.url', parent::getEndpoint() . $query, $value);
        }

        return $value;
    }

}