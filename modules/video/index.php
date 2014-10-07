<?php
/* ========================================================================
 * Open eClass 3.0
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
 * @abstract upload and display multimedia files
*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Video';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';

/**** The following is added for statistics purposes ***/
require_once 'include/action.php';
$action = new action();
$action->record('MODULE_ID_VIDEO');
/**************************************/

require_once 'include/lib/forcedownload.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/log.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/search/videoindexer.class.php';
require_once 'modules/search/videolinkindexer.class.php';

$nameTools = $langVideo;

if (isset($_SESSION['givenname'])) {
    $nick = q($_SESSION['givenname'] . ' ' . $_SESSION['surname']);
}

$is_in_tinymce = (isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce') ? true : false;
$menuTypeID = ($is_in_tinymce) ? 5: 2;
list($filterv, $filterl, $compatiblePlugin) = (isset($_REQUEST['docsfilter']))
        ? select_proper_filters($_REQUEST['docsfilter'])
        : array('', '', true);

if ($is_in_tinymce) {
    $_SESSION['embedonce'] = true; // necessary for baseTheme

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

    if (!$is_in_tinymce and (!isset($_GET['showQuota']))) {
        if (!isset($_GET['form_input']) and (!isset($_GET['action'])) and (!isset($_GET['table_edit']))) {
            $tool_content .= action_bar(array(
                array('title' => $langAddV,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;form_input=file",
                      'icon' => 'fa-plus-circle',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success'),
                array('title' => $langAddVideoLink,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;form_input=url",
                      'icon' => 'fa-plus-circle',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success'),
                array('title' => $langCategoryAdd,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=addcategory",
                      'icon' => 'fa-plus-circle'),
                array('title' => $langQuotaBar,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;showQuota=true",
                      'icon' => 'fa-pie-chart')));
        } else {
            $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));
        }
    }
        
    $diskQuotaVideo = Database::get()->querySingle("SELECT video_quota FROM course WHERE code=?s", $course_code)->video_quota;
    $updir = "$webDir/video/$course_code"; //path to upload directory
    $diskUsed = dir_total_space($updir);
    $idx = new Indexer();
    $vdx = new VideoIndexer($idx);
    $vldx = new VideolinkIndexer($idx);

    if (isset($_GET['showQuota']) and $_GET['showQuota'] == TRUE) {
            $nameTools = $langQuotaBar;
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langVideo);
            $tool_content .= showquota($diskQuotaVideo, $diskUsed);
            draw($tool_content, $menuTypeID);
            exit;
    }

    // visibility commands
    if (isset($_GET['vis'])) {
            $table = select_table($_GET['table']);
            Database::get()->query("UPDATE $table SET visible = ?d WHERE id = ?d", $_GET['vis'], $_GET['vid']);
            $action_message = "<p class='success'>$langViMod</p>";
    }

    // Public accessibility commands
    if (isset($_GET['public']) or isset($_GET['limited'])) {
            $new_public_status = isset($_GET['public'])? 1: 0;
            $table = select_table($_GET['table']);
            Database::get()->query("UPDATE $table SET public = ?d WHERE id = ?d", $new_public_status, $_GET['vid']);
            $action_message = "<p class='success'>$langViMod</p>";
    }

    /**
     * display form for add / edit category
     */
    if (isset($_GET['action'])) {
        $tool_content .=  "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>";
        if ($_GET['action'] == 'editcategory') {
            $myrow = Database::get()->querySingle("SELECT * FROM video_category WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
            if ($myrow) {
                    $form_name = ' value="' . q($myrow->name) . '"';
                    $form_description = standard_text_escape($myrow->description);
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
                    Database::get()->query("UPDATE video
                            SET title = ?s,
                                description = ?s,
                                creator = ?s,
                                publisher = ?s,
                                category = ?d
                             WHERE id = ?d",
                            $_POST['title'], $_POST['description'], $_POST['creator'], $_POST['publisher'], $_POST['selectcategory'], $id);
            } elseif ($table == 'videolink') {
                Database::get()->query("UPDATE videolink
                        SET url = ?s,
                            title = ?s,
                            description = ?s,
                            creator = ?s,
                            publisher = ?s,
                            category = ?d
                        WHERE id = ?d",
                        canonicalize_url($_POST['url']), $_POST['title'],
                        $_POST['description'], $_POST['creator'],
                        $_POST['publisher'], $_POST['selectcategory'], $id);
            }
            if ($table == 'video') {
                    $vdx->store($id);
            } else {
                    $vldx->store($id);
            }
            $txt_description = ellipsize(canonicalize_whitespace(strip_tags($_POST['description'])), 50, '+');
            Log::record($course_id, MODULE_ID_VIDEO, LOG_MODIFY, array('id' => $id,
                                                                        'url' => canonicalize_url($_POST['url']),
                                                                        'title' => $_POST['title'],
                                                                        'description' => $txt_description));
            $tool_content .= "<p class='success'>$langGlossaryUpdated</p><br />";
        }
    }
    if (isset($_POST['add_submit'])) {  // add
            if(isset($_POST['URL'])) { // add videolink
                    $url = $_POST['URL'];
                    if (!is_url_accepted($url,"https?")) {
                        $tool_content .= "<p class='error'>$langLinkNotPermitted</p><br />";
                    } else {
                    if ($_POST['title'] == '') {
                        $title = $url;
                    } else {
                        $title = $_POST['title'];
                    }
                    $q = Database::get()->query('INSERT INTO videolink (course_id, url, title, description, category, creator, publisher, date)
                                                        VALUES (?s, ?s, ?s, ?s, ?d, ?s, ?s, ?s)',
                                                $course_id, canonicalize_url($url), $title, $_POST['description'], $_POST['selectcategory'], $_POST['creator'], $_POST['publisher'], $_POST['date']);
                    $id = $q->lastInsertID;
                    $vldx->store($id);
                    $txt_description = ellipsize(canonicalize_whitespace(strip_tags($_POST['description'])), 50, '+');
                    Log::record($course_id, MODULE_ID_VIDEO, LOG_INSERT, @array('id' => $id,
                                                                            'url' => canonicalize_url($url),
                                                                            'title' => $title,
                                                                            'description' => $txt_description));
                    $tool_content .= "<p class='success'>$langLinkAdded</p><br />";
                }
            } else {  // add video
                    if (isset($_FILES['userFile']) && is_uploaded_file($_FILES['userFile']['tmp_name'])) {

                    validateUploadedFile($_FILES['userFile']['name'], $menuTypeID);

                    if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaVideo) {
                        $tool_content .= "<p class='caution'>$langNoSpace<br />
                                                    <a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p><br />";
                        draw($tool_content, $menuTypeID, null, $head_content);
                        exit;
                    } else {
                        $file_name = $_FILES['userFile']['name'];
                        $tmpfile = $_FILES['userFile']['tmp_name'];
                        // convert php file in phps to protect the platform against malicious codes
                        $file_name = preg_replace("/\.php.*$/", ".phps", $file_name);
                        // check for dangerous file extensions
                        if (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' . 'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' . 'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $file_name)) {
                            $tool_content .= "<p class='caution'>$langUnwantedFiletype:  $file_name<br />";
                            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p><br />";
                            draw($tool_content, $menuTypeID, null, $head_content);
                            exit;
                        }
                        $file_name = str_replace(" ", "%20", $file_name);
                        $file_name = str_replace("%20", "", $file_name);
                        $file_name = str_replace("\'", "", $file_name);
                        $safe_filename = sprintf('%x', time()) . randomkeys(16) . "." . get_file_extension($file_name);
                        $iscopy = copy("$tmpfile", "$updir/$safe_filename");
                        if (!$iscopy) {
                            $tool_content .= "<p class='success'>$langFileNot<br />
                                                    <a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p><br />";
                            draw($tool_content, $menuTypeID, null, $head_content);
                            exit;
                        }
                        $path = '/' . $safe_filename;
                        $url = $file_name;
                        $id = Database::get()->query('INSERT INTO video
                                                           (course_id, path, url, title, description, category, creator, publisher, date)
                                                           VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, ?s, ?s)'
                                        , $course_id, $path, $url, $_POST['title'], $_POST['description'], $_POST['selectcategory']
                                        , $_POST['creator'], $_POST['publisher'], $_POST['date'])->lastInsertID;
                    }
                    $vdx->store($id);
                    $txt_description = ellipsize(canonicalize_whitespace(strip_tags($_POST['description'])), 50, '+');
                    Log::record($course_id, MODULE_ID_VIDEO, LOG_INSERT, @array('id' => $id,
                                                                                'path' => $path,
                                                                                'url' => $_POST['url'],
                                                                                'title' => $_POST['title'],
                                                                                'description' => $txt_description));
                    $tool_content .= "<p class='success'>$langFAdd</p><br />";
                }
            }
        }	// end of add
        if (isset($_GET['delete'])) {
                if ($_GET['delete'] == 'delcat') { // delete video category
                    $q = Database::get()->queryArray("SELECT id FROM video WHERE category = ?d AND course_id = ?d", $_GET['id'], $course_id);
                    foreach ($q as $a) {
                        delete_video($a->id, 'video');
                    }
                    $q = Database::get()->queryArray("SELECT id FROM videolink WHERE category = ?d AND course_id = ?d", $_GET['id'], $course_id);
                    foreach ($q as $a) {
                        delete_video($a->id, 'videolink');
                    }
                    delete_video_category($_GET['id']);
                } else {  // delete video / videolink
                    $table = select_table($_GET['table']);
                    delete_video($_GET['id'], $table);
                }
                $tool_content .= "<p class='success'>$langGlossaryDeleted</p><br />";
        } elseif (isset($_GET['form_input']) && $_GET['form_input'] == 'file') { // display video form
            $tool_content .= "
                <form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$course_code' enctype='multipart/form-data' onsubmit=\"return checkrequired(this, 'title');\">
                <fieldset>
                <legend>$langAddV</legend>
                <table class='table table-striped table-bordered table-hover'>
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
                <td><input type='text' name='title' size='55'></td>
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
                <td><input type='text' name='date' value='" . date('Y-m-d G:i:s') . "' size='55'></td>
                </tr>
                <tr><th>$langCategory:</th>
                <td><select name='selectcategory'>
                <option value='0'>--</option>";
                $resultcategories = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY `name`", $course_id);
                foreach ($resultcategories as $myrow) {
                    $tool_content .=  "<option value='$myrow->id'";
                    $tool_content .= '>' . q($myrow->name) . "</option>";
                }
                $tool_content .=  "</select>
                </tr>
                <tr>
                <th>&nbsp;</th>
                <td class='right'><input type='submit' name='add_submit' value='" . q($langUpload) . "'></td>
                </tr>
                </table>
                </fieldset>
                <div class='smaller right'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div></form> <br>";

        } elseif (isset($_GET['form_input']) && $_GET['form_input'] == 'url') { // display video links form
            $nameTools = $langAddVideoLink;
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langVideo);
            $tool_content .= "
                <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'title');\">
                <table width='100%' class='tbl'>
                <tr>
                  <th valign='top' width='190'>$langGiveURL:<input type='hidden' name='id' value=''></th>
                  <td class='smaller'><input type='text' name='URL' size='55'>
                      <br />
                      $langURL
                  </td>
                <tr>
                  <th>$langTitle:</th>
                  <td><input type='text' name='title' size='55'></td>
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
                  <td><input type='text' name='date' value='" . date('Y-m-d G:i') . "' size='55'></td>
                </tr>
                </tr>
                <tr><th>$langCategory:</th>
                <td><select name='selectcategory'>
                <option value='0'>--</option>";
                $resultcategories = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY `name`", $course_id);
                foreach ($resultcategories as $myrow) {
                    $tool_content .=  "<option value='$myrow->id'";
                    $tool_content .= '>' . q($myrow->name) . "</option>";
                }
                $tool_content .=  "</select>
                </tr>
                <tr>
                  <th>&nbsp;</th>
                  <td class='right'><input type='submit' name='add_submit' value='$langAdd'></td>
                </tr>
                </table>
                </form>
                <br/>";
        }

    // ------------------- if no submit -----------------------
    if (isset($_GET['id']) and isset($_GET['table_edit']))  {
            $id = intval($_GET['id']);
            $table_edit = select_table($_GET['table_edit']);

            $myrow = Database::get()->querySingle("SELECT * FROM $table_edit WHERE course_id = ?d AND id = ?d ORDER BY title", $course_id, $id);

            $id = $myrow->id;
            $url = $myrow->url;
            $title = $myrow->title;
            $description = $myrow->description;
            $creator = $myrow->creator;
            $publisher = $myrow->publisher;
            $category = $myrow->category;
            $nameTools = $langModify;
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langVideo);
            $tool_content .= "
                <form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'title');\">
                <table width='100%' class='tbl'>";
            if ($table_edit == 'videolink') {
                $tool_content .= "
                        <tr>
                        <th>$langURL:</th>
                        <td><input type='text' name='url' value='" . q($url) . "' size='55'></td>
                        </tr>";
            } elseif ($table_edit == 'video') {
                $tool_content .= "<input type='hidden' name='url' value='" . q($url) . "'>";
            }
            @$tool_content .= "
                <tr>
                  <th width='90'>$langTitle:</th>
                  <td><input type='text' name='title' value='" . q($title) . "' size='55'></td>
                </tr>
                <tr>
                  <th>$langDescr:</th>
                  <td><textarea rows='3' name='description' cols='52'>" . q($description) . "</textarea></td>
               </tr>
               <tr>
                 <th>$langcreator:</th>
                 <td><input type='text' name='creator' value='" . q($creator) . "' size='55'></td>
               </tr>
               <tr>
                 <th>$langpublisher:</th>
                 <td><input type='text' name='publisher' value='" . q($publisher) . "' size='55'></td>
               </tr>
               <tr><th>$langCategory:</th>
            <td><select name='selectcategory'>
            <option value='0'>--</option>";
            $resultcategories = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY `name`", $course_id);
            foreach ($resultcategories as $myrow) {
                $tool_content .=  "<option value='$myrow->id'";
                if (isset($category) and $category == $myrow->id) {
                        $tool_content .= " selected='selected'";
                }
                $tool_content .= '>' . q($myrow->name) . "</option>";
            }
            $tool_content .=  "</select>
            </tr>
            <tr>
              <th>&nbsp;</th>
              <td class='right'><input type='submit' name='edit_submit' value='$langModify'>
                  <input type='hidden' name='id' value='$id'>
                  <input type='hidden' name='table' value='$table_edit'>
              </td>
            </tr>
            </table>
            </form>";
    }

}   // end of admin check

