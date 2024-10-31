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

require_once 'genericparam.php';

class H5PApp extends ExtAPP
{
    const NAME = "H5P";

    public function __construct()
    {
        parent::__construct();
    }

    public function getDisplayName()
    {
        return self::NAME;
    }

    public function getShortDescription()
    {
        return $GLOBALS['langH5PShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langH5PLongDescription'];
    }

    public function getConfigUrl()
    {
        return 'modules/admin/h5pconf.php';
    }

    public function isConfigured()
    {
        return TRUE;
    }

    public function isEnabled()
    {
        return TRUE;
    }
}







