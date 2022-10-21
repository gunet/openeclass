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
                $tool_content .= "<div class='alert alert-success'>" . q(sprintf($langEBookDeleted, $title)) . "</div>";
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
        <div class='col-12'>
        <div class='form-wrapper form-edit p-3 rounded'>
            <form class='form-horizontal' role='form' method='post' action='create.php?course=$course_code' enctype='multipart/form-data'>" .
                fileSizeHidenInput() . "
                <div class='form-group'>
                    <label for='ebook_title' class='col-sm-6 control-label-notes'>$langTitle: </label>
                    <div class='col-sm-12'>
                        <input type='text' class='form-control' id='ebook_title' name='title' placeholder='$langTitle'>
                    </div>
                </div>

            

                <div class='form-group mt-3'>
                    <label for='fileUpload' class='col-sm-6 control-label-notes'>$langZipFile:</label>
                    <div class='col-sm-12'>
                      <input type='file' name='file' id='fileUpload'><small class='help-block'>$langOptional</small>
                    </div>
                </div>

               

                <div class='row mt-3'>
                      <div class='infotext col-sm-offset-2 col-sm-10 margin-bottom-fat'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>
                </div>

               

                <div class='form-group mt-5'>
                    <div class='col-12 '>
                       <div class='row'>
                           <div class='col-6'>
                              ".
                              form_buttons(array(
                                  array(
                                      'class' => 'btn-primary btn-sm submitAdminBtn w-100',
                                      'text'  => $langCreate,
                                      'name'  => 'submit',
                                      'value' => (isset($_GET['newPoll']) ? $langCreate : $langModify)
                                  )
                              ))
                              ."
                           </div>
                           <div class='col-6'>
                              ".
                              form_buttons(array(
                                  array(
                                      'class' => 'btn-secondary btn-sm cancelAdminBtn w-100',
                                      'href' => "index.php?course=$course_code",
                                  )
                              ))
                              ."
                           </div>
                       </div>
                    </div>
                </div>
            </form>
        </div></div>";
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
    $tool_content .= "<div class='col-12'><div class='alert alert-warning'>$langNoEBook</div></div>";
} else if(!isset($_GET['create'])){
    $tool_content .= "<div class='table-responsive'>";
    $tool_content .= "<table class='table-default'><thead>
     <tr class='list-header'>
       <th class = 'text-white text-left'>$langEBooks</th>" .
            ($is_editor ?
                    "<th class='text-white text-center option-btn-cell'>".icon('fa-cogs')."</th>" :
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
            $title_link .= '&nbsp;' . icon('fa-edit', $langEditChange, "edit.php?course=$course_code&amp;id=" . $r->id);
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
        $content = "<td class='option-btn-cell' style='width: 90px;'>
               <div class='reorder-btn text-center' style='font-size: 16px; cursor: pointer;'>
                    <span class='fa fa-arrows' style='cursor: pointer;'></span>
               </div><div class='text-center mt-3'>";
        $content .= action_button(array(
                    array('title' => $langEditChange,
                          'url' => "edit.php?course=$course_code&amp;id=$id",
                          'icon' => 'fa-edit'),
                    array('title' => $vis ? $langViewHide : $langViewShow,
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vis=$id",
                          'icon' => $vis ? 'fa-eye-slash' : 'fa-eye'),
                    array('title' => $langDelete,
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$id",
                          'icon' => 'fa-times',
                          'class' => 'delete',
                          'confirm' => $langEBookDelConfirm)
        ));
        $content .= "</div></td>";
        return "$content";
    }
}
