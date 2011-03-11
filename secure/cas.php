<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Giannis Kapetanakis <bilias@edu.physics.uoc.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$path2add = 2;

include "../include/baseTheme.php";
include('../include/CAS/CAS.php');
include "../modules/auth/auth.inc.php";

$auth = 7;
cas_authenticate($auth);
if (phpCAS::checkAuthentication()) {
	$cas = get_cas_settings($auth);
	$attrs = get_cas_attrs(phpCAS::getAttributes(), $cas);
	$_SESSION['cas_uname'] = phpCAS::getUser();
	$_SESSION['uname'] = $_SESSION['cas_uname'];
	$_SESSION['cas_nom'] = $attrs['casuserlastattr'];
	$_SESSION['cas_prenom'] = $attrs['casuserfirstattr'];
	$_SESSION['cas_email'] = $attrs['casusermailattr'];
}

header("Location: $urlServer");
?>
