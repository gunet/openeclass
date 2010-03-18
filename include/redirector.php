<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*

 * Redirector Component

 *

 * @author Evelthon Prodromou <eprodromou@upnet.gr>

 * @version $Id$

 *

 * @abstract Used by eclass personalised. In charge of redirecting the user's browser

 * to the desired tool he/she clicks on the personalised interface. It is based on the diploma

 * thesis of Evelthon Prodromou

 *

 */

if (isset($_SESSION['uid']) && isset($perso)) {
	switch ($perso){
		case 1: { //assignments
			//$c is the lesson code.
			$_SESSION["dbname"] = $c;
			$url = $urlServer."modules/work/work.php?id=".$i;
			header("location:".$url);
			break;
		}
		case 2: {//announcements
			//$c is the lesson code.
			$_SESSION["dbname"] = $c;
			header("location:".$urlServer."modules/announcements/announcements.php");
			break;
		}
		case 4: {//agenda
			//$c is the lesson code.
			$_SESSION["dbname"] = $c;
			header("location:".$urlServer."modules/agenda/agenda.php");
			break;
		}
		case 5: {//forum
			$_SESSION["dbname"] = $c;
			$url = $urlServer."modules/phpbb/viewtopic.php?topic=".$t."&forum=".$f."&sub=".$s;
			header("location:".$url);
			break;
		}
		case 6: {//documents
			$_SESSION["dbname"] = $c;
			$url = $urlServer."modules/document/document.php?openDir=" . $p;
			header("location:".$url);
			break;
		}
	}
} elseif (!isset($_SESSION['uid'])) {
	die("UNAUTHORISED ACCESS. THIS IS AN INTERNAL SCRIPT AND CANNOT BE ACCESSED DIRECTLY. Please go back to <a href=\"$urlServer\">the login page</a>");
}

?>

