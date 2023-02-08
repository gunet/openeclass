<?php
require_once 'genericrequiredparam.php';

class ZoomApp extends ExtApp
{
    const ENABLEDCOURSES = "enabledcourses";
    const ZOOMURL = "https://zoom.us/";

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", ZoomApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName()
    {
        return "Zoom";
    }

    public function getShortDescription()
    {
        return $GLOBALS['langZoomShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langZoomLongDescription'];
    }

    public function getConfigUrl(): string
    {
        return 'modules/admin/zoomconf.php';
    }
}
