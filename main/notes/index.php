<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */


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

include '../../include/baseTheme.php';
$require_valid_uid = true;
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

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('newTitle'));
    $v->labels(array(
        'newTitle' => "$langTheField $langTitle"
    ));
    if($v->validate()) {
        $newTitle = $_POST['newTitle'];
        $newContent = $_POST['newContent'];
        $refobjid = ($_POST['refobjid'] == "0") ? $_POST['refcourse'] : $_POST['refobjid'];
        if (!empty($_POST['id'])) { //existing note
            $id = intval(getDirectReference($_POST['id']));
            Notes::update_note($id, $newTitle, $newContent, $refobjid);
            Session::flash('message', $langNoteModify);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('main/notes/index.php');
        } else { // new note
            $id = Notes::add_note($newTitle, $newContent, $refobjid);
            Session::flash('message', $langNoteAdd);
            Session::flash('alert-class', 'alert-success');
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
    Session::flash('message', $langNoteDel);
    Session::flash('alert-class', 'alert-success');
//    redirect_to_home_page('main/notes/index.php');
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

    $tool_content .= "

    <div class='row'>
        
    <div class='col-lg-6 col-12'>
        <div class='form-wrapper form-edit rounded border-0 px-0'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return checkrequired(this, 'antitle');\">
                <fieldset>
                <legend class='mb-0' aria-label='$langForm'></legend>
                <div class='form-group".(Session::getError('newTitle') ? " has-error" : "")."'>
                    <label for='newTitle' class='col-sm-12 control-label-notes'>$langTitle <span class='asterisk Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>
                        <input name='newTitle' type='text' class='form-control' id='newTitle' value='" . $titleToModify . "' placeholder='$langTitle'>
                        <span class='help-block Accent-200-cl'>".Session::getError('newTitle')."</span>
                    </div>
                </div>
                <div class='form-group mt-4'>
                  <label for='newContent' class='col-sm-12 control-label-notes'>$langNoteBody</label>
                  <div class='col-sm-12'>
                    " . rich_text_editor('newContent', 4, 20, $contentToModify) . "
                  </div>
                </div>
                <div class='form-group mt-4'>
                  <label for='refobjgentype' class='col-sm-12 control-label-notes'>$langReferencedObject</label>
                  <div class='col-sm-12'>
                    ".References::build_object_referennce_fields($gen_type_selected, $course_selected, $type_selected, $object_selected). "
                  </div>
                </div>
                <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2'>
                     <input class='btn submitAdminBtn' type='submit' name='submitNote' value='$langAdd'> 
                     <a class='btn cancelAdminBtn' href='$_SERVER[SCRIPT_NAME]'>$langCancel</a>
                  
                </div>";
                if($noteToModify!=""){
                    $tool_content .="<input type='hidden' name='id' value='" . getIndirectReference($noteToModify)."' />";
                }
                $tool_content .= "</fieldset>
                ". generate_csrf_token_form_field() ."
            </form>
        </div>
    </div>
    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
</div>";

} elseif (isset($_GET['nid'])) {

    $note = Notes::get_note(intval(getDirectReference($_GET['nid'])));
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langNotes);
    $pageName = q($note->title);
    $tool_content .= "
    <div class='col-12'>
        <div class='card panelCard card-default px-lg-4 py-lg-3'>
            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                <h3>".q($note->title)."</h3>
                <div>
                ".
                        action_button(array(
                            array('title' => $langEditChange,
                                'url' => "$_SERVER[SCRIPT_NAME]?modify=".getIndirectReference($note->id),
                                'icon' => 'fa-edit'),
                            array('title' => $langDelete,
                                'url' => "$_SERVER[SCRIPT_NAME]?delete=".getIndirectReference($note->id),
                                'confirm' => $langSureToDelNote,
                                'class' => 'delete',
                                'icon' => 'fa-xmark')
                        ))
                ."
                </div>
            </div>
            <div class='card-body'> 
                <div class='col-12'>$note->content</div>
            </div>
            <div class='card-footer border-0'>
                <p class='small-text'>". format_locale_date(strtotime($note->date_time)). "</p>
            </div>
        </div></div>";
} else {
    /* display actions toolbar */
    $action_bar = action_bar(array(
                array('title' => $langAddNote,
                    'url' => "$_SERVER[SCRIPT_NAME]?addNote=1",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success')
            ));
    $tool_content .= $action_bar;
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
            <div class='table-responsive'>
                <table id='tableNotes' class='table-default'>";
    if ($noteNumber > 0) {
        $tool_content .= "<thead><tr class='list-header'>";
        $tool_content .= "<th>$langCategoryNotes</th>";
        $tool_content .= "<th class='text-end' aria-label='$langSettingSelect'>".icon('fa-gears')."</th>";
        $tool_content .= "</tr></thead>";
    }

    foreach ($notelist as $note) {
        $content = standard_text_escape($note->content);
        $tool_content .= "<tr><td><b>";
        if (empty($note->title)) {
            $tool_content .= $langNoteNoTitle;
        } else {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?nid=" . getIndirectReference($note->id)."'>" . q($note->title) . "</a>";
        }
        $tool_content .= "</b><br><small>" . format_locale_date(strtotime($note->date_time), '', false) . "</small>";
        if (!is_null($note->reference_obj_type)) {
            $tool_content .= "<br><small>$langReferencedObject: " . References::item_link($note->reference_obj_module, $note->reference_obj_type, $note->reference_obj_id, $note->reference_obj_course) . "</small>";
        }
        $tool_content .= "<div class = 'note-content' data-id= '" . getIndirectReference($note->id) . "'>$content</div>";
        $tool_content .= "</td>";

        $tool_content .= "<td class='option-btn-cell text-end'>" .
                action_button(array(
                    array('title' => $langEditChange,
                        'url' => "$_SERVER[SCRIPT_NAME]?modify=" . getIndirectReference($note->id),
                        'icon' => 'fa-edit'),
                    array('title' => $langDelete,
                        'url' => "$_SERVER[SCRIPT_NAME]?delete=" . getIndirectReference($note->id),
                        'confirm' => $langSureToDelNote,
                        'class' => 'delete',
                        'icon' => 'fa-xmark'),
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
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoNote</span></div></div>\n";
        }
    }
}
add_units_navigation(TRUE);

draw($tool_content, 1, null, $head_content);
