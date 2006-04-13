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
begin_page();

?>
<style type="text/css">
	.dropbox_detail {  font-size: small}
	.dropbox_date {  font-style: italic}
	.dropbox_person {  font-weight: bold}
	.dropbox_listTitle { font-style: italic; color: black}
</style>

<?

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
	echo "<h3>", htmlspecialchars(getUserNameFromId ($_GET['mailing'])), "</h3>";
	echo "<a href='index.php?origin=$origin'>".$dropbox_lang["mailingBackToDropbox"].'</a><br><br>';
}
else
{
?>

<? if (isset($origin)) {  ?>
	<form method="post" action="dropbox_submit.php?<? echo "origin=$origin"; ?>" enctype="multipart/form-data" onsubmit="return checkForm(this)">
<? } else { ?>
	<form method="post" action="dropbox_submit.php" enctype="multipart/form-data" onsubmit="return checkForm(this)">
<? } ?>

<table align="center">
	<tr>
		<td align="right">
		<?=$dropbox_lang["uploadFile"]?> :
		</td>
		<td>
			<input type="file" name="file" size="20" <? if ($dropbox_cnf["allowOverwrite"]) echo 'onChange="checkfile(this.value)"'; ?>>
			<input type="hidden" name="dropbox_unid" value="<?=$dropbox_unid?>">
		<?
			if (isset($origin) and $origin=='learnpath') 
				 echo "<input type='hidden' name='origin' value='learnpath'>"; 
		?>
		</td>
	</tr>
<?
	if ($dropbox_cnf["allowOverwrite"]) {
?>
	<tr id="overwrite" style="display: none">
		<td valign="top" align="right">
		</td>
		<td>
		<input type="checkbox" name="cb_overwrite" id="cb_overwrite" value="true"><?=$dropbox_lang["overwriteFile"]?>
		</td>
	</tr>
<?
	}
?>
	<tr>
	<td valign="top" align="right">
		<?= $dropbox_lang["authors"]?> :
	</td>
	<td>
		<input type="text" name="authors" value="<?= getUserNameFromId($uid)?>" size="30"> 
	</td>
	</tr>
	<tr>
	<td valign="top" align="right">
		<?=$dropbox_lang["description"]?> :
	</td>
	<td>
		<textarea name="description" cols="25" rows="2"></textarea>
	</td>
	</tr>
	<tr>
		<td valign="top"  align="right">
			<?=$dropbox_lang["sendTo"]?> :
		</td>
		<td valign="top"  align="left">
			<select name="recipients[]" size="<?php
if ($dropbox_person -> isCourseTutor || $dropbox_person -> isCourseAdmin)
{
    echo 5;
}
else
{
    echo 3;
}

?>" multiple>

<?

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
	echo "<option value=".$res['user_id'].">".$res['name']."</option>";
}
if ($dropbox_person -> isCourseTutor || $dropbox_person -> isCourseAdmin)
{
    if ( $dropbox_cnf["allowMailing"])  // RH: Mailing starting point
    {
	    echo '<option value="'.$dropbox_cnf["mailingIdBase"].'">'.$dropbox_lang["mailingInSelect"].'</option>';
    }
}
    if ($dropbox_cnf["allowJustUpload"])  // RH
    {
	    echo '<option value="0">'.$dropbox_lang["justUploadInSelect"].'</option>';
    }

?>
        	</select>
		</td>
	</tr>
	<tr>
	<td></td>
	<td>
		<input type="Submit" name="submitWork" value="<?=$dropbox_lang["ok"]?>">
	</td>
	</tr>
</table>
</form>

<?
}  // RH: Mailing: end of 'Mailing detail: no form upload'

/**
 * ========================================
 * FILES LIST
 * ========================================
 */

?>
<table border="1" cellspacing="0" cellpadding="0" width="100%">
	<tr>
	<td valign="top" align="center">
<?
/**
 * --------------------------------------
 *       RECEIVED FILES LIST:  TABLE HEADER
 * --------------------------------------
 */
