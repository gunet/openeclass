<?php
require_once 'genericrequiredparam.php';

class CobyApp extends ExtApp
{
    const ENABLEDCOURSES = "enabledcourses";
    const COBYURL = "url";
    const SHAREDSECRET = "secret";

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Enabled courses", self::ENABLEDCOURSES, "0"));
        $this->registerParam(new GenericRequiredParam($this->getName(), "Coby URL", self::COBYURL, ""));
        $this->registerParam(new GenericParam($this->getName(), $GLOBALS['langCobySecret'] ?? "Shared Secret", self::SHAREDSECRET, ""));
    }

    public function getDisplayName()
    {
        return "Coby";
    }

    public function getShortDescription()
    {
        return $GLOBALS['langCobyShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langCobyLongDescription'];
    }

    public function getConfigUrl(): string
    {
        return 'modules/admin/cobyapp.php';
    }
}

