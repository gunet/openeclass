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


$require_current_course = true;
$require_help = true;
$helpTopic = 'EBook';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileManageLib.inc.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action_stats = new action();
$action_stats->record(MODULE_ID_EBOOK);
/* * *********************************** */

$toolName = $langEBook;

if ($is_editor) {
    if (isset($_GET['create'])) {
        $pageName = $langCreate;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')
            ));        
    } else {
        $tool_content .= action_bar(array(
            array('title' => $langCreate,
                  'url' => "index.php?course=$course_code&amp;create=1",
                  'icon' => 'fa-plus-circle',
                  'button-class' => 'btn-success',
                  'level' => 'primary-label')
            ));
    }

    if (isset($_REQUEST['delete']) or isset($_POST['delete_x'])) {
        $id = $_REQUEST['delete'];
        $r = Database::get()->querySingle("SELECT title FROM ebook WHERE course_id = ?d AND id = ?d", $course_id, $id);
        if ($r) {
            $title = $r->title;
            Database::get()->query("DELETE FROM ebook_subsection WHERE section_id IN
                                         (SELECT id FROM ebook_section WHERE ebook_id = ?d)", $id);
            Database::get()->query("DELETE FROM ebook_section WHERE ebook_id = ?d", $id);
            Database::get()->query("DELETE FROM ebook WHERE id = ?d", $id);
            $basedir = $webDir . 'courses/' . $course_code . '/ebook/' . $id;
            my_delete($basedir);
            Database::get()->query("DELETE FROM document WHERE
                                 subsystem = " . EBOOK . " AND
                                 subsystem_id = ?d AND
                                 course_id = ?d", $id, $course_id);
            $tool_content .= "<div class='alert alert-success'>" . q(sprintf($langEBookDeleted, $title)) . "</div>";
        }
    } elseif (isset($_GET['create'])) {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langEBook);
        enableCheckFileSize();
        $tool_content .= "
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='create.php?course=$course_code' enctype='multipart/form-data'>" .
                fileSizeHidenInput() . "
                <div class='form-group'>
                    <label for='ebook_title' class='col-sm-2 control-label'>$langTitle: </label>
                    <div class='col-sm-10'>
                        <input type='text' class='form-control' id='ebook_title' name='title' placeholder='$langTitle'>                    
                    </div>
                </div>
                <div class='form-group'>
                    <label for='fileUpload' class='col-sm-2 control-label'>$langZipFile:</label>
                    <div class='col-sm-10'>                    
                      <input type='file' name='file' id='fileUpload'>
 $langOptional
                    </div>
                </div>
                <div class='row'>
                      <div class='infotext-sm col-sm-offset-2 col-sm-10 margin-bottom-fat'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2 '>
                        <input type='submit' class='btn btn-primary' name='submit' value='$langSend' />     
                    </div>
                </div>                         
            </form>
        </div>";
    } elseif (isset($_GET['down'])) {
        move_order('ebook', 'id', intval($_GET['down']), 'order', 'down', "course_id = $course_id");
    } elseif (isset($_GET['up'])) {
        move_order('ebook', 'id', intval($_GET['up']), 'order', 'up', "course_id = $course_id");
    } elseif (isset($_GET['vis'])) {
        Database::get()->query("UPDATE ebook SET visible = NOT visible
                                 WHERE course_id = ?d AND
                                       id = ?d", $course_id, $_GET['vis']);
    }
}

if ($is_editor) {
    $visibility_check = '';
} else {
    $visibility_check = "AND visible = 1 AND ebook_subsection.id IS NOT NULL";
}
$q = Database::get()->queryArray("SELECT ebook.id, ebook.title, visible, MAX(ebook_subsection.id) AS sid
                      FROM ebook LEFT JOIN ebook_section ON ebook.id = ebook_id
                           LEFT JOIN ebook_subsection ON ebook_section.id = section_id
                      WHERE course_id = ?d
                            $visibility_check
                      GROUP BY ebook.id
                      ORDER BY `order`", $course_id);

if (!$q && !isset($_GET['create'])) {
    $tool_content .= "<div class='alert alert-warning'>$langNoEBook</div>";
} else if(!isset($_GET['create'])){
    $tool_content .= "<div class='table-responsive'>";
    $tool_content .= "<table class='table-default'>
     <tr class='list-header'>
       <th class = 'text-left'>$langEBook</th>" .
            ($is_editor ?
                    "<th class='text-center option-btn-cell'>".icon('fa-gears')."</th>" :
                    '<th>&nbsp;</th>') . "
     </tr>";

    $k = 0;
    $num = count($q);
    foreach ($q as $r) {
        $vis_class = $r->visible ? '' : 'not_visible';
        if (is_null($r->sid)) {
            $title_link = q($r->title) . ' <i>(' . $langEBookNoSections . ')</i>';
        } else {
            $title_link = "<a href='show.php/$course_code/$r->id/'>" . q($r->title) . "</a>";
        }
        $warning = is_null($r->sid) ? " <i>($langInactive)</i>" : '';
        $tool_content .= "<tr class = '$vis_class'>
                <td>$title_link</td>
                 <td class='option-btn-cell'>".
                   tools($r->id, $k, $num, $r->visible) . 
                "</td></tr>";
        $k++;
    }
    $tool_content .= "</table>";
    $tool_content .= "</div>";
}

draw($tool_content, 2, null, $head_content);


/**
 * @brief display action button
 * @global type $is_editor
 * @global type $langModify
 * @global type $langDelete
 * @global type $langMove
 * @global type $langDown
 * @global type $langUp
 * @global type $langEBookDelConfirm
 * @global type $course_code
 * @global type $langVisibility
 * @param type $id
 * @param type $title
 * @param type $k
 * @param type $num
 * @param type $vis
 * @return string
 */
function tools($id, $k, $num, $vis) {
    global $is_editor, $langModify, $langDelete, $langMove, $langDown, $langUp, 
           $langEBookDelConfirm, $course_code, $langVisibility;

    if (!$is_editor) {
        return '';
    } else {        
        $num--;
        $content = action_button(array(
                    array('title' => $langModify,
                          'url' => "edit.php?course=$course_code&amp;id=$id",
                          'icon' => 'fa-edit'),
                    array('title' => $langDelete,                          
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$id",
                          'icon' => 'fa-times',
                          'class' => 'delete',
                          'confirm' => $langEBookDelConfirm),
                    array('title' => $langVisibility,
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vis=$id",
                          'icon' => $vis ? 'fa-eye' : 'fa-eye-slash'),
                    array('title' => "$langMove $langDown",
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;down=$id",
                          'icon' => 'fa-arrow-down',
                          'show' => $k < $num),
                    array('title' => "$langMove $langUp",
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;up=$id",
                          'icon' => 'fa-arrow-up',
                          'show' => $k > 0)
        ));
        return "$content";        
    }
}
