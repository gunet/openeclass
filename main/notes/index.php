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


/*
 * Notes Component
 *
 * @version 1.0
 * @abstract This component displays personal user notes and offers several operations on them.
 * The user can:
 * 1. Add new notes
 * 2. Delete notes (one by one or all at once)
 * 3. Modify existing notes
 * 4. Re-arrange the order of her notes
 * 5. Associate notes with courses and course objects
 */

$require_login = true;
$require_help = TRUE;
$helpTopic = 'portfolio';

if(isset($_POST['setsessioncourse']) && $_POST['setsessioncourse'] == 1)
    $require_current_course = TRUE;

include '../../include/baseTheme.php';
$require_valid_uid = true;
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/references.class.php';
require_once 'main/notes/notes.class.php';

//angela: Do we need recording of personal actions????
// The following is added for statistics purposes
//require_once 'include/action.php';
//$action = new action();
//$action->record(MODULE_ID_ANNOUNCE);

$toolName = $langNotes;

ModalBoxHelper::loadModalBox();
load_js('tools.js');
load_js('references.js');
load_js('trunk8');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
        $langEmptyNoteTitle . '";</script>';
$head_content .= "<script>
    $(document).ready(function(){
            $('.note-content').each(function() {
                $(this).trunk8({
                    lines: '4',
                    fill: '&hellip;<div class=\"announcements-more\"><a href=\"$_SERVER[SCRIPT_NAME]?nid=' +
                        $(this).data('id') + '\">$langMore</a></div>'
                });
            })
        });
</script>";

$noteNumber = Notes::count_user_notes();
$displayForm = true;
/* up and down commands */
if (isset($_GET['down'])) {
    $thisNoteId = intval(getDirectReference($_GET['down']));
    Notes::movedown_note($thisNoteId);
    redirect_to_home_page('main/notes/index.php');  
}
if (isset($_GET['up'])) {
    $thisNoteId = intval(getDirectReference($_GET['up']));
    Notes::moveup_note($thisNoteId);
    redirect_to_home_page('main/notes/index.php');  
}

/* submit form: new or updated note */
if (isset($_POST['submitNote'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('newTitle'));
    $v->labels(array(
        'newTitle' => "$langTheField $langTitle"
    ));    
    if($v->validate()) {
        $newTitle = $_POST['newTitle'];
        $newContent = $_POST['newContent'];
        $refCourseId = (isset($_POST['setsessioncourse']) &&  $_POST['setsessioncourse'] == 1) ? "course:".$course_id:$_POST['refcourse'];
        $refobjid = ($_POST['refobjid'] == "0") ? $refCourseId : $_POST['refobjid'];
        if (!empty($_POST['id'])) { //existing note
            $id = intval(getDirectReference($_POST['id']));
            Notes::update_note($id, $newTitle, $newContent, $refobjid);
            Session::Messages($langNoteModify, 'alert-success');
            redirect_to_home_page('main/notes/index.php');        
        } else { // new note
            $id = Notes::add_note($newTitle, $newContent, $refobjid);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo $langNoteAdd;
                exit;
            }
            Session::Messages($langNoteAdd, 'alert-success');
            redirect_to_home_page('main/notes/index.php');
        }
    } else {
        $new_or_modify = empty($_POST['id']) ? "addNote=1" : "modify=$_POST[id]";
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("main/notes/index.php?$new_or_modify");
    }
} // end of if $submit

/* delete */
if (isset($_GET['delete'])) {
    $thisNoteId = intval(getDirectReference($_GET['delete']));
    Notes::delete_note($thisNoteId);
    Session::Messages($langNoteDel, 'alert-success');
    redirect_to_home_page('main/notes/index.php');
}



