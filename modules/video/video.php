<?php
/* ========================================================================
 * Open eClass 2.11
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * @file: video.php
 *
 * @abstract The script makes 5 things:
1. Upload video
2. Give them a name
3. Modify data about video
4. Delete link to video and simultaneously remove them
5. Show video list to students and visitors
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
require_once '../../include/lib/modalboxhelper.class.php';
require_once '../../include/lib/multimediahelper.class.php';
require_once '../../include/lib/mediaresource.factory.php';

$nameTools = $langVideo;

if (isset($_SESSION['prenom'])) { 
        $nick = q($_SESSION['prenom'].' '.$_SESSION['nom']);
}

$is_in_tinymce = (isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce') ? true : false;
$menuTypeID = ($is_in_tinymce) ? 5: 2;
list($filterv, $filterl, $compatiblePlugin) = (isset($_REQUEST['docsfilter'])) 
        ? select_proper_filters($_REQUEST['docsfilter']) 
        : array('', '', true);

if ($is_in_tinymce) {
    $_SESSION['embedonce'] = true; // necessary for baseTheme

    load_js('jquery');
    load_js('tinymce/jscripts/tiny_mce/tiny_mce_popup.js');
    load_js('tinymce.popup.urlgrabber.min.js');
}

if($is_editor) {
        load_js('tools.js');
        ModalBoxHelper::loadModalBox(true);
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

// visibility commands
if (isset($_GET['vis'])) {    
        $new_vis_status = intval($_GET['vis']);
        $table = select_table($_GET['table']);
        db_query("UPDATE $table SET visible = $new_vis_status WHERE id = " . intval($_GET['vid']), $currentCourseID);        
        $action_message = "<p class='success'>$langViMod</p>";
}

// Public accessibility commands
if (isset($_GET['public']) or isset($_GET['limited'])) {
        $new_public_status = intval(isset($_GET['public']))? 1: 0;        
        $table = select_table($_GET['table']);
        db_query("UPDATE $table SET public = $new_public_status WHERE id = " . intval($_GET['vid']), $currentCourseID);
        $action_message = "<p class='success'>$langViMod</p>";
}

/**
 * display form for add / edit category
 */
if (isset($_GET['action'])) {    
    $tool_content .=  "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours'>";
    if ($_GET['action'] == 'editcategory') {
        $result = db_query("SELECT * FROM video_category WHERE id = $_GET[id]", $currentCourseID);
        if ($myrow = mysql_fetch_array($result)) {
                $form_name = ' value="' . q($myrow['name']) . '"';
                $form_description = standard_text_escape($myrow['description']);
        } else {
                $form_name = $form_description = '';
        }
        $tool_content .= "<input type='hidden' name='id' value='$_GET[id]' />";
        $form_legend = $langCategoryMod;
    } else {
            $form_name = $form_description = '';
            $form_legend = $langCategoryAdd;
    }
    $tool_content .= "<fieldset><legend>$form_legend</legend>
                    <table width='100%' class='tbl'>
                    <tr><th>$langCategoryName:</th>
                        <td><input type='text' name='categoryname' size='53'$form_name /></td></tr>
                    <tr><th>$langDescription:</th>
                        <td><textarea rows='5' cols='50' name='description'>$form_description</textarea></td></tr>
                    <tr><th>&nbsp;</th>
                        <td class='right'><input type='submit' name='submitCategory' value='$form_legend' /></td></tr>
                    </table></fieldset></form>";

}

if (isset($_POST['submitCategory'])) {
    submit_video_category();  
}

