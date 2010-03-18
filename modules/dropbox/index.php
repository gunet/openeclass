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
 * File exchange Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This is responsible for exchanging files between the users of a course
 *
 * Based on code by Jan Bols
 *
 */

require_once("dropbox_init1.inc.php");
$nameTools = $dropbox_lang["dropbox"];

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_DROPBOX');
/**************************************/

$tool_content .="
<div id=\"operations_container\">
  <ul id=\"opslist\">
    <li><a href=\"".$_SERVER['PHP_SELF']."?upload=1\">".$dropbox_lang['uploadFile']."</a></li>
  </ul>
</div>";

/*
* get order status of sent list.
* The sessionvar sentOrder keeps preference of user to by what field to order the sent files list by
*/

if (isset($_GET["sentOrder"]) && in_array($_GET["sentOrder"], array("lastDate", "firstDate", "title", "size", "author", "recipient"))) {
	$sentOrder = $_GET["sentOrder"];
} else {
	if (isset($_SESSION["sentOrder"]) && in_array($_SESSION["sentOrder"], array("lastDate", "firstDate", "title", "size", "author", "recipient"))) {
		$sentOrder = $_SESSION["sentOrder"];
	} else {
		$sentOrder = "lastDate"; //default sortorder value if nothing is specified
	}
}
$_SESSION['sentOrder'] = $sentOrder;

/*
* get order status of received list.
* The sessionvar receivedOrder keeps preference of user to by what field to order the received files list by
*/
if (isset($_GET["receivedOrder"]) && in_array($_GET["receivedOrder"], array("lastDate", "firstDate", "title", "size", "author", "sender"))) {
	$receivedOrder = $_GET["receivedOrder"];
} else {
	if (isset($_SESSION["receivedOrder"]) && in_array($_SESSION["receivedOrder"], array("lastDate", "firstDate", "title", "size", "author", "sender"))) {
		$receivedOrder = $_SESSION["receivedOrder"];
	} else {
		$receivedOrder = "lastDate"; //default sortorder value if nothing is specified
	}
}
$_SESSION['receivedOrder'] = $receivedOrder;

/*
* rest of variables
*/
require_once("dropbox_class.inc.php");

if (isset($_GET['mailing']))  // RH: Mailing detail window passes parameter
{
	checkUserOwnsThisMailing($_GET['mailing'], $uid);
	$dropbox_person = new Dropbox_Person( $_GET['mailing'], $is_courseAdmin, $is_courseTutor);
	$mailingInUrl = "&mailing=" . urlencode( $_GET['mailing']);
}
else
{
	$dropbox_person = new Dropbox_Person($uid, $is_adminOfCourse, $is_adminOfCourse);
	$mailingInUrl = "";
}
$dropbox_person->orderReceivedWork ($receivedOrder);
$dropbox_person->orderSentWork ($sentOrder);
$dropbox_unid = md5(uniqid(rand(), true));	//this var is used to give a unique value to every
//page request. This is to prevent resubmiting data

/*
 * ========================================
 * FORM UPLOAD FILE
 * ========================================
 */
