<?php
/* ========================================================================
 * Open eClass 2.6
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

/*
 * video
 *
 * @author Dimitris Tsachalis <ditsa@ccf.auth.gr>
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract
 *
 */
/*******************************************************************
*			   VIDEO UPLOADER AND DOWNLOADER
********************************************************************

The script makes 5 things:
1. Upload video
2. Give them a name
3. Modify data about video
4. Delete link to video and simultaneously remove them
5. Show video list to students and visitors

On the long run, the idea is to allow sending realvideo . Which means only
establish a correspondence between RealServer Content Path and the user's
documents path.

*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Video';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once '../../include/lib/fileUploadLib.inc.php';

/**** The following is added for statistics purposes ***/
require_once '../../include/action.php';
$action = new action();
$action->record('MODULE_ID_VIDEO');
/**************************************/

require_once '../../include/lib/forcedownload.php';
require_once 'video_functions.php';

$nameTools = $langVideo;

if (isset($_SESSION['prenom'])) { 
        $nick = q($_SESSION['prenom'].' '.$_SESSION['nom']);
}

$is_in_tinymce = (isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce') ? true : false;
$menuTypeID = ($is_in_tinymce) ? 5: 2;

if ($is_in_tinymce) {
    
    $_SESSION['embedonce'] = true; // necessary for baseTheme

    load_js('jquery');
    load_js('tinymce/jscripts/tiny_mce/tiny_mce_popup.js');
    
    $head_content .= <<<EOF
<script type='text/javascript'>
$(document).ready(function() {

    $("a.fileURL").click(function() { 
        var URL = $(this).attr('href');
        var win = tinyMCEPopup.getWindowArg("window");

        // insert information now
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

        // are we an image browser
        if (typeof(win.ImageDialog) != "undefined") {
            // we are, so update image dimensions...
            if (win.ImageDialog.getImageData)
                win.ImageDialog.getImageData();

            // ... and preview if necessary
            if (win.ImageDialog.showPreviewImage)
                win.ImageDialog.showPreviewImage(URL);
        }

        // close popup window
        tinyMCEPopup.close();
        return false;
    });
});
</script>
EOF;
}

$filterv = '';
$filterl = '';
$eclplugin = true;
if (isset($_REQUEST['docsfilter'])) {
    
    switch ($_REQUEST['docsfilter']) {
        case 'image':
            $ors = '';
            $first = true;
            foreach (get_supported_images() as $imgfmt)
            {
                if ($first)
                {
                    $ors .= "path LIKE '%$imgfmt%'";
                    $first = false;
                } else
                    $ors .= " OR path LIKE '%$imgfmt%'";
            }
            
            $filterv = "WHERE ( $ors )";
            $filterl = "WHERE false";
            break;
        case 'zip':
            $filterv = $filterl = "WHERE false";
            break;
        case 'media':
            $eclplugin = false;
            break;
        case 'eclmedia':
        case 'file':
        default:
            break;
    }
}

// ----------------------
// download video
// ----------------------

if (isset($_GET['action']) and $_GET['action'] == "download") {
	$id = q($_GET['id']);
	$real_file = $webDir."/video/".$currentCourseID."/".$id;
	if (strpos($real_file, '/../') === FALSE && file_exists($real_file)) {
                $result = db_query("SELECT url FROM video WHERE path = " .
                                   quote($id), $currentCourseID);
		$row = mysql_fetch_array($result);
		if (!empty($row['url'])) {
			$id = $row['url'];
		}
		send_file_to_client($real_file, my_basename($id), 'inline', true);
		exit;
	} else {
		header("Refresh: ${urlServer}modules/video/video.php?course=$code_cours");
	}
}

// ----------------------
// play video
// ----------------------

if (isset($_GET['action']) and $_GET['action'] == "play")
{
        $id = q($_GET['id']);
        $videoPath = $urlServer ."video/". $currentCourseID . $id;
        $videoURL = "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=download&amp;id=". $id;
        
        $result = db_query("SELECT url FROM video WHERE path = ". quote($id), $currentCourseID);
        $row = mysql_fetch_array($result);
        
        if (strpos($videoPath, '/../') === FALSE && !empty($row))
        {
            echo media_html_object($videoPath, $videoURL);
            exit;
        }
        else
        {
            header("Refresh: ${urlServer}modules/video/video.php?course=$code_cours");
        }
}

// ----------------------
// play videolink
// ----------------------

if (isset($_GET['action']) and $_GET['action'] == "playlink")
{
        $id = q($_GET['id']);
        
        echo medialink_iframe_object(urldecode($id));
        exit;
}


if($is_editor) {
        load_js('tools.js');
        load_modal_box(true);
        $head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyVideoTitle");
		return false;
	} else {
		return true;
	}
}

