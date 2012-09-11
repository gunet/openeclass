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

$require_login = TRUE;
$require_current_course = TRUE;
$guest_allowed = FALSE;
$require_help = TRUE;
$helpTopic = 'Dropbox';
include '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

// javascript functions
$head_content ='<script type="text/javascript">
                function confirmation (name) {
                        if (confirm("'.$langConfirmDelete1.'" + name + "'.$langConfirmDelete2.'" )) {
                                return true;
                        } else {
                                return false;
                        }
                        return true;
                }

                function confirmationall () {
                        if (confirm("'.$langConfirmDeleteAll.'" )) {
                                return true;
                        } else {
                                return false;
                        }
                        return true;
                }
                
                function confirmationpurge () {
                        if (confirm("'.$langPurgeFile.'" )) {
                                return true;
                        } else {
                                return false;
                        }
                        return true;
                }

		function checkForm (frm) {
                        if (frm.elements["recipients[]"].selectedIndex < 0) {
                                alert("'.$langNoUserSelected.'");
                                return false;
                        } else if (frm.file.value == "") {
                                alert("'.$langNoFileSpecified.'");
                                return false;
                        } else {
                                return true;
                        }
                }
        </script>';

/**
 * --------------------------------------
 *       INITIALISE OTHER VARIABLES & CONSTANTS
 * --------------------------------------
 */
$dropbox_cnf["sysPath"] = $webDir."/courses/".$course_code."/dropbox";

if (!is_dir($dropbox_cnf["sysPath"])) {
	mkdir($dropbox_cnf["sysPath"]);
}

// get dropbox quotas from database
$d = mysql_fetch_array(db_query("SELECT dropbox_quota FROM course WHERE code = '$course_code'"));
$diskQuotaDropbox = $d['dropbox_quota'];
if (get_config('dropbox_allow_student_to_student') == true) {
	$dropbox_cnf["allowStudentToStudent"] = true;
} else {
	$dropbox_cnf["allowStudentToStudent"] = false;
}
$basedir = $dropbox_cnf["sysPath"];
$diskUsed = dir_total_space($basedir);

/*
* Checks if there are files in the dropbox_file table that aren't used anymore in dropbox_person table.
* If there are, all entries concerning the file are deleted from the db + the file is deleted from the server
*/
function removeUnusedFiles() {
        
    global $dropbox_cnf, $course_id;

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
        unlink($dropbox_cnf["sysPath"] . "/" . $res["filename"]);
    }
}