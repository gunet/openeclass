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
    const PRIVATE_URL = "private_url";
    const CHECKAUTH_URL = "checkauth_url";
    const RLOGIN_URL = "rlogin_url";
    const RLOGINCAS_URL = "rlogincas_url";
    const LMS_URL = "lms_url";
    CONST SECRET = "secret";
    const NAME = "OpenDelos";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos URL", OpenDelosApp::URL, "http://HOST/api/lms/public/"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos Private URL", OpenDelosApp::PRIVATE_URL, "http://HOST/api/dataservices/private/lms/"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos CheckAuth URL", OpenDelosApp::CHECKAUTH_URL, "http://HOST/api/dataservices/private/check_auth"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos RLogin URL", OpenDelosApp::RLOGIN_URL, "http://HOST/rlogin"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "OpenDelos RLogin CAS URL", OpenDelosApp::RLOGINCAS_URL, "http://HOST/admin/cauth/cas"));
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

}