</script>
hContent;
	
$d = mysql_fetch_array(db_query("SELECT video_quota FROM cours WHERE code='$currentCourseID'",$mysqlMainDb));
$diskQuotaVideo = $d['video_quota'];
$updir = "$webDir/video/$currentCourseID"; //path to upload directory
$diskUsed = dir_total_space($updir);

if (isset($_GET['showQuota']) and $_GET['showQuota'] == TRUE) {
	$nameTools = $langQuotaBar;
	$navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$code_cours", 'name' => $langVideo);
	$tool_content .= showquota($diskQuotaVideo, $diskUsed);
	draw($tool_content, $menuTypeID);
	exit;
}	

if (isset($_POST['edit_submit'])) { // edit
	if(isset($_POST['id'])) {
		$id = intval($_POST['id']);
		if (isset($_POST['table'])) {
			$table = q($_POST['table']);
		}
		if ($table == 'video') {
			$sql = "UPDATE video SET titre = ".autoquote($_POST['titre']).",
                                                 description = ".autoquote($_POST['description']).",
                                                 creator = ".autoquote($_POST['creator']).",
                                                 publisher = ".autoquote($_POST['publisher'])."
                                             WHERE id = $id";	
		} elseif ($table == 'videolinks') {
			$sql = "UPDATE videolinks SET url = ".autoquote(canonicalize_url($_POST['url'])).",
                                                      titre = ".autoquote($_POST['titre']).",
                                                      description = ".autoquote($_POST['description']).",
                                                      creator = ".autoquote($_POST['creator']).",
                                                      publisher = ".autoquote($_POST['publisher'])."
                                                  WHERE id = $id";
		}
		$result = db_query($sql, $currentCourseID);
		$tool_content .= "<p class='success'>$langTitleMod</p><br />";
		$id = "";
	}
}	
if (isset($_POST['add_submit'])) {  // add
		if(isset($_POST['URL'])) { // add videolinks
			$url = $_POST['URL'];
			if ($_POST['titre'] == '') {
				$titre = $url;
			} else {
				$titre = $_POST['titre'];
			}
			$sql = 'INSERT INTO videolinks (url, titre, description, creator, publisher, date)
                                VALUES ('.autoquote(canonicalize_url($url)).',
                                        '.autoquote($titre).',
				        '.autoquote($_POST['description']).',
                                        '.autoquote($_POST['creator']).',
                                        '.autoquote($_POST['publisher']).',
                                        '.autoquote($_POST['date']).')';
			$result = db_query($sql, $currentCourseID);
			$tool_content .= "<p class='success'>$langLinkAdded</p><br />";
		} else {  // add video
			if (isset($_FILES['userFile']) && is_uploaded_file($_FILES['userFile']['tmp_name'])) {
			    
			    validateUploadedFile($_FILES['userFile']['name'], $menuTypeID);
			    
				if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaVideo) {
					$tool_content .= "<p class='caution'>$langNoSpace<br />
						<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours'>$langBack</a></p><br />";
						draw($tool_content, $menuTypeID, null, $head_content);
						exit;
				} else {
					$file_name = $_FILES['userFile']['name'];
					$tmpfile = $_FILES['userFile']['tmp_name'];
					// convert php file in phps to protect the platform against malicious codes
					$file_name = preg_replace("/\.php$/", ".phps", $file_name);
					// check for dangerous file extensions
					if (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' .'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' .'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $file_name)) {
						$tool_content .= "<p class='caution'>$langUnwantedFiletype:  $file_name<br />";
						$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours'>$langBack</a></p><br />";
						draw($tool_content, $menuTypeID, null, $head_content);
						exit;
					}
					$file_name = str_replace(" ", "%20", $file_name);
					$file_name = str_replace("%20", "", $file_name);
					$file_name = str_replace("\'", "", $file_name);
					$safe_filename = date("YmdGis").randomkeys("8").".".get_file_extension($file_name);
					$iscopy = copy("$tmpfile", "$updir/$safe_filename");
					if(!$iscopy) {
						$tool_content .= "<p class='success'>$langFileNot<br />
						<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours'>$langBack</a></p><br />";
						draw($tool_content, $menuTypeID, null, $head_content);
						exit;
					}
					$path = '/' . $safe_filename;
					$url = $file_name;
                                        $sql = 'INSERT INTO video
                                                       (path, url, titre, description, creator, publisher, date)
                                                       VALUES ('.quote($path).', '.
                                                                 autoquote($url).', '.
                                                                 autoquote($_POST['titre']).', '.
                                                                 autoquote($_POST['description']).', '.
                                                                 autoquote($_POST['creator']).', '.
                                                                 autoquote($_POST['publisher']).', '.
                                                                 autoquote($_POST['date']).')';
				}
				$result = db_query($sql, $currentCourseID);
				$tool_content .= "<p class='success'>$langFAdd</p><br />";
			}
		}
	}	// end of add
	if (isset($_GET['delete'])) { // delete
		$id = intval($_GET['id']);
		$table = q($_GET['table']);
		$sql_select="SELECT * FROM $table WHERE id='".mysql_real_escape_string($id)."'";
		$result = db_query($sql_select,$currentCourseID);
		$myrow = mysql_fetch_array($result);
		if($table == "video") {
			unlink("$webDir/video/$currentCourseID/".$myrow['path']);
		}
		$sql = "DELETE FROM $table WHERE id='".mysql_real_escape_string($id)."'";
		$result = db_query($sql,$currentCourseID);
		$tool_content .= "<p class='success'>$langDelF</p><br />";
		$id="";
	} elseif (isset($_GET['form_input']) && $_GET['form_input'] == 'file') { // display video form
		$nameTools = $langAddV;
		$navigation[] = array('url' => "video.php?course=$code_cours", 'name' => $langVideo);
		$tool_content .= "
              <form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$code_cours' enctype='multipart/form-data' onsubmit=\"return checkrequired(this, 'titre');\">
              <fieldset>
              <legend>$langAddV</legend>
		<table width='100%' class='tbl'>
		<tr>
		  <th valign='top'>$langWorkFile:</th>
		  <td>
		    <input type='hidden' name='id' value=''>
		    <input type='file' name='userFile' size='38'>
                    <br />
                   <span class='smaller'>$langPathUploadFile</span>
		  </td>
		<tr>
		  <th>$langTitle:</th>
		  <td><input type='text' name='titre' size='55'></td>
		</tr>
		<tr>
		  <th>$langDescr:</th>
		  <td><textarea rows='3' name='description' cols='52'></textarea></td>
		</tr>
		<tr>
		  <th>$langcreator:</th>
		  <td><input type='text' name='creator' value='$nick' size='55'></td>
		</tr>
		<tr>
		  <th>$langpublisher:</th>
		  <td><input type='text' name='publisher' value='$nick' size='55'></td>
		</tr>
		<tr>
		  <th>$langdate:</th>
		  <td><input type='text' name='date' value='".date('Y-m-d G:i:s')."' size='55'></td>
		</tr>
		<tr>
		  <th>&nbsp;</th>
		  <td class='right'><input type='submit' name='add_submit' value='".q($dropbox_lang['uploadFile'])."'></td>
		</tr>

		</table>
        </fieldset>
              <div class='smaller right'>$langMaxFileSize ". ini_get('upload_max_filesize') . "</div></form> <br>";        
              
	} elseif (isset($_GET['form_input']) && $_GET['form_input'] == 'url') { // display video links form
		$nameTools = $langAddVideoLink;
		$navigation[] = array ('url' => "video.php?course=$code_cours", 'name' => $langVideo);
		$tool_content .= "
		<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours' onsubmit=\"return checkrequired(this, 'titre');\">
                <fieldset>
                <legend>$langAddVideoLink</legend>
		<table width='100%' class='tbl'>
		<tr>
		  <th valign='top' width='190'>$langGiveURL:<input type='hidden' name='id' value=''></th>
		  <td class='smaller'><input type='text' name='URL' size='55'>
                      <br />
                      $langURL
                  </td>
		<tr>
		  <th>$langTitle:</th>
		  <td><input type='text' name='titre' size='55'></td>
		</tr>
		<tr>
		  <th>$langDescr:</th>
		  <td><textarea rows='3' name='description' cols='52'></textarea></td>
		</tr>
		<tr>
		  <th>$langcreator:</th>
		  <td><input type='text' name='creator' value='$nick' size='55'></td>
		</tr>
		<tr>
		  <th>$langpublisher:</th>
		  <td><input type='text' name='publisher' value='$nick' size='55'></td>
		</tr>
		<tr>
		  <th>$langdate:</th>
		  <td><input type='text' name='date' value='".date('Y-m-d G:i')."' size='55'></td>
		</tr>
		<tr>
		  <th>&nbsp;</th>
		  <td class='right'><input type='submit' name='add_submit' value='".q($langAdd)."'></td>
		</tr>
		</table>
                </fieldset>
		</form>
		<br/>";
	}

