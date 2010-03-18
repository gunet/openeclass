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
$require_login = TRUE;
$require_current_course = TRUE;
$guest_allowed = FALSE;
$require_help = TRUE;
$helpTopic = 'Dropbox';
include_once '../../include/baseTheme.php';

$tool_content = "";

// javascript functions
$head_content ='<script type="text/javascript">
                function confirmation (name) {
                if (confirm("'.$dropbox_lang['confirmDelete1'].'" + name + "'.$dropbox_lang['confirmDelete2'].'" )) {
                        return true;
                } else {
                        return false;
                }
                return true;
                }

                function confirmationall (name) {
                if (confirm("'.$dropbox_lang['all'].'" )) {
                        return true;
                } else {
                        return false;
                }
                return true;
                }

		function confirmsend (){
                if (confirm("'.$dropbox_lang['mailingConfirmSend'].'")) {
                        return true;
                } else {
                        return false;
                }
                return true;
        	}
	
		function checkForm (frm) {
                if (frm.elements["recipients[]"].selectedIndex < 0) {
                        alert("'.$dropbox_lang['noUserSelected'].'");
                        return false;
                } else if (frm.file.value == "") {
                        alert("'.$dropbox_lang['noFileSpecified'].'");
                        return false;
                } else {
                        return true;
                }
        }
        </script>';

/**
 * --------------------------------------
 *       DATABASE TABLE VARIABLES
 * --------------------------------------
 */
$dropbox_cnf["postTbl"] = "dropbox_post";
$dropbox_cnf["fileTbl"] = "dropbox_file";
$dropbox_cnf["personTbl"] = "dropbox_person";
$dropbox_cnf["introTbl"] = "tool_intro";
$dropbox_cnf["userTbl"] = "user";
$dropbox_cnf["courseUserTbl"] = "cours_user";

/**
 * --------------------------------------
 *       INITIALISE OTHER VARIABLES & CONSTANTS
 * --------------------------------------
 */
$dropbox_cnf["courseId"] = $currentCourseID;
$dropbox_cnf["cid"] = $cours_id;
$dropbox_cnf["sysPath"] = $webDir."courses/".$currentCourseID."/dropbox"; //path to dropbox subdir in course containing the uploaded files
if (!is_dir($dropbox_cnf["sysPath"])) {
	mkdir($dropbox_cnf["sysPath"]);
} 
	
// get dropbox quotas from database
$d = mysql_fetch_array(db_query("SELECT dropbox_quota FROM `".$mysqlMainDb."`.`cours` WHERE code='$currentCourseID'"));
$diskQuotaDropbox = $d['dropbox_quota'];
$dropbox_cnf["allowJustUpload"] = false;
$dropbox_cnf["allowStudentToStudent"] = false;

/**
 * --------------------------------------
 * RH:   INITIALISE MAILING VARIABLES
 * --------------------------------------
 */
$dropbox_cnf["allowMailing"] = false;  // false = no mailing functionality
$dropbox_cnf["mailingIdBase"] = 1000000000;  // bigger than any user_id,
$dropbox_cnf["mailingZipRegexp"] = '/^(.*)(STUDENTID|USERID|LOGINNAME)(.*)\.ZIP$/i';
$dropbox_cnf["mailingWhereSTUDENTID"] = "officialCode";
$dropbox_cnf["mailingWhereUSERID"] = "username";
$dropbox_cnf["mailingWhereLOGINNAME"] = "username";
$dropbox_cnf["mailingFileRegexp"] = '/^(.+)\.\w{1,4}$/';


/*
 * ========================================
 *       Often used functions
 * ========================================
 */
/*
* returns username or false if user isn't registered anymore
*/
function getUserNameFromId ($id)  // RH: Mailing: return 'Mailing ' + id
{
    global $dropbox_cnf, $dropbox_lang, $mysqlMainDb;

    $mailingId = $id - $dropbox_cnf["mailingIdBase"];
    if ($mailingId > 0) return $dropbox_lang["mailingAsUsername"] . $mailingId;

    $sql = "SELECT CONCAT(nom,' ', prenom) AS name
		FROM `" . $dropbox_cnf["userTbl"] . "` WHERE user_id='" . addslashes($id) . "'";
    $result = db_query($sql, $mysqlMainDb);
    $res = mysql_fetch_array($result);
    if ($res == FALSE) return FALSE;
    return stripslashes($res["name"]);
}

