<?php
require_once 'genericrequiredparam.php';

class ZoomApp extends ExtApp
{
    const ZOOMURL = "url";
    const ENABLEDCOURSES = "enabledcourses";
    const ZOOMDEFAULTURL = "https://zoom.us/";
    const ZOOMCUSTOMURL = 'custom_zoom_url';

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Zoom URL", ZoomApp::ZOOMURL, ZoomApp::ZOOMDEFAULTURL));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", ZoomApp::ENABLEDCOURSES, "0"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Οι χρήστες συμπληρώνουν το Zoom URL", ZoomApp::ZOOMCUSTOMURL, "0"));
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
