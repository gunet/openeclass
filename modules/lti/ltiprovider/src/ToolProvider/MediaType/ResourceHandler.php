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

namespace IMSGlobal\LTI\ToolProvider\MediaType;
use IMSGlobal\LTI\ToolProvider\ToolProvider;
use IMSGlobal\LTI\Profile\ResourceHandler as ProfileResourceHandler;

/**
 * Class to represent an LTI Resource Handler
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version  3.0.0
 * @license  GNU Lesser General Public License, version 3 (<http://www.gnu.org/licenses/lgpl.html>)
 */
class ResourceHandler
{

/**
 * Class constructor.
 *
 * @param ToolProvider $toolProvider   Tool Provider object
 * @param ProfileResourceHandler $resourceHandler   Resource handler object
 */
    function __construct($toolProvider, $resourceHandler)
    {

        $this->resource_type = new \stdClass;
        $this->resource_type->code = $resourceHandler->item->id;
        $this->resource_name = new \stdClass;
        $this->resource_name->default_value = $resourceHandler->item->name;
        $this->resource_name->key = "{$resourceHandler->item->id}.resource.name";
        $this->description = new \stdClass;
        $this->description->default_value = $resourceHandler->item->description;
        $this->description->key = "{$resourceHandler->item->id}.resource.description";
        $this->icon_info = new \stdClass;
        $this->icon_info->default_location = new \stdClass;
        $this->icon_info->default_location->path = $resourceHandler->icon;
        $this->icon_info->key = "{$resourceHandler->item->id}.icon.path";
        $this->message = array();
        foreach ($resourceHandler->requiredMessages as $message) {
            $this->message[] = new Message($message, $toolProvider->consumer->profile->capability_offered);
        }
        foreach ($resourceHandler->optionalMessages as $message) {
            if (in_array($message->type, $toolProvider->consumer->profile->capability_offered)) {
                $this->message[] = new Message($message, $toolProvider->consumer->profile->capability_offered);
            }
        }

    }

}
