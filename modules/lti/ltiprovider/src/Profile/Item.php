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
 * Class to represent a generic item object
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.0
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class Item
{

/**
 * ID of item.
 *
 * @var string $id
 */
    public $id = null;
/**
 * Name of item.
 *
 * @var string $name
 */
    public $name = null;
/**
 * Description of item.
 *
 * @var string $description
 */
    public $description = null;
/**
 * URL of item.
 *
 * @var string $url
 */
    public $url = null;
/**
 * Version of item.
 *
 * @var string $version
 */
    public $version = null;
/**
 * Timestamp of item.
 *
 * @var int $timestamp
 */
    public $timestamp = null;

/**
 * Class constructor.
 *
 * @param string $id           ID of item (optional)
 * @param string $name         Name of item (optional)
 * @param string $description  Description of item (optional)
 * @param string $url          URL of item (optional)
 * @param string $version      Version of item (optional)
 * @param int    $timestamp    Timestamp of item (optional)
 */

    function __construct($id = null, $name = null, $description = null, $url = null, $version = null, $timestamp = null)
    {

        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->url = $url;
        $this->version = $version;
        $this->timestamp = $timestamp;

    }

}
