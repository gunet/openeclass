<?php
require_once 'genericrequiredparam.php';

class SebApp extends ExtApp
{
    const ENABLEDCOURSES = "enabledcourses";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", self::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName() {
        return "SEB";
    }

    public function getShortDescription() {
        return $GLOBALS['langSafeExamBrowserShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langSafeExamBrowserLongDescription'];
    }

    public function getConfigUrl(): string {
        return 'modules/admin/sebapp.php';
    }
}
