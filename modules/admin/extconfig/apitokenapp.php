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

class APITokenApp extends ExtApp
{

    const REMOTE_IP = "remote_url";
    const NAME = "name";
    const COMMENTS = "comments";

    public function __construct() {
        parent::__construct();

        $this->registerParam(new GenericParam($this->getName(), "Remote IP", APITokenApp::REMOTE_IP));
        $this->registerParam(new GenericParam($this->getName(), "Εφαρμογή", APITokenApp::NAME));
        $this->registerParam(new GenericParam($this->getName(), "Σχόλια", APITokenApp::COMMENTS, '', ExtParam::TYPE_MULTILINE));
    }

    public function getDisplayName()
    {
        return "API Token";
    }

    public function getShortDescription()
    {
        return $GLOBALS['langAPITokenShortDesc'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langAPITokenLongDesc'];
    }

    public function getConfigUrl() {
        return 'modules/admin/apitokenconf.php';
    }

}
