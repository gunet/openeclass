<?php

require_once 'genericrequiredparam.php';

class JitsiApp extends ExtApp
{
    const JITSIURL = "url";
    const ENABLEDCOURSES = "enabledcourses";
    const JITSIDEFAULTURL = "https://meet.jit.si/";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Jitsi URL", JitsiApp::JITSIURL, JitsiApp::JITSIDEFAULTURL));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", JitsiApp::ENABLEDCOURSES, "0"));
    }

    public function getDisplayName() {
        return "Jitsi";
    }

    public function getShortDescription() {
        return $GLOBALS['langJitsiShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langJitsiLongDescription'];
    }

    public function getConfigUrl(): string {
        return 'modules/admin/jitsiconf.php';
    }

}
