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

class ColmoocApp extends ExtApp {

    const NAME = "ColMOOC";

    const PLATFORM_ID = "platform_id";
    const BASE_URL = "base_url";
    const CHAT_URL = "chat_url";
    const ANALYTICS_URL = "analytics_url";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Platform Id", ColmoocApp::PLATFORM_ID, "201"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Base URL", ColmoocApp::BASE_URL, "https://mklab.iti.gr"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Chat URL", ColmoocApp::CHAT_URL, "https://mklab.iti.gr/colmooc-chat"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Analytics URL", ColmoocApp::ANALYTICS_URL, "https://mklab.iti.gr/colmoocapi/analytics"));
    }

    public function getDisplayName() {
        return self::NAME;
    }

    public function getShortDescription() {
        return $GLOBALS['langColmoocDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langColmoocDescription'];
    }

}
