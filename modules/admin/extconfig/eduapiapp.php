<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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

class EduApiApp extends ExtApp
{

    const NAME = "EduApi";

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
        return $GLOBALS['langEduApiShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langEduApiLongDescription'];
    }

    public function getConfigUrl()
    {
        return 'modules/admin/eduapiconf.php';
    }

    public function isConfigured()
    {
        $base_url = get_config('eduapi_base_url');
        $token_url = get_config('eduapi_token_url');
        $client_id = get_config('eduapi_client_id');
        $client_secret = get_config('eduapi_client_secret');

        return !empty($base_url) && !empty($token_url) && !empty($client_id) && !empty($client_secret);
    }

    public function isEnabled()
    {
        return parent::isEnabled() && $this->isConfigured();
    }
}
