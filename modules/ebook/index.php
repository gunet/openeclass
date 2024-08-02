<?php
/* ========================================================================
 * Open eClass 3.5
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
$helpTopic = 'ebook';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileManageLib.inc.php';

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toReorder'])) {
        reorder_table('ebook', 'course_id', $course_id, $_POST['toReorder'],
            isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
    }
    exit;
}

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action_stats = new action();
$action_stats->record(MODULE_ID_EBOOK);
/* * *********************************** */

$toolName = $langEBook;

load_js('sortable/Sortable.min.js');
$head_content .= "
    <script>
        $(document).ready(function(){
            Sortable.create(tosort,{
                handle: '.fa-arrows',
                animation: 150,
                onEnd: function (evt) {

                var itemEl = $(evt.item);

                var idReorder = itemEl.attr('data-id');
                var prevIdReorder = itemEl.prev().attr('data-id');

                $.ajax({
                  type: 'post',
                  dataType: 'text',
                  data: {
                          toReorder: idReorder,
                          prevReorder: prevIdReorder,
                        }
                    });
                }
            });
        });
    </script>
";

if ($is_editor) {
    if (isset($_GET['create'])) {
        $pageName = $langCreate;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary')
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
        if (!resource_belongs_to_progress_data(MODULE_ID_EBOOK, $id)) {
            $r = Database::get()->querySingle("SELECT title FROM ebook WHERE course_id = ?d AND id = ?d", $course_id, $id);
            if ($r) {
                $title = $r->title;
                Database::get()->query("DELETE FROM ebook_subsection WHERE section_id IN
                                             (SELECT id FROM ebook_section WHERE ebook_id = ?d)", $id);
                Database::get()->query("DELETE FROM ebook_section WHERE ebook_id = ?d", $id);
                Database::get()->query("DELETE FROM ebook WHERE id = ?d", $id);
                $basedir = $webDir . '/courses/' . $course_code . '/ebook/' . $id;
                my_delete($basedir);
                Database::get()->query("DELETE FROM document WHERE
                                     subsystem = " . EBOOK . " AND
                                     subsystem_id = ?d AND
                                     course_id = ?d", $id, $course_id);
                $tool_content .= "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>" . q(sprintf($langEBookDeleted, $title)) . "</span></div>";
            } else {
                //Session::Messages($langResourceBelongsToCert, "alert-warning");
                Session::flash('message',$langResourceBelongsToCert); 
                Session::flash('alert-class', 'alert-warning');
            }
        }
    } elseif (isset($_GET['create'])) {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langEBook);
        enableCheckFileSize();
        $tool_content .= "
        <div class='d-lg-flex gap-4 mt-5'>
    <div class='flex-grow-1'>
        <div class='form-wrapper form-edit rounded'>
            <form class='form-horizontal' role='form' method='post' action='create.php?course=$course_code' enctype='multipart/form-data'>" .
                fileSizeHidenInput() . "
                <div class='row form-group'>
                    <label for='ebook_title' class='col-12 control-label-notes mb-1'>$langTitle</label>
                    <div class='col-12'>
                        <input type='text' class='form-control' id='ebook_title' name='title' placeholder='$langTitle'>
                    </div>
                </div>

            

                <div class='row form-group mt-4'>
                    <label for='fileUpload' class='control-label-notes mb-1'>$langZipFile</label>
                    <div class='col-12'>
                      <input type='file' name='file' id='fileUpload'></br>
                      <small class='help-block'>$langOptional</small>
                    </div>
                </div>

               

                <div class='row mt-4'>
                      
                      <div class='col-12 infotext TextBold'>$langMaxFileSize" . ini_get('upload_max_filesize') . "</div>
                </div>

               

                <div class='form-group mt-5'>
                    
                       <div class='col-12 d-flex justify-content-end align-items-center'>
                          
                              ".
                              form_buttons(array(
                                  array(
                                      'class' => 'submitAdminBtn',
                                      'text'  => $langCreate,
                                      'name'  => 'submit',
                                      'value' => (isset($_GET['newPoll']) ? $langCreate : $langModify)
                                  ),
                                  array(
                                    'class' => 'cancelAdminBtn ms-1',
                                    'href' => "index.php?course=$course_code",
                                )
                              ))
                              ."
                           
                        </div>
                       
                    
                </div>
            </form>
        </div></div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
</div>";
    } elseif (isset($_GET['vis'])) {
        if (!resource_belongs_to_progress_data(MODULE_ID_EBOOK, $_GET['vis'])) {
            Database::get()->query("UPDATE ebook SET visible = NOT visible
                                 WHERE course_id = ?d AND
                                       id = ?d", $course_id, $_GET['vis']);
        } else {
            //Session::Messages($langResourceBelongsToCert, "alert-warning");
            Session::flash('message',$langResourceBelongsToCert); 
            Session::flash('alert-class', 'alert-warning');
        }
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
                      GROUP BY ebook.id, ebook.title, visible
                      ORDER BY `order`", $course_id);

if (!$q && !isset($_GET['create'])) {
    $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoEBook</span></div></div>";
} else if(!isset($_GET['create'])){
    $tool_content .= "<div class='table-responsive'>";
    $tool_content .= "<table class='table-default'><thead>
     <tr class='list-header'>
       <th class = 'text-start'>$langEBooks</th>" .
            ($is_editor ?
                    "<th class='text-end option-btn-cell'>".icon('fa-cogs')."</th>" :
                    '') . "
     </tr></thead><tbody id='tosort'>";
    foreach ($q as $r) {
        $vis_class = $r->visible ? '' : 'not_visible';
        if (is_null($r->sid)) {
            $title_link = q($r->title) . ' <i>(' . $langEBookNoSections . ')</i>';
        } else {
            $title_link = "<a href='show.php?$course_code/$r->id/'>" . q($r->title) . "</a>";
        }
        if ($is_editor) {
            $title_link .= '&nbsp;&nbsp;&nbsp;' . icon('fa-edit', $langEditChange, "edit.php?course=$course_code&amp;id=" . $r->id);
        }
        $tool_content .= "<tr class = '$vis_class' data-id='$r->id'>
                <td>$title_link</td>".
                   tools($r->id, $r->visible) .
                "</tr>";

    }
    $tool_content .= "</tbody></table>";
    $tool_content .= "</div>";
}

draw($tool_content, 2, null, $head_content);


/**
 * @brief display action button
 * @global type $is_editor
 * @global type $langEditChange
 * @global type $langDelete
 * @global type $langEBookDelConfirm
 * @global type $langViewShow
 * @global type $course_code
 * @global type $langViewHide
 * @param type $id
 * @param type $vis
 * @return string
 */
function tools($id, $vis) {
    global $is_editor, $langEditChange, $langDelete, $langViewShow,
           $langEBookDelConfirm, $course_code, $langViewHide;

    if (!$is_editor) {
        return '';
    } else {
        $content = "
            <td class='option-btn-cell '>
                <div class='d-flex justify-content-end align-items-center gap-2'>
                    <div class='reorder-btn' style='font-size: 16px; cursor: pointer;'>
                        <span class='fa fa-arrows' style='cursor: pointer;'></span>
                    </div>
                    <div>";
                $content .= action_button(array(
                            array('title' => $langEditChange,
                                'url' => "edit.php?course=$course_code&amp;id=$id",
                                'icon' => 'fa-edit'),
                            array('title' => $vis ? $langViewHide : $langViewShow,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vis=$id",
                                'icon' => $vis ? 'fa-eye-slash' : 'fa-eye'),
                            array('title' => $langDelete,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$id",
                                'icon' => 'fa-xmark',
                                'class' => 'delete',
                                'confirm' => $langEBookDelConfirm)
                ));
        $content .= "</div>
                 </div>
            </td>";
        return "$content";
    }
}
