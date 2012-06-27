<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/*
 * Redirector Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * @abstract Used by eclass personalised. In charge of redirecting the user's browser
 * to the desired tool he/she clicks on the personalised interface. It is based on the diploma
 * thesis of Evelthon Prodromou
 */

if (isset($_SESSION['uid']) && isset($_GET['perso'])) {
	$perso = $_GET['perso'];
	$c = $_GET['c'];
	$_SESSION['dbname'] = $c;
	switch ($perso){
		case 1: { //assignments
			$i = intval($_GET['i']);
			$url = 'modules/work/index.php?id=' . $i;
			break;
		}
		case 2: { //announcements
			$url = 'modules/announcements/index.php';
			break;
		}
		case 4: { //agenda
			$url = 'modules/agenda/index.php';
			break;
		}
		case 5: { //forum
			$url = "modules/forum/viewtopic.php?topic=" .
			       intval($_GET['t']) . "&forum=" .
			       intval($_GET['f']) . "&sub=" .
			       intval($_GET['s']);
			break;
		}
		case 6: { //documents
			$url = "modules/document/index.php?openDir=" . $_GET['p'];	
			break;
		}
	}
	redirect_to_home_page($url);
} elseif (!isset($_SESSION['uid'])) {
	die("UNAUTHORISED ACCESS. THIS IS AN INTERNAL SCRIPT AND CANNOT BE ACCESSED DIRECTLY. Please go back to <a href=\"$urlServer\">the login page</a>");
}
