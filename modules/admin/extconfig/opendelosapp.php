<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== 
 */

require_once 'genericrequiredparam.php';

class OpenDelosApp extends ExtApp {

    const URL = "url";
    const PUBLIC_API = "public_api";
    const PRIVATE_API = "private_api";
    const CHECKAUTH_API = "checkauth_api";
    const RLOGIN_API = "rlogin_api";
    const RLOGINCAS_API = "rlogincas_api";
    const LMS_URL = "lms_url";
    CONST SECRET = "secret";
    const NAME = "OpenDelos";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos URL", OpenDelosApp::URL, "http://HOST"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos Public API", OpenDelosApp::PUBLIC_API, "/api/lms/public/"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos Private API", OpenDelosApp::PRIVATE_API, "/api/dataservices/private/lms/"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos RLogin API", OpenDelosApp::RLOGIN_API, "/rlogin"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos RLogin CAS API", OpenDelosApp::RLOGINCAS_API, "/admin/cauth/cas"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos LMS URL", OpenDelosApp::LMS_URL));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos Secret", OpenDelosApp::SECRET));
    }

    public function getDisplayName() {
        return self::NAME;
    }

    public function getShortDescription() {
        return $GLOBALS['langOpenDelosDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langOpenDelosDescription'];
    }

    public function getConfigUrl() {
        return 'modules/admin/opendelosmoduleconf.php';
    }

}