if (isset($_POST['edit_submit'])) { // edit
    if(isset($_POST['id'])) {
        $id = intval($_POST['id']);
        if (isset($_POST['table'])) {
                $table = select_table($_POST['table']);
        }
        if ($table == 'video') {
                $sql = "UPDATE video SET titre = ".autoquote($_POST['titre']).",
                                         description = ".autoquote($_POST['description']).",
                                         creator = ".autoquote($_POST['creator']).",
                                         publisher = ".autoquote($_POST['publisher']).",
                                         category = ".autoquote($_POST['selectcategory'])."    
                                     WHERE id = $id";	
        } elseif ($table == 'videolinks') {
                $sql = "UPDATE videolinks SET url = ".autoquote(canonicalize_url($_POST['url'])).",
                                              titre = ".autoquote($_POST['titre']).",
                                              description = ".autoquote($_POST['description']).",
                                              creator = ".autoquote($_POST['creator']).",
                                              publisher = ".autoquote($_POST['publisher']).",
                                              category = ".autoquote($_POST['selectcategory'])."
                                          WHERE id = $id";
        }
        $result = db_query($sql, $currentCourseID);
        $tool_content .= "<p class='success'>$langGlossaryUpdated</p><br />";
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
                $sql = 'INSERT INTO videolinks (url, titre, description, category, creator, publisher, date)
                        VALUES ('.autoquote(canonicalize_url($url)).',
                                '.autoquote($titre).',
                                '.autoquote($_POST['description']).',
                                '.intval($_POST['selectcategory']).',
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
                            $file_name = preg_replace("/\.php.*$/", ".phps", $file_name);
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
                            $safe_filename = sprintf('%x', time()) . randomkeys(16) . "." . get_file_extension($file_name);
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
                                           (path, url, titre, description, category, creator, publisher, date)
                                           VALUES ('.quote($path).', '.
                                                     autoquote($url).', '.
                                                     autoquote($_POST['titre']).', '.
                                                     autoquote($_POST['description']).', '.
                                                     intval($_POST['selectcategory']).', '.
                                                     autoquote($_POST['creator']).', '.
                                                     autoquote($_POST['publisher']).', '.
                                                     autoquote($_POST['date']).')';
                    }
                    $result = db_query($sql, $currentCourseID);
                    $tool_content .= "<p class='success'>$langFAdd</p><br />";
                }
        }
    }	// end of add
    if (isset($_GET['delete'])) {
            $id = intval($_GET['id']);
            if ($_GET['delete'] == 'delcat') { // delete video category
                $q = db_query("SELECT id FROM video WHERE category = $id", $currentCourseID);
                while ($a = mysql_fetch_array($q)) {                       
                    delete_video($a['id'], 'video');
                }
                $q = db_query("SELECT id FROM videolinks WHERE category = $id", $currentCourseID);
                while ($a = mysql_fetch_array($q)) {
                    delete_video($a['id'], 'videolinks');
                }
                delete_video_category($id);
            } else {  // delete video / videolinks
                $table = select_table($_GET['table']);
                delete_video($id, $table);
            }
            $tool_content .= "<p class='success'>$langDelF</p><br />";
    } elseif (isset($_GET['form_input']) && $_GET['form_input'] == 'file') { // display video form
            $nameTools = $langAddV;
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$code_cours", 'name' => $langVideo);
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
                <th>$langDate:</th>
                <td><input type='text' name='date' value='".date('Y-m-d G:i:s')."' size='55'></td>
              </tr>
              <tr><th>$langCategory:</th>
                <td><select name='selectcategory'>
                <option value='0'>--</option>";
                $resultcategories = db_query("SELECT * FROM video_category ORDER BY `name`", $currentCourseID);
                while ($myrow = mysql_fetch_array($resultcategories)) {
                    $tool_content .=  "<option value='$myrow[id]'";                
                    $tool_content .= '>' . q($myrow['name']) . "</option>";
                }
                $tool_content .=  "</select>
                </tr>
              <tr>
                <th>&nbsp;</th>
                <td class='right'><input type='submit' name='add_submit' value='".q($langUpload)."'></td>
              </tr>
              </table>
            </fieldset>
            <div class='smaller right'>$langMaxFileSize ". ini_get('upload_max_filesize') . "</div></form><br />";

    } elseif (isset($_GET['form_input']) && $_GET['form_input'] == 'url') { // display video links form
            $nameTools = $langAddVideoLink;
            $navigation[] = array ('url' => "$_SERVER[SCRIPT_NAME]?course=$code_cours", 'name' => $langVideo);
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
              <th>$langDate:</th>
              <td><input type='text' name='date' value='".date('Y-m-d G:i')."' size='55'></td>
            </tr>
            <tr><th>$langCategory:</th>
                <td><select name='selectcategory'>
                <option value='0'>--</option>";
                $resultcategories = db_query("SELECT * FROM video_category ORDER BY `name`", $currentCourseID);
                while ($myrow = mysql_fetch_array($resultcategories)) {
                    $tool_content .=  "<option value='$myrow[id]'";                
                    $tool_content .= '>' . q($myrow['name']) . "</option>";
                }
                $tool_content .=  "</select>
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
	$table_edit = select_table($_GET['table_edit']);			
        $result = db_query("SELECT * FROM $table_edit WHERE id = $id ORDER BY titre", $currentCourseID);
        $myrow = mysql_fetch_array($result);
        $id = $myrow['id'];
        $url = $myrow['url'];
        $titre = $myrow['titre'];
        $description = $myrow['description'];
        $creator = $myrow['creator'];
        $publisher = $myrow['publisher'];
        $category = $myrow['category'];

        $nameTools = $langModify;
        $navigation[] = array ('url' => "$_SERVER[SCRIPT_NAME]?course=$code_cours", 'name' => $langVideo);
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
        } elseif ($table_edit == 'video') {
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
        <tr><th>$langCategory:</th>
            <td><select name='selectcategory'>
            <option value='0'>--</option>";
            $resultcategories = db_query("SELECT * FROM video_category ORDER BY `name`", $currentCourseID);
            while ($myrow = mysql_fetch_array($resultcategories)) {
                $tool_content .=  "<option value='$myrow[id]'";
                if (isset($category) and $category == $myrow['id']) {
                        $tool_content .= " selected='selected'";
                }
                $tool_content .= '>' . q($myrow['name']) . "</option>";
            }
            $tool_content .=  "</select>
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

if (!isset($_GET['form_input']) && !$is_in_tinymce) {
        $tool_content .= "
        <div id='operations_container'>
	  <ul id='opslist'>
	    <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;form_input=file'>$langAddV</a></li>
	    <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;form_input=url'>$langAddVideoLink</a></li>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=addcategory'>$langCategoryAdd</a></li>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;showQuota=TRUE'>$langQuotaBar</a></li>
	  </ul>
	</div>";
}
}   // end of admin check
$count_video = mysql_fetch_array(db_query("SELECT COUNT(*) FROM video $filterv", $currentCourseID));
$count_video_links = mysql_fetch_array(db_query("SELECT COUNT(*) FROM videolinks $filterl", $currentCourseID));

