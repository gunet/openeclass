<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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
/* * *****************************************************************
 *                          VIDEO UPLOADER AND DOWNLOADER
 * *******************************************************************

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

$require_current_course = true;
$require_help = true;
$helpTopic = 'Video';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_VIDEO);
/* * *********************************** */

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
$menuTypeID = ($is_in_tinymce) ? 5 : 2;
list($filterv, $filterl, $compatiblePlugin) = (isset($_REQUEST['docsfilter'])) ? select_proper_filters($_REQUEST['docsfilter']) : array('', '', true);

if ($is_in_tinymce) {
    $_SESSION['embedonce'] = true; // necessary for baseTheme
    load_js('jquery');
    load_js('tinymce.popup.urlgrabber.min.js');
}

if ($is_editor) {
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

    $diskQuotaVideo = Database::get()->querySingle("SELECT video_quota FROM course WHERE code=?s", $course_code)->video_quota;
    $updir = "$webDir/video/$course_code"; //path to upload directory
    $diskUsed = dir_total_space($updir);
    $idx = new Indexer();
    $vdx = new VideoIndexer($idx);
    $vldx = new VideolinkIndexer($idx);

    if (isset($_GET['showQuota']) and $_GET['showQuota'] == true) {
        $nameTools = $langQuotaBar;
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langVideo);
        $tool_content .= showquota($diskQuotaVideo, $diskUsed);
        draw($tool_content, $menuTypeID);
        exit;
    }

// visibility commands
    if (isset($_GET['vis'])) {
        $new_vis_status = intval($_GET['vis']);
        $table = select_table($_GET['table']);
        Database::get()->query("UPDATE $table SET visible = ?d WHERE id = ?d AND course_id = ?d", $new_vis_status, $_GET['vid'], $course_id);
        $action_message = "<p class='success'>$langViMod</p>";
    }
// Public accessibility commands
    if (isset($_GET['public']) or isset($_GET['limited'])) {
        $new_public_status = intval(isset($_GET['public'])) ? 1 : 0;
        $table = select_table($_GET['table']);
        Database::get()->query("UPDATE $table SET public = ?d WHERE id = ?d AND course_id = ?d", $new_public_status, $_GET['vid'], $course_id);
        $action_message = "<p class='success'>$langViMod</p>";
    }

    if (isset($_POST['edit_submit'])) { // edit
        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);
            if (isset($_POST['table'])) {
                $table = select_table($_POST['table']);
            }
            if ($table == 'video') {
                Database::get()->query("UPDATE video SET title = ?s,
                                                 description = ?s,
                                                 creator = ?s,
                                                 publisher = ?s
                                             WHERE id = ?d"
                        , $_POST['title'], $_POST['description'], $_POST['creator'], $_POST['publisher'], $id);
            } elseif ($table == 'videolink') {
                Database::get()->query("UPDATE videolink SET url = ?s,
                                                      title = ?s,
                                                      description = ?s,
                                                      creator = ?s,
                                                      publisher = ?s
                                                  WHERE id = ?d", canonicalize_url($_POST['url']), $_POST['title'], $_POST['description'], $_POST['creator'], $_POST['publisher'], $id);
            }
            if ($table == 'video')
                $vdx->store($id);
            else
                $vldx->store($id);
            $txt_description = ellipsize(canonicalize_whitespace(strip_tags($_POST['description'])), 50, '+');
            Log::record($course_id, MODULE_ID_VIDEO, LOG_MODIFY, array('id' => $id,
                'url' => canonicalize_url($_POST['url']),
                'title' => $_POST['title'],
                'description' => $txt_description));
            $tool_content .= "<p class='success'>$langTitleMod</p><br />";
            $id = "";
        }
    }
    if (isset($_POST['add_submit'])) {  // add
        if (isset($_POST['URL'])) { // add videolinks
            $url = $_POST['URL'];
            if (!is_url_accepted($url,"https?")){
                $tool_content .= "<p class='error'>$langLinkNotPermitted</p><br />";
            }
            else{
                if ($_POST['title'] == '') {
                    $title = $url;
                } else {
                    $title = $_POST['title'];
                }
                $q = Database::get()->query('INSERT INTO videolink (course_id, url, title, description, creator, publisher, date)
                                                    VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s)', 
                                            $course_id, canonicalize_url($url), $title, $_POST['description'], $_POST['creator'], $_POST['publisher'], $_POST['date']);
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
                                                       (course_id, path, url, title, description, creator, publisher, date)
                                                       VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)'
                                    , $course_id, $path, $url, $_POST['title'], $_POST['description']
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
    }       // end of add
    if (isset($_GET['delete'])) { // delete
        $id = intval($_GET['id']);
        $table = select_table($_GET['table']);
        $myrow = Database::get()->querySingle("SELECT * FROM $table WHERE course_id = ?d AND id = ?d", $course_id, $id);
        if ($table == 'video') {
            unlink("$webDir/video/$course_code/" . $myrow->path);
        }
        Database::get()->querySingle("DELETE FROM $table WHERE course_id = ?d AND id = ?d", $course_id, $id);
        if ($table == 'video') {
            $vdx->remove($id);
        } else {
            $vldx->remove($id);
        }
        Log::record($course_id, MODULE_ID_VIDEO, LOG_DELETE, array('id' => $id, 'title' => $myrow->title));
        $tool_content .= "<p class='success'>$langDelF</p><br />";
        $id = "";
    } elseif (isset($_GET['form_input']) && $_GET['form_input'] == 'file') { // display video form
        $nameTools = $langAddV;
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langVideo);
        $tool_content .= "
                <form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$course_code' enctype='multipart/form-data' onsubmit=\"return checkrequired(this, 'title');\">
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
                <tr>
                  <th>&nbsp;</th>
                  <td class='right'><input type='submit' name='add_submit' value='$langAdd'></td>
                </tr>
                </table>
                </fieldset>
                </form>
                <br/>";
    }

