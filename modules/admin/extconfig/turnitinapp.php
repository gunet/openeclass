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


/**
 *
 * @author jexi
 */

require_once 'genericparam.php';

class TurnitinApp extends ExtApp {

    const NAME = "Turnitin";

    public function __construct() {
        parent::__construct();
    }

    public function getDisplayName() {
        return self::NAME;
    }

    public function getShortDescription() {
        return $GLOBALS['langTurnitinShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langUnplagLongDescription'];
    }

    public function getConfigUrl() {
        return 'modules/admin/turnitinmoduleconf.php';
    }

    /**
     * Return true if any TII servers are enabled, else false
     *
     * @return boolean
     */
    public function isConfigured() {
        return Database::get()->querySingle("SELECT COUNT(*) AS count FROM lti_apps WHERE enabled = true AND is_template = true AND type = 'turnitin' AND course_id is null")->count > 0;
    }
}
