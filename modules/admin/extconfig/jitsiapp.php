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

class JitsiApp extends ExtApp
{
    const JITSIURL = "url";
    const ENABLEDCOURSES = "enabledcourses";
    const JITSIDEFAULTURL = "https://meet.jit.si/";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Jitsi URL", JitsiApp::JITSIURL, JitsiApp::JITSIDEFAULTURL));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", JitsiApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName() {
        return "Jitsi";
    }

    public function getShortDescription() {
        return $GLOBALS['langJitsiShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langJitsiLongDescription'];
    }

    public function getConfigUrl(): string {
        return 'modules/admin/jitsiconf.php';
    }

}
