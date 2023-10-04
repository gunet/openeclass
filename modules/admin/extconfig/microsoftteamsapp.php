<?php
require_once 'genericrequiredparam.php';

class MicrosoftTeamsApp extends ExtApp
{

    const ENABLEDCOURSES = "enabledcourses";
    const MICROSOFTTEAMSURL = "https://teams.live.com/";

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", MicrosoftTeamsApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName()
    {
        return "Microsoft Teams";
    }

    public function getShortDescription()
    {
        return $GLOBALS['langMsTeamsShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langMsTeamsLongDescription'];
    }

    public function getConfigUrl(): string
    {
        return 'modules/admin/microsoftteamsconf.php';
    }
}