if ($count_video[0] > 0 or $count_video_links[0]> 0) {
        $tool_content .= "<table width='100%' class='tbl_alt'><tr>
                            <th colspan='2'><div align='left'>$langVideoDirectory</div></th>";        
        $tool_content .= "<th width='70'>$langDate</th>";
        if (!$is_in_tinymce and $is_editor) {  
            $tool_content .= "<th width='170' colspan='2'>$langActions</th>";
        }
        $tool_content .= "</tr>";
        //display uncategorized links
        showlinksofcategory();
        $tool_content .= "</table>";
    } else {
        $tool_content .= "<p class='alert1'>$langNoVideo</p>";
    }

    
ModalBoxHelper::loadModalBox(true);

$num_of_categories = db_query_get_single_value("SELECT COUNT(*) FROM `video_category`", $currentCourseID);

if ($num_of_categories > 0) { // categories found ?
    $resultcategories = db_query("SELECT * FROM `video_category` ORDER BY `name`", $currentCourseID);                       
    $tool_content .= "<br />
    <table width='100%' class='tbl'>
    <tr>
      <td class='bold'>$langCatVideoDirectory</td>
      <td width='1'><img src='$themeimg/folder_closed.png' title='$showall' alt='$showall'></td>
      <td width='60'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;d=0'>$shownone</a></td>
      <td width='1'><img src='$themeimg/folder_open.png' title='$showall' alt='$showall'></td>
      <td width='60'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;d=1'>$showall</a></td>
    </tr>
    </table>";
   
    $tool_content .= "<table width='100%' class='tbl_alt'>";    
    $catcounter = 1;
    while ($myrow = mysql_fetch_array($resultcategories)) {
        $description = standard_text_escape($myrow['description']);
        if ((isset($_GET['d']) and $_GET['d'] == 1) or (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow['id'])) {
            $folder_icon = icon('folder_open', $shownone);
        } else {
            $folder_icon = icon('folder_closed', $showall);
        }
        $tool_content .= "<tr><th width='15' valign='top'>$folder_icon</th>";
        if (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow['id']) {
            $tool_content .= "<th colspan='3' valign='top' align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours'>".q($myrow['name'])."</a>";
        } else {
            $tool_content .= "<th width = '85%' colspan='3' valign='top' align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;cat_id=$myrow[id]'>".q($myrow['name'])."</a>";
        }
        if (!empty($description)) {
                $tool_content .= "<br />$description</th>";
        }        
        
        if ($is_editor) {                   
            $tool_content .= "<th colspan='2' style='padding-left: 45px;'>".icon('edit',$langModify, "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=$myrow[id]&amp;action=editcategory")."&nbsp;".
                             icon('delete', $langDelete, "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=$myrow[id]&amp;delete=delcat", "onclick=\"javascript:if(!confirm('$langCatDel')) return false;\"")."</th>";
        }        
            // display all links
        if (isset($_GET['d']) and $_GET['d'] == 1) {
                showlinksofcategory($myrow['id']);            
        } else {
            // display links of specific category
            if (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow['id']) {
                showlinksofcategory($_GET['cat_id']);
            }        
        }        
    }
    $tool_content .=  "</table>";            
}

