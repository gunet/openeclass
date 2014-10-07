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
$helpTopic = 'Notes';

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

$nameTools = $langNotes;

ModalBoxHelper::loadModalBox();
load_js('jquery');
load_js('tools.js');
load_js('references.js');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
        $langEmptyNoteTitle . '";</script>';

$noteNumber = Notes::count_user_notes();

$displayForm = true;
/* up and down commands */
if (isset($_GET['down'])) {
    $thisNoteId = intval($_GET['down']);
    Notes::movedown_note($thisNoteId);
}
if (isset($_GET['up'])) {
    $thisNoteId = intval($_GET['up']);
    Notes::moveup_note($thisNoteId);
}

/* submit form: new or updated note*/
if (isset($_POST['submitNote'])) {
    
    $newTitle = $_POST['newTitle'];       
    $newContent = $_POST['newContent'];
    $refobjid = ($_POST['refobjid'] == "0")? $_POST['refcourse']:$_POST['refobjid'];
    if (!empty($_POST['id'])) { //existing note
        $id = intval($_POST['id']);
        Notes::update_note($id, $newTitle, $newContent, $refobjid);
        $message = "<p class='success'>$langNoteModify</p>";
    } else { // new note
        $id = Notes::add_note($newTitle, $newContent, $refobjid);
        $message = "<p class='success'>$langNoteAdd</p>";
    }    
} // end of if $submit

/* delete */
if (isset($_GET['delete'])) {
    $thisNoteId = intval($_GET['delete']);
    Notes::delete_note($thisNoteId);
    $message = "<p class='success'>$langNoteDel</p>";
}

/* edit */
if (isset($_GET['modify'])) {
    $modify = intval($_GET['modify']);
    $note = Notes::get_note($modify);
    if ($note) {
        $noteToModify = $note->id;
        $contentToModify = $note->content;
        $titleToModify = q($note->title);
        $titleToModify = q($note->title);
        $gen_type_selected = $note->reference_obj_module;
        $course_selected = $note->reference_obj_course;
        $type_selected = $note->reference_obj_type;
        $object_selected = $note->reference_obj_id;
    }
}

if (isset($message) && $message) {
    $tool_content .= $message . "<br/>";
    $displayForm = false; //do not show form
}

/* display form */
if ($displayForm and (isset($_GET['addNote']) or isset($_GET['modify']))) {
    $tool_content .= "
    <form method='post' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return checkrequired(this, 'antitle');\">
    <fieldset>
    <legend>$langNote</legend>
    <table class='tbl' width='100%'>";
    if (isset($_GET['modify'])) {
        $langAdd = $nameTools = $langModifNote;
    } else {
        $nameTools = $langAddNote;
    }
    $navigation[] = array('url' => "index.php", 'name' => $langNotes);
    if (!isset($noteToModify))
        $noteToModify = "";
    if (!isset($contentToModify))
        $contentToModify = "";
    if (!isset($titleToModify))
        $titleToModify = "";
    if (!isset($gen_type_selected))
        $gen_type_selected = null;
    if (!isset($course_selected))
        $course_selected = null;
    if (!isset($type_selected))
        $type_selected = null;
    if (!isset($object_selected))
        $object_selected = null;
    
    $tool_content .= "
    <tr><th>$langNoteTitle:</th></tr>
    <tr>
      <td><input type='text' name='newTitle' value='$titleToModify' size='50' /></td>
    </tr>
    <tr><th>$langNoteBody:</th></tr>
    <tr>
      <td>" . rich_text_editor('newContent', 4, 20, $contentToModify) . "</td>
    </tr>
    <tr><th>$langReferencedObject:</th></tr>
    <tr>
      <td>".
      References::build_object_referennce_fields($gen_type_selected, $course_selected, $type_selected, $object_selected)
   ."</td>
    </tr>
    <tr>
      <td class='right'><input type='submit' name='submitNote' value='$langAdd' /></td>
    </tr>
    </table>
    <input type='hidden' name='id' value='$noteToModify' />
    </fieldset>
    </form>";
} else {
    /* display actions toolbar */
    $tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[SCRIPT_NAME]?addNote=1'>" . $langAddNote . "</a></li>
      </ul>
    </div>";
}


