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
require_once 'modules/lti/classes/LtiBasicOutcomes.php';

/**
 * A service implementing Basic Outcomes.
 */
class LtiServiceBasicOutcomes extends LtiServiceBase {

    // Scope for accessing the service
    const SCOPE_BASIC_OUTCOMES = 'https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome';

    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->id = 'basicoutcomes';
        $this->name = 'Basic Outcomes';
    }

    /**
     * Get the resources for this service.
     *
     * @return array
     */
    public function getResources(): array {
        if (empty($this->resources)) {
            $this->resources = array();
            $this->resources[] = new LtiBasicOutcomes($this);
        }

        return $this->resources;
    }

    /**
     * Get the scope(s) permitted for this service.
     *
     * @return array|null
     */
    public function getPermittedScopes(): ?array {
        return [self::SCOPE_BASIC_OUTCOMES];
    }

    /**
     * Get the scopes defined by this service.
     *
     * @return array|null
     */
    public function getScopes(): ?array {
        return [self::SCOPE_BASIC_OUTCOMES];
    }

    /**
     * Return an array of key/claim mapping allowing LTI 1.1 custom parameters to be transformed to LTI 1.3 claims.
     *
     * @return array Key/value pairs of params to claim mapping.
     */
    public function getJwtClaimMappings(): array {
        return [
            'lis_outcome_service_url' => [
                'suffix' => 'bo',
                'group' => 'basicoutcome',
                'claim' => 'lis_outcome_service_url',
                'isarray' => false
            ],
            'lis_result_sourcedid' => [
                'suffix' => 'bo',
                'group' => 'basicoutcome',
                'claim' => 'lis_result_sourcedid',
                'isarray' => false
            ]
        ];
    }

}