<?php
/**
 * Dropbox module for Claroline
 * GUI interface page for Dropbox. This contains the upload form and the sent and received files list
 *
 * 1. Initialises vars and check installed tables
 * 2. Form upload
 * 3. Received & sent lists
 *
 * @version 1.20
 * @copyright 2004
 * @author Jan Bols <jan@ivpv.UGent.be>
 * with contributions by René Haentjens <rene.haentjens@UGent.be> (see RH)
 *
 * RH: Mailing: this form is called with ?mailing=pseudo_id for detail window
 */
/**
 *   +----------------------------------------------------------------------
 *   |   This program is free software; you can redistribute it and/or     
 *   |   modify it under the terms of the GNU General Public License       
 *   |   as published by the Free Software Foundation; either version 2    
 *   |   of the License, or (at your option) any later version.            
 *   |                                                                     
 *   |   This program is distributed in the hope that it will be useful,   
 *   |   but WITHOUT ANY WARRANTY; without even the implied warranty of    
 *   |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     
 *   |   GNU General Public License for more details.                      
 *   |                                                                      
 *   |   You should have received a copy of the GNU General Public License 
 *   |   along with this program; if not, write to the Free Software       
 *   |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA         
 *   |   02111-1307, USA. The GNU GPL license is also available through    
 *   |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html        
 *   +----------------------------------------------------------------------
 * | Authors: Jan Bols          <jan@ivpv.UGent.be>                       |
 *   +----------------------------------------------------------------------
 */

require_once("dropbox_init1.inc.php");
$nameTools = $dropbox_lang["dropbox"];
//begin_page();

//<style type="text/css">
//	.dropbox_detail {  font-size: small}
//	.dropbox_date {  font-style: italic}
//	.dropbox_person {  font-weight: bold}
//	.dropbox_listTitle { font-style: italic; color: black}
//</style>



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
session_register("sentOrder");


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
session_register("receivedOrder");

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

/**
 * ========================================
 * FORM UPLOAD FILE
 * ========================================
 */
if (isset($_GET['mailing']))  // RH: Mailing detail: no form upload
{
	$tool_content .= "<h3>". htmlspecialchars(getUserNameFromId($_GET['mailing'])). "</h3>";
	$tool_content .= "<a href='index.php?origin=$origin'>".$dropbox_lang["mailingBackToDropbox"].'</a><br><br>';
}
else
{

 if (isset($origin)) {  
	$tool_content .= <<<tCont
	<form method="post" action="dropbox_submit.php?origin=$origin" enctype="multipart/form-data" onsubmit="return checkForm(this)">
tCont;
 } else { $tool_content .= <<<tCont2
	<form method="post" action="dropbox_submit.php" enctype="multipart/form-data" onsubmit="return checkForm(this)">
tCont2;
 } 
if ($dropbox_cnf["allowOverwrite"]) {
	$jsCheckFile = 'onChange="checkfile(this.value)"'; 
} else {
	$jsCheckFile ="";
}
$tool_content .= "
<table>
<thead>
	<tr>
		<th>
		".$dropbox_lang["uploadFile"]." :
		</th>
		<td>
			<input type=\"file\" name=\"file\" size=\"\" $jsCheckFile>
			<input type=\"hidden\" name=\"dropbox_unid\" value=\"$dropbox_unid\">";
		
			if (isset($origin) and $origin=='learnpath') 
				 $tool_content .= "<input type='hidden' name='origin' value='learnpath'>"; 
		$tool_content .= "
		</td>
	</tr>";

	if ($dropbox_cnf["allowOverwrite"]) {
$tool_content .= "
	<tr id=\"overwrite\" style=\"display: none\">
		<td>
		</td>
		<td>
		<input type=\"checkbox\" name=\"cb_overwrite\" id=\"cb_overwrite\" value=\"true\">".$dropbox_lang["overwriteFile"]."
		</td>
	</tr>";

	}
	
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
	<th >
		".$dropbox_lang["authors"]." :
	</th>
	<td>
		<input type=\"text\" name=\"authors\" value=\"".getUserNameFromId($uid)."\" size=\"30\"> 
	</td>
	</tr>
	<tr>
	<th>
		".$dropbox_lang["description"]." :
	</th>
	<td>
		<textarea name=\"description\" cols=\"25\" rows=\"2\"></textarea>
	</td>
	</tr>
	<tr>
		<th>
			".$dropbox_lang["sendTo"]." :
		</th>
		<td>
			<select name=\"recipients[]\" size=\"$reciepientsSize\" multiple>";



/*
*  if current user is a teacher then show all users of current course
*/
if ($dropbox_person -> isCourseTutor || $dropbox_person -> isCourseAdmin 
	|| $dropbox_cnf["allowStudentToStudent"])  // RH: also if option is set

{
	// select all users except yourself
    $sql = "SELECT DISTINCT u.user_id , CONCAT(u.nom,' ', u.prenom) AS name
        	FROM `" . $dropbox_cnf["userTbl"] . "` u, `" . $dropbox_cnf["courseUserTbl"] . "` cu
        	WHERE cu.code_cours='" . $dropbox_cnf["courseId"] . "'
        	AND cu.user_id=u.user_id AND u.user_id != '" .$uid. "'
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
        	WHERE cu.code_cours='" . $dropbox_cnf["courseId"] . "'
        	AND cu.user_id=u.user_id AND (cu.statut!=5 OR cu.tutor=1) AND u.user_id != '" .$uid. "'
        	ORDER BY UPPER(u.nom), UPPER(u.prenom)";
}
$result = db_query($sql);
while ($res = mysql_fetch_array($result))
{
	$tool_content .= "<option value=".$res['user_id'].">".$res['name']."</option>";
}
if ($dropbox_person -> isCourseTutor || $dropbox_person -> isCourseAdmin)
{
    if ( $dropbox_cnf["allowMailing"])  // RH: Mailing starting point
    {
	    $tool_content .= '<option value="'.$dropbox_cnf["mailingIdBase"].'">'.$dropbox_lang["mailingInSelect"].'</option>';
    }
}
    if ($dropbox_cnf["allowJustUpload"])  // RH
    {
	    $tool_content .= '<option value="0">'.$dropbox_lang["justUploadInSelect"].'</option>';
    }

$tool_content .= "
        	</select>
		</td>
	</tr>
	</thead></table>
	<br>
		<input type=\"Submit\" name=\"submitWork\" value=\"".$dropbox_lang["ok"]."\">
</form>
<br>";
//==========================================================================
//END of send_file form
//==========================================================================
}  // RH: Mailing: end of 'Mailing detail: no form upload'

