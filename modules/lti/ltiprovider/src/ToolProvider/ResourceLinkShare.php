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

namespace IMSGlobal\LTI\ToolProvider;

/**
 * Class to represent a tool consumer resource link share
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.0
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class ResourceLinkShare
{

/**
 * Consumer key value.
 *
 * @var string $consumerKey
 */
    public $consumerKey = null;
/**
 * Resource link ID value.
 *
 * @var string $resourceLinkId
 */
    public $resourceLinkId = null;
/**
 * Title of sharing context.
 *
 * @var string $title
 */
    public $title = null;
/**
 * Whether sharing request is to be automatically approved on first use.
 *
 * @var boolean $approved
 */
    public $approved = null;

/**
 * Class constructor.
 */
    public function __construct()
    {
    }

}
