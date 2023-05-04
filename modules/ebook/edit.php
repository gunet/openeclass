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
 * @file edit.php
 * @brief edit ebook information
 */

$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'ebook';

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/document/doc_init.php';

$toolName = $langEBook;

if (isset($_REQUEST['id'])) {
    $ebook_id = intval($_REQUEST['id']);
} else {
    redirect_to_home_page();
}

define('EBOOK_DOCUMENTS', true);
doc_init();

if (isset($_GET['delete'])) {
    Database::get()->query("DELETE FROM ebook_section WHERE ebook_id = ?d AND id = ?d", $ebook_id, $_GET['delete']);
    $return_url = "modules/ebook/edit.php?course=$course_code&id=$ebook_id&editEbook=1";
    redirect_to_home_page($return_url);
} elseif (isset($_GET['editEbook'])) {
        $info = Database::get()->querySingle("SELECT * FROM `ebook` WHERE course_id = ?d AND id = ?d", $course_id, $ebook_id);
        if (!$info) {
            redirect_to_home_page("modules/ebook/index.php?course=$course_code");
        }
        $pageName = $langEBookInfoEdit;
        $tool_content .= action_bar(array(
                        array('title' => $langBack,
                              'url' => "edit.php?course=$course_code&amp;id=$info->id",
                              'icon' => 'fa-reply',
                             'level' => 'primary-label')
                        ));
        // Form #1 - edit title
        $tool_content .= "
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                <input type='hidden' name='id' value='$ebook_id' />
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langTitle:</label>
                    <div class='col-sm-9 input-group'>
                        <input class='form-control' type='text' name='ebook_title' value='" . q($info->title) . "' />
                        <span class='input-group-btn'>
                            <button class='btn btn-primary' name='title_submit' type='submit' value='$langModify'>$langModify</button>
                        </span>
                    </div>
                </div>
            </form>
        </div>";
        // Form #2 - edit sections
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <fieldset>
        <h4>$langCourseUnits</h4>
        <input type='hidden' name='id' value='$ebook_id' />
          <table class='table-default'>
          <tr>
            <th class='text-left'>$langID</th>
            <th>$langTitle</th>
            <th width='75' class='text-center'>$langActions</th>
          </tr>";
        $q = Database::get()->queryArray("SELECT id, public_id, title FROM ebook_section
                           WHERE ebook_id = ?d
                           ORDER BY CONVERT(public_id, UNSIGNED), public_id", $info->id);
        $sections = array('' => '---');
        if (isset($_GET['s'])) {
            $edit_section = $_GET['s'];
        } else {
            $edit_section = null;
        }
        $section_editing = false;
        foreach ($q as $section) {
            $sid = $section->id;
            $qsid = Session::has('new_section_id') ? Session::get('new_section_id') : q($section->public_id);
            $qstitle = Session::has('new_section_title') ? Session::get('new_section_title') : q($section->title);
            $sections[$sid] = $qsid . '. ' . ellipsize($section->title, 25);
            if ($sid === $edit_section) {
                $section_id = "
                                <input type='hidden' name='csid' value='$sid'>
                                <div class='form-group'>
                                    <input type='text' class='form-control' name='new_section_id' value='$qsid'>
                                </div>";
                $section_title = "<div class='form-group".(Session::getError('new_section_title') ? " has-error" : "")."'>
                                    <input type='text size='3' class='form-control' name='new_section_title' value='$qstitle'>
                                    <span class='help-block'>".Session::getError('new_section_title')."</span>
                                </div>
                                ";
                $section_editing = true;
                $section_tools = "<input class='btn btn-primary' type='submit' name='new_section_submit' value='$langModify' />";
            } else {
                $section_id = q($section->public_id);
                $section_title = q($section->title);
                $section_tools = action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "edit.php?course=$course_code&amp;id=$ebook_id&amp;s=$sid&amp;editEbook=1",
                                          'icon' => 'fa-edit'),
                                    array('title' => $langDelete,
                                          'url' => "edit.php?course=$course_code&amp;id=$ebook_id&amp;delete=$sid&amp;editEbook=1",
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => $langEBookSectionDelConfirm)
                ));
            }
            $tool_content .= "
            <tr>
              <td style='width:70px;'>$section_id</td>
              <td>$section_title</td>
              <td class='text-center'>$section_tools</td>
            </tr>";
        }
        $new_section_id = Session::has('new_section_id') ? Session::get('new_section_id') : '';
        if (!$section_editing) {
            $tool_content .= "
            <tr>
                <td style='width:70px'>
                    <input class='form-control' type='text' name='new_section_id' value='".q($new_section_id)."'>
                </td>
                <td>
                    <div class='form-group".(Session::getError('new_section_title') ? " has-error" : "")."'>
                        <input class='form-control' type='text' size='35' name='new_section_title'>
                        <span class='help-block'>".Session::getError('new_section_title')."</span>
                    </div>
                </td>
                <td class='center'>
                    <input class='btn btn-primary' type='submit' name='new_section_submit' value='$langAdd'>
                </td>
            </tr>";
        }
        $tool_content .= "
          </table>
          </fieldset></form>";
} elseif (isset($_POST['new_section_submit'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('new_section_title'));
    $v->labels(array(
        'new_section_title' => "$langTheField $langTitle"
    ));
    if($v->validate()) {
        if (isset($_POST['csid'])) {
            Database::get()->query("UPDATE ebook_section
                                     SET public_id = ?s, title = ?s
                                     WHERE ebook_id = ?d AND id = ?d"
                    , $_POST['new_section_id'], $_POST['new_section_title'], $ebook_id, $_POST['csid']);
        } else {
            Database::get()->query("INSERT INTO ebook_section SET ebook_id = ?d,
                                                            public_id = ?s,
                                                            title = ?s"
                    , $ebook_id, $_POST['new_section_id'], $_POST['new_section_title']);
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
    }
    $redirect_link = isset($_POST['csid']) ? "modules/ebook/edit.php?course=$course_code&id=$ebook_id&editEbook=1" : "modules/ebook/edit.php?course=$course_code&id=$ebook_id&editEbook=1";
    redirect_to_home_page($redirect_link);
} elseif (isset($_POST['title_submit'])) {
    $info = Database::get()->querySingle("SELECT id, title FROM `ebook` WHERE course_id = ?d AND id = ?d", $course_id, $ebook_id);
    $ebook_title = trim($_POST['ebook_title']);
    if (!empty($ebook_title) and $info->title != $ebook_title) {
        Database::get()->query("UPDATE `ebook` SET title = ?s WHERE id = ?d", $ebook_title, $info->id);
    }
    Session::Messages($langEBookTitleModified, 'alert-success');
    redirect_to_home_page('modules/ebook/edit.php?course=' . $course_code . '&id=' . $ebook_id);
} elseif (isset($_POST['submit'])) {
    $basedir = $webDir . 'courses/' . $course_code . '/ebook/' . $ebook_id;
    list($paths, $files, $file_ids, $id_map) = find_html_files();
    foreach ($_POST['sid'] as $file_id => $sid) {
        if (!empty($sid)) {
            $sid = intval($sid);
            $file_id = intval($file_id);
            $qssid = $_POST['ssid'][$file_id];
            $qtitle = $_POST['title'][$file_id];
            if (isset($_POST['oldssid'][$file_id])) {
                $oldssid = intval($_POST['oldssid'][$file_id]);
                Database::get()->query("UPDATE ebook_subsection
                                                 SET section_id = ?s,
                                                     public_id = ?s,
                                                     title = ?s,
                                                     file_id = ?d
                                                 WHERE id = ?d"
                        , $sid, $qssid, $qtitle, $file_id, $oldssid);
                unset($_POST['oldssid'][$file_id]);
            } else {
                Database::get()->query("INSERT INTO ebook_subsection
                                                 SET section_id = ?s,
                                                     public_id = ?s,
                                                     title = ?s,
                                                     file_id = ?d"
                        , $sid, $qssid, $qtitle, $file_id);
            }
        }
    }
    if (isset($_POST['oldssid'])) {
        $oldssids = array();
        foreach ($_POST['oldssid'] as $key => $oldssid) {
            $oldssids[] = intval($oldssid);
        }
        if (count($oldssids)) {
            Database::get()->query('DELETE FROM ebook_subsection WHERE id IN (' . implode(', ', $oldssids) . ')');
        }
    }
    Session::Messages($langEBookSectionsModified, 'alert-success');
    redirect_to_home_page('modules/ebook/edit.php?course=' . $course_code . '&id=' . $ebook_id);
} else {
    $info = Database::get()->querySingle("SELECT * FROM `ebook` WHERE course_id = ?d AND id = ?d", $course_id, $ebook_id);

    if (!$info) {
        $tool_content .= "<div class='alert alert-warning'>$langNoEBook</div>";
    } else {
        $pageName = $langModify;
        $basedir = $webDir . '/courses/' . $course_code . '/ebook/' . $ebook_id;
        list($paths, $files, $file_ids, $id_map, $editable) = find_html_files();

        $sections = Database::get()->queryArray("SELECT id, public_id, title FROM ebook_section
                           WHERE ebook_id = ?d
                           ORDER BY CONVERT(public_id, UNSIGNED), public_id", $info->id);
        if ($sections){

            $sections_table = "<ul class='list-group'>";
            foreach ($sections as $section){
                $sections_table .=
                        "
                        <li class='list-group-item'>
                            ".q($section->public_id).".&nbsp;
                            ".q($section->title)."
                        </li>
                        ";
            }
            $sections_table .= "</ul>";


        } else {
            $sections_table = "<span class='not_visible'> - $langNoEBookSections - </span>";
        }
        // Form #1 - edit ebook title
        $tool_content .= action_bar(array(
                        array('title' => $langBack,
                              'url' => "index.php?course=$course_code",
                              'icon' => 'fa-reply',
                             'level' => 'primary-label')
                        ));
        $tool_content .= "
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <h3 class='panel-title'>$langEBookInfo &nbsp;
                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&id=$info->id&editEbook=1'>
                            <i class='fa fa-edit' title='$langEdit' data-toggle='tooltip'></i>
                        </a>
                    </h3>
                </div>
                <div class='panel-body'>
                    <div class='row  margin-bottom-fat'>
                        <div class='col-sm-2'>
                            <strong>$langTitle:</strong>
                        </div>
                        <div class='col-sm-10'>
                            " . q($info->title) . "
                        </div>
                    </div>
                    <div class='row  margin-bottom-fat'>
                        <div class='col-sm-2'>
                            <strong>$langCourseUnits:</strong>
                        </div>
                        <div class='col-sm-10'>
                            $sections_table
                        </div>
                    </div>
                </div>
            </div>";

        $q = Database::get()->queryArray("SELECT id, public_id, title FROM ebook_section
                           WHERE ebook_id = ?d
                           ORDER BY CONVERT(public_id, UNSIGNED), public_id", $info->id);
        $sections = array('' => '---');
        foreach ($q as $section) {
            $sid = $section->id;
            $qsid = q($section->public_id);
            $qstitle = q($section->title);
            $sections[$sid] = $qsid . '. ' . ellipsize($section->title, 25);
        }
        $pageName = $langEBookPages;
        $tool_content .= action_bar(array(
                            array('title' => $langNewEBookPage,
                              'url' => "new.php?course=$course_code&ebook_id=$ebook_id&amp;from=ebookEdit",
                              'icon' => 'fa-plus-circle',
                              'button-class' => 'btn-success',
                              'level' => 'primary-label'),
                            array('title' => $langFileAdmin,
                                  'url' => "document.php?course=$course_code&amp;ebook_id=$ebook_id",
                                  'icon' => 'fa-hdd-o',
                                  'level' => 'primary-label')
                        ));
        // Form #3 - edit subsection file assignment
        $q = Database::get()->queryArray("SELECT ebook_section.id AS sid,
                                  ebook_section.id AS psid,
                                  ebook_section.title AS section_title,
                                  ebook_subsection.id AS ssid,
                                  ebook_subsection.public_id AS pssid,
                                  ebook_subsection.title AS subsection_title,
                                  ebook_subsection.file_id as file_id
                           FROM ebook_section, ebook_subsection
                           WHERE ebook_section.ebook_id = $info->id AND
                                 ebook_section.id = ebook_subsection.section_id
                                 ORDER BY CONVERT(psid, UNSIGNED), psid,
                                          CONVERT(pssid, UNSIGNED), pssid");
        if (count($files) > 0 || count($q) > 0) {
            $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                <input type='hidden' name='id' value='$ebook_id' />
                <fieldset>
                    <table class='table-default'>
                    <tr class='list-header'>
                      <th>$langFileName</th>
                      <th>$langTitle</th>
                      <th>$langSection</th>
                      <th>$langReorder</th>
                    </tr>";
                   foreach ($q as $r) {
                       $file_id = $r->file_id;
                       $display_id = $r->sid . ',' . $r->ssid;
                       if ($editable[$id_map[$file_id]]) {
                           $edit = '&nbsp;' . icon('fa-edit', $langEditChange,
                               "new.php?course=$course_code&amp;editPath=" .
                               $paths[$id_map[$file_id]] . "&amp;ebook_id=$ebook_id&amp;back=edit");
                       } else {
                           $edit = '';
                       }
                       $tool_content .= "
                            <tr>
                                <td class='smaller'><a href='show.php/$course_code/$ebook_id/$display_id/' target='_blank'>" . q($files[$id_map[$file_id]]) . "</a>$edit</td>
                                <td><input type='text' name='title[$file_id]' size='30' value='" . q($r->subsection_title) . "'></td>
                                <td>" . selection($sections, "sid[$file_id]", $r->sid, 'class="form-control"') . "</td>
                                <td class='center' style='width: 50px;'>
                                    <input type='hidden' name='oldssid[$file_id]' value='$r->ssid'>
                                    <input type='text' class='form-control' name='ssid[$file_id]' value='" . q($r->pssid) . "'>
                                </td>
                            </tr>";
                       unset($files[$id_map[$file_id]]);
                   }
                   foreach ($files as $key => $file) {
                       $path = $paths[$key];
                       $file_id = $file_ids[$key];
                       $title = get_html_title($basedir . $path);
                       $tool_content .= "
                        <tr class='not_visible'>
                            <td class='smaller'><a href='show.php/$course_code/$ebook_id/_" . q($file) . "' target='_blank'>" . q($file) . "</a></td>
                            <td><input type='text' name='title[$file_id]' size='30' value='" . q($title) . "' /></td>
                            <td>" . selection($sections, "sid[$file_id]", ' ', 'class="form-control"') . "</td>
                            <td class='center' style='width: 50px;'>
                               <input class='form-control' type='text' name='ssid[$file_id]'>
                           </td>
                        </tr>";
                   }
                   $tool_content .= "
                    <tr>
                      <td colspan='3'>&nbsp;</td>
                      <td><input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'></td>
                    </table>
                </fieldset>
             </form>";
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langEBookNoPages</div>";
        }
    }
}
$pageName = $langEBookEdit;

draw($tool_content, 2, null, $head_content);

function find_html_files() {
    global $group_sql;

    $disk_paths = array();
    $public_paths = array();
    $file_ids = array();
    $q = Database::get()->queryArray("SELECT id, path, filename, editable FROM document
                              WHERE $group_sql AND (format = 'html' OR format = 'htm')");
    foreach ($q as $row) {
        $disk_paths[] = $row->path;
        $public_paths[] = public_file_path($row->path, $row->filename);
        $file_ids[] = $row->id;
        $editable[] = $row->editable;
    }
    array_multisort($public_paths, $disk_paths, $file_ids);
    $id_map = array_flip($file_ids);
    return array(&$disk_paths, &$public_paths, &$file_ids, &$id_map, &$editable);
}

function get_html_title($file) {
    $dom = new DOMDocument();
    $dom->loadHTMLFile($file);
    if (!is_object($dom)) {
        return '';
    }
    $title_elements = $dom->getElementsByTagName('title');
    if (!is_object($title_elements) or !$title_elements->length) {
        return '';
    }
    $title = $title_elements->item(0)->nodeValue;
    return html_entity_decode($title, ENT_QUOTES, 'UTF-8');
}
