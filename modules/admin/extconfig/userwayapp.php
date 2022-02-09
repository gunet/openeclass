<?php

require_once 'genericrequiredparam.php';

class UserWayApp extends ExtApp
{
    const CODE = 'code';
    const NAME = 'UserWay';

    public function __construct()
    {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(),
            'UserWay HTML Code', UserWayApp::CODE, '',
            ExtParam::TYPE_MULTILINE));
    }

    public function getDisplayName()
    {
        return self::NAME;
    }

    public function getShortDescription()
    {
        return $GLOBALS['langUserWayShortDescription'];
    }

    public function getLongDescription()
    {
        return $GLOBALS['langUserWayLongDescription'];
    }
}