/**
 * ========================================
 * FILES LIST
 * ========================================
 */

$tool_content .= <<<tCont3

tCont3;
/**
 * --------------------------------------
 *       RECEIVED FILES LIST:  TABLE HEADER
 * --------------------------------------
 */
if (!isset($_GET['mailing']))  // RH: Mailing detail: no received files
{
$numberDisplayed = count($dropbox_person -> receivedWork);  
$tool_content .= <<<tCont4
	<table width="99%">
	<thead>
	<tr>
	<th>
tCont4;
$tool_content .= "
 	".strtoupper($dropbox_lang["receivedTitle"])."";
$tool_content .= <<<tCont5
	</th>
	
	
	<td>
	
tCont5;
if (!isset($origin)) {
	$origin="";
}
$tool_content .= "
<div>
		<form class=\"sort\" name=\"formReceived\" method=\"get\" action=\"index.php?origin=$origin\">
		".$dropbox_lang["orderBy"]."";
		
		 if (isset($origin) and $origin=='learnpath') 
			 $tool_content .= "<input type='hidden' name='origin' value='learnpath'>"; 
			 
		 
		$tool_content .= "
		<select name=\"receivedOrder\" onchange=\"javascript: this.form.submit()\">";
		
		if ($receivedOrder=="lastDate") {
			$tool_content .= "<option value=\"lastDate\" selected>";
		}	else {
			$tool_content .= "<option value=\"lastDate\">";
		}
		
		$tool_content .= "".$dropbox_lang['lastDate']."</option>";
		
		if ($dropbox_cnf["allowOverwrite"]) { 
			if ($receivedOrder=="firstDate") {
				$tool_content .= "<option value=\"firstDate\" selected>";	
			} else {
				$tool_content .= "<option value=\"firstDate\">";
			}
		}
			$tool_content .= "".$dropbox_lang['firstDate']."</option>";
			
			if ($receivedOrder=="title"){
				$tool_content .="<option value=\"title\" selected>";
			} else {
				$tool_content .="<option value=\"title\">";
			}
			$tool_content .= "".$dropbox_lang['title']."</option>";
			
			if ($receivedOrder=="size"){
				$tool_content .="option value=\"size\" selected>";
			} else {
				$tool_content .="<option value=\"size\">";
			}
			$tool_content .= "".$dropbox_lang['size']."</option>";
			
			if ($receivedOrder=="author"){
				$tool_content .="option value=\"author\" selected>";
			} else {
				$tool_content .="<option value=\"author\">";
			}
			
			$tool_content .= "".$dropbox_lang['author']."</option>";
			
			if ($receivedOrder=="sender"){
				$tool_content .="option value=\"sender\" selected>";
			} else {
				$tool_content .="<option value=\"ender\">";
			}
			$tool_content .= "".$dropbox_lang['sender']."</option>";
			
			$tool_content .= "
			</select>
			</form>
			</div>
		</td>
		<td ><div class=\"cellpos\">";

// check if there are received documents. If yes then display the icon deleteall
$dr_unid = urlencode( $dropbox_unid);
if ($numberDisplayed > 0) {
	
	$dr_lang_all = addslashes( $dropbox_lang["all"]);
	if (isset($origin)) { $tool_content .= <<<tCont6
	<a href="dropbox_submit.php?origin=$origin&deleteReceived=all&dropbox_unid=$dr_unid" onClick="return confirmationall('$dr_lang_all');">
		<img src="../../images/delete.gif" border="0" title="$langDelete"></a>
tCont6;
} else {$tool_content .= "
	<a href=\"dropbox_submit.php?deleteReceived=all&dropbox_unid=$dr_unid\" onClick=\"return confirmationall('".$dropbox_lang["all"]."');\">
	<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";

	}
} 

 if (isset($origin) and $origin=='learnpath')
	  $tool_content .= "<input type='hidden' name='origin' value='learnpath'>"; 

$tool_content .= "
		</div></td>
		</tr>
		</thead>
		</table><br>
		<table width=99%>
		<thead>
		<tr>
			<th>".$dropbox_lang['file']."</th>
			<th>".$dropbox_lang['fileSize']."</th>
			<th>".$dropbox_lang["authors"]."</th>
			<th>".$dropbox_lang['date']."</th>
			<th>".$dropbox_lang["description"]."</th>
			<th>$langDelete</th>
		</tr>
		</thead>
		<tbody>
		<!--</table>-->

";

/**
 * --------------------------------------
 *       RECEIVED FILES LIST
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

	$tool_content .= "
	<tr>
	<!--<td >wwww-->";
	
	/*if (isset($origin))  {
	$tool_content .= "<a href='dropbox_download.php?origin=$origin&id=".urlencode($w->id)."' target=_blank>
		<img  src='../../images/travaux.gif' border='0'></a>";
	} else  {
	$tool_content .= "<a href='dropbox_download.php?id=".urlencode($w->id)."' target=_blank>
		<img  src='../../images/travaux.gif' border='0'></a>";
	}*/
	
	$tool_content .= <<<tCont8
	<!--</td>
	<td >-->
	<!--<table width="99%" border="0" cellspacing="0" cellpadding="0">-->
	
	<td>
tCont8;

	if (isset($origin)) 
		$tool_content .= "<a href='dropbox_download.php?origin=$origin&id=".urlencode($w->id)."' target=_blank'>".$w->title."</a>"; 
	else
		$tool_content .= "<a href='dropbox_download.php?id=".urlencode($w->id)."' target=_blank>".$w->title."</a>"; 

$fSize = ceil(($w->filesize)/1024);
$tool_content .= <<<tCont9
	</td>
	<td>
	$fSize kB
	</td>
	
tCont9;

	$tool_content .= "<td >
		$w->author
		</td>
		
		<td>$w->uploadDate";
		
		if ($w->uploadDate != $w->lastUploadDate)
	{

$tool_content .= "
	
	(".$dropbox_lang['lastUpdated']." $w->lastUploadDate)
	";
	

	}
		
		
	$tool_content .= "	</td>
		
	<td >$w->description</td>
	<td><div class=\"cellpos\">

	";

if (isset($origin)) { $tool_content .= "
	<a href=\"dropbox_submit.php?origin=$origin\"&deleteReceived=".urlencode($w->id)."&dropbox_unid=".urlencode($dropbox_unid)."\" onClick='return confirmation(\"$w->title\");'>
	<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";
 } else { $tool_content .= "
	<a href=\"dropbox_submit.php?deleteReceived=".urlencode($w->id)."&dropbox_unid=".urlencode($dropbox_unid)."\" onClick='return confirmation(\"$w->title\");'>
	<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";
 
	} 

$tool_content .="
	</div>
	</td>
	</tr>";


//    if ($w -> author != '')
//    {
//$tool_content .= "
//	<tr>
//		<td colspan=\"2\"><span class=\"dropbox_detail\">
//		".$dropbox_lang["authors"].': '.$w->author."
//		</span></td>
//	</tr> ";
//
//    }
//    if ($w->description != '')
//    {
//		//only show description info if this is filled in in DB
//       $tool_content .= "
//	<tr>
//	<td colspan=\"2\"><span class=\"dropbox_detail\">".$dropbox_lang["description"].': '.$w->description."</span></td>
//	</tr> ";
//	
//    }
//    $tool_content .= "
//	<tr>
//	<td colspan=\"2\"><span class=\"dropbox_detail\">
//	".$dropbox_lang["sentBy"]."
//	 <span class=\"dropbox_person\">$w->uploaderName</span>".$dropbox_lang["sentOn"]." 
//	<span class=\"dropbox_date\">$w->uploadDate</span>
//	</span></td>
//	</tr> ";

//	if ($w->uploadDate != $w->lastUploadDate)
//	{
//
//$tool_content .= "
//	<tr>
//	<td colspan=\"2\">
//	<span class=\"dropbox_detail\">".$dropbox_lang['lastUpdated']."
//	<span class=\"dropbox_date\">$w->lastUploadDate</span></span>
//	</td>
//	</tr>";
//	
//
//	}
//$tool_content .= "
//	</table>
//	</td>
//	</tr>";
//

    	$i++;
	} //end of foreach
	if ($numberDisplayed == 0) {  // RH
$tool_content .= "
<tr>
<td colspan=\"6\">".$dropbox_lang['tableEmpty']."
</td>
</tr>";
	
	}
