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
 * Class to represent a content-item placement object
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.2
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class ContentItemPlacement
{

/**
 * Class constructor.
 *
 * @param int $displayWidth       Width of item location
 * @param int $displayHeight      Height of item location
 * @param string $documentTarget  Location to open content in
 * @param string $windowTarget    Name of window target
 */
    function __construct($displayWidth, $displayHeight, $documentTarget, $windowTarget)
    {

        if (!empty($displayWidth)) {
            $this->displayWidth = $displayWidth;
        }
        if (!empty($displayHeight)) {
            $this->displayHeight = $displayHeight;
        }
        if (!empty($documentTarget)) {
            $this->documentTarget = $documentTarget;
        }
        if (!empty($windowTarget)) {
            $this->windowTarget = $windowTarget;
        }

    }

}
