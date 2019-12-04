<?php
//openeclass neccessary additional file to render Windows Live functionable
$_REQUEST['hauth_done'] = 'Live';

require_once '../../../../include/baseTheme.php';

use Hybrid\Auth;
use Hybrid\Endpoint;

Hybrid_Endpoint::process();
