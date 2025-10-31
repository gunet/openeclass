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

class aiapp extends ExtApp {

    public function getDisplayName()
    {
        return $GLOBALS['langAI'];
    }

    public function getConfigUrl() {
        return 'modules/admin/aimoduleconf.php';
    }

    public function getShortDescription()
    {
        return $GLOBALS['langAIShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langAILongDescription'];
    }

    /**
     * Override isEnabled to check if any AI providers are enabled in database
     */
    public function isEnabled() {
        try {
            $result = Database::get()->querySingle("SELECT COUNT(*) as count FROM ai_providers WHERE enabled = 1");
            return $result && $result->count > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Override setEnabled to enable/disable all AI providers
     */
    function setEnabled($status) {
        try {
            Database::get()->query("UPDATE ai_providers SET enabled = ?d", [$status]);
        } catch (Exception $e) {
            error_log("Failed to update AI providers enabled status: " . $e->getMessage());
        }
    }

}
