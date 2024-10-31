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
 * Class to represent an outcome
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.2
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class Outcome
{

/**
 * Language value.
 *
 * @var string $language
 */
    public $language = null;
/**
 * Outcome status value.
 *
 * @var string $status
 */
    public $status = null;
/**
 * Outcome date value.
 *
 * @var string $date
 */
    public $date = null;
/**
 * Outcome type value.
 *
 * @var string $type
 */
    public $type = null;
/**
 * Outcome data source value.
 *
 * @var string $dataSource
 */
    public $dataSource = null;

/**
 * Outcome value.
 *
 * @var string $value
 */
    private $value = null;

/**
 * Class constructor.
 *
 * @param string $value     Outcome value (optional, default is none)
 */
    public function __construct($value = null)
    {

        $this->value = $value;
        $this->language = 'en-US';
        $this->date = gmdate('Y-m-d\TH:i:s\Z', time());
        $this->type = 'decimal';

    }

/**
 * Get the outcome value.
 *
 * @return string Outcome value
 */
    public function getValue()
    {

        return $this->value;

    }

/**
 * Set the outcome value.
 *
 * @param string $value  Outcome value
 */
    public function setValue($value)
    {

        $this->value = $value;

    }

}