ModalBoxHelper::loadModalBox(true);

$count_video = Database::get()->querySingle("SELECT COUNT(*) AS count FROM video WHERE course_id = ?d $filterv ORDER BY title", $course_id)->count;
$count_video_links = Database::get()->querySingle("SELECT count(*) AS count FROM videolink WHERE course_id = ?d $filterl ORDER BY title", $course_id)->count;
$num_of_categories = Database::get()->querySingle("SELECT COUNT(*) AS count FROM `video_category` WHERE course_id = ?d", $course_id)->count;

$expand_all = isset($_GET['d']) && $_GET['d'] == '1';
if ($count_video[0] > 0 or $count_video_links[0] > 0) {
    $tool_content .= "<div class='table-responsive'><table class='table table-striped table-bordered table-hover'>
        <tr><th>$langVideoDirectory</th>
        <th class='text-center'>$langDate</th>
        <th class='text-center'>" . icon('fa-gears') . "</th>";
    $tool_content .= "</tr>";
    //display uncategorized links
    showlinksofcategory();

    if ($num_of_categories > 0) { // categories found ?
        $tool_content .= "<tr><th colspan='2'>$langCatVideoDirectory</th>
            <td class='text-center'>" .
            ($expand_all?
                icon('fa-folder-open', $showall, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;d=0"):
                icon('fa-folder', $shownone, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;d=1")) .
            "</td></tr>";
        $resultcategories = Database::get()->queryArray("SELECT * FROM `video_category` WHERE course_id = ?d ORDER BY name", $course_id);
        foreach ($resultcategories as $myrow) {
            $description = standard_text_escape($myrow->description);
            if ((isset($_GET['d']) and $_GET['d'] == 1) or (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id)) {
                $folder_icon = icon('fa-folder-open-o', $shownone);
            } else {
                $folder_icon = icon('fa-folder-o', $showall);
            }
            $tool_content .= "<tr><th colspan='2'>$folder_icon ";
            if (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id) {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>".q($myrow->name)."</a>";
            } else {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;cat_id=$myrow->id'>".q($myrow->name)."</a>";
            }
            if (!empty($description)) {
                    $tool_content .= '<br>' . $description;
            }
            $tool_content .= "</th><td class='option-btn-cell'>";
            if ($is_editor) {
                $tool_content .= action_button(array(
                    array('title' => $langModify,
                          'icon' => 'fa-edit',
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$myrow->id&amp;action=editcategory"),
                    array('title' => $langDelete,
                          'icon' => 'fa-times',
                          'class' => 'delete',
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$myrow->id&amp;delete=delcat",
                          'confirm' => $langCatDel)));
            }
            $tool_content .= '</td></tr>';
            if ($expand_all or (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id)) {
                showlinksofcategory($myrow->id);
            }
        }
    }
    $tool_content .= "</table></div>";
} else {
    $tool_content .= "<div class='alert alert-warning' role='alert'>$langNoVideo</div>";
}

add_units_navigation(TRUE);

draw($tool_content, $menuTypeID, null, $head_content);


/**
 *
 * @param type $table
 * @return return table name
 */
function select_table($table)
{
        if ($table == 'videolink') {
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
 * @brief add / edit video category
 * @global type $course_id
 * @global type $langCategoryAdded
 * @global type $langCategoryModded
 * @global type $categoryname
 * @global type $description
 */
function submit_video_category()
{
        global $langCategoryAdded, $langCategoryModded,
               $categoryname, $description, $course_id;

        register_posted_variables(array('categoryname' => true,
                                        'description' => true), 'all', 'trim');
        $description = purify($description);
        if (isset($_POST['id'])) {
                Database::get()->query("UPDATE `video_category` SET name = ?s,
                                        description = ?s WHERE id = ?d", $categoryname, $description, $_POST['id']);
                $catlinkstatus = $langCategoryModded;
        } else {
                Database::get()->query("INSERT INTO `video_category` SET name = ?s,
                                description = ?s, course_id = ?d", $categoryname, $description, $course_id);
                $catlinkstatus = $langCategoryAdded;
        }
}


/**
 * @brief delete video / videolink
 * @global type $course_id
 * @global type $webDir
 * @param type $id
 * @param type $table
 */
function delete_video($id, $table) {
        global $course_id, $course_code, $webDir, $vdx, $vldx;

        $myrow = Database::get()->querySingle("SELECT * FROM $table WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $title = $myrow->title;
        if ($table == "video") {
                unlink("$webDir/video/$course_code/" . $myrow->path);
        }
        Database::get()->query("DELETE FROM $table WHERE course_id = ?d AND id = ?d", $course_id, $id);
        if ($table == 'video') {
            $vdx->remove($id);
        } else {
            $vldx->remove($id);
        }
        Log::record($course_id, MODULE_ID_VIDEO, LOG_DELETE, array('id' => $id, 'title' => $title));
}


/**
 * @brief delete video category
 * @param type $id
 */
function delete_video_category($id)
{
        Database::get()->query("DELETE FROM video_category WHERE id = ?d", $id);
}


/**
 * @brief display links of category (if category is defined) else display all
 * @global type $is_in_tinymce
 * @global type $themeimg
 * @global type $tool_content
 * @global type $is_editor
 * @global type $course_id
 * @global type $course_code
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

    global $course_id, $is_in_tinymce, $themeimg, $tool_content, $is_editor, $course_code;
    global $langDelete, $langVisible, $langConfirmDelete;
    global $langPreview, $langSave, $langResourceAccess, $langResourceAccess, $langModify;
    global $filterv, $filterl, $langcreator, $langpublisher;

    if ($is_editor) {
        $vis_q = '';
    } else {
        $vis_q = "AND visible = 1";
    }
    if ($cat_id > 0) {
        $results['video'] = Database::get()->queryArray("SELECT * FROM video $filterv WHERE course_id = ?d AND category = ?d $vis_q ORDER BY title", $course_id, $cat_id);
        $results['videolink'] = Database::get()->queryArray("SELECT * FROM videolink $filterl WHERE course_id = ?d AND category = ?d $vis_q ORDER BY title", $course_id, $cat_id);
    } else {
        $results['video'] = Database::get()->queryArray("SELECT * FROM video $filterv WHERE course_id = ?d AND (category IS NULL OR category = 0) $vis_q ORDER BY title", $course_id);
        $results['videolink'] = Database::get()->queryArray("SELECT * FROM videolink $filterl WHERE course_id = ?d AND (category IS NULL OR category = 0) $vis_q ORDER BY title", $course_id);
    }

    $i = 0;
    foreach($results as $table => $result) {
        foreach ($result as $myrow) {
            $myrow->course_id = $course_id;
            if (resource_access($myrow->visible, $myrow->public)) {
                switch($table) {
                    case 'video':
                        $vObj = MediaResourceFactory::initFromVideo($myrow);
                        if ($is_in_tinymce && !$compatiblePlugin) // use Access/DL URL for non-modable tinymce plugins
                            $vObj->setPlayURL($vObj->getAccessURL());
                        $link_href = MultimediaHelper::chooseMediaAhref($vObj);
                        $link_to_save = $vObj->getAccessURL();
                        break;
                    case "videolink":
                        $vObj = MediaResourceFactory::initFromVideoLink($myrow);
                        $link_href = MultimediaHelper::chooseMedialinkAhref($vObj);
                        $link_to_save = $vObj->getPath();
                        break;
                    default:
                        exit;
                }
                $tool_content .= "<tr><td>". $link_href;
                if (!$is_in_tinymce and (!empty($myrow->creator) or !empty($myrow->publisher))) {
                    $tool_content .= '<br><small>';
                    if ($myrow->creator == $myrow->publisher) {
                        $tool_content .= "$langcreator: " . q($myrow->creator);
                    } else {
                        $emit = false;
                        if (!empty($myrow->creator)) {
                            $tool_content .= "$langcreator: " . q($myrow->creator);
                            $emit = true;
                        }
                        if (!empty($myrow->publisher)) {
                            $tool_content .= ($emit? ', ': '') . "$langpublisher: " . q($myrow->publisher);
                        }
                    }
                    $tool_content .= '</small>';
                }
                $tool_content .= "</td><td class='text-center'>". nice_format(date('Y-m-d', strtotime($myrow->date))) .
                    "</td><td class='option-btn-cell'>" .
                    action_button(array(
                        array('title' => $langSave,
                              'url' => $link_to_save,
                              'icon' => 'fa-floppy-o'),
                        array('title' => $langModify,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$myrow->id&amp;table_edit=$table",
                              'icon' => 'fa-edit',
                              'show' => !$is_in_tinymce and $is_editor),
                        array('title' => $langDelete,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$myrow->id&amp;delete=yes&amp;table=$table",
                              'icon' => 'fa-times',
                              'confirm' => $langConfirmDelete,
                              'class' => 'delete'),
                        array('title' => $langVisible,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vid=$myrow->id&amp;table=$table&amp;vis=" .
                                       ($myrow->visible? '0': '1'),
                              'icon' => $myrow->visible? 'fa-eye-slash': 'fa-eye'),
                        array('title' => $langResourceAccess,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vid=$myrow->id&amp;table=$table&amp;" .
                                       ($myrow->public? 'limited=1': 'public=1'),
                              'icon' => $myrow->public? 'fa-unlock': 'fa-lock',
                              'show' => !$is_in_tinymce and $is_editor and course_status($course_id) == COURSE_OPEN))) .
                    "</td></tr>";
            } // end of check resource access
        } // foreach row
    } // foreach table
}
