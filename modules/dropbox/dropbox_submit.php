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

/**
 * Handles actions submitted from the main page index.php by POST or GET requests
 *
 * 1. Initialise vars
 * 2. Prevent resubmit
 * 3. Form submit & file upload
 * 4. Form submit feedback
 * 5. Delete entries
 * 6. Delete entries feedback
 *
 */

include 'functions.php';

include '../../include/lib/forcedownload.php';
include 'include/sendMail.inc.php';

$nameTools = $dropbox_lang['dropbox'];

/**
 * ========================================
 * PREVENT RESUBMITING
 * ========================================
 * This part checks if the $dropbox_unid var has the same ID
 * as the session var $dropbox_uniqueid that was registered as a session
 * var before.
 * The resubmit prevention only works with GET requests, because it gives some annoying
 * behaviours with POST requests.
 */

if (isset($_POST['dropbox_unid'])) {
	$dropbox_unid = $_POST['dropbox_unid'];
} elseif (isset($_GET['dropbox_unid']))
{
	$dropbox_unid = $_GET['dropbox_unid'];
} else {
	die($dropbox_lang['badFormData']);
}

if (isset($_SESSION["dropbox_uniqueid"]) && isset($_GET["dropbox_unid"]) && $dropbox_unid == $_SESSION["dropbox_uniqueid"]) {

	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") {
		$mypath = "https";
	} else {
		$mypath = "http";
	}
	$mypath=$mypath."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php?course=$course_code";

	header("Location: $mypath");
}

$dropbox_uniqueid = $dropbox_unid;
$_SESSION['dropbox_uniqueid'] = $dropbox_uniqueid;

require_once("dropbox_class.inc.php");

/*
 form submission
*/
if (isset($_POST["submitWork"]))
{
	$error = FALSE;
	$errormsg = '';

	if (!isset($_POST['authors']) || !isset($_POST['description']))
	{
		$error = TRUE;
		$errormsg = $dropbox_lang["badFormData"];
	}
	elseif (!isset( $_POST['recipients']) || count( $_POST['recipients']) <= 0)
	{
		$error = TRUE;
		$errormsg = $dropbox_lang["noUserSelected"];
	}
	else
	{
		$thisIsJustUpload = FALSE;  // RH
		foreach($_POST['recipients'] as $rec)
		{
			if ($rec == 0)
			{
				$thisIsJustUpload = TRUE;
			}
		}
		if ($thisIsJustUpload && ( count($_POST['recipients']) != 1))
		{
			$error = TRUE;
			$errormsg = $dropbox_lang["mailingJustUploadNoOther"];
		}
		elseif (empty( $_FILES['file']['name']))
		{
			$error = TRUE;
			$errormsg = $dropbox_lang["noFileSpecified"];
		}
	}

     /*
     * --------------------------------------
     *     FORM SUBMIT : UPLOAD NEW FILE
     * --------------------------------------
     */
	if (!$error) {
		$cwd = getcwd();
		if (is_dir($dropbox_cnf["sysPath"])) {
			$dropbox_space = dir_total_space($dropbox_cnf["sysPath"]);
		}
		$dropbox_filename = $_FILES['file']['name'];
		$dropbox_filesize = $_FILES['file']['size'];
		$dropbox_filetype = $_FILES['file']['type'];
		$dropbox_filetmpname = $_FILES['file']['tmp_name'];

		if ($dropbox_filesize + $dropbox_space > $diskQuotaDropbox)
		{
			$errormsg = $dropbox_lang["quotaError"];
			$error = TRUE;
		} elseif (!is_uploaded_file($dropbox_filetmpname)) // check user found : no clean error msg.
		{
			die ($dropbox_lang["badFormData"]);
		}

		// set title
		$dropbox_title = $dropbox_filename;
		$format = get_file_extension($dropbox_filename);
		$dropbox_filename = safe_filename($format);
		// set author
		if ($_POST['authors'] == '')
		{
			$_POST['authors'] = uid_to_name($uid);
		}
		if ($error) {}
		elseif ($thisIsJustUpload)  // RH: $newWorkRecipients is empty array
		{
			$newWorkRecipients = array();
		} else {
			$newWorkRecipients = $_POST["recipients"];
		}
		//After uploading the file, create the db entries
		if (!$error) {
			$subject_dropbox = "$logo - $dropbox_lang[newDropboxFile]";
			$c = course_code_to_title($course_code);
			$filename_final = $dropbox_cnf['sysPath'] . '/' . $dropbox_filename;
			move_uploaded_file($dropbox_filetmpname, $filename_final)
				or die($dropbox_lang["uploadError"]);
			@chmod($filename_final, 0644);
			new Dropbox_SentWork($uid, $dropbox_title, $_POST['description'], $_POST['authors'], $dropbox_filename, $dropbox_filesize, $newWorkRecipients);
			if (isset($_POST['mailing']) and $_POST['mailing']) {	// send mail to recipients of dropbox file
				foreach($newWorkRecipients as $userid) {
                                        if (get_user_email_notification($userid, $course_id)) {  
                                                $linkhere = "&nbsp;<a href='${urlServer}modules/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
                                                $unsubscribe = "<br /><br />".sprintf($langLinkUnsubscribe, $title);            
                                                $body_dropbox_message = "$langInCourses '$c' $dropbox_lang[mailnotify] <br /><br />$gunet<br /><a href='$urlServer'>$urlServer</a> $unsubscribe$linkhere";
                                                $plain_body_dropbox_message = "$langInCourses '$c' $dropbox_lang[mailnotify] \n\n$gunet\n<a href='$urlServer'>$urlServer</a> $unsubscribe$linkhere";
                                                $emailaddr = uid_to_email($userid);
                                                send_mail_multipart('', '', '', $emailaddr, $subject_dropbox, $plain_body_dropbox_message, $body_dropbox_message, $charset);	        
                                        }                                  					
				}
			}
		}
		chdir ($cwd);
	} //end if(!$error)
	if (!$error) {
		$tool_content .= "<p class=\"success\">".$dropbox_lang["docAdd"]."<br />
		<a href='index.php?course=$course_code'>".$dropbox_lang['backList']."</a></p><br/>";
	}
	else
	{
		$tool_content .= "<p class=\"caution\">".$errormsg."<br /><br />
		<a href='index.php?course=$course_code'>".$dropbox_lang['backList']."</a><br/>";
	}
}

/*
 * ========================================
 * DELETE RECEIVED OR SENT FILES
 * ========================================
 * - DELETE ALL RECEIVED FILES
 * - DELETE 1 RECEIVED FILE
 * - DELETE ALL SENT FILES
 * - DELETE 1 SENT FILE
 */
if (isset($_GET['deleteReceived']) || isset($_GET['deleteSent']))
{
	$dropbox_person = new Dropbox_Person( $uid, $is_editor, $is_editor);
	if (isset($_SESSION["sentOrder"]))
	{
		$dropbox_person->orderSentWork ($_SESSION["sentOrder"]);
	}
	if (isset($_SESSION["receivedOrder"]))
	{
		$dropbox_person->orderReceivedWork ($_SESSION["receivedOrder"]);
	}

	if (isset($_GET['deleteReceived']))
	{
		if ($_GET["deleteReceived"] == "all")
		{
			$dropbox_person->deleteAllReceivedWork( );
		} elseif (is_numeric( $_GET["deleteReceived"]))
		{
			$dropbox_person->deleteReceivedWork( $_GET['deleteReceived']);
		}
		else
		{
			die($dropbox_lang["generalError"]);
		}
	}
	else
	{
		if ($_GET["deleteSent"] == "all")
		{
			$dropbox_person->deleteAllSentWork( );
		}elseif ( is_numeric( $_GET["deleteSent"]))
		{
			$dropbox_person->deleteSentWork( $_GET['deleteSent']);
		}
		else
		{
			die($dropbox_lang["generalError"]);
		}
	}
	$tool_content .= "<p class=\"success\">".$dropbox_lang["fileDeleted"]."<br />
	<a href='index.php?course=$course_code'>".$dropbox_lang['backList']."</a></p><br/>";
}

draw($tool_content, 2, null, $head_content);