add_units_navigation(TRUE);

if (isset($head_content)) {
	draw($tool_content, $menuTypeID, null, $head_content);
} else {
        draw($tool_content, $menuTypeID);
}


/**
 * 
 * @param type $table
 * @return return table name
 */
function select_table($table)
{
        if ($table == 'videolinks') {
                return $table;
        } else {
                return 'video';
        }
}

function select_proper_filters($requestDocsFilter) {
    $filterv = '';
    $filterl = '';
    $compatiblePlugin = true;
    
    switch ($requestDocsFilter) {
        case 'image':
            $ors = '';
            $first = true;
            foreach (MultimediaHelper::getSupportedImages() as $imgfmt)
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
            $compatiblePlugin = false;
            break;
        case 'eclmedia':
        case 'file':
        default:
            break;
    }
    
    return array($filterv, $filterl, $compatiblePlugin);
}

/**
 * @brief add video category
 * @global type $cours_id
 * @global type $langCategoryAdded
 * @global type $langCategoryModded
 * @global type $categoryname
 * @global type $description
 */
function submit_video_category()
{
        global $langCategoryAdded, $langCategoryModded,
               $categoryname, $description, $currentCourseID;

        register_posted_variables(array('categoryname' => true,
                                        'description' => true), 'all', 'trim');
        $set_sql = "SET name = " . quote($categoryname) . ",
                        description = " . quote(purify($description));

        if (isset($_POST['id'])) {
                $id = intval($_POST['id']);
                db_query("UPDATE `video_category` $set_sql WHERE id = $id", $currentCourseID);
                $catlinkstatus = $langCategoryModded;
        } else {                
                db_query("INSERT INTO `video_category` $set_sql", $currentCourseID);
                $catlinkstatus = $langCategoryAdded;
        }
}

