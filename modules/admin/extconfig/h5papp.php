<?php

require_once 'genericparam.php';

class H5PApp extends ExtAPP
{
    const NAME = "H5P";

    public function __construct()
    {
        parent::__construct();
    }

    public function getDisplayName()
    {
        return self::NAME;
    }

    public function getShortDescription()
    {
        return $GLOBALS['langH5PShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langH5PLongDescription'];
    }

    public function getConfigUrl()
    {
        return 'modules/admin/h5pconf.php';
    }

    public function isConfigured()
    {
        return TRUE;
    }

    public function isEnabled()
    {
        return TRUE;
    }
}