if (isset($_GET['mailing']))  // RH: Mailing detail: no form upload
{
	$tool_content .= "<h3>". htmlspecialchars(getUserNameFromId($_GET['mailing'])). "</h3>";
	$tool_content .= "<a href='index.php'>".$dropbox_lang["mailingBackToDropbox"].'</a><br><br>';
}
elseif(isset($_REQUEST['upload']) && $_REQUEST['upload'] == 1)
{

	$tool_content .= <<<tCont2
    <form method="post" action="dropbox_submit.php" enctype="multipart/form-data" onsubmit="return checkForm(this)">
tCont2;
	$tool_content .= "
    <table width='99%' class='FormData'>
    <tbody>
    <tr>
      <th class='left' width='220'>&nbsp;</th>
      <td><b>".$dropbox_lang["uploadFile"]."</b></td>
    </tr>
    <tr>
      <th class='left'>".$dropbox_lang['file']." :</th>
      <td><input type='file' name='file' size='35' />
          <input type='hidden' name='dropbox_unid' value='$dropbox_unid' />
      </td>
    </tr>";


	if ($dropbox_person -> isCourseTutor || $dropbox_person -> isCourseAdmin)
	{
		$reciepientsSize= 5;
	}
	else
	{
		$reciepientsSize = 3;
	}

	$tool_content .= "
    <tr>
      <th class='left'>".$dropbox_lang["authors"]." :</th>
      <td><input type='text' name='authors' value='".getUserNameFromId($uid)."' size='40' class='FormData_InputText' /></td>
    </tr>
    <tr>
      <th class='left'>".$dropbox_lang["description"]." :</th>
      <td><textarea name='description' cols='37' rows='2' class='FormData_InputText'></textarea></td>
    </tr>
    <tr>
      <th class='left'>".$dropbox_lang["sendTo"]." :</th>
      <td>
        <select name='recipients[]' size='$reciepientsSize' multiple='true'  class='auth_input'>";

	/*
	*  if current user is a teacher then show all users of current course
	*/
	if ($dropbox_person -> isCourseTutor || $dropbox_person -> isCourseAdmin
	|| $dropbox_cnf["allowStudentToStudent"])  // RH: also if option is set

	{
		// select all users except yourself
		$sql = "SELECT DISTINCT u.user_id , CONCAT(u.nom,' ', u.prenom) AS name
        	FROM `" . $dropbox_cnf["userTbl"] . "` u, `" . $dropbox_cnf["courseUserTbl"] . "` cu
        	WHERE cu.cours_id = $dropbox_cnf[cid]
        	AND cu.user_id = u.user_id AND u.user_id != $uid
        	ORDER BY UPPER(u.nom), UPPER(u.prenom)";
	}
	/*
	* if current user is student then show all teachers of current course
	*/
	else
	{
		// select all the teachers except yourself
		$sql = "SELECT DISTINCT u.user_id , CONCAT(u.nom,' ', u.prenom) AS name
        	FROM `" . $dropbox_cnf["userTbl"] . "` u, `" . $dropbox_cnf["courseUserTbl"] . "` cu
        	WHERE cu.cours_id = $dropbox_cnf[cid]
        	AND cu.user_id = u.user_id AND (cu.statut <> 5 OR cu.tutor = 1) AND u.user_id != $uid
        	ORDER BY UPPER(u.nom), UPPER(u.prenom)";
	}
	$result = db_query($sql);
	while ($res = mysql_fetch_array($result))
	{
		$tool_content .= "
           <option value=".$res['user_id'].">".$res['name']."</option>";
	}
	if ($dropbox_person -> isCourseTutor || $dropbox_person -> isCourseAdmin)
	{
		if ( $dropbox_cnf["allowMailing"])  // RH: Mailing starting point
		{
			$tool_content .= '
           <option value="'.$dropbox_cnf["mailingIdBase"].'">'.$dropbox_lang["mailingInSelect"].'</option>';
		}
	}
	if ($dropbox_cnf["allowJustUpload"])  // RH
	{
		$tool_content .= '
           <option value="0">'.$dropbox_lang["justUploadInSelect"].'</option>';
	}

	$tool_content .= "
        </select>
      </td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type='Submit' name='submitWork' value='".$dropbox_lang["ok"]."' /></td>
    </tr>
    </tbody>
    </table>
    </form>
    <p align='right'><small>$langMaxFileSize ".ini_get('upload_max_filesize')."</small></p>";
	//==========================================================================
	//END of send_file form
	//==========================================================================
}

/*
 * ========================================
 * FILES LIST
 * ========================================
 */

/*
 * --------------------------------------
 * RECEIVED FILES LIST:  TABLE HEADER
 * --------------------------------------
 */
