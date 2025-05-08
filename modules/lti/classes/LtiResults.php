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
require_once 'modules/lti/classes/LtiLineItem.php';

/**
 * A resource implementing LISResult container.
 */
class LtiResults extends LtiResourceBase {

    /**
     * Class constructor.
     *
     * @param LtiServiceGradebookServices $service Service instance
     */
    public function __construct($service) {
        parent::__construct($service);
        $this->id = 'Result.collection';
        $this->template = '/{context_id}/lineitems/{resource_id}/lineitem/results';
        $this->variables[] = 'Results.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.resultcontainer+json';
        $this->methods[] = 'GET';
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

        $isget = $response->getRequestMethod() === self::HTTP_GET;
        $ltiAppId = (isset($_REQUEST['lti_app_id'])) ? $_REQUEST['lti_app_id'] : null;

        $scope = LtiServiceGradebookServices::SCOPE_GRADEBOOKSERVICES_RESULT_READ;

        try {
            if (!$this->checkTool($ltiAppId, $response->getRequestData(), array($scope))) {
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
                    $useridfilter = (isset($_REQUEST['user_id'])) ? $_REQUEST['user_id'] : 0;
                    $limitnum = (isset($_REQUEST['limit'])) ? $_REQUEST['limit'] : 0;
                    $limitfrom = (isset($_REQUEST['from'])) ? $_REQUEST['from'] : 0;
                    $json = $this->getJsonForGetRequest($item->id, $limitfrom, $limitnum, $useridfilter, $ltiAppId, $response);
                    $response->setContentType($this->formats[0]);
                    $response->setBody($json);
                    break;
                default:  // Should not be possible.
                    $response->setCode(405);
                    $response->setReason("Invalid request method specified.");
                    return;
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
     * @param int $itemid        Grade item instance ID
     * @param int $limitfrom     Offset for the first result to include in this paged set
     * @param int $limitnum      Maximum number of results to include in the response, ignored if zero
     * @param int $useridfilter  The user id to filter the results.
     * @param int $ltiAppId      Lti tool typeid (or null)
     * @param LtiServiceResponse $response   The response element needed to add a header.
     *
     * @return string
     */
    private function getJsonForGetRequest(int $itemid, int $limitfrom, int $limitnum, int $useridfilter, int $ltiAppId, LtiServiceResponse $response): string {

        if ($useridfilter > 0) {
            $assignmentSubmits = Database::get()->queryArray("SELECT * FROM assignment_submit WHERE assignment_id = ?d AND uid = ?d", $itemid, $useridfilter);
        } else {
            $assignmentSubmits = Database::get()->queryArray("SELECT * FROM assignment_submit WHERE assignment_id = ?d", $itemid);
        }
        $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $itemid);

        $firstpage = null;
        $nextpage = null;
        $prevpage = null;
        $lastpage = null;

        if ($assignmentSubmits && $limitnum > 0) {
            // Since we only display grades that have been modified, we need to filter first in order to support paging.
            $resultsubmits = array_filter($assignmentSubmits, function ($assignmentSubmit) {
                return !empty($assignmentSubmit->grade_submission_date);
            });
            // We save the total count to calculate the last page.
            $totalcount = count($resultsubmits);
            // We slice to the requested item offset to ensure proper item is always first, and we always return first pageset of any remaining items.
            $assignmentSubmits = array_slice($resultsubmits, $limitfrom);
            if (count($assignmentSubmits) > 0) {
                $pagedsubmits = array_chunk($assignmentSubmits, $limitnum);
                $pageset = 0;
                $assignmentSubmits = $pagedsubmits[$pageset];
            }
            if ($limitfrom >= $totalcount || $limitfrom < 0) {
                $outofrange = true;
            } else {
                $outofrange = false;
            }
            $limitprev = $limitfrom - $limitnum >= 0 ? $limitfrom - $limitnum : 0;
            $limitcurrent = $limitfrom;
            $limitlast = $totalcount - $limitnum + 1 >= 0 ? $totalcount - $limitnum + 1 : 0;
            $limitfrom += $limitnum;

            $baseurl = $this->getEndpoint();
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

        $jsonresults = [];
        $lineitem = new LtiLineItem($this->getService());
        $endpoint = $lineitem->getEndpoint();
        if ($assignmentSubmits) {
            foreach ($assignmentSubmits as $assignmentSubmit) {
                if (!empty($assignmentSubmit->grade_submission_date)) {
                    $jsonresults[] = LtiServiceGradebookServices::resultForJson($assignment, $assignmentSubmit, $endpoint, $ltiAppId);
                }
            }
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
        return json_encode($jsonresults);
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

        if (strpos($value, '$Results.url') !== false) {
            $resolved = '';
            $this->params['context_id'] = $course_id;
            $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : 0;
            if (!empty($id)) {
                $grades = Database::get()->queryArray("SELECT s.* FROM assignment a LEFT JOIN assignment_submit s ON (a.id = s.assignment_id) WHERE s.assignment_id = ?d AND a.course_id = ?d", $id, $course_id);
                if (count($grades) > 0) {
                    $this->params['item_id'] = $grades[0]->id;
                    $resolved = parent::getEndpoint();
                }
            }
            $value = str_replace('$Results.url', $resolved, $value);
        }
        return $value;
    }

}