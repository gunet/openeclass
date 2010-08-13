<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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

include '../../include/init.php';
$course_id = course_code_to_id(escapeSimple($_GET['c']));
$id = intval($_GET['id']);
if ($course_id !== false) {
        db_query("UPDATE link SET hits = hits + 1 WHERE course_id = $course_id AND id = $id");
        $q = db_query("SELECT url FROM link WHERE course_id = $course_id AND id = $id");
        if ($q and mysql_num_rows($q) > 0) {
                list($url) = mysql_fetch_row($q);
                header('Location: ' . $url);
                exit;
        }
}
$_SESSION['errMessage'] = $langAccountResetInvalidLink;
header('Location: ' . $urlServer);
