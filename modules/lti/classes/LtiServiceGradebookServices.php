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
require_once 'modules/lti/classes/LtiLineItem.php';
require_once 'modules/lti/classes/LtiLineItems.php';
require_once 'modules/lti/classes/LtiScores.php';
require_once 'modules/lti/classes/LtiResults.php';

/**
 * A service implementing LTI Gradebook Services.
 */
class LtiServiceGradebookServices extends LtiServiceBase {

    // Read-only access to Gradebook services
    const GRADEBOOKSERVICES_READ = 1;
    // Full access to Gradebook services
    const GRADEBOOKSERVICES_FULL = 2;
    // Scope for full access to Lineitem service
    const SCOPE_GRADEBOOKSERVICES_LINEITEM = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem';
    // Scope for full access to Lineitem service
    const SCOPE_GRADEBOOKSERVICES_LINEITEM_READ = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly';
    // Scope for access to Result service
    const SCOPE_GRADEBOOKSERVICES_RESULT_READ = 'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly';
    // Scope for access to Score service
    const SCOPE_GRADEBOOKSERVICES_SCORE = 'https://purl.imsglobal.org/spec/lti-ags/scope/score';

    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->id = 'gradebookservices';
        $this->name = $this->getComponentId();
    }

    /**
     * Get the resources for this service.
     *
     * @return array
     */
    public function getResources(): array {
        // The containers should be ordered in the array after their elements.
        // Lineitems should be after lineitem.
        if (empty($this->resources)) {
            $this->resources = array();
            $this->resources[] = new LtiLineItem($this);
            $this->resources[] = new LtiLineItems($this);
            $this->resources[] = new LtiResults($this);
            $this->resources[] = new LtiScores($this);
        }

        return $this->resources;
    }

    /**
     * Get the scope(s) permitted for this service.
     *
     * @return array|null
     */
    public function getPermittedScopes(): ?array {
        $scopes = array();
        // $ok = !empty($this->getLtiApp());
        // if ($ok) {
        $scopes[] = self::SCOPE_GRADEBOOKSERVICES_LINEITEM_READ;
        $scopes[] = self::SCOPE_GRADEBOOKSERVICES_RESULT_READ;
        $scopes[] = self::SCOPE_GRADEBOOKSERVICES_SCORE;
        $scopes[] = self::SCOPE_GRADEBOOKSERVICES_LINEITEM;
        // }

        return $scopes;
    }

    /**
     * Get the scopes defined by this service.
     *
     * @return array|null
     */
    public function getScopes(): ?array {
        return [
            self::SCOPE_GRADEBOOKSERVICES_LINEITEM_READ,
            self::SCOPE_GRADEBOOKSERVICES_RESULT_READ,
            self::SCOPE_GRADEBOOKSERVICES_SCORE,
            self::SCOPE_GRADEBOOKSERVICES_LINEITEM
        ];
    }

    /**
     * Return an array of key/claim mapping allowing LTI 1.1 custom parameters to be transformed to LTI 1.3 claims.
     *
     * @return array Key/value pairs of params to claim mapping.
     */
    public function getJwtClaimMappings(): array {
        return [
            'custom_gradebookservices_scope' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'scope',
                'isarray' => true
            ],
            'custom_lineitems_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'lineitems',
                'isarray' => false
            ],
            'custom_lineitem_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'lineitem',
                'isarray' => false
            ],
            'custom_results_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'results',
                'isarray' => false
            ],
            'custom_result_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'result',
                'isarray' => false
            ],
            'custom_scores_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'scores',
                'isarray' => false
            ],
            'custom_score_url' => [
                'suffix' => 'ags',
                'group' => 'endpoint',
                'claim' => 'score',
                'isarray' => false
            ]
        ];
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
        global $urlServer;
        $launchparameters = [];

        if ($this->isUsedInContext($ltiAppId, $courseid)) {
            $launchparameters['gradebookservices_scope'] = implode(',', $this->getPermittedScopes());
            // $launchparameters['lineitems_url'] = '$LineItems.url';
            // $launchparameters['lineitem_url'] = '$LineItem.url';
            $launchparameters['lineitems_url'] = $urlServer . "modules/lti/services.php/" . $courseid . "/lineitems?lti_app_id=". $ltiAppId;
            if (!is_null($resourceid)) {
                $launchparameters['lineitem_url'] = $urlServer . "modules/lti/services.php/" . $courseid . "/lineitems/" . $resourceid . "/lineitem?lti_app_id=" . $ltiAppId;
            }
        }

        return $launchparameters;
    }

    /**
     * Fetch the lineitem instances.
     *
     * @param string      $courseid   ID of course
     * @param string|null $resourceid Resource identifier used for filtering
     * @param string|null $ltilinkid  Resource Link identifier used for filtering
     * @param string|null $tag
     * @param int         $limitfrom  Offset for the first line item to include in a paged set
     * @param int         $limitnum   Maximum number of line items to include in the paged set
     * @param string      $ltiAppId   The tool lti app id.
     *
     * @return array
     */
    public function getLineitems(string $courseid, ?string $resourceid, ?string $ltilinkid, ?string $tag, int $limitfrom, int $limitnum, string $ltiAppId): array {
        $sql = "SELECT a.* FROM assignment a WHERE a.course_id = ?d AND a.lti_template = ?d";
        $lineitemstoreturn = Database::get()->queryArray($sql, $courseid, $ltiAppId);

        if (!is_array($lineitemstoreturn)) {
            $lineitemstoreturn = array();
        }
        $lineitemsandtotalcount = array();

        // limit results if necessary
        $lineitemsandtotalcount[] = count($lineitemstoreturn);
        if (($limitnum) && ($limitnum > 0)) {
            $lineitemstoreturn = array_slice($lineitemstoreturn, $limitfrom, $limitnum);
        }
        $lineitemsandtotalcount[] = $lineitemstoreturn;

        return $lineitemsandtotalcount;
    }

    /**
     * Fetch a lineitem instance.
     *
     * Returns the lineitem instance if found, otherwise false.
     *
     * @param string $courseid ID of course
     * @param string $itemid   ID of lineitem
     * @param string $ltiAppId The tool lti app id.
     *
     * @return stdClass|bool
     */
    public function getLineitem(string $courseid, string $itemid, string $ltiAppId): stdClass|bool {
        $lineitem = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d AND course_id = ?d AND lti_template = ?d", $itemid, $courseid, $ltiAppId);
        if (empty($lineitem)) {
            return false;
        }
        return $lineitem;
    }

    /**
     * Get the json object representation of the grade item
     *
     * @param stdClass  $item      Grade Item record
     * @param string    $endpoint  Endpoint for lineitems container request
     * @param string    $ltiAppId  The tool lti app id.
     *
     * @return stdClass
     */
    public static function itemForJson(stdClass $item, string $endpoint, string $ltiAppId): stdClass {
        $lineitem = new stdClass();
        $lineitem->id = "{$endpoint}/{$item->id}/lineitem?lti_app_id={$ltiAppId}";
        $lineitem->label = $item->title;
        $lineitem->scoreMaximum = floatval($item->max_grade);
        $lineitem->resourceId = '';
        $lineitem->tag = '';
        $lineitem->resourceLinkId = strval($item->id);
        $lineitem->ltiLinkId = strval($item->id);

        if (!empty($item->tii_instructorcustomparameters)) {
            $customParams = ltiSplitParameters($item->tii_instructorcustomparameters);
            if (isset($customParams['default_maxpoints']) && floatval($customParams['default_maxpoints']) > 0.0) {
                $lineitem->scoreMaximum = floatval($customParams['default_maxpoints']);
            }
            if (isset($customParams['default_peermark']) && intval($customParams['default_peermark']) === 1) {
                $lineitem->tag = "grade";
            }
        }

        return $lineitem;
    }

    /**
     * Get the object matching the JSON representation of the result.
     *
     * @param stdClass $assignment  Assignment record
     * @param stdClass $assignmentSubmit  Submit record
     * @param string   $endpoint  Endpoint for lineitem
     * @param int|null $ltiAppId  The tool lti app id to include in the result url.
     *
     * @return stdClass
     */
    public static function resultForJson(stdClass $assignment, stdClass $assignmentSubmit, string $endpoint, ?int $ltiAppId): stdClass {
        if (is_null($ltiAppId)) {
            $id = "{$endpoint}/results?user_id={$assignmentSubmit->uid}";
        } else {
            $id = "{$endpoint}/results?lti_app_id={$ltiAppId}&user_id={$assignmentSubmit->uid}";
        }

        $result = new stdClass();
        $result->id = $id;
        $result->userId = $assignmentSubmit->uid;

        if (!empty($assignmentSubmit->grade)) {
            $result->resultScore = floatval($assignmentSubmit->grade);
            $result->resultMaximum = floatval($assignment->max_grade);
            if (!empty($assignmentSubmit->grade_comments)) {
                $result->comment = $assignmentSubmit->grade_comments;
            }
            if (is_null($ltiAppId)) {
                $result->scoreOf = $endpoint;
            } else {
                $result->scoreOf = "{$endpoint}?lti_app_id={$ltiAppId}";
            }
            $result->timestamp = date('c', strtotime($assignmentSubmit->grade_submission_date));
        }

        return $result;
    }

    /**
     * Check if a user can be graded in a course.
     *
     * @param int $courseid The course
     * @param int $userid The user
     * @return bool
     */
    public static function isUserGradableInCourse(int $courseid, int $userid): bool {
        $gradableuser = false;
        $is_enrolled = Database::get()->querySingle("SELECT count(user_id) AS is_enrolled FROM course_user WHERE course_id = ?d AND user_id = ?d AND status = 5", $courseid, $userid)->is_enrolled;
        if ($is_enrolled > 0) {
            $gradableuser = true;
        }
        return $gradableuser;
    }

    /**
     * Validates specific ISO 8601 format of the timestamps.
     *
     * @param string $date The timestamp to check.
     * @return boolean true or false if the date matches the format.
     */
    public static function validateIso8601Date(string $date): bool {
        if (preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])' .
                '(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))' .
                '([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)' .
                '?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $date) > 0) {
            return true;
        } else {
            return false;
        }
    }

}