/*
* returns loginname or false if user isn't registered anymore
*/
function getLoginFromId ($id)
{
    global $dropbox_cnf, $dropbox_lang, $mysqlMainDb;

    $sql = "SELECT username FROM `" . $dropbox_cnf["userTbl"] . "` WHERE user_id='" . addslashes($id) . "'";
    $result = db_query($sql,$mysqlMainDb);
    $res = mysql_fetch_array($result);
    if ($res == FALSE) return FALSE;
    return stripslashes( $res["username"]);
}

/*
* returns boolean indicating if user with user_id=$id is a course member
*/
function isCourseMember($id)
{
    global $dropbox_cnf, $dropbox_lang, $mysqlMainDb;

    $sql = "SELECT * FROM `" . $dropbox_cnf["courseUserTbl"] . "`
		WHERE user_id = '" . addslashes( $id) . "' AND cours_id = '" . $dropbox_cnf["cid"] . "'";
    $result = db_query($sql, $mysqlMainDb); 
    if (mysql_num_rows($result) == 1)
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/*
* Checks if there are files in the dropbox_file table that aren't used anymore in dropbox_person table.
* If there are, all entries concerning the file are deleted from the db + the file is deleted from the server
*/
function removeUnusedFiles()
{
    global $dropbox_cnf, $dropbox_lang, $currentCourseID;
    // select all files that aren't referenced anymore
    $sql = "SELECT DISTINCT f.id, f.filename
			FROM `" . $dropbox_cnf["fileTbl"] . "` f
			LEFT JOIN `" . $dropbox_cnf["personTbl"] . "` p ON f.id = p.fileId
			WHERE p.personId IS NULL";
    $result = db_query($sql, $currentCourseID);
    while ($res = mysql_fetch_array($result))
    {
	//delete the selected files from the post and file tables
	$sql = "DELETE FROM `" . $dropbox_cnf["postTbl"] . "` WHERE fileId='" . $res['id'] . "'";
        $result1 = db_query($sql, $currentCourseID);
        $sql = "DELETE FROM `" . $dropbox_cnf["fileTbl"] . "` WHERE id='" . $res['id'] . "'";
        $result1 = db_query($sql, $currentCourseID);

		//delete file from server
        unlink($dropbox_cnf["sysPath"] . "/" . $res["filename"]);
    }
}

/*
* RH: Mailing (2 new functions)
*
* Mailing zip-file is posted to (recipientId = ) mailing pseudo_id
* and is only visible to its uploader (personId).
*
* Mailing content files have uploaderId == mailing pseudo_id, a normal recipient,
* and are visible initially to recipient and pseudo_id.
*/
function checkUserOwnsThisMailing($mailingPseudoId, $userId)
{
    // user must be == uploaderId of dropbox_file posted to mailing pseudo_id

    global $dropbox_cnf, $dropbox_lang, $currentCourseID;

    $sql = "SELECT f.uploaderId FROM `" . $dropbox_cnf["fileTbl"] . "` f
			LEFT JOIN `" . $dropbox_cnf["postTbl"] . "` p ON f.id = p.fileId
			WHERE p.recipientId = '" . $mailingPseudoId . "'";
    $result = db_query($sql, $currentCourseID);

    if ($res = mysql_fetch_array($result))
    {
	    if ($res['uploaderId'] == $userId) return TRUE;
    }
    die($dropbox_lang["queryError"]);
}

function removeMoreIfMailing($fileId)
{
    // if file was posted to a mailing pseudo_id (i.e. delete zip-file)
    // then delete pseudo_id from person table for all content files

	global $dropbox_cnf, $dropbox_lang, $currentCourseID;

    $sql = "SELECT p.recipientId FROM `" . $dropbox_cnf["postTbl"] . "` p
		WHERE p.fileId = '" . $fileId . "'";
    $result = db_query($sql, $currentCourseID);

    if ($res = mysql_fetch_array($result))
    {
	    $mailingPseudoId = $res['recipientId'];
	    if ($mailingPseudoId > $dropbox_cnf["mailingIdBase"])
	    {
	        $sql = "DELETE FROM `" . $dropbox_cnf["personTbl"] . "` WHERE personId='" . $mailingPseudoId . "'";
	        $result1 = db_query($sql, $currentCourseID);
        }
    }
}
?>