/* display form */
if (isset($_GET['addNote']) or isset($_GET['modify'])) {
    $navigation[] = array('url' => "index.php", 'name' => $langNotes);
    if (isset($_GET['modify'])) {
        $langAdd = $pageName = $langModifNote;
        $modify = intval(getDirectReference($_GET['modify']));
        $note = Notes::get_note($modify);      
    } else {
        $pageName = $langAddNote;
    }
    $noteToModify = isset($note) ? $note->id : '';    
    $titleToModify = Session::has('newTitle') ? Session::get('newTitle') : (isset($note) ? q($note->title) : '');
    $contentToModify = Session::has('newContent') ? Session::get('newContent') : (isset($note) ? $note->content : '');
    $gen_type_selected = isset($note) ? $note->reference_obj_module : null;
    $course_selected = isset($note) ? $note->reference_obj_course : null;
    $type_selected = isset($note) ? $note->reference_obj_type : null;
    $object_selected = isset($note) ? $note->reference_obj_id : null;

    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => $_SERVER['SCRIPT_NAME']
        )
    )). "
    <div class='form-wrapper'>
    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return checkrequired(this, 'antitle');\">
    <fieldset>
    <div class='form-group".(Session::getError('newTitle') ? " has-error" : "")."'>
        <label for='newTitle' class='col-sm-2 control-label'>$langTitle:</label>
        <div class='col-sm-10'>
            <input name='newTitle' type='text' class='form-control' id='newTitle' value='" . $titleToModify . "' placeholder='$langTitle'>
            <span class='help-block'>".Session::getError('newTitle')."</span>
        </div>
    </div>
    <div class='form-group'>
      <label for='newContent' class='col-sm-2 control-label'>$langNoteBody:</label>
      <div class='col-sm-10'>
        " . rich_text_editor('newContent', 4, 20, $contentToModify) . "
      </div>
    </div>
    <div class='form-group'>
      <label for='refobjgentype' class='col-sm-2 control-label'>$langReferencedObject:</label>
      <div class='col-sm-10'>
        ".References::build_object_referennce_fields($gen_type_selected, $course_selected, $type_selected, $object_selected). "
      </div>
    </div>
    <div class='form-group'>
      <div class='col-sm-10 col-sm-offset-2'>
        <input class='btn btn-primary' type='submit' name='submitNote' value='$langAdd'> 
        <a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]'>$langCancel</a>
      </div>
    </div>";
    if($noteToModify!=""){
        $tool_content .="<input type='hidden' name='id' value='" . getIndirectReference($noteToModify)."' />";
    }
    $tool_content .="</fieldset>
    </form></div>";
    
} elseif (isset($_GET['nid'])) {
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => $_SERVER['SCRIPT_NAME']
        )
    ));
    
    $note = Notes::get_note(intval(getDirectReference($_GET['nid'])));
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langNotes);
    $pageName = q($note->title);    
    $tool_content .= "
        <div class='panel panel-action-btn-default'>
            <div class='panel-heading'>
                <div class='pull-right'>".
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => "$_SERVER[SCRIPT_NAME]?modify=".getIndirectReference($note->id),
                            'icon' => 'fa-edit'),
                        array('title' => $langDelete,
                            'url' => "$_SERVER[SCRIPT_NAME]?delete=".getIndirectReference($note->id),
                            'confirm' => $langSureToDelNote,
                            'class' => 'delete',
                            'icon' => 'fa-times')
                    ))
               ."</div>
                <div class='panel-title h3'>".q($note->title)."</div>
            </div>
            <div class='panel-body'>
                <div class='label label-success'>". claro_format_locale_date($dateFormatLong, strtotime($note->date_time)). "</div><br><br>
                $note->content
            </div>
        </div>";
} else {
    /* display actions toolbar */
    $tool_content .= action_bar(array(
                array('title' => $langAddNote,
                    'url' => "$_SERVER[SCRIPT_NAME]?addNote=1",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success')
            ));



    /* display notes */
    //$notelist = isset($_GET['nid']) ? array(Notes::get_note(intval($_GET['nid']))) : Notes::get_user_notes();
    if (isset($_GET['course'])) {
        $cid = course_code_to_id($_GET['course']);
        $notelist = Notes::get_all_course_notes($cid);
    } else { 
        $notelist = Notes::get_user_notes();
    }
    //$notelist = isset($_GET['nid']) ? array(Notes::get_note(intval($_GET['nid']))) : Notes::get_user_notes();

    $iterator = 1;
    $bottomNote = $noteNumber = count($notelist);

    $tool_content .= "
            <script type='text/javascript' src='../../modules/auth/sorttable.js'></script>
            <div class='table-responsive'>
                <table class='table-default'>";
    if ($noteNumber > 0) {
        $tool_content .= "<tr class='list-header'>";
        $tool_content .= "<th class='text-left'>$langCategoryNotes</th>";
        $tool_content .= "<th class='text-center'>".icon('fa-gears')."</th>";
        $tool_content .= "</tr>";
    }

    foreach ($notelist as $note) {
        $content = standard_text_escape($note->content);
        $note->date_time = claro_format_locale_date($dateFormatLong, strtotime($note->date_time));
        $tool_content .= "<tr><td><b>";
        if (empty($note->title)) {
            $tool_content .= $langNoteNoTitle;
        } else {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?nid=" . getIndirectReference($note->id)."'>" . q($note->title) . "</a>";
        }
        $tool_content .= "</b><br><small>" . nice_format($note->date_time) . "</small>";
        if (!is_null($note->reference_obj_type)) {
            $tool_content .= "<br><small>$langReferencedObject: " . References::item_link($note->reference_obj_module, $note->reference_obj_type, $note->reference_obj_id, $note->reference_obj_course) . "</small>";
        }

        //$tool_content .= standard_text_escape(ellipsize_html($content, 500, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?nid=" . getIndirectReference($note->id)."'><span class='smaller'>[$langMore]</span></a></strong>"));
        $tool_content .= "<div class = 'note-content' data-id= '" . getIndirectReference($note->id) . "'>$content</div>";
        $tool_content .= "</td>";

        $tool_content .= "<td class='option-btn-cell'>" .
                action_button(array(
                    array('title' => $langEditChange,
                        'url' => "$_SERVER[SCRIPT_NAME]?modify=" . getIndirectReference($note->id),
                        'icon' => 'fa-edit'),
                    array('title' => $langDelete,
                        'url' => "$_SERVER[SCRIPT_NAME]?delete=" . getIndirectReference($note->id),
                        'confirm' => $langSureToDelNote,
                        'class' => 'delete',
                        'icon' => 'fa-times'),
                    array('title' => $langMove . " " . $langUp,
                        'url' => "$_SERVER[SCRIPT_NAME]?up=" . getIndirectReference($note->id),
                        'level' => 'primary',
                        'disabled' => $iterator == 1,
                        'icon' => 'fa-arrow-up'),
                    array('title' => $langMove . " " . $langDown,
                        'url' => "$_SERVER[SCRIPT_NAME]?down=" . getIndirectReference($note->id),
                        'level' => 'primary',
                        'disabled' => $iterator >= $bottomNote,
                        'icon' => 'fa-arrow-down')
                )) .
                "</td>";

        $tool_content .= "</tr>";
        $iterator ++;
    } // end of while
    $tool_content .= "</table></div>";

    if ($noteNumber < 1) {
        $no_content = true;
        if (isset($_GET['addNote'])) {
            $no_content = false;
        }
        if (isset($_GET['modify'])) {
            $no_content = false;
        }
        if ($no_content) {
            $tool_content .= "<p class='alert alert-warning text-center'>$langNoNote</p>\n";
        }
    }
}
add_units_navigation(TRUE);

draw($tool_content, 1, null, $head_content);