/**
 * @brief delete video / videolinks
 * @global type $currentCourseID
 * @param type $id
 * @param type $table
 */
function delete_video($id, $table) {
        global $currentCourseID, $webDir;
                
        $result = db_query("SELECT * FROM $table WHERE id = $id", $currentCourseID);
        $myrow = mysql_fetch_array($result);
        if ($table == "video") {
                unlink("$webDir/video/$currentCourseID/".$myrow['path']);
        }        
        $result = db_query("DELETE FROM $table WHERE id = $id", $currentCourseID);
}


/**
 * @brief delete video category
 * @global type $currentCourseID
 * @param type $id
 */
function delete_video_category($id)
{
	global $currentCourseID;
		
        db_query("DELETE FROM video_category WHERE id = $id", $currentCourseID);        
}


/**
 * @brief display links of category (if category is defined) else display all
 * @global type $currentCourseID
 * @global type $is_in_tinymce
 * @global type $themeimg
 * @global type $tool_content
 * @global type $is_editor
 * @global type $code_cours
 * @global type $cours_id
 * @global type $langDelete
 * @global type $langVisible
 * @global type $langPreview
 * @global type $langSave
 * @global type $langResourceAccess
 * @global type $langResourceAccess
 * @global type $langModify
 * @global type $langConfirmDelete
 * @global type $filterv
 * @global type $filterl
 * @param type $cat_id
 */
