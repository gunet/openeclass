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
require_once 'modules/lti/classes/LtiServiceBasicOutcomes.php';
require_once 'modules/lti/classes/LtiServiceResponse.php';

/**
 * A resource implementing the Basic Outcomes service.
 */
class LtiBasicOutcomes extends LtiResourceBase {

    /**
     * Class constructor.
     *
     * @param LtiServiceBasicOutcomes $service Service instance
     */
    public function __construct(LtiServiceBasicOutcomes $service) {
        parent::__construct($service);
        $this->id = 'Outcomes.LTI1';
        $this->template = '';
        $this->formats[] = 'application/vnd.ims.lti.v1.outcome+xml';
        $this->methods[] = 'POST';
    }

    /**
     * Get the resource fully qualified endpoint.
     *
     * @return string
     */
    public function getEndpoint(): string {
        global $urlServer;
        return $urlServer . "modules/lti/service.php";
    }

    /**
     * Execute the request for this resource.
     *
     * @param LtiServiceResponse $response Response object for this request.
     */
    public function execute(LtiServiceResponse $response): void {
        // Should never be called as the endpoint sends requests to the LTI 1 service endpoint.
    }

}