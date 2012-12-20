<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/**
 * Checks if there are files in the dropbox_file table that aren't used anymore in dropbox_person table.
 * If there are, all entries concerning the file are deleted from the db + the file is deleted from the server
 * @file functions.php
 * @global type $dropbox_dir
 * @global type $course_id
 */
function removeUnusedFiles() {
        
    global $dropbox_dir, $course_id;

    // select all files that aren't referenced anymore
    $sql = "SELECT DISTINCT f.id, f.filename
			FROM dropbox_file f
			LEFT JOIN dropbox_person p ON f.id = p.fileId
			WHERE f.course_id = $course_id AND p.personId IS NULL";
    $result = db_query($sql);
    while ($res = mysql_fetch_array($result)) {
	//delete the selected files from the post and file tables
	$sql = "DELETE FROM dropbox_post WHERE fileId='" . $res['id'] . "'";
        $result1 = db_query($sql);
        $sql = "DELETE FROM dropbox_file WHERE id='" . $res['id'] . "'";
        $result1 = db_query($sql);
        //delete file from server
        unlink($dropbox_dir . "/" . $res["filename"]);
    }
}