if (!isset($_GET['mailing']))  // RH: Mailing detail: no received files
{
$numberDisplayed = count($dropbox_person -> receivedWork);  
?>
	<table cellpadding="5" cellspacing="1" border="0" width="100%">
	<tr bgcolor="<?= $color2; ?>">
	<td align="center" colspan="2">
 	<div class="dropbox_listTitle"><?= strtoupper($dropbox_lang["receivedTitle"])?></div>
	</td>
	</tr>
	<tr bgcolor="<?= $color1; ?>">
	<td colspan="2">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td align="left" width="100%">
		<form name="formReceived" method="get" action="index.php?<? echo "origin=$origin"; ?>">
		<span class="dropbox_listTitle"><?= $dropbox_lang["orderBy"]?></span>
		<?
		 if (isset($origin) and $origin=='learnpath') 
			 echo "<input type='hidden' name='origin' value='learnpath'>"; 
		?>
		<select name="receivedOrder" onchange="javascript: this.form.submit()">
		<option value="lastDate" <? if ($receivedOrder=="lastDate") {
			echo "selected";
			}?>><?=$dropbox_lang['lastDate']?></option>
			<? if ($dropbox_cnf["allowOverwrite"]) { ?>
			<option value="firstDate" <? if ($receivedOrder=="firstDate") {
				echo "selected";
			}?>><?=$dropbox_lang['firstDate']?></option>
				<?php } ?>
			<option value="title" <? if ($receivedOrder=="title") {
				echo "selected";
				}?>><?=$dropbox_lang['title']?></option>
			<option value="size" <? if ($receivedOrder=="size") {
				echo "selected";
				}?>><?=$dropbox_lang['size']?></option>
			<option value="author" <? if ($receivedOrder=="author") {
				echo "selected";
				}?>><?=$dropbox_lang['author']?></option>
			<option value="sender" <? if ($receivedOrder=="sender") {
				echo "selected";
				}?>><?=$dropbox_lang['sender']?></option>
			</select>
			</form>
		</td>
		<td align="right" width="30%">
<? 
// check if there are received documents. If yes then display the icon deleteall
if ($numberDisplayed > 0) {
	if (isset($origin)) { ?>
	<a href="dropbox_submit.php?<?echo "origin=$origin"; ?>&deleteReceived=all&dropbox_unid=<?=urlencode( $dropbox_unid)?>" onClick="return confirmationall('<?=addslashes( $dropbox_lang["all"])?>');">
		<img src="../../images/delete.gif" border="0" title="<?= $langDelete; ?>"></a>
<? } else { ?>
	<a href="dropbox_submit.php?deleteReceived=all&dropbox_unid=<?=urlencode($dropbox_unid)?>" onClick="return confirmationall('<?echo $dropbox_lang["all"]?>');">
	<img src="../../images/delete.gif" border="0" title="<?= $langDelete; ?>"></a> 

	<? }
} 

 if (isset($origin) and $origin=='learnpath')
	  echo "<input type='hidden' name='origin' value='learnpath'>"; 

?>
		</td>
		</tr>
		</table>
	</td>
</tr>
<?

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
    ?>

	<tr>
	<td valign="top" algin="left" width="25">
	<?
	if (isset($origin))  {
	echo "<a href='dropbox_download.php?origin=$origin&id=".urlencode($w->id)."' target=_blank>
		<img  src='../../images/travaux.gif' border='0'></a>";
	} else  {
	echo "<a href='dropbox_download.php?id=".urlencode($w->id)."' target=_blank>
		<img  src='../../images/travaux.gif' border='0'></a>";
	}
	?>
	</td>
	<td valign="top" align="left">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td valign="top">
<?
	if (isset($origin)) 
		echo "<a href='dropbox_download.php?origin=$origin&id=".urlencode($w->id)."' target=_blank'>".$w->title."</a>"; 
	else
		echo "<a href='dropbox_download.php?id=".urlencode($w->id)."' target=_blank>".$w->title."</a>"; 

?>
	<span class="dropbox_detail">(<?=ceil(($w->filesize)/1024)?> kB)</span>
	</td>
	<td align="right" valign="top">
<? 

if (isset($origin)) { ?>
	<a href="dropbox_submit.php?<?php echo "origin=$origin"; ?>&deleteReceived=<?=urlencode($w->id)?>&dropbox_unid=<?=urlencode($dropbox_unid)?>" onClick='return confirmation("<?= $w->title?>");'>
	<img src="../../images/delete.gif" border="0" title="<?= $langDelete; ?>"></a>
<? } else { ?>
	<a href="dropbox_submit.php?deleteReceived=<?=urlencode($w->id)?>&dropbox_unid=<?=urlencode($dropbox_unid)?>" onClick='return confirmation("<?= $w->title?>");'>
	<img src="../../images/delete.gif" border="0" title="<?php echo $langDelete; ?>"></a>
<? 
	} 

?>
	</td>
	</tr>

<?
    if ($w -> author != '')
    {
?>
	<tr>
		<td colspan="2"><span class="dropbox_detail">
		<?=$dropbox_lang["authors"].': '.$w -> author?>
		</span></td>
	</tr> 
<?
    }
    if ($w -> description != '')
    {
		//only show description info if this is filled in in DB
        ?>
	<tr>
	<td colspan="2"><span class="dropbox_detail"><?=$dropbox_lang["description"].': '.$w -> description?></span></td>
	</tr> 
	<?
    }
    ?>
	<tr>
	<td colspan="2"><span class="dropbox_detail">
	<?=$dropbox_lang["sentBy"]?>
	 <span class="dropbox_person"><?=$w -> uploaderName?></span> <?=$dropbox_lang["sentOn"]?> 
	<span class="dropbox_date"><?=$w -> uploadDate?></span>
	</span></td>
	</tr> 
<?
	if ($w -> uploadDate != $w->lastUploadDate)
	{
?>

	<tr>
	<td colspan="2">
	<span class="dropbox_detail"><?=$dropbox_lang['lastUpdated']?>
	<span class="dropbox_date"><?=$w->lastUploadDate?></span></span>
	</td>
	</tr>
	
<?
	}
?>
	</table>
	</td>
	</tr>

<?
    	$i++;
	} //end of foreach
	if ($numberDisplayed == 0) {  // RH
?>

<tr>
<td align="center"><?=$dropbox_lang['tableEmpty']?>
</td>
</tr>
	
<?
	}