// ------------------- if no submit -----------------------
    if (isset($_GET['id']) and isset($_GET['table_edit'])) {
        $id = intval($_GET['id']);
        $table_edit = select_table($_GET['table_edit']);
        if ($id) {
            $myrow = Database::get()->querySingle("SELECT * FROM $table_edit WHERE course_id = ?d AND id = ?d ORDER BY title", $course_id, $id);

            $id = $myrow->id;
            $url = $myrow->url;
            $title = $myrow->title;
            $description = $myrow->description;
            $creator = $myrow->creator;
            $publisher = $myrow->publisher;

            $nameTools = $langModify;
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langVideo);
            $tool_content .= "
                <form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'title');\">
                <fieldset>
                <legend>$langModify</legend>
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
               <tr>
                 <th>&nbsp;</th>
                 <td class='right'><input type='submit' name='edit_submit' value='$langModify'>
                     <input type='hidden' name='id' value='$id'>
                     <input type='hidden' name='table' value='$table_edit'>
                 </td>
               </tr>
               </table>
               </fieldset>
               </form>
               <br/>";
        }
    }       // if id

    if (!isset($_GET['form_input']) && !$is_in_tinymce) {
        $tool_content .= "
          <div id='operations_container'>
          <ul id='opslist'>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;form_input=file'>$langAddV</a></li>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;form_input=url'>$langAddVideoLink</a></li>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;showQuota=TRUE'>$langQuotaBar</a></li>
          </ul>
        </div>";
    }


    $count_video = Database::get()->querySingle("SELECT COUNT(*) as count FROM video WHERE course_id = ?d $filterv ORDER BY title", $course_id)->count;
    $count_video_links = Database::get()->querySingle("SELECT count(*) as count FROM videolink WHERE course_id = ?d $filterl ORDER BY title", $course_id)->count;

    if ($count_video[0] <> 0 || $count_video_links[0] <> 0) {
        // print the list if there is no editing
        $results['video'] = Database::get()->queryArray("SELECT * FROM video WHERE course_id = ?d $filterv ORDER BY title", $course_id);
        $results['videolink'] = Database::get()->queryArray("SELECT * FROM videolink WHERE course_id = ?d $filterl ORDER BY title", $course_id);
        $i = 0;
        $count_video_presented_for_admin = 1;
        $tool_content .= "
        <table width='100%' class='tbl_alt'>
        <tr>
          <th colspan='2'><div align='left'>$langVideoDirectory</div></th>";
        if (!$is_in_tinymce) {
            $tool_content .= "
                <th width='150'><div align='left'>$langcreator</div></th>
                <th width='150'><div align='left'>$langpublisher</div></th>";
        }
        $tool_content .= "<th width='70'>$langDate</th>";
        if (!$is_in_tinymce) {
            $tool_content .= "<th width='110'>$langActions</th>";
        }
        $tool_content .= "</tr>";
        foreach ($results as $table => $result) {
            foreach ($result as $myrow) {
                switch ($table) {
                    case 'video':
                        $vObj = MediaResourceFactory::initFromVideo($myrow);
                        if ($is_in_tinymce && !$compatiblePlugin) // use Access/DL URL for non-modable tinymce plugins
                            $vObj->setPlayURL($vObj->getAccessURL());

                        $link_href = MultimediaHelper::chooseMediaAhref($vObj);
                        $link_href .= (!$is_in_tinymce) ? "<br/><small>" . q($myrow->description) . "</small>" : '';
                        $link_to_add = "<td>" . $link_href . "</td>";
                        $link_to_add .= (!$is_in_tinymce) ? "<td>" . q($myrow->creator) . "</td><td>" . q($myrow->publisher) . "</td>" : '';
                        $link_to_add .= "<td align='center'>" . nice_format(date('Y-m-d', strtotime($myrow->date))) . "</td>";
                        $link_to_save = "<a href='" . $vObj->getAccessURL() . "'><img src='$themeimg/save_s.png' alt='$langSave' title='$langSave'></a>&nbsp;&nbsp";
                        break;
                    case 'videolink':
                        $vObj = MediaResourceFactory::initFromVideoLink($myrow);
                        $link_href = MultimediaHelper::chooseMedialinkAhref($vObj);
                        $link_to_add = "<td>" . $link_href . "<br/>" . q($myrow->description) . "</td>";
                        $link_to_add .= (!$is_in_tinymce) ? "<td>" . q($myrow->creator) . "</td><td>" . q($myrow->publisher) . "</td>" : '';
                        $link_to_add .= "<td align='center'>" . nice_format(date('Y-m-d', strtotime($myrow->date))) . "</td>";
                        $link_to_save = "<a href='" . q($vObj->getPath()) . "' target='_blank'><img src='$themeimg/links_on.png' alt='$langPreview' title='$langPreview'></a>&nbsp;&nbsp;";
                        break;
                    default:
                        exit;
                }
                if ($is_editor and $myrow->visible == '1') {
                    $visibility = 0;
                    $vis_icon = 'visible';
                    if ($i % 2) {
                        $rowClass = "class='odd'";
                    } else {
                        $rowClass = "class='even'";
                    }
                    $tool_content .= "<tr $rowClass>";
                } else {
                    $tool_content .= "<tr class='invisible'>";
                    $visibility = 1;
                    $vis_icon = 'invisible';
                }
                $tool_content .= "<td width='1' valign='top'>
                                      <img style='padding-top:3px;' src='$themeimg/arrow.png' alt=''>
                                   </td>
                                   $link_to_add";
                if (!$is_in_tinymce) {
                    $tool_content .= "<td>
                                      $link_to_save" . icon('edit', $langModify, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$myrow->id&amp;table_edit=$table") . "
                                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=" . $myrow->id . "&amp;delete=yes&amp;table=$table' onClick=\"return confirmation('" . js_escape($langConfirmDelete . " " . $myrow->title) . "');\">
                                        <img src='$themeimg/delete.png' title='$langDelete'></a>&nbsp;";
                    $tool_content .= icon($vis_icon, $langVisible, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vid=$myrow->id&amp;vis=$visibility&amp;table=$table") . "&nbsp;";
                    if (course_status($course_id) == COURSE_OPEN) {
                        if ($myrow->public) {
                            $tool_content .= icon('access_public', $langResourceAccess, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vid=$myrow->id&amp;limited=1&amp;table=$table");
                        } else {
                            $tool_content .= icon('access_limited', $langResourceAccess, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vid=$myrow->id&amp;public=1&amp;table=$table");
                        }
                    }
                }
                $tool_content .= "</td></tr>";
                $i++;
                $count_video_presented_for_admin++;
            } // while
        } // foreach
        $tool_content.="</table>";
    } else {
        $tool_content .= "<p class='alert1'>$langNoVideo</p>";
    }
}   // if uid=prof_id
// student view
else {

    ModalBoxHelper::loadModalBox(true);

    $results['video'] = Database::get()->queryArray("SELECT * FROM video WHERE course_id = ?d $filterv ORDER BY title", $course_id);
    $results['videolink'] = Database::get()->queryArray("SELECT * FROM videolink WHERE course_id = ?d $filterl ORDER BY title", $course_id);
    $count_video = Database::get()->querySingle("SELECT COUNT(*) as count FROM video WHERE course_id = ?d $filterv", $course_id)->count;
    $count_video_links = Database::get()->querySingle("SELECT COUNT(*) as count FROM videolink WHERE course_id = ?d $filterl", $course_id)->count;

    if ($count_video[0] <> 0 || $count_video_links[0] <> 0) {
        $tool_content .= "
                <table width='100%' class='tbl_alt'>
                <tr>
                  <th colspan='2'><div align='left'>$langDirectory $langVideo</div></th>";
        if (!$is_in_tinymce) {
            $tool_content .= "<th width='70'>$langActions</th>";
        }
        $tool_content .= "</tr>";
        $i = 0;
        $count_video_presented = 1;
        foreach ($results as $table => $result) {
            foreach ($result as $myrow) {
                switch ($table) {
                    case 'video':
                        $vObj = MediaResourceFactory::initFromVideo($myrow);
                        if ($is_in_tinymce && !$compatiblePlugin) // use Access/DL URL for non-modable tinymce plugins
                            $vObj->setPlayURL($vObj->getAccessURL());

                        $link_href = MultimediaHelper::chooseMediaAhref($vObj);
                        $link_href .= (!$is_in_tinymce) ? "<br/><small>" . q($myrow->description) . "</small>" : '';
                        $link_to_add = "<td>" . $link_href . "</td>";
                        $link_to_save = "<a href='" . $vObj->getAccessURL() . "'><img src='$themeimg/save_s.png' alt='$langSave' title='$langSave'></a>&nbsp;&nbsp;";
                        break;
                    case 'videolink':
                        $vObj = MediaResourceFactory::initFromVideoLink($myrow);
                        $link_href = MultimediaHelper::chooseMedialinkAhref($vObj);
                        $link_to_add = "<td>" . $link_href . "<br/>" . q($myrow->description) . "</td>";
                        $link_to_save = "<a href='" . q($vObj->getPath()) . "' target='_blank'><img src='$themeimg/links_on.png' alt='$langPreview' title='$langPreview'></a>&nbsp;&nbsp;";
                        break;
                    default:
                        exit;
                }
                if ($i % 2) {
                    $rowClass = "class='odd'";
                } else {
                    $rowClass = "class='even'";
                }
                if (resource_access($myrow->visible, $myrow->public)) {
                    $tool_content .= "<tr $rowClass>";
                    $tool_content .= "<td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' alt=''></td>";
                    $tool_content .= $link_to_add;
                    if (!$is_in_tinymce) {
                        $tool_content .= "<td align='center'>$link_to_save</td>";
                    }
                    $tool_content .= "</tr>";
                }
                $i++;
                $count_video_presented++;
            }
        }
        $tool_content .= "</table>";
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

/**
 *
 * @param type $table
 * @return return table name
 */
function select_table($table) {
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
            foreach (MultimediaHelper::getSupportedImages() as $imgfmt) {
                if ($first) {
                    $ors .= "path LIKE '%$imgfmt%'";
                    $first = false;
                } else
                    $ors .= " OR path LIKE '%$imgfmt%'";
            }

            $filterv = "AND ( $ors )";
            $filterl = "AND false";
            break;
        case 'zip':
            $filterv = $filterl = "AND false";
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