if (!isset($_GET['mailing']))  // RH: Mailing detail: no received files
{
	$numberDisplayed = count($dropbox_person -> receivedWork);
	$tool_content .= "
    <table width='99%' class='FormData'>
    <thead>
    <tr>
      <th class='left' width='220' style='border: 1px solid #edecdf'><u>
 	".strtoupper($dropbox_lang["receivedTitle"])."</u></th>";

	// check if there are received documents. If yes then display the icon deleteall
	$dr_unid = urlencode( $dropbox_unid);
	if ($numberDisplayed > 0)
	{
		$dr_lang_all = addslashes( $dropbox_lang["all"]);
		$tool_content .= "
      <th width='3' style='border: 1px solid #edecdf'>
        <a href='dropbox_submit.php?deleteReceived=all&amp;dropbox_unid=$dr_unid' onClick=\"return confirmationall('".$dropbox_lang['all']."');\"><img src='../../images/delete.gif' title='$langDelete' /></a></th>";
	}

	$tool_content .= "</tr>
      </thead>
      </table>

      <table width='99%' class='dropbox'>
      <thead>
      <tr>
         <th colspan='2' class='left'>&nbsp;$dropbox_lang[file]</th>
         <th width='130' class='left'>$dropbox_lang[authors]</th>
         <th width='130'>$dropbox_lang[date]</th>
         <th width='20'>$langDelete</th>
      </tr>
      </thead>
      <tbody>";

 /*
 * --------------------------------------
 * RECEIVED FILES LIST
 * --------------------------------------
 */

	$numberDisplayed = count($dropbox_person -> receivedWork);  // RH
	$i = 0;
	foreach ($dropbox_person -> receivedWork as $w)
	{
		if ($w -> uploaderId == $uid)  // RH: justUpload
		{
			$numberDisplayed -= 1; continue;
		}
		if ($i%2==0) {
	           $tool_content .= "\n       <tr>";
	        } else {
	           $tool_content .= "\n       <tr class=\"odd\">";
            }
		$tool_content .= "
        <td width=\"3\"><img src=\"../../template/classic/img/inbox.gif\" title=\"$dropbox_lang[receivedTitle]\" /></td>
        <td>";

		$tool_content .= "<a href='dropbox_download.php?id=".urlencode($w->id)."' target=_blank>".$w->title."</a>";

		$fSize = ceil(($w->filesize)/1024);
		$tool_content .= <<<tCont9
        <small>&nbsp;&nbsp;&nbsp;($fSize kB)</small>
        <br />
        <small>$w->description</small>
        </td>
tCont9;
		$tool_content .= "<td>$w->author</td><td>".$w->uploadDate;

		if ($w->uploadDate != $w->lastUploadDate)
		{
			$tool_content .= " (".$dropbox_lang['lastUpdated']." $w->lastUploadDate)";
		}

		$tool_content .= "
        </td>
        <td><div class=\"cellpos\">";

	$tool_content .= "
        <a href=\"dropbox_submit.php?deleteReceived=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid)."\" onClick='return confirmation(\"$w->title\");'>
        <img src=\"../../template/classic/img/delete-small.png\" title=\"$langDelete\" /></a>";

	$tool_content .= "</div></td></tr>";
	$i++;
	} //end of foreach
	if ($numberDisplayed == 0) {  // RH
	$tool_content .= "
      <tr>
        <td colspan=\"6\">".$dropbox_lang['tableEmpty']."</td>
      </tr>";
	}
	$tool_content .= "
      </tbody>
      </table>";

}  // RH: Mailing: end of 'Mailing detail: no received files'

/*
 * --------------------------------------
 *�SENT FILES LIST:  TABLE HEADER
 * --------------------------------------
 */