?>

</table>

<?
}  // RH: Mailing: end of 'Mailing detail: no received files'

/**
 * --------------------------------------
 *       SENT FILES LIST:  TABLE HEADER
 * --------------------------------------
 */

$numSent = count($dropbox_person -> sentWork);
?>
	<table cellpadding="5" cellspacing="1" border="0" width="100%">
	<tr bgcolor="<?= $color2; ?>">
	<td colspan ="2" align="center">
	<div class="dropbox_listTitle"><?=strtoupper($dropbox_lang["sentTitle"])?></div>
	</td>
	</tr>	
	<tr bgcolor="<?= $color1; ?>">
	<td colspan="2">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td align="left" width="100%">
	<form name="formSent" method="get" action="index.php?<? echo "origin=$origin"; ?>">
<?
		 if (isset($origin) and $origin=='learnpath') 
			 echo "<input type='hidden' name='origin' value='learnpath'>"; 
?>
	<span class="dropbox_listTitle"><?=$dropbox_lang["orderBy"]?></span>
	<select name="sentOrder" onchange="javascript: this.form.submit()">
	<option value="lastDate" <?php if ($sentOrder=="lastDate") {
					echo "selected";
					}?>><?=$dropbox_lang['lastDate']?></option>
	<? if ($dropbox_cnf["allowOverwrite"]) { ?>
			<option value="firstDate" <?php if ($sentOrder=="firstDate") {
				echo "selected";
				}?>><?=$dropbox_lang['firstDate']?></option>
					<?php } ?>
	<option value="title" <? if ($sentOrder=="title") {
				echo "selected";
				}?>><?=$dropbox_lang['title']?></option>
	<option value="size" <? if ($sentOrder=="size") {
				echo "selected";
				}?>><?=$dropbox_lang['size']?></option>
	<option value="author" <? if ($sentOrder=="author") {
				echo "selected";
				}?>><?=$dropbox_lang['author']?></option>
	<option value="recipient" <? if ($sentOrder=="recipient") {
				echo "selected";
				}?>><?=$dropbox_lang['recipient']?></option>
	</select>
	</form>
	</td>
	<td align="right" width="30%">

<? 
// if the user has sent files then display the icon deleteall
if ($numSent > 0) { 

if (isset($origin)) { ?>
	<a href="dropbox_submit.php?<?php echo "origin=$origin"; ?>&deleteSent=all&dropbox_unid=<?=urlencode( $dropbox_unid).$mailingInUrl?>"
	onClick="return confirmationall('<?=addslashes($dropbox_lang["all"])?>');">
	<img src="../../images/delete.gif" border="0" title="<?= $langDelete; ?>"></a>
<? } else { ?>
	<a href="dropbox_submit.php?deleteSent=all&dropbox_unid=<?=urlencode( $dropbox_unid).$mailingInUrl?>"
	onClick="return confirmationall('<?=addslashes($dropbox_lang["all"])?>');">
	<img src="../../images/delete.gif" border="0" title="<?= $langDelete; ?>"></a>
<? 
	} 
}
?>
 	</td>
	</tr>
	</table>
	</td>
	</tr>

<?

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
?>
		<tr>
		<td valign="top" algin="left"  width="25">
			<a href="<?=$ahref?>" target="_blank">
			<img  src="<?=$imgsrc?>" border="0"></a>
		</td>
		<td valign="top" align="left">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
		<td valign="top">
		<a href="<?=$ahref?>" target="_blank">
		<?=$w -> title?></a> 
		<span class="dropbox_detail">(<?=ceil(($w->filesize)/1024)?> kB)</span>
		</td>
		<td align="right" valign="top">

