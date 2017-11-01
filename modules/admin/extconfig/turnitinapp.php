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


/** 
 * 
 * @author jexi
 */

require_once 'genericparam.php';

class TurnitinApp extends ExtApp {
    const APIKEY = "apikey";
    const APISECRET = "apisecret";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericParam($this->getName(), "API Key", TurnitinApp::APIKEY));
        $this->registerParam(new GenericParam($this->getName(), "API Secret", TurnitinApp::APISECRET));
    }

    public function getDisplayName() {
        return "Turnitin";
    }

    public function getShortDescription() {
        return $GLOBALS['langTurnitinShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langUnplagLongDescription'];
    }
}