$tool_content .= "
</tbody>
</table>";

}  // RH: Mailing: end of 'Mailing detail: no received files'

/**
 * --------------------------------------
 *       SENT FILES LIST:  TABLE HEADER
 * --------------------------------------
 */

$numSent = count($dropbox_person -> sentWork);
$tool_content .= <<<tCont10
	<br><br><br>
	<table width="99%">
	<thead>
	<tr>
	<th>
tCont10;
$tool_content .= strtoupper($dropbox_lang["sentTitle"]);
$tool_content .="
	</th>
	<td>";	
$tool_content .= "	
	<form class=\"sort\" name=\"formSent\" method=\"get\" action=\"index.php?origin=$origin\">";

		 if (isset($origin) and $origin=='learnpath') 
			 $tool_content .= "<input type='hidden' name='origin' value='learnpath'>"; 
$tool_content .= "
	<span class=\"dropbox_listTitle\">".$dropbox_lang["orderBy"]."</span>
	<select name=\"sentOrder\" onchange=\"javascript: this.form.submit()\">";

	if ($sentOrder=="lastDate") {
		$tool_content .= "<option value=\"lastDate\" selected>";
	} else {
		$tool_content .= "<option value=\"lastDate\">";
	}
	$tool_content .= "".$dropbox_lang['lastDate']."</option>";
	
	if ($dropbox_cnf["allowOverwrite"]) {
		$tool_content .= "<option value=\"firstDate\" selected>";
	} else {
		$tool_content .= "<option value=\"firstDate\">";
	}
	$tool_content .= "".$dropbox_lang['firstDate']."</option>";

	if ($sentOrder=="title") {
		$tool_content .= "<option value=\"title\" selected>";
	} else {
		$tool_content .= "<option value=\"title\">";
	}
	$tool_content .= "".$dropbox_lang['title']."</option>";

	if ($sentOrder=="size") {
		$tool_content .= "<option value=\"size\" selected>";
	} else {
		$tool_content .= "<option value=\"size\">";
	}
	$tool_content .= "".$dropbox_lang['size']."</option>";

	if ($sentOrder=="author") {
		$tool_content .= "<option value=\"author\" selected>";
	} else {
		$tool_content .= "<option value=\"author\">";
	}
	$tool_content .= "".$dropbox_lang['author']."</option>";
	
	if ($sentOrder=="recipient") {
		$tool_content .= "<option value=\"recipient\" selected>";
	} else {
		$tool_content .= "<option value=\"recipient\">";
	}
	$tool_content .= "".$dropbox_lang['recipient']."</option>";
	
