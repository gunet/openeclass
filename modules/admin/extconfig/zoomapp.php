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

class ZoomApp extends ExtApp
{
    const ZOOMURL = "url";
    const ENABLEDCOURSES = "enabledcourses";
    const CLIENT_ID = "clientId";
    const CLIENT_SECRET = "clientSecret";
    const ACCOUNT_ID = "accountId";
    const ZOOMDEFAULTURL = "https://zoom.us";
    const ZOOMCUSTOMURL = 'custom_zoom_url';
    const API = 'api';
    const CUSTOM = 'custom';

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericParam($this->getName(), "Zoom URL", ZoomApp::ZOOMURL, ZoomApp::ZOOMDEFAULTURL));
        $this->registerParam(new GenericParam($this->getName(), "Enabled courses", ZoomApp::ENABLEDCOURSES, "0"));
        $this->registerParam(new GenericParam($this->getName(), "Οι χρήστες συμπληρώνουν το Zoom URL", ZoomApp::ZOOMCUSTOMURL, "0"));
        $this->registerParam(new GenericParam($this->getName(), "Account ID", ZoomApp::ACCOUNT_ID, null));
        $this->registerParam(new GenericParam($this->getName(), "Client ID", ZoomApp::CLIENT_ID, null));
        $this->registerParam(new GenericParam($this->getName(), "Client Secret", ZoomApp::CLIENT_SECRET, null));
    }

    public function getDisplayName()
    {
        return "Zoom";
    }

    public function getShortDescription()
    {
        return $GLOBALS['langZoomShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langZoomLongDescription'];
    }

    public function getConfigUrl(): string
    {
        return 'modules/admin/zoomconf.php';
    }
}