// ------------------- if no submit -----------------------
if (isset($_GET['id']) and isset($_GET['table_edit']))  {
	$id = intval($_GET['id']);
	$table_edit = q($_GET['table_edit']);
	if ($id) {
		$sql = "SELECT * FROM $table_edit WHERE id = $id ORDER BY titre";
		$result = db_query($sql,$currentCourseID);
		$myrow = mysql_fetch_array($result);
		$id = $myrow[0];
		if ($table_edit == 'videolinks') {
			$url= $myrow[1];
			$titre = $myrow[2];
			$description = $myrow[3];
			$creator = $myrow[4];
			$publisher = $myrow[5];
		} elseif ($table_edit == 'video') {
			$url= $myrow[2];
			$titre = $myrow[3];
			$description = $myrow[4];
			$creator = $myrow[5];
			$publisher = $myrow[6];
		}
		$nameTools = $langModify;
		$navigation[] = array ('url' => "video.php?course=$code_cours", 'name' => $langVideo);
		$tool_content .= "
           <form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$code_cours' onsubmit=\"return checkrequired(this, 'titre');\">
           <fieldset>
           <legend>$langModify</legend>

           <table width='100%' class='tbl'>";
		if ($table_edit == 'videolinks') {
			$tool_content .= "
           <tr>
             <th>$langURL:</th>
             <td><input type='text' name='url' value='".q($url)."' size='55'></td>
           </tr>";
		}
		elseif ($table_edit == 'video') {
			$tool_content .= "<input type='hidden' name='url' value='".q($url)."'>";
		}
		@$tool_content .= "
		<tr>
		  <th width='90'>$langTitle:</th>
		  <td><input type='text' name='titre' value='".q($titre)."' size='55'></td>
		</tr>
		<tr>
		  <th>$langDescr:</th>
		  <td><textarea rows='3' name='description' cols='52'>".q($description)."</textarea></td>
	       </tr>
	       <tr>
		 <th>$langcreator:</th>
		 <td><input type='text' name='creator' value='".q($creator)."' size='55'></td>
	       </tr>
	       <tr>
		 <th>$langpublisher:</th>
		 <td><input type='text' name='publisher' value='".q($publisher)."' size='55'></td>
	       </tr>
	       <tr>
		 <th>&nbsp;</th>
		 <td class='right'><input type='submit' name='edit_submit' value='".q($langModify)."'>
		     <input type='hidden' name='id' value='".$id."'>
		     <input type='hidden' name='table' value='".$table_edit."'>
		 </td>
	       </tr>
	       </table>
	       </fieldset>
	       </form>
	       <br/>";
	}
}	// if id

