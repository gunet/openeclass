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

require_once 'genericrequiredparam.php';

class MicrosoftTeamsApp extends ExtApp
{

    const ENABLEDCOURSES = "enabledcourses";
    const MICROSOFTTEAMSURL = "https://teams.live.com/";

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", MicrosoftTeamsApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName()
    {
        return "Microsoft Teams";
    }

    public function getShortDescription()
    {
        return $GLOBALS['langMsTeamsShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langMsTeamsLongDescription'];
    }

    public function getConfigUrl(): string
    {
        return 'modules/admin/microsoftteamsconf.php';
    }
}