<?php  // RH: Mailing: clickable images for examine and send
if ($w->recipients[0]['id'] == $uid)
{
	$langSentTo = $dropbox_lang["justUploadInList"] . '&nbsp;';  // RH: justUpload
}
elseif ($w->recipients[0]['id'] > $dropbox_cnf["mailingIdBase"])
{
?>
<a href="dropbox_submit.php?<?php echo "origin=$origin"; ?>&mailingIndex=<?=urlencode( $i)?>&dropbox_unid=<?=urlencode( $dropbox_unid).$mailingInUrl?>">
<img src="../image/checkzip.gif" border="0" title="<?=$dropbox_lang["mailingExamine"]?>"></a>
<?  // RH: Mailing: filesize is set to zero on send, allow no 2nd send!
	if ($w->filesize != 0)
	{
		$langSentTo = '';  // unsent: do not write 'Sent to'

	if (isset($origin)) {
?>
	<a href="dropbox_submit.php?<?php echo "origin=$origin"; ?>&mailingIndex=<?=urlencode($i)?>&mailingSend=yes&dropbox_unid=<?=urlencode( $dropbox_unid).$mailingInUrl?>" onClick='return confirmsend();'>
		<img src="../../images/sendzip.gif" border="0" title="<?=$dropbox_lang["mailingSend"]?>"></a>

<?
	 } else {
?>
	<a href="dropbox_submit.php?mailingIndex=<?=urlencode($i)?>&mailingSend=yes&dropbox_unid=<?=urlencode( $dropbox_unid).$mailingInUrl?>" onClick='return confirmsend();'>
		<img src="../../images/sendzip.gif" border="0" title="<?=$dropbox_lang["mailingSend"]?>"></a>

<? 
	} 
?>

<?
	}
}
?>
<!--	Users cannot delete their own sent files -->
<?
	if (isset($origin)) {
?>
		<a href="dropbox_submit.php?<?php echo "origin=$origin"; ?>&deleteSent=<?=urlencode($w->id)?>&dropbox_unid=<?=urlencode($dropbox_unid) . $mailingInUrl?>"
		onClick='return confirmation("<?= $w->title?>");'>
		<img src="../../images/delete.gif" border="0" title="<?= $langDelete; ?>"></a>
<? 
	} else {
?>		
	<a href="dropbox_submit.php?deleteSent=<?=urlencode($w->id)?>&dropbox_unid=<?=urlencode($dropbox_unid) . $mailingInUrl?>"
		onClick='return confirmation("<?= $w->title ?>");'>
		<img src="../../images/delete.gif" border="0" title="<?= $langDelete; ?>"></a>
<?
	}
?>
	</td>
	</tr>
<?
    if ($w -> author != '')
    {
?>
	<tr>
	<td colspan="2">
	<span class="dropbox_detail"><?=$dropbox_lang["authors"].': '.$w -> author?><br></span>
	</td>
	</tr> 
<?
    }
    if ($w -> description != '')
    {
?>
	<tr>
	<td colspan="2"><span class="dropbox_detail">
	<?= $dropbox_lang["description"].': '.$w -> description?><br>
	</span></td>
	</tr> 

<?
    }
?>
		<tr>
		<td colspan="2">
		<table border="0" cellspacing="0" cellpadding="0">
		<tr>
		<td align="left" valign="top">
		<span class="dropbox_detail"><?= $langSentTo?></span>
		</td>
		<td align="left" valign="top"><span class="dropbox_detail">
		<span class="dropbox_person">
<?
		foreach($w -> recipients as $r)
			{
				echo $r["name"] . "<br>\n";
			}
														
?>
		</span></span></td>
		<td align="left" valign="top">
		<span class="dropbox_detail">&nbsp;<?=$dropbox_lang["sentOn"]?> 
		<span class="dropbox_date"><?=$w -> uploadDate?></span></span>
		</td>
		</tr>
		</table>
		</td>
		</tr> 
<?
	if ($w -> uploadDate != $w->lastUploadDate) {
?>
		<tr>
		<td colspan="2"><span class="dropbox_detail"><?=$dropbox_lang["lastResent"]?> <span class="dropbox_date"><?=$w->lastUploadDate?></span></span></td>
		</tr>
<?
	}
?>
		</table>
		</td>
		</tr>
<?
    $i++;
	} //end of foreach
	if (count($dropbox_person -> sentWork)==0) {
?>
	<tr>
	<td align="center"><?=$dropbox_lang['tableEmpty']?>
	</td>
	</tr>
<?
	}
?>
	</table>
	</td>
</tr>
</table>