function showlinksofcategory($cat_id = 0) {
    
    global $currentCourseID, $is_in_tinymce, $themeimg, $tool_content, $is_editor, $code_cours, $cours_id;
    global $langDelete, $langVisible, $langConfirmDelete;
    global $langPreview, $langSave, $langResourceAccess, $langResourceAccess, $langModify;
    global $filterv, $filterl, $langcreator, $langpublisher;
   
    if ($is_editor) {
        $vis_q = '';
    } else {    
        $vis_q = "AND visible = 1";
    }
    if ($cat_id > 0) {
        $results['video'] = db_query("SELECT * FROM video $filterv WHERE category = $cat_id $vis_q ORDER BY titre", $currentCourseID);
        $results['videolinks'] = db_query("SELECT * FROM videolinks $filterl WHERE category = $cat_id $vis_q ORDER BY titre", $currentCourseID);
    } else {
        $results['video'] = db_query("SELECT * FROM video $filterv WHERE (category IS NULL OR category = 0) $vis_q ORDER BY titre", $currentCourseID);
        $results['videolinks'] = db_query("SELECT * FROM videolinks $filterl WHERE (category IS NULL OR category = 0) $vis_q ORDER BY titre", $currentCourseID);
    }
    
    $i = 0;         
    foreach($results as $table => $result) {
        while ($myrow = mysql_fetch_array($result)) {
                $myrow['course_id'] = $cours_id;
                switch($table) {
                    case 'video':
                        $vObj = MediaResourceFactory::initFromVideo($myrow);
                        if ($is_in_tinymce && !$compatiblePlugin) // use Access/DL URL for non-modable tinymce plugins
                            $vObj->setPlayURL($vObj->getAccessURL());

                        $link_href = MultimediaHelper::chooseMediaAhref($vObj);
                        $link_href .= (!$is_in_tinymce) ? "<br/><small>" . q($myrow[4]) . "</small>" : '';                            
                        $link_to_add = "<td>". $link_href . "</br />";
                        $link_to_add .= (!$is_in_tinymce) ? "<small>$langcreator: " . q($myrow['creator']) . ", $langpublisher: " . q($myrow['publisher']) . "</small>" : '';
                        $link_to_add .= "</td><td align='center'>". nice_format(date('Y-m-d', strtotime($myrow['date'])))."</td>";
                        $link_to_save = "<a href='" . $vObj->getAccessURL() . "'><img src='$themeimg/save_s.png' alt='$langSave' title='$langSave'></a>&nbsp;&nbsp;";
                        break;
                    case "videolinks":
                        $vObj = MediaResourceFactory::initFromVideoLink($myrow);
                        $link_href = MultimediaHelper::chooseMedialinkAhref($vObj);
                        if ($cat_id > 0) {
                            $link_to_add = "<td width = '70%'>". $link_href ."<br />" . q($myrow['description']) . "<br />";
                        } else {                            
                            $link_to_add = "<td width = '73%'>". $link_href ."<br/>" . q($myrow['description']) . "<br />";
                        }
                        $link_to_add .= (!$is_in_tinymce) ? "<small>$langcreator: " . q($myrow['creator']) . ", $langpublisher: " . q($myrow['publisher']) . "</small>" : '';
                        $link_to_add .= "</td><td width = '15%' align='center'>" . nice_format(date('Y-m-d', strtotime($myrow['date']))) . "</td>";
                        $link_to_save = "<a href='" . q($vObj->getPath()) . "' target='_blank'><img src='$themeimg/links_on.png' alt='".q($langPreview)."' title='".q($langPreview)."'></a>&nbsp;";
                        break;
                    default:
                        exit;
                }
                if ($is_editor or resource_access($myrow['visible'], $myrow['public'])) {
                    if ($myrow['visible'] == '1') {
                            $visibility = 0;
                            $vis_icon = 'visible';
                            if ($i % 2) {
                                    $rowClass = "class='odd'";
                            } else {
                                    $rowClass = "class='even'";
                            }
                            $tool_content .= "<tr $rowClass>";
                    } elseif ($is_editor and $myrow['visible'] == 0) {
                            $tool_content .= "<tr class='invisible'>";
                            $visibility = 1;
                            $vis_icon = 'invisible';                                    
                    }                        
                    if ($cat_id > 0) {
                        $tool_content .= "<td>&nbsp;</td>";
                    }
                    $tool_content .= "<td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' alt=''></td>$link_to_add";
                    if ($is_editor) {
                        $width = '17%';
                    } else {
                        $width = '5%';
                    }
                    // preview / save icon
                    $tool_content .= "<td align='center' width=$width> $link_to_save";
                    if (!$is_in_tinymce and $is_editor) {
                        $tool_content .= "                           
                              <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=$myrow[id]&amp;table_edit=$table'>
                              <img src='$themeimg/edit.png' title='".q($langModify)."'>
                              </a><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=$myrow[id]&amp;delete=yes&amp;table=$table' onClick=\"return confirmation('".js_escape("$langConfirmDelete")."');\">
                              <img src='$themeimg/delete.png' title='".q($langDelete)."'></a>&nbsp;";
                            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;vid=$myrow[id]&amp;vis=$visibility&amp;table=$table'>" .    
                                                        "<img src='$themeimg/$vis_icon.png' " .
                                                        "title='".q($langVisible)."' alt='".q($langVisible)."' /></a>&nbsp;";
                    }
                    if (!$is_in_tinymce and $is_editor) {
                        if (course_status($cours_id) == COURSE_OPEN) {
                            if ($myrow['public']) {
                                    $tool_content .= "&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;vid=$myrow[id]&amp;limited=1&amp;table=$table'>" .
                                                    "<img src='$themeimg/access_public.png' " .
                                                    "title='".q($langResourceAccess)."' alt='".q($langResourceAccess)."' /></a>";
                            } else {
                                    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;vid=$myrow[id]&amp;public=1&amp;table=$table'>" .
                                                    "<img src='$themeimg/access_limited.png' " .
                                                    "title='".q($langResourceAccess)."' alt='".q($langResourceAccess)."' /></a>";
                            }
                        }
                    }
                    $tool_content .= "</td>";
                    $tool_content .= "</tr>";
                } // end of check resource access
            $i++;                    
        } // while
    } // foreach        
}