/* display notes */
//$notelist = isset($_GET['nid']) ? array(Notes::get_note(intval($_GET['nid']))) : Notes::get_user_notes();
if (isset($_GET['course'])) {
    $cid = course_code_to_id($_GET['course']);
    $notelist = Notes::get_all_course_notes($cid);
} else { 
    if (isset($_GET['nid'])) {
        $notelist = array(Notes::get_note(intval($_GET['nid'])));
    } else {
        $notelist = Notes::get_user_notes();
    }
}
//$notelist = isset($_GET['nid']) ? array(Notes::get_note(intval($_GET['nid']))) : Notes::get_user_notes();

$iterator = 1;
$bottomNote = $noteNumber = count($notelist);

$tool_content .= "
        <script type='text/javascript' src='../../modules/auth/sorttable.js'></script>
        <table width='100%' class='sortable' id='t1'>";
if ($noteNumber > 0) {
    $tool_content .= "<tr><th colspan='2'>$langNotes</th>";
    if ($noteNumber > 1) {
        $colsNum = 2;
    } else {
        $colsNum = 2;
    }
    $tool_content .= "<th width='60' colspan='$colsNum' class='center'>$langActions</th>";
    $tool_content .= "</tr>";
}
$k = 0;
if ($notelist)
    foreach ($notelist as $note) {
        $content = standard_text_escape($note->content);
        $note->date_time = claro_format_locale_date($dateFormatLong, strtotime($note->date_time));
        if ($k % 2 == 0) {
            $tool_content .= "<tr class='even'>";
        } else {
          $tool_content .= "<tr class='odd'>";
        }
        $tool_content .= "<td width='16' valign='top'>
			<img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
			<td><b>";
        if (empty($note->title)) {
            $tool_content .= $langNoteNoTitle;
        } else {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?nid=$note->id'>" . q($note->title) . "</a>";
        }
        $tool_content .= "</b><div class='smaller'>" . nice_format($note->date_time) . "</div>";
        if(!is_null($note->reference_obj_type)){
            $tool_content .= "<div class='smaller'>$langReferencedObject: " . References::item_link($note->reference_obj_module, $note->reference_obj_type, $note->reference_obj_id, $note->reference_obj_course) . "</div>";
        }
        if (isset($_GET['nid'])) {
            $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langNotes);
            $nameTools = q($note->title);
            $tool_content .= $content;
        } else {
            $tool_content .= standard_text_escape(ellipsize_html($content, 500, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?nid=$note->id'> <span class='smaller'>[$langMore]</span></a></strong>"));
        }
        $tool_content .= "</td>";

        $tool_content .= "
            <td width='70' class='right'>
                  <a href='$_SERVER[SCRIPT_NAME]?modify=$note->id'>
                  <img src='$themeimg/edit.png' title='" . $langModify . "' /></a>&nbsp;
                  <a href='$_SERVER[SCRIPT_NAME]?delete=$note->id' onClick=\"return confirmation('$langSureToDelNote');\">
                  <img src='$themeimg/delete.png' title='" . $langDelete . "' /></a>&nbsp;
            </td>";
        if ($noteNumber > 1) {
            $tool_content .= "<td align='center' width='35' class='right'>";
        }
        if ($iterator != 1) {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?up=$note->id'>
                        <img class='displayed' src='$themeimg/up.png' title='" . $langMove . " " . $langUp . "' />
                        </a>";
        }
        if ($iterator < $bottomNote) {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?down=" . $note->id . "'>
                        <img class='displayed' src='$themeimg/down.png' title='" . $langMove . " " . $langDown . "' />
                        </a>";
        }
        if ($noteNumber > 1) {
            $tool_content .= "</td>";
        }

        $tool_content .= "</tr>";
        $iterator ++;
        $k++;
    } // end of while
$tool_content .= "</table>";

if ($noteNumber < 1) {
    $no_content = true;
    if (isset($_GET['addNote'])) {
        $no_content = false;
    }
    if (isset($_GET['modify'])) {
        $no_content = false;
    }
    if ($no_content) {
        $tool_content .= "<p class='alert1'>$langNoNote</p>\n";
    }
}

add_units_navigation(TRUE);

draw($tool_content, 1, null, $head_content);
