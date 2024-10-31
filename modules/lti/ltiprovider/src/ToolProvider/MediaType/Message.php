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

/**
 * Class to represent an LTI Message
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version  3.0.0
 * @license  GNU Lesser General Public License, version 3 (<http://www.gnu.org/licenses/lgpl.html>)
 */
class Message
{

/**
 * Class constructor.
 *
 * @param Message $message               Message object
 * @param array   $capabilitiesOffered   Capabilities offered
 */
    function __construct($message, $capabilitiesOffered)
    {

        $this->message_type = $message->type;
        $this->path = $message->path;
        $this->enabled_capability = array();
        foreach ($message->capabilities as $capability) {
            if (in_array($capability, $capabilitiesOffered)) {
                $this->enabled_capability[] = $capability;
            }
        }
        $this->parameter = array();
        foreach ($message->constants as $name => $value) {
            $parameter = new \stdClass;
            $parameter->name = $name;
            $parameter->fixed = $value;
            $this->parameter[] = $parameter;
        }
        foreach ($message->variables as $name => $value) {
            if (in_array($value, $capabilitiesOffered)) {
                $parameter = new \stdClass;
                $parameter->name = $name;
                $parameter->variable = $value;
                $this->parameter[] = $parameter;
            }
        }

    }

}
