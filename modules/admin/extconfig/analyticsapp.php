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

class AnalyticsApp extends ExtApp {

    const CODE = 'code';
    const NAME = 'Analytics';

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(),
            'Analytics HTML Code', AnalyticsApp::CODE, '',
            ExtParam::TYPE_MULTILINE));
    }

    public function getDisplayName() {
        return self::NAME;
    }

    public function getShortDescription() {
        return $GLOBALS['langAnalyticsDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langAnalyticsDescription'];
    }

}
