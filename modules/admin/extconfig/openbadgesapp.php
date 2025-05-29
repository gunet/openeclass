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

class OpenBadgesApp extends ExtAPP
{
    const NAME = "Open Badges";

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
        return $GLOBALS['langOpenBadgesShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langOpenBadgesLongDescription'];
    }

    public function getConfigUrl()
    {
        return 'modules/admin/openbadgeconf.php';
    }

    public function isConfigured()
    {
        return FALSE;
    }

    public function isEnabled()
    {
        return FALSE;
    }
}