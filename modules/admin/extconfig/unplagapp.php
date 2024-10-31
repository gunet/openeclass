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

class UnplagApp extends ExtApp {

    const APIKEY = "apikey";
    const APISECRET = "apisecret";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "API Key", UnplagApp::APIKEY));
        $this->registerParam(new GenericRequiredParam($this->getName(), "API Secret", UnplagApp::APISECRET));
    }

    public function getDisplayName() {
        return "Unicheck";
    }

    public function getShortDescription() {
        return $GLOBALS['langUnplagShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langUnplagLongDescription'];
    }

}
