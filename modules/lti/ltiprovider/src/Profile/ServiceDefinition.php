<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

namespace IMSGlobal\LTI\Profile;

/**
 * Class to represent an LTI service object
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.0
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

class ServiceDefinition
{

/**
 * Media types supported by service.
 *
 * @var array $formats
 */
    public $formats = null;
/**
 * HTTP actions accepted by service.
 *
 * @var array $actions
 */
    public $actions = null;
/**
 * ID of service.
 *
 * @var string $id
 */
    public $id = null;
/**
 * URL for service requests.
 *
 * @var string $endpoint
 */
    public $endpoint = null;

/**
 * Class constructor.
 *
 * @param array  $formats   Array of media types supported by service
 * @param array  $actions   Array of HTTP actions accepted by service
 * @param string $id        ID of service (optional)
 * @param string $endpoint  URL for service requests (optional)
 */

    function __construct($formats, $actions, $id = null, $endpoint = null)
    {

        $this->formats = $formats;
        $this->actions = $actions;
        $this->id = $id;
        $this->endpoint = $endpoint;

    }

    function setId($id) {

        $this->id = $id;

    }

}