if (!isset($_GET['form_input']) && !$is_in_tinymce ) {
          $tool_content .= "
        <div id='operations_container'>
	  <ul id='opslist'>
	    <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;form_input=file'>$langAddV</a></li>
	    <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;form_input=url'>$langAddVideoLink</a></li>
	    <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;showQuota=TRUE'>$langQuotaBar</a></li>
	  </ul>
	</div>";
}

$count_video = mysql_fetch_array(db_query("SELECT count(*) FROM video $filterv ORDER BY titre",$currentCourseID));
$count_video_links = mysql_fetch_array(db_query("SELECT count(*) FROM videolinks $filterl
				ORDER BY titre",$currentCourseID));

if ($count_video[0]<>0 || $count_video_links[0]<>0) {
        // print the list if there is no editing
        $results['video'] = db_query("SELECT * FROM video $filterv ORDER BY titre",$currentCourseID);
        $results['videolinks'] = db_query("SELECT * FROM videolinks $filterl ORDER BY titre",$currentCourseID);
        $i = 0;
        $count_video_presented_for_admin = 1;
        $tool_content .= "
        <table width='100%' class='tbl_alt'>
        <tr>
        
          <th colspan='2'><div align='left'>$langVideoDirectory</div></th>";
        if (!$is_in_tinymce)
        {
          $tool_content .= "
          <th width='150'><div align='left'>$langcreator</div></th>
          <th width='150'><div align='left'>$langpublisher</div></th>";
        }
        $tool_content .= "<th width='70'>$langdate</th>";
        if (!$is_in_tinymce)
            $tool_content .= "<th width='70'>$langActions</th>";
        $tool_content .= "</tr>";
        foreach($results as $table => $result)
                while ($myrow = mysql_fetch_array($result)) {
                        switch($table){
				case 'video':
					if (isset($vodServer)) {
                                            $mediaURL = $vodServer."$currentCourseID/".$myrow[1];
                                            $mediaPath = $mediaURL;
                                            $mediaPlay = $mediaURL;
					} else {
                                            list($mediaURL, $mediaPath, $mediaPlay) = media_url($myrow['path']);
					}
                                        
                                        if ($is_in_tinymce) {
                                            $furl = (is_supported_media($myrow[1], true) && $eclplugin) ? $mediaPlay : $mediaURL;
                                            $link_href = "<a href='$furl' class='fileURL'>". q($myrow[3]) ."</a>";
                                        } else {
                                            $link_href = choose_media_ahref($mediaURL, $mediaPath, $mediaPlay, q($myrow[3]), $myrow[1]) ."<br/><small>". q($myrow[4]) . "</small>";
                                        }
                                        
                                        $link_to_add = "<td>". $link_href . "</td>";
                                        
                                        if (!$is_in_tinymce)
                                            $link_to_add .= "<td>" . q($myrow[5]) . "</td><td>" . q($myrow[6]) . "</td>";
                                        
                                        $link_to_add .= "<td align='center'>". nice_format(date('Y-m-d', strtotime($myrow[7])))."</td>";
                                        $link_to_save = "<a href='$mediaURL'><img src='$themeimg/save_s.png' alt='".q($langSave)."' title='".q($langSave)."'></a>&nbsp;&nbsp;";
					break;
				case "videolinks":
                                        $aclass = ($is_in_tinymce) ? 'fileURL' : null;
                                        $link_href = choose_medialink_ahref(q($myrow[1]), q($myrow[2]), $aclass);
                                        
                                        $link_to_add = "<td>". $link_href ."<br/>" . q($myrow[3]) . "</td>";
                                    
                                        if (!$is_in_tinymce)
                                            $link_to_add .= "<td>" . q($myrow[4]) . "</td><td>" . q($myrow[5]) . "</td>";
                                        
                                        $link_to_add .= "<td align='center'>" . nice_format(date('Y-m-d', strtotime($myrow[6]))) . "</td>";
                                        $link_to_save = "<a href='".q($myrow[1])."' target='_blank'><img src='$themeimg/links_on.png' alt='".q($langPreview)."' title='".q($langPreview)."'></a>&nbsp;&nbsp;";
					break;
				default:
					exit;
			}
                        if ($i%2) {
				$rowClass = "class='odd'";
			} else {
				$rowClass = "class='even'";
			}
                        $tool_content .= "
                                <tr $rowClass>
                                   <td width='1' valign='top'>
                                      <img style='padding-top:3px;' src='$themeimg/arrow.png' alt=''>
                                   </td>
                                   $link_to_add";
                        if (!$is_in_tinymce)
                        {
                            $tool_content .= "
                                   <td align='right'>
                                      $link_to_save<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=$myrow[0]&amp;table_edit=$table'>
                                      <img src='$themeimg/edit.png' title='".q($langModify)."'>
                                      </a>&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=$myrow[0]&amp;delete=yes&amp;table=$table' onClick=\"return confirmation('".js_escape("$langConfirmDelete $myrow[2]")."');\">
                                      <img src='$themeimg/delete.png' title='".q($langDelete)."'></a>
                                   </td>";
                        }
                        $tool_content .= "</tr>";
                        $i++;
                        $count_video_presented_for_admin++;
		} // while
		$tool_content.="</table>";
	}
	else
	{
		$tool_content .= "<p class='alert1'>$langNoVideo</p>";
	}
}   // if uid=prof_id

// student view
else {
    
    load_modal_box(true);
    
	$results['video'] = db_query("SELECT *  FROM video $filterv ORDER BY titre", $currentCourseID);
	$results['videolinks'] = db_query("SELECT * FROM videolinks $filterl ORDER BY titre", $currentCourseID);
	$count_video = mysql_fetch_array(db_query("SELECT count(*) FROM video $filterv ORDER BY titre", $currentCourseID));
	$count_video_links = mysql_fetch_array(db_query("SELECT count(*) FROM videolinks $filterl ORDER BY titre",$currentCourseID));
        
	if ($count_video[0]<>0 || $count_video_links[0]<>0) {
		$tool_content .= "
		<table width='100%' class='tbl_alt'>
		<tr>
                  <th colspan='2'><div align='left'>$langDirectory $langVideo</div></th>";
                if (!$is_in_tinymce)
                    $tool_content .= "<th width='70'>$langActions</th>";
		$tool_content .= "</tr>";
		$i=0;
		$count_video_presented=1;
		foreach($results as $table => $result) {
			while ($myrow = mysql_fetch_array($result)) {
				switch($table){
					case 'video':
						if (isset($vodServer)) {
                                                    $mediaURL = $vodServer."$currentCourseID/".$myrow[1];
                                                    $mediaPath = $mediaURL;
                                                    $mediaPlay = $mediaURL;
						} else {
                                                    list($mediaURL, $mediaPath, $mediaPlay) = media_url($myrow['path']);
						}
                                                
                                                if ($is_in_tinymce) {
                                                    $furl = (is_supported_media($myrow[1], true) && $eclplugin) ? $mediaPlay : $mediaURL;
                                                    $link_href = "<a href='$furl' class='fileURL'>". q($myrow[3]) ."</a>";
                                                } else {
                                                    $link_href = choose_media_ahref($mediaURL, $mediaPath, $mediaPlay, q($myrow[3]), $myrow[1]) ."<br/><small>". q($myrow[4]) . "</small>";
                                                }

                                                $link_to_add = "<td>". $link_href . "</td>";
                                                $link_to_save = "<a href='$mediaURL'>
                                                        <img src='$themeimg/save_s.png' alt='".q($langSave)."' title='".q($langSave)."'></a>&nbsp;&nbsp;";
						break;
					case 'videolinks':
                                                $aclass = ($is_in_tinymce) ? 'fileURL' : null;
                                                $link_href = choose_medialink_ahref(q($myrow[1]), q($myrow[2]), $aclass);
                                                
                                                $link_to_add = "<td>". $link_href ."<br/>" . q($myrow[3]) . "</td>";
                                                
                                                $link_to_save = "<a href='".q($myrow[1])."' target='_blank'>
                                                        <img src='$themeimg/links_on.png' alt='".q($langPreview)."' title='".q($langPreview)."'></a>&nbsp;&nbsp;";
						break;
					default:
						exit;
				}
				if ($i%2) {
					$rowClass = "class='odd'";
				} else {
					$rowClass = "class='even'";
				}
				$tool_content .= "<tr $rowClass>";
				$tool_content .= "<td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' alt=''></td>";
				$tool_content .= $link_to_add;
                                if (!$is_in_tinymce)
                                    $tool_content .= "<td align='center'>$link_to_save</td>";
				$tool_content .= "</tr>";
				$i++;
				$count_video_presented++;
			}
		}
		$tool_content .= "</table>\n";
	} else {
		$tool_content .= "<p class='alert1'>$langNoVideo</p>";
	}
}

add_units_navigation(TRUE);

if (isset($head_content)) {
	draw($tool_content, $menuTypeID, null, $head_content);
} else {
        draw($tool_content, $menuTypeID);
}
