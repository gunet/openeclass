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

class UniFlixApp extends ExtApp {

    const URL = "url";
    const PUBLIC_API = "public_api";
    const PRIVATE_API = "private_api";
    const CHECKAUTH_API = "checkauth_api";
    const RLOGIN_API = "rlogin_api";
    const RLOGINCAS_API = "rlogincas_api";
    const LMS_URL = "lms_url";
    CONST SECRET = "secret";
    const NAME = "UniFlix";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "UniFlix URL", UniFlixApp::URL, "http://HOST"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "UniFlix Public API", UniFlixApp::PUBLIC_API, "/api/lms/public/"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "UniFlix Private API", UniFlixApp::PRIVATE_API, "/api/dataservices/private/lms/"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "UniFlix RLogin API", UniFlixApp::RLOGIN_API, "/rlogin"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "UniFlix RLogin CAS API", UniFlixApp::RLOGINCAS_API, "/admin/cauth/cas"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "UniFlix LMS URL", UniFlixApp::LMS_URL));
        $this->registerParam(new GenericRequiredParam($this->getName(), "UniFlix Secret", UniFlixApp::SECRET));
    }

    public function getDisplayName() {
        return self::NAME;
    }

    public function getShortDescription() {
        return $GLOBALS['langUniFlixDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langUniFlixDescription'];
    }

    public function getConfigUrl() {
        return 'modules/admin/uniflixmoduleconf.php';
    }

}

