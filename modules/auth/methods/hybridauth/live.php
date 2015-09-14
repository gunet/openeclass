<?php
//openeclass neccessary additional file to render Windows Live functionable
$_REQUEST['hauth_done'] = 'Live';
require_once( "Hybrid/Auth.php" );
require_once( "Hybrid/Endpoint.php" );
Hybrid_Endpoint::process();
?>