$tool_content .= "
	</select>
	</form>
	</td>
	<td >

<div class=\"cellpos\">";
// if the user has sent files then display the icon deleteall
if ($numSent > 0) { 

if (isset($origin)) { $tool_content .= "
	<a href=\"dropbox_submit.php?origin=$origin&deleteSent=all&dropbox_unid=".urlencode( $dropbox_unid).$mailingInUrl."\"
	onClick=\"return confirmationall('".addslashes($dropbox_lang["all"])."');\">
	<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";
 } else { $tool_content .= "
	<a href=\"dropbox_submit.php?deleteSent=all&dropbox_unid=".urlencode( $dropbox_unid).$mailingInUrl."\"
	onClick=\"return confirmationall('".addslashes($dropbox_lang["all"])."');\">
	<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";

	} 
}
$tool_content .= "
 	</div></td>
	</tr>
	</thead>
	</table>
	<br>
	<table width=99%>
		<thead>
		<tr>
			<th>".$dropbox_lang['file']."</th>
			<th>".$dropbox_lang['fileSize']."</th>
			<th>".$dropbox_lang['col_recipient']."</th>
			<th>".$dropbox_lang['date']."</th>
			<th>".$dropbox_lang["description"]."</th>
			<th>$langDelete</th>
		</tr>
		</thead>
		<tbody>
	";



