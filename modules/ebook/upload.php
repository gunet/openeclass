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


$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once '../../include/pclzip/pclzip.lib.php';

mysql_select_db($mysqlMainDb);
$basedir = $webDir . 'courses/' . $currentCourseID . '/ebook';

if (!$is_adminOfCourse or !isset($_FILES['file'])) {
        header('Location: ' . $urlServer);
} else {
        if ($_FILES['file']['type'] != 'application/zip') {
                echo "Error no zip...";
                die;
        }
        if (!is_dir($basedir)) {
                mkdir($basedir, 0775);
        }
        chdir($basedir);

        list($id, $order) = mysql_fetch_row(db_query("SELECT MAX(`public_id`), MAX(`order`) FROM ebook WHERE course_id = $cours_id"));
        if (!$order) {
                $order = 1;
        } else {
                $order++;
        }
        if (!$id) {
                $id = 1;
        } else {
                $id++;
        }
        mkdir($id, 0777);
        chdir($id);
        $zip = new pclZip($_FILES['file']['tmp_name']);
        if (!$zip->extract()) {
                echo 'Error extracting...';
                die;
        }
        db_query("INSERT INTO ebook SET `public_id` = $id, `order` = $order, `course_id` = $cours_id, `title` = " .
                  autoquote($_POST['title']));
        header("Location: $urlAppend/modules/ebook/edit.php?id=$id");
}
