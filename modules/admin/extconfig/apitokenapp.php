<?php

require_once 'genericrequiredparam.php';

class APITokenApp extends ExtApp
{

    const REMOTE_IP = "remote_url";
    const NAME = "name";
    const COMMENTS = "comments";

    public function __construct() {
        parent::__construct();

        $this->registerParam(new GenericParam($this->getName(), "Remote IP", APITokenApp::REMOTE_IP));
        $this->registerParam(new GenericParam($this->getName(), "Όνομα", APITokenApp::NAME));
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
        return $GLOBALS['langAPITokenShortDesc'];
    }

    public function getConfigUrl() {
        return 'modules/admin/apitokenconf.php';
    }

}
