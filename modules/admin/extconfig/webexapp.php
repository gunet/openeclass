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

class WebexApp extends ExtApp
{
    const WEBEXURL = "url";
    const ENABLEDCOURSES = "enabledcourses";
    const WEBEXDEFAULURL = "https://webex.com/";
    const WEBEXCUSTOMURL = 'custom_webex_url';

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "WebEx URL", WebexApp::WEBEXURL, WebexApp::WEBEXDEFAULURL));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", WebexApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName()
    {
        return "Webex";
    }

    public function getShortDescription()
    {
        return $GLOBALS['langWebexShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langWebexLongDescription'];
    }

    public function getConfigUrl(): string
    {
        return 'modules/admin/webexconf.php';
    }

}