$numSent = count($dropbox_person -> sentWork);
$tool_content .= "

      <br />

      <table width='99%' class='FormData'>
      <tr>
        <th class='left' width='220' style='border: 1px solid #edecdf'><u>";
        $tool_content .= strtoupper($dropbox_lang["sentTitle"]);
        $tool_content .="</u></th>";
	// if the user has sent files then display the icon deleteall
	if ($numSent > 0) {
	$tool_content .= "
        <th width='3' style='border: 1px solid #edecdf'>
            <a href='dropbox_submit.php?deleteSent=all&amp;dropbox_unid=".urlencode( $dropbox_unid).$mailingInUrl."'
	onClick='return confirmationall('".addslashes($dropbox_lang["all"])."');'>
            <img src='../../images/delete.gif' title='$langDelete' /></a>
        </th>";
	}

	/* exoume vgalei to sort
	$tool_content .= "
        <form class=\"sort\" name=\"formSent\" method=\"get\" action=\"index.php\">
        <span class=\"dropbox_listTitle\">".$dropbox_lang["orderBy"]."</span>
         <select name=\"sentOrder\" onchange=\"javascript: this.form.submit()\" class=\"auth_input\">";

if ($sentOrder=="lastDate") {
	$tool_content .= "
           <option value=\"lastDate\" selected>";
} else {
	$tool_content .= "
           <option value=\"lastDate\">";
}
$tool_content .= "".$dropbox_lang['lastDate']."</option>";

$tool_content .= "".$dropbox_lang['firstDate']."</option>";

if ($sentOrder=="title") {
	$tool_content .= "
           <option value=\"title\" selected>";
} else {
	$tool_content .= "
           <option value=\"title\">";
}
$tool_content .= "".$dropbox_lang['title']."</option>";

if ($sentOrder=="size") {
	$tool_content .= "
           <option value=\"size\" selected>";
} else {
	$tool_content .= "
           <option value=\"size\">";
}
$tool_content .= "".$dropbox_lang['size']."</option>";

if ($sentOrder=="author") {
	$tool_content .= "
           <option value=\"author\" selected>";
} else {
	$tool_content .= "
           <option value=\"author\">";
}
$tool_content .= "".$dropbox_lang['author']."</option>";

if ($sentOrder=="recipient") {
	$tool_content .= "
           <option value=\"recipient\" selected>";
} else {
	$tool_content .= "
           <option value=\"recipient\">";
}
$tool_content .= "".$dropbox_lang['recipient']."</option>";

$tool_content .= "
        </select>
        </form>";
*/
$tool_content .= "
        <td>&nbsp;</td>
      </tr>
      </table>

      <table width=99% class='dropbox'>
      <thead>
      <tr>
        <th colspan='2' class='left'>&nbsp;$dropbox_lang[file]</th>
        <th width='130' class='left'>$dropbox_lang[col_recipient]</th>
        <th width='130'>$dropbox_lang[date]</th>
        <th width='20'>$langDelete</th>
      </tr>
      </thead>
      <tbody>
	";

/*
 * --------------------------------------
 * SENT FILES LIST
 * --------------------------------------
 */
$i = 0;
foreach ($dropbox_person -> sentWork as $w)
{
	$langSentTo = $dropbox_lang["sentTo"] . '&nbsp;';  // RH: Mailing: not for unsent

	// RH: Mailing: clickable folder image for detail

	if ( $w->recipients[0]['id'] > $dropbox_cnf["mailingIdBase"])
	{
		$ahref = "index.php?mailing=" . urlencode($w->recipients[0]['id']);
		$imgsrc = '../../images/folder.gif';
	}
	else
	{
		$ahref = "dropbox_download.php?id=" . urlencode($w->id) . $mailingInUrl;
		$imgsrc = '../../template/classic/img/outbox.gif';
	}
	$fSize = ceil(($w->filesize)/1024);
		if ($i%2==0) {
	           $tool_content .= "\n       <tr>";
	        } else {
	           $tool_content .= "\n       <tr class=\"odd\">";
            	}
	$tool_content .= <<<tCont12

		<td width="3"><img src="../../template/classic/img/outbox.gif" title="$w->title" /></td>
		<td ><a href="$ahref" target="_blank">
		$w->title</a>
        <small>&nbsp;&nbsp;&nbsp;($fSize kB)</small>
        <br />
        <small>$w->description</small></td>

tCont12;
	$tool_content .="<td>";

	foreach($w -> recipients as $r)
	{
		$tool_content .=  $r["name"] . ", <br>\n";
	}
	$tool_content = strrev(substr(strrev($tool_content), 7));

	$tool_content .= "</td><td>$w->uploadDate</td>

		<td><div class=\"cellpos\">";
	//<!--	Users cannot delete their own sent files -->

	$tool_content .= "
	<a href=\"dropbox_submit.php?deleteSent=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid) . $mailingInUrl."\"
		onClick='return confirmation(\"$w->title\");'>
		<img src=\"../../template/classic/img/delete-small.png\" title=\"$langDelete\" /></a>";
	$tool_content .= "</div></td></tr>";

	// RH: Mailing: clickable images for examine and send
	if ($w -> uploadDate != $w->lastUploadDate) {
		$tool_content .= "<tr><td colspan=\"2\">
		<span class=\"dropbox_detail\">".$dropbox_lang["lastResent"]." <span class=\"dropbox_date\">$w->lastUploadDate</span></span></td>
		</tr>";
	}
	$i++;
} //end of foreach

if (count($dropbox_person->sentWork)==0) {
	$tool_content .= "<tr>
	<td colspan=\"6\">".$dropbox_lang['tableEmpty']."</td></tr>";
}

$tool_content .= "</tbody></table>";
add_units_navigation(TRUE);
draw($tool_content, 2, 'dropbox', $head_content);

