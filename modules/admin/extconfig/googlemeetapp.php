<?php

require_once 'genericrequiredparam.php';

class GoogleMeetApp extends ExtApp
{
    const ENABLEDCOURSES = "enabledcourses";
    const GOOGLEMEETURL = "https://meet.google.com/";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", GoogleMeetApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName() {
        return "Google Meet";
    }

    public function getShortDescription() {
        return $GLOBALS['langGoogleMeetShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langGoogleMeetLongDescription'];
    }

    public function getConfigUrl(): string {
        return 'modules/admin/googlemeetconf.php';
    }

}