/**
 * --------------------------------------
 *       SENT FILES LIST
 * --------------------------------------
 */
$i = 0;
foreach ($dropbox_person -> sentWork as $w)
{
	$langSentTo = $dropbox_lang["sentTo"] . '&nbsp;';  // RH: Mailing: not for unsent

	// RH: Mailing: clickable folder image for detail

	if ( $w->recipients[0]['id'] > $dropbox_cnf["mailingIdBase"])
	{
		if (isset($origin)) 
			$ahref = "index.php?origin=$origin&mailing=" . urlencode($w->recipients[0]['id']);
		else 
			$ahref = "index.php?mailing=" . urlencode($w->recipients[0]['id']);
		$imgsrc = '../../images/folder.gif';
	}
	else
	{
		if (isset($origin))
			$ahref = "dropbox_download.php?origin=$origin&id=" . urlencode($w->id) . $mailingInUrl;
		else
			$ahref = "dropbox_download.php?id=" . urlencode($w->id) . $mailingInUrl;
		$imgsrc = '../../images/travaux.gif';
	}
$fSize = ceil(($w->filesize)/1024);
$tool_content .= <<<tCont12
		<tr>
		<td >
		<a href="$ahref" target="_blank">
		$w->title</a> 
		</td>
		<td>
		$fSize kB
		</td>
tCont12;
$tool_content .="
		<td>";
		
			foreach($w -> recipients as $r)
			{
				$tool_content .=  $r["name"] . ", <br>\n";
			}
			$tool_content = strrev(substr(strrev($tool_content), 7));
			
			$tool_content .= "
		</td>
		<td>
		
		$w->uploadDate
		</td>
		<td>
		$w->description
		</td>
		<td><div class=\"cellpos\">";
		//<!--	Users cannot delete their own sent files -->

	if (isset($origin)) {
$tool_content .= "
		<a href=\"dropbox_submit.php?origin=$origin&deleteSent=".urlencode($w->id)."&dropbox_unid=".urlencode($dropbox_unid) . $mailingInUrl."\"
		onClick='return confirmation(\"$w->title\");'>
		<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";

	} else {
$tool_content .= "		
	<a href=\"dropbox_submit.php?deleteSent=".urlencode($w->id)."&dropbox_unid=".urlencode($dropbox_unid) . $mailingInUrl."\"
		onClick='return confirmation(\"$w->title\");'>
		<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";

	} //if delete...
		
		$tool_content .= "</div>
		</td>
		</tr>
		";


 // RH: Mailing: clickable images for examine and send
/*if ($w->recipients[0]['id'] == $uid)
{
	$langSentTo = $dropbox_lang["justUploadInList"] . '&nbsp;';  // RH: justUpload
}
elseif ($w->recipients[0]['id'] > $dropbox_cnf["mailingIdBase"])
{
$tool_content .= "
<a href=\"dropbox_submit.php?origin=$origin&mailingIndex=".urlencode( $i)."&dropbox_unid=".urlencode( $dropbox_unid).$mailingInUrl."\">
<img src=\"../image/checkzip.gif\" border=\"0\" title=\"".$dropbox_lang["mailingExamine"]."\"></a>";
  // RH: Mailing: filesize is set to zero on send, allow no 2nd send!
	if ($w->filesize != 0)
	{
		$langSentTo = '';  // unsent: do not write 'Sent to'

	if (isset($origin)) {
$tool_content .= "
	<a href=\"dropbox_submit.php?origin=$origin>&mailingIndex=".urlencode($i)."&mailingSend=yes&dropbox_unid=".urlencode( $dropbox_unid).$mailingInUrl."\" onClick='return confirmsend();'>
		<img src=\"../../images/sendzip.gif\" border=\"0\" title=\"".$dropbox_lang["mailingSend"]."\"></a>";


	 } else {
$tool_content .= "
	<a href=\"dropbox_submit.php?mailingIndex=".urlencode($i)."&mailingSend=yes&dropbox_unid=".urlencode( $dropbox_unid).$mailingInUrl."\" onClick='return confirmsend();'>
		<img src=\"../../images/sendzip.gif\" border=\"0\" title=\"".$dropbox_lang["mailingSend"]."\"></a>";


	} 

	}
}*/

//<!--	Users cannot delete their own sent files -->
//
//	if (isset($origin)) {
//$tool_content .= "
//		<a href=\"dropbox_submit.php?origin=$origin&deleteSent=".urlencode($w->id)."&dropbox_unid=".urlencode($dropbox_unid) . $mailingInUrl."\"
//		onClick='return confirmation(\"$w->title\");'>
//		<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";
//
//	} else {
//$tool_content .= "		
//	<a href=\"dropbox_submit.php?deleteSent=".urlencode($w->id)."&dropbox_unid=".urlencode($dropbox_unid) . $mailingInUrl."\"
//		onClick='return confirmation(\"$w->title\");'>
//		<img src=\"../../images/delete.gif\" border=\"0\" title=\"$langDelete\"></a>";
//
//	} //if delete...
	
/*$tool_content .= "
	</td>
	</tr>";*/

//    if ($w -> author != '')
//    {
//$tool_content .= "
//	<tr>
//	<td colspan=\"2\">
//	<span class=\"dropbox_detail\">".$dropbox_lang["authors"].': '.$w->author."<br></span>
//	</td>
//	</tr> ";
//
//    }
//    if ($w -> description != '')
//    {
//$tool_content .= "
//	<tr>
//	<td colspan=\"2\"><span class=\"dropbox_detail\">
//	".$dropbox_lang["description"].': '.$w->description."<br>
//	</span></td>
//	</tr> ";
//
//
//    }
/*$tool_content .= <<<tCont13
		<tr>
		<td colspan="2">
		<table border="0" cellspacing="0" cellpadding="0">
		<tr>
		<td align="left" valign="top">
		<span class="dropbox_detail">$langSentTo</span>
		</td>
		<td align="left" valign="top"><span class="dropbox_detail">
		<span class="dropbox_person">
tCont13;*/

//		foreach($w -> recipients as $r)
//			{
//				$tool_content .=  $r["name"] . "<br>\n";
//			}
//														
//$tool_content .= "
//		</span></span></td>
//		<td align=\"left\" valign=\"top\">
//		<span class=\"dropbox_detail\">&nbsp;".$dropbox_lang["sentOn"]."
//		<span class=\"dropbox_date\">$w->uploadDate</span></span>
//		</td>
//		</tr>
//		</table>
//		</td>
//		</tr> ";

	if ($w -> uploadDate != $w->lastUploadDate) {
$tool_content .= "
		<tr>
		<td colspan=\"2\"><span class=\"dropbox_detail\">".$dropbox_lang["lastResent"]." <span class=\"dropbox_date\">$w->lastUploadDate</span></span></td>
		</tr>";

	}
/*$tool_content .= "
		</table>
		</td>
		</tr>";*/

    $i++;
	} //end of foreach
	if (count($dropbox_person->sentWork)==0) {
$tool_content .= "
	<tr>
	<td colspan=\"6\">".$dropbox_lang['tableEmpty']."
	</td>
	</tr>";

	}
$tool_content .= "
</tbody>
	</table>
";


draw($tool_content, 2, 'dropbox', $head_content);
?>

