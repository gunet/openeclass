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
require_once 'modules/lti/classes/LtiContextMemberships.php';
require_once 'modules/lti/classes/LtiResourceBase.php';
require_once 'modules/lti/classes/LtiServiceResponse.php';

/**
 * A service implementing Memberships.
 */
class LtiServiceMemberships extends LtiServiceBase {

    // Default prefix for context-level roles
    const CONTEXT_ROLE_PREFIX = 'http://purl.imsglobal.org/vocab/lis/v2/membership#';
    // Context-level role for Instructor
    const CONTEXT_ROLE_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor';
    // Context-level role for Learner
    const CONTEXT_ROLE_LEARNER = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner';
    // Capability used to identify Instructors
    const INSTRUCTOR_CAPABILITY = 1;
    // Always include field
    const ALWAYS_INCLUDE_FIELD = 1;
    // Allow the instructor to decide if included
    const DELEGATE_TO_INSTRUCTOR = 2;
    // Instructor chose to include field
    const INSTRUCTOR_INCLUDED = 1;
    // Instructor delegated and approved for include
    const INSTRUCTOR_DELEGATE_INCLUDED = array(self::DELEGATE_TO_INSTRUCTOR && self::INSTRUCTOR_INCLUDED);
    // Scope for reading membership data
    const SCOPE_MEMBERSHIPS_READ = 'https://purl.imsglobal.org/spec/lti-nrps/scope/contextmembership.readonly';

    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->id = 'memberships';
        $this->name = $this->getComponentId();
    }

    /**
     * Get the resources for this service.
     *
     * @return array
     */
    public function getResources(): array {
        if (empty($this->resources)) {
            $this->resources = array();
            $this->resources[] = new LtiContextMemberships($this);
        }

        return $this->resources;
    }

    /**
     * Get the scope(s) permitted for the tool relevant to this service.
     *
     * @return array|null
     */
    public function getPermittedScopes(): ?array {
        $scopes = array();
        $ok = !empty($this->getLtiApp());
        if ($ok) {
            $scopes[] = self::SCOPE_MEMBERSHIPS_READ;
        }

        return $scopes;
    }

    /**
     * Get the scope(s) defined by this service.
     *
     * @return array|null
     */
    public function getScopes(): ?array {
        return [self::SCOPE_MEMBERSHIPS_READ];
    }

    /**
     * Get the JSON for members.
     *
     * @param LtiResourceBase    $resource   Resource handling the request
     * @param stdClass           $course     Course
     * @param string             $role       User role requested (empty if none)
     * @param int                $limitfrom  Position of first record to be returned
     * @param int                $limitnum   Maximum number of records to be returned
     * @param stdClass|null      $ltiApp     LTI app record
     * @param LtiServiceResponse $response   Response object for the request
     *
     * @return string
     */
    public function getMembersJson(LtiResourceBase $resource, stdClass $course, string $role, int $limitfrom, int $limitnum, ?stdClass $ltiApp, LtiServiceResponse $response): string {
        $istutor = null;
        $exclude = array();

        if (!empty($role)) {
            if ((strpos($role, 'http://') !== 0) && (strpos($role, 'https://') !== 0)) {
                $role = self::CONTEXT_ROLE_PREFIX . $role;
            }
            if ($role === self::CONTEXT_ROLE_INSTRUCTOR) {
                $istutor = self::INSTRUCTOR_CAPABILITY;
            } else if ($role === self::CONTEXT_ROLE_LEARNER) {
                $exclude = array_keys($this->getEnrolledUsers($course, self::INSTRUCTOR_CAPABILITY, 'u.id'));
            }
        }

        $users = $this->getEnrolledUsers($course, $istutor, 'u.*');

        if (($response->getAccept() === 'application/vnd.ims.lti-nrps.v2.membershipcontainer+json') ||
            (($response->getAccept() !== 'application/vnd.ims.lis.v2.membershipcontainer+json') &&
            ($this->getLtiApp()->lti_version === LTI_VERSION_1_3))) {
            $json = $this->usersToJson($resource, $users, $course, $exclude, $limitfrom, $limitnum, $ltiApp, $response);
        } else {
            $json = $this->usersToJsonLd($resource, $users, $course, $exclude, $limitfrom, $limitnum, $ltiApp, $response);
        }

        return $json;
    }

    /**
     * Returns list of users enrolled into course.
     *
     * @param stdClass $course
     * @param int|null $istutor
     * @param string $userfields requested user record fields
     * @return array of user records
     */
    public function getEnrolledUsers(stdClass $course, int|null $istutor = null, string $userfields = 'u.*'): array {
        $sql = "SELECT $userfields FROM course_user cu JOIN user u ON (u.id = cu.user_id) WHERE cu.course_id = ?d ";
        if ($istutor === 1) {
            $sql .= " AND cu.tutor = 1";
        }

        return Database::get()->queryArray($sql, $course->id);
    }

    /**
     * Gets the IMS role string for the specified user and LTI course module.
     *
     * @param stdClass $user      User object
     * @param stdClass $course    The course object of the LTI activity
     *
     * @return string A role string suitable for passing with an LTI launch
     */
    public function ltiGetImsRole(stdClass $user, stdClass $course): string {
        $roles = array();

        $sql = "SELECT cu.* FROM course_user cu WHERE cu.course_id = ?d AND cu.user_id = ?d";
        $cu = Database::get()->querySingle($sql, $course->id, $user->id);

        if ($cu->tutor === 1) {
            $roles[] = 'Instructor';
            $roles[] = 'http://purl.imsglobal.org/vocab/lis/v2/person#Administrator';
        } else {
            $roles[] = 'Learner';
            $roles[] = self::CONTEXT_ROLE_LEARNER;
        }

        return join(',', $roles);
    }

    /**
     * Get the NRP service JSON representation of the users.
     *
     * Note that when a limit is set and the exclude array is not empty, then the number of memberships
     * returned may be less than the limit.
     *
     * @param LtiResourceBase    $resource    Resource handling the request
     * @param array              $users       Array of user records
     * @param stdClass           $course      Course object
     * @param array              $exclude     Array of user records to be excluded from the response
     * @param int                $limitfrom   Position of first record to be returned
     * @param int                $limitnum    Maximum number of records to be returned
     * @param stdClass|null      $ltiApp      LTI app instance record
     * @param LtiServiceResponse $response    Response object for the request
     *
     * @return string
     */
    private function usersToJson(LtiResourceBase $resource, array $users, stdClass $course, array $exclude, int $limitfrom, int $limitnum, ?stdClass $ltiApp, LtiServiceResponse $response): string {
        $context = new stdClass();
        $context->id = $course->id;
        $context->label = trim($course->title);
        $context->title = trim($course->title);

        $arrusers = [
            'id' => $resource->getEndpoint(),
            'context' => $context,
            'members' => []
        ];

        $n = 0;
        $more = false;
        foreach ($users as $user) {
            if (in_array($user->id, $exclude)) {
                continue;
            }
            $n++;
            if ($limitnum > 0) {
                if ($n <= $limitfrom) {
                    continue;
                }
                if (count($arrusers['members']) >= $limitnum) {
                    $more = true;
                    break;
                }
            }

            $member = new stdClass();
            $member->status = 'Active';
            $member->roles = explode(',', $this->ltiGetImsRole($user, $course));

            $includedcapabilities = [
                'User.id'              => ['type' => 'id',
                                            'member.field' => 'user_id',
                                            'source.value' => $user->id],
                'Person.sourcedId'     => ['type' => 'id',
                                            'member.field' => 'lis_person_sourcedid',
                                            'source.value' => $user->id],
                'Person.name.full'     => ['type' => 'name',
                                            'member.field' => 'name',
                                            'source.value' => "{$user->givenname} {$user->surname}"],
                'Person.name.given'    => ['type' => 'givenname',
                                            'member.field' => 'given_name',
                                            'source.value' => $user->givenname],
                'Person.name.family'   => ['type' => 'familyname',
                                            'member.field' => 'family_name',
                                            'source.value' => $user->surname],
                'Person.email.primary' => ['type' => 'email',
                                            'member.field' => 'email',
                                            'source.value' => $user->email],
                'User.username'        => ['type' => 'name',
                                           'member.field' => 'ext_user_username',
                                           'source.value' => $user->username],
            ];

            // TODO: do we need a $member->message = [$message]; for basicoutcomes and grade_items perhaps ?

            foreach ($includedcapabilities as $capabilityname => $capability) {
                $member->{$capability['member.field']} = $capability['source.value'];
            }

            $arrusers['members'][] = $member;
        }
        if ($more) {
            $nextlimitfrom = $limitfrom + $limitnum;
            $nextpage = "{$resource->getEndpoint()}?limit={$limitnum}&from={$nextlimitfrom}";
            if (!is_null( $ltiApp )) {
                $nextpage .= "&rlid={$ltiApp->id}";
            }
            $response->addAdditionalHeader("Link: <{$nextpage}>; rel=\"next\"");
        }

        $response->setContentType('application/vnd.ims.lti-nrps.v2.membershipcontainer+json');

        return json_encode($arrusers);
    }

    /**
     * Get the JSON-LD representation of the users.
     *
     * Note that when a limit is set and the exclude array is not empty, then the number of memberships
     * returned may be less than the limit.
     *
     * @param LtiResourceBase    $resource     Resource handling the request
     * @param array              $users        Array of user records
     * @param stdClass           $course       Course object
     * @param array              $exclude      Array of user records to be excluded from the response
     * @param int                $limitfrom    Position of first record to be returned
     * @param int                $limitnum     Maximum number of records to be returned
     * @param stdClass|null      $ltiApp       LTI app record
     * @param LtiServiceResponse $response     Response object for the request
     *
     * @return string
     */
    private function usersToJsonLd(LtiResourceBase $resource, array $users, stdClass $course, array $exclude, int $limitfrom, int $limitnum, ?stdClass $ltiApp, LtiServiceResponse $response): string {
        $arrusers = [
            '@context' => 'http://purl.imsglobal.org/ctx/lis/v2/MembershipContainer',
            '@type' => 'Page',
            '@id' => $resource->getEndpoint(),
        ];

        $arrusers['pageOf'] = [
            '@type' => 'LISMembershipContainer',
            'membershipSubject' => [
                '@type' => 'Context',
                'contextId' => $course->id,
                'membership' => []
            ]
        ];

        $n = 0;
        $more = false;
        foreach ($users as $user) {
            if (in_array($user->id, $exclude)) {
                continue;
            }
            $n++;
            if ($limitnum > 0) {
                if ($n <= $limitfrom) {
                    continue;
                }
                if (count($arrusers['pageOf']['membershipSubject']['membership']) >= $limitnum) {
                    $more = true;
                    break;
                }
            }

            $member = new stdClass();
            $member->{"@type" } = 'LISPerson';
            $membership = new stdClass();
            $membership->status = 'Active';
            $membership->role = explode(',', $this->ltiGetImsRole($user, $course));

            $includedcapabilities = [
                'User.id'              => ['type' => 'id',
                                            'member.field' => 'userId',
                                            'source.value' => $user->id],
                'Person.sourcedId'     => ['type' => 'id',
                                            'member.field' => 'sourcedId',
                                            'source.value' => $user->id],
                'Person.name.full'     => ['type' => 'name',
                                            'member.field' => 'name',
                                            'source.value' => "{$user->givenname} {$user->surname}"],
                'Person.name.given'    => ['type' => 'name',
                                            'member.field' => 'givenName',
                                            'source.value' => $user->givenname],
                'Person.name.family'   => ['type' => 'name',
                                            'member.field' => 'familyName',
                                            'source.value' => $user->surname],
                'Person.email.primary' => ['type' => 'email',
                                            'member.field' => 'email',
                                            'source.value' => $user->email],
                'User.username'        => ['type' => 'name',
                                           'member.field' => 'ext_user_username',
                                           'source.value' => $user->username]
            ];

            // TODO: do we need a $member->message = [$message]; for basicoutcomes and grade_items perhaps ?

            foreach ($includedcapabilities as $capabilityname => $capability) {
                $member->{$capability['member.field']} = $capability['source.value'];
            }

            $membership->member = $member;

            $arrusers['pageOf']['membershipSubject']['membership'][] = $membership;
        }
        if ($more) {
            $nextlimitfrom = $limitfrom + $limitnum;
            $nextpage = "{$resource->getEndpoint()}?limit={$limitnum}&from={$nextlimitfrom}";
            if (!is_null( $ltiApp )) {
                $nextpage .= "&rlid={$ltiApp->id}";
            }
            $arrusers['nextPage'] = $nextpage;
        }

        $response->setContentType('application/vnd.ims.lis.v2.membershipcontainer+json');

        return json_encode($arrusers);
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
            // $launchparameters['context_memberships_url'] = '$ToolProxyBinding.memberships.url';
            // $launchparameters['context_memberships_v2_url'] = '$ToolProxyBinding.memberships.url';
            $memberships_url = $urlServer . "modules/lti/services.php/CourseSection/" . $courseid . "/bindings/" . $ltiAppId . "/memberships";
            $launchparameters['context_memberships_url'] = $memberships_url;
            $launchparameters['context_memberships_v2_url'] = $memberships_url;
            $launchparameters['context_memberships_versions'] = '1.0,2.0';
        }

        return $launchparameters;
    }

    /**
     * Return an array of key/claim mapping allowing LTI 1.1 custom parameters to be transformed to LTI 1.3 claims.
     *
     * @return array Key/value pairs of params to claim mapping.
     */
    public function getJwtClaimMappings(): array {
        return [
            'custom_context_memberships_v2_url' => [
                'suffix' => 'nrps',
                'group' => 'namesroleservice',
                'claim' => 'context_memberships_url',
                'isarray' => false
            ],
            'custom_context_memberships_versions' => [
                'suffix' => 'nrps',
                'group' => 'namesroleservice',
                'claim' => 'service_versions',
                'isarray' => true
            ]
        ];
    }

}