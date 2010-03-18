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

require_once("dropbox_init1.inc.php");
include "../../include/lib/forcedownload.php";
$nameTools = $dropbox_lang["dropbox"];

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

if (isset($_POST["dropbox_unid"])) {
	$dropbox_unid = $_POST["dropbox_unid"];
} elseif (isset($_GET["dropbox_unid"]))
{
	$dropbox_unid = $_GET["dropbox_unid"];
} else {
	die($dropbox_lang["badFormData"]);
}

if (isset($_SESSION["dropbox_uniqueid"]) && isset($_GET["dropbox_unid"]) && $dropbox_unid == $_SESSION["dropbox_uniqueid"]) {

	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") {
		$mypath = "https";
	} else {
		$mypath = "http";
	}
	$mypath=$mypath."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php";

	header("Location: $mypath");
}

$dropbox_uniqueid = $dropbox_unid;
$_SESSION['dropbox_uniqueid'] = $dropbox_uniqueid;

require_once("dropbox_class.inc.php");

/*
 * ========================================
 * FORM SUBMIT
 * ========================================
 * - VALIDATE POSTED DATA
 * - UPLOAD NEW FILE
 */
if (isset($_POST["submitWork"]))
{
	require("../../include/lib/fileUploadLib.inc.php");

	$error = FALSE;
	$errormsg = '';


if (!isset( $_POST['authors']) || !isset( $_POST['description']))
	{
		$error = TRUE;
		$errormsg = $dropbox_lang["badFormData"];
	}
	elseif ( !isset( $_POST['recipients']) || count( $_POST['recipients']) <= 0)
	{
		$error = TRUE;
		$errormsg = $dropbox_lang["noUserSelected"];
	}
	else
	{
		$thisIsAMailing = FALSE;  // RH: Mailing selected as destination
		$thisIsJustUpload = FALSE;  // RH

		foreach($_POST['recipients'] as $rec)
		{
			if ( $rec == $dropbox_cnf["mailingIdBase"])
			{
				$thisIsAMailing = TRUE;
			}
			elseif ($rec == 0)
			{
				$thisIsJustUpload = TRUE;
			}
			elseif ( !isCourseMember( $rec)) die( $dropbox_lang["badFormData"]);
		}

		if ( $thisIsAMailing && ( count($_POST['recipients']) != 1))
		{
			$error = TRUE;

			$errormsg = $dropbox_lang["mailingSelectNoOther"];
		}
		elseif ($thisIsJustUpload && ( count($_POST['recipients']) != 1))
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

		if (!$error) {
			// set title
			$dropbox_title = $dropbox_filename;
			$format = get_file_extension($dropbox_filename);
                	$dropbox_filename = safe_filename($format);
			// Transform any .php file in .phps fo security
			// $dropbox_filename = php2phps ($dropbox_filename);
			// set author
			if ($_POST['authors'] == '')
			{
				$_POST['authors'] = getUserNameFromId($uid);
			}

			if ($error) {}
			elseif ($thisIsAMailing)  // RH: $newWorkRecipients is integer - see class
			{
				if (!preg_match($dropbox_cnf["mailingZipRegexp"], $dropbox_title))
				{
					$newWorkRecipients = $dropbox_cnf["mailingIdBase"];
				} else {
					$error = TRUE;
					$errormsg = $dropbox_title . ": " . $dropbox_lang["mailingWrongZipfile"];
				}
			}
			elseif ($thisIsJustUpload)  // RH: $newWorkRecipients is empty array
			{
				$newWorkRecipients = array();
			} else {
				$newWorkRecipients = $_POST["recipients"];
			}

			//After uploading the file, create the db entries
			if (!$error)
			{
				move_uploaded_file($dropbox_filetmpname, $dropbox_cnf["sysPath"] . '/' . $dropbox_filename)
				or die($dropbox_lang["uploadError"]);
				new Dropbox_SentWork($uid, $dropbox_title, $_POST['description'], $_POST['authors'], $dropbox_filename, $dropbox_filesize, $newWorkRecipients);
			}
		}
		chdir ($cwd);
	} //end if(!$error)

	if (!$error) {
		$tool_content .= "<p class=\"success_small\">".$dropbox_lang["docAdd"]."<br />
		<a href='index.php'>".$dropbox_lang['backList']."</a></p><br/>";
	}
	else
	{
		$tool_content .= "<p class=\"caution_small\">".$errormsg."<br />
		<a href='index.php'>".$dropbox_lang['backList']."</a><br/>";
		$tool_content .=  "<b><font color='#FF0000'>".$errormsg."</font></b><br><br>";
	}
}


/*
 * ========================================
 * // RH: EXAMINE OR SEND MAILING  (NEW)
 * ========================================
 */
