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
 * Class to represent a resource handler object
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.0
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

class ResourceHandler
{

/**
 * General details of resource handler.
 *
 * @var Item $item
 */
    public $item = null;
/**
 * URL of icon.
 *
 * @var string $icon
 */
    public $icon = null;
/**
 * Required Message objects for resource handler.
 *
 * @var array $requiredMessages
 */
    public $requiredMessages = null;
/**
 * Optional Message objects for resource handler.
 *
 * @var array $optionalMessages
 */
    public $optionalMessages = null;

/**
 * Class constructor.
 *
 * @param Item      $item      General details of resource handler
 * @param string    $icon      URL of icon
 * @param array     $requiredMessages  Array of required Message objects for resource handler
 * @param array     $optionalMessages  Array of optional Message objects for resource handler
 */
    function __construct($item, $icon, $requiredMessages, $optionalMessages)
    {

        $this->item = $item;
        $this->icon = $icon;
        $this->requiredMessages = $requiredMessages;
        $this->optionalMessages = $optionalMessages;

    }

}
