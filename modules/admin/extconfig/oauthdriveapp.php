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

abstract class OAuthDriveApp extends ExtApp {

    const CLIENTID = "clientid";
    const SECRET = "secret";
    const REDIRECT = "redirect";

    public function __construct() {
        $drivename = $this->getName();
        $this->registerParam(new GenericRequiredParam($drivename, $this->getAppParamName(), OAuthDriveApp::CLIENTID));
        $this->registerParam(new GenericRequiredParam($drivename, $this->getKeyParamName(), OAuthDriveApp::SECRET));
        $this->registerParam(new GenericRequiredParam($drivename, $this->getURLParamName(), OAuthDriveApp::REDIRECT, $this->getURLDefaultValue()));
    }

    protected function getAppParamName() {
        return "Κωδικός εφαρμογής";
    }

    protected function getKeyParamName() {
        return "Κλειδί εφαρμογής";
    }

    protected function getURLParamName() {
        return "Διεύθυνση επιστροφής";
    }

    public function getShortDescription() {
        return "Short description about " . $this->getDisplayName();
    }

    public function getLongDescription() {
        return "Long description about " . $this->getDisplayName();
    }

    protected abstract function getURLDefaultValue();
}
