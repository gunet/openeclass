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
 * A resource implementing LISResult container.
 */
class LtiScores extends LtiResourceBase {

    /**
     * Class constructor.
     *
     * @param LtiServiceGradebookServices $service Service instance
     */
    public function __construct($service) {
        parent::__construct($service);
        $this->id = 'Score.collection';
        $this->template = '/{context_id}/lineitems/{resource_id}/lineitem/scores';
        $this->variables[] = 'Scores.url';
        $this->formats[] = 'application/vnd.ims.lis.v1.scorecontainer+json';
        $this->formats[] = 'application/vnd.ims.lis.v1.score+json';
        $this->methods[] = self::HTTP_POST;
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
        if ($isget) {
            $contenttype = $response->getAccept();
        } else {
            $contenttype = $response->getContentType();
        }
        $container = empty($contenttype) || ($contenttype === $this->formats[0]);
        $ltiAppId = (isset($_REQUEST['lti_app_id'])) ? $_REQUEST['lti_app_id'] : null;

        $scope = LtiServiceGradebookServices::SCOPE_GRADEBOOKSERVICES_SCORE;

        try {
            if (!$this->checkTool($ltiAppId, $response->getRequestData(), array($scope))) {
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
            $item = $this->getService()->getLineitem($contextid, $resourceid, $ltiAppId);
            if ($item === false) {
                throw new Exception("Line item does not exist: Resource assignment $resourceid doesn't exist", 404);
            }
            $json = '[]';
            switch ($response->getRequestMethod()) {
                case self::HTTP_GET:
                    $response->setCode(405);
                    $response->setReason("GET requests are not allowed.");
                    break;
                case self::HTTP_POST:
                    try {
                        $this->processPostRequest($response, $response->getRequestData(), $item, $contextid, $ltiAppId);
                        $response->setContentType($this->formats[1]);
                    } catch (Exception $e) {
                        $response->setCode($e->getCode());
                        $response->setReason($e->getMessage());
                    }
                    break;
                default:  // Should not be possible.
                    $response->setCode(405);
                    $response->setReason("Invalid request method specified.");
                    break;
            }
            $response->setBody($json);
        } catch (Exception $e) {
            $response->setCode($e->getCode());
            $response->setReason($e->getMessage());
        }
    }

    /**
     * Process the scoring POST request.
     *
     * @param LtiServiceResponse $response Response object for this request.
     * @param string   $body   POST body
     * @param stdClass $item   Grade item instance
     * @param string   $contextid
     * @param string   $ltiAppId
     *
     * @throws Exception
     */
    private function processPostRequest(LtiServiceResponse $response, string $body, stdClass $item, string $contextid, string $ltiAppId): void {
        $score = json_decode($body);
        if (empty($score) ||
                !isset($score->userId) ||
                !isset($score->timestamp) ||
                !isset($score->gradingProgress) ||
                !isset($score->activityProgress) ||
                !LtiServiceGradebookServices::validateIso8601Date($score->timestamp) ||
                (isset($score->scoreGiven) && !is_numeric($score->scoreGiven)) ||
                (isset($score->scoreGiven) && !isset($score->scoreMaximum)) ||
                (isset($score->scoreMaximum) && !is_numeric($score->scoreMaximum)) ||
                (!LtiServiceGradebookServices::isUserGradableInCourse($contextid, $score->userId))
                ) {
            throw new Exception('LtiScores Error: Incorrect score received ' . $body, 400);
        }

        if (!isset($score->scoreMaximum)) {
            $score->scoreMaximum = 1;
        }
        $response->setCode(200);

        $assignmentSubmit = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE uid = ?d AND assignment_id = ?d", $score->userId, $item->id);
        if ($assignmentSubmit && !empty($assignmentSubmit->grade_submission_date)) {
            if (strtotime($assignmentSubmit->grade_submission_date) >= strtotime($score->timestamp)) {
                $exmsg = "Refusing score with an earlier timestamp for item " . $item->id . " and user " . $score->userId;
                throw new Exception($exmsg, 409);
            }
        }
        if (isset($score->scoreGiven)) {
            if ($score->gradingProgress != 'FullyGraded') {
                $score->scoreGiven = null;
            }
        }

        // finalize and save grade
        require_once 'modules/work/functions.php';
        require_once 'modules/progress/AssignmentEvent.php';
        require_once 'modules/analytics/AssignmentAnalyticsEvent.php';
        require_once 'include/log.class.php';

        $finalgrade = null;
        if (isset($score->scoreGiven)) {
            $finalgrade = round($score->scoreGiven, 5);
        }
        $timemodified = strtotime($score->timestamp);
        $timemodifiedFormatted = date('Y-m-d H:i:s', $timemodified);
        $datemodifiedFormatted = date('Y-m-d', $timemodified);

        if (!$assignmentSubmit) {
            Database::get()->query("INSERT INTO assignment_submit
                                    (uid, assignment_id, submission_date, submission_ip, comments, grade, grade_comments, grade_submission_date)
                                     VALUES (?d, ?d, ?t, ?s, '', ?f, '', ?t)",
                $score->userId, $item->id, $timemodifiedFormatted, Log::get_client_ip(), $finalgrade, $datemodifiedFormatted);
        } else {
            Database::get()->query("UPDATE assignment_submit SET grade = ?f, grade_submission_date = ?t, submission_ip = ?s WHERE uid = ?d AND assignment_id = ?d",
                $finalgrade, $datemodifiedFormatted, Log::get_client_ip(), $score->userId, $item->id);
        }

        triggerGame($contextid, $score->userId, $item->id);
        triggerAssignmentAnalytics($contextid, $score->userId, $item->id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
        triggerAssignmentAnalytics($contextid, $score->userId, $item->id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
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

        if (strpos($value, '$Scores.url') !== false) {
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
            $value = str_replace('$Scores.url', $resolved, $value);
        }

        return $value;
    }

}