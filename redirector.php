<?php 
/**===========================================================================
*              GUnet eClass 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
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

/**
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

if (session_is_registered("uid") && isset($perso)) {
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

//		case 3: {//documents
//			
//			//			echo "switch two";
//			break;
//		}

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


}elseif (!session_is_registered("uid")){
	die("UNAUTHORISED ACCESS. THIS IS AN INTERNAL SCRIPT AND CANNOT BE ACCESSED DIRECTLY. Please go back to <a href=\"$urlServer\">the login page</a>");
}
?>