if (isset($_GET['mailingIndex']))  // examine or send
{
	$dropbox_person = new Dropbox_Person($uid, $is_adminOfCourse, $is_adminOfCourse);
	if (isset($_SESSION["sentOrder"]))
	{
		$dropbox_person->orderSentWork($_SESSION["sentOrder"]);
	}
	$i = $_GET['mailingIndex'];
	$mailing_item = $dropbox_person->sentWork[$i];
	$mailing_title = $mailing_item->title;
	$mailing_file = $dropbox_cnf["sysPath"] . '/' . $mailing_item->filename;
	$errormsg = '<b>' . $mailing_item->recipients[0]['name'] . ' ('
	. "<a href='dropbox_download.php?id=".urlencode($mailing_item->id)."'>'"
	. $mailing_title . '</a>):</b><br><br>';

	if (!preg_match($dropbox_cnf["mailingZipRegexp"], $mailing_title, $nameParts))
	{
		$var = strtoupper($nameParts[2]);  // the variable part of the name
		$sel = "SELECT u.user_id, u.nom, u.prenom, cu.statut
				FROM `".$mysqlMainDb."`.`user` u
				LEFT JOIN `".$mysqlMainDb."`.`cours_user` cu
				ON cu.user_id = u.user_id AND cu.cours_id = $cours_id";
		$sel .= " WHERE u.".$dropbox_cnf["mailingWhere".$var]." = '";

		function getUser($thisRecip)
		{
			global $dropbox_lang, $var, $sel;
			unset($students);

			$result = db_query($sel);
			while (($res = mysql_fetch_array($result))) {$students[] = $res;}
			mysql_free_result($result);

			if (count($students) == 1)
			{
				return($students[0]);
			}
			elseif (count($students) > 1)
			{
				return ' <'.$dropbox_lang["mailingFileRecipDup"].$var."= $thisRecip>";
			}
			else
			{
				return ' <'.$dropbox_lang["mailingFileRecipNotFound"].$var."= $thisRecip>";
			}
		}

		$preFix = $nameParts[1]; $postFix = $nameParts[3];
		$preLen = strlen($preFix); $postLen = strlen($postFix);

		function findRecipient($thisFile)
		{
			global $dropbox_cnf, $dropbox_lang, $nameParts, $preFix, $preLen, $postFix, $postLen;

			if ( preg_match($dropbox_cnf["mailingFileRegexp"], $thisFile, $matches))
			{
				$thisName = $matches[1];
				if ( substr($thisName, 0, $preLen) == $preFix)
				{
					if ( $postLen == 0 || substr($thisName, -$postLen) == $postFix)
					{
						$thisRecip = substr($thisName, $preLen, strlen($thisName) - $preLen - $postLen);
						if ( $thisRecip) return getUser($thisRecip);
						return ' <'.$dropbox_lang["mailingFileNoRecip"].'>';
					}
					else
					{
						return ' <'.$dropbox_lang["mailingFileNoPostfix"].$postFix.'>';
					}
				}
				else
				{
					return ' <'.$dropbox_lang["mailingFileNoPrefix"].$preFix.'>';
				}
			}
			else
			{
				return ' <'.$dropbox_lang["mailingFileFunny"].'>';
			}
		}

		require("../../include/pclzip/pclzip.lib.php");

		$zipFile = new pclZip($mailing_file);
		$goodFiles  = array();
		$zipContent = $zipFile->listContent();
		$ucaseFiles = array();

		if ($zipContent)
		{
			foreach($zipFile->listContent() as $thisContent)
			{
				$thisFile = substr(strrchr('/' . $thisContent['filename'], '/'), 1);
				$thisFileUcase = strtoupper($thisFile);
				if (preg_match("~.(php.*|phtml)$~i", $thisFile))
				{
					$error = TRUE; $errormsg .= $thisFile . ': ' . $dropbox_lang["mailingZipPhp"];
					break;
				}
				elseif (!$thisContent['folder'])
				{
					if ($ucaseFiles[$thisFileUcase])
					{
						$error = TRUE; $errormsg .= $thisFile . ': ' . $dropbox_lang["mailingZipDups"];
						break;
					}
					else
					{
						$goodFiles[$thisFile] = findRecipient($thisFile);
						$ucaseFiles[$thisFileUcase] = "yep";
					}
				}

			}
		}
		else
		{
			$error = TRUE;
			$errormsg .= $dropbox_lang["mailingZipEmptyOrCorrupt"];
		}

		if (!$error)
		{
			$students = array();  // collect all recipients in this course
			foreach($goodFiles as $thisFile => $thisRecip)
			{
				$errormsg .= htmlspecialchars($thisFile) . ': ';
				if (is_string($thisRecip))  // see findRecipient
				{
					$errormsg .= '<font color="#FF0000">' . htmlspecialchars($thisRecip) . '</font><br>';
				}
				else
				{
					if (isset( $_GET['mailingSend']))
					{
						$errormsg .= $dropbox_lang["mailingFileSentTo"];
					}
					else
					{
						$errormsg .= $dropbox_lang["mailingFileIsFor"];
					}
					$errormsg .= htmlspecialchars($thisRecip[1].' '.$thisRecip[2]);

					if ( is_null($thisRecip[3]))
					{
						$errormsg .= $dropbox_lang["mailingFileNotRegistered"];
					}
					else
					{
						$students[] = $thisRecip[0];
					}
					$errormsg .= '<br>';

				}
			}

			// find student course members not among the recipients

			$sql = "SELECT u.nom, u.prenom
					FROM `".$mysqlMainDb."`.`cours_user` cu
					LEFT JOIN  `".$mysqlMainDb."`.`user` u
					ON cu.user_id = u.user_id AND cu.cours_id = $cours_id
					WHERE cu.statut = 5
					AND u.user_id NOT IN ('" . implode("', '" , $students) . "')";
			$result = db_query($sql);

			if ( mysql_num_rows($result) > 0)
			{
				$remainingUsers = '';
				while ( ($res = mysql_fetch_array($result)))
				{
					$remainingUsers .= ', ' . htmlspecialchars($res[0].' '.$res[1]);
				}
				$errormsg .= '<br>' . $dropbox_lang["mailingNothingFor"] . substr($remainingUsers, 1) . '.<br>';
			}

			if ( isset( $_GET['mailingSend']))
			{
				chdir($dropbox_cnf["sysPath"]);
				$zipFile->extract(PCLZIP_OPT_REMOVE_ALL_PATH);

				$mailingPseudoId = $dropbox_cnf["mailingIdBase"] + $mailing_item->id;

				foreach( $goodFiles as $thisFile => $thisRecip)
				{
					if ( is_string($thisRecip))  // remove problem file
					{
						@unlink($dropbox_cnf["sysPath"] . '/' . $thisFile);
					}
					else
					{
						$newName = $uid . "_" . $thisFile . "_" . uniqid('');
						if (rename($dropbox_cnf["sysPath"] . '/' . $thisFile, $dropbox_cnf["sysPath"] . '/' . $newName))
						new Dropbox_SentWork( $mailingPseudoId, $thisFile, $mailing_item->description, $mailing_item->author, $newName, $thisContent['size'], array($thisRecip[0]));
					}
				}

				$sendDT = addslashes(date("Y-m-d H:i:s",time()));
				// set filesize to zero on send, to avoid 2nd send (see index.php)
				$sql = "UPDATE `".$dropbox_cnf["fileTbl"]."`
						SET filesize = '0'
						, uploadDate = '".$sendDT."', lastUploadDate = '".$sendDT."'
						WHERE id='".addslashes($mailing_item->id)."'";
				$result = mysql_query($sql) or die($dropbox_lang["queryError"]);
			}
			elseif ( $mailing_item->filesize != 0)
			{
				$errormsg .= '<br>' . $dropbox_lang["mailingNotYetSent"] . '<br>';
			}
		}
	}
	else
	{
		$error = TRUE; $errormsg .= $dropbox_lang["mailingWrongZipfile"];
	}


	/*
     * ========================================
     * EXAMINE OR SEND MAILING FEEDBACK
     * ========================================
     */
	if ($error) {
		$tool_content.="<b><font color=\"#FF0000\">$errormsg</font></b><br><br>
		<a href=\"index.php\">".$dropbox_lang["backList"]."></a><br>";
	}
	else
	{
		$tool_content .= "$errormsg<br><br>
		<a href=\"index.php\">".$dropbox_lang["backList"]."</a><br>";
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

	if (isset($_GET['mailing']))  // RH
	{
		checkUserOwnsThisMailing($_GET['mailing'], $uid);
		$dropbox_person = new Dropbox_Person( $_GET['mailing'], $is_adminOfCourse, $is_adminOfCourse);
	}
	else
	{
		$dropbox_person = new Dropbox_Person( $uid, $is_adminOfCourse, $is_adminOfCourse);
	}

	if (isset($_SESSION["sentOrder"]))
	{
		$dropbox_person->orderSentWork ($_SESSION["sentOrder"]);
	}
	if (isset($_SESSION["receivedOrder"]))
	{
		$dropbox_person->orderReceivedWork ($_SESSION["receivedOrder"]);
	}

	if (isset( $_GET['deleteReceived']))
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

     /*
     * ========================================
     * DELETE FILE FEEDBACK
     * ========================================
     */
	$tool_content .= "<p class=\"success_small\">".$dropbox_lang["fileDeleted"]."<br />
	<a href='index.php'>".$dropbox_lang['backList']."</a></p><br/>";
}
draw($tool_content, 2, 'dropbox', $head_content);
?>
