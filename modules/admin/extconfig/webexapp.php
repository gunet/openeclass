<?php
require_once 'genericrequiredparam.php';

class WebexApp extends ExtApp
{
    const ENABLEDCOURSES = "enabledcourses";
    const WEBEXURL = "https://webex.com/";

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", WebexApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName()
    {
        return "Webex";
    }

    public function getShortDescription()
    {
        return $GLOBALS['langWebexShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langWebexLongDescription'];
    }

    public function getConfigUrl(): string
    {
        return 'modules/admin/webexconf.php';
    }

}
