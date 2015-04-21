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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Coursedescription';
$require_login = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/log.php';

$toolName = $langCourseDescription;
$pageName = $langEditCourseProgram;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langCourseProgram);

if (isset($_REQUEST['id'])) {
    $editId = intval($_REQUEST['id']);
    $q = Database::get()->querySingle("SELECT title, comments, type FROM course_description WHERE course_id = ?d AND id = ?d", $course_id, $editId);
    $cdtitle = Session::has('editTitle') ? Session::get('editTitle') : $q->title;
    $comments = Session::has('editComments') ? Session::get('editComments') : $q->comments;
    $defaultType = Session::has('editType') ? Session::get('editType') : $q->type;
} else {
    $editId = false;
    $cdtitle = Session::has('editTitle') ? Session::get('editTitle') : "";
    $comments = Session::has('editComments') ? Session::get('editComments') : "";
    $defaultType = Session::has('editType') ? Session::get('editType') : "";
}

$q = Database::get()->queryArray("SELECT id, title FROM course_description_type ORDER BY `order`");
$types = array();
$types[''] = '';
foreach ($q as $type) {
    $title = $titles = @unserialize($type->title);
    if ($titles !== false) {
        if (isset($titles[$language]) && !empty($titles[$language])) {
            $title = $titles[$language];
        } else if (isset($titles['en']) && !empty($titles['en'])) {
            $title = $titles['en'];
        } else {
            $title = array_shift($titles);
        }
    }
    $types[$type->id] = $title;
}

$tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "index.php?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));

$tool_content .= "
    <div class='row'>
        <div class='col-xs-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code'>";
if ($editId !== false) {
    $tool_content .= "<input type='hidden' name='editId' value='$editId' />";
}
$tool_content .= "
                    <fieldset>
                        <div class='form-group'>
                            <label for='typSel' class='col-sm-2 control-label'>$langType:</label>
                            <div class='col-sm-10'>
                            " . selection($types, 'editType', $defaultType, 'class="form-control" id="typSel"') . "
                            </div>
                        </div>
                        <div class='form-group".(Session::getError('editTitle') ? " has-error" : "")."'>
                            <label for='titleSel' class='col-sm-2 control-label'>$langTitle:</label>
                            <div class='col-sm-10'>
                                <input type='text' name='editTitle' class='form-control' value='$cdtitle' size='40' id='titleSel'>
                                <span class='help-block'>".Session::getError('editTitle')."</span>                                    
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='editComments' class='col-sm-2 control-label'>$langContent:</label>
                            <div class='col-sm-10'>
                            " . @rich_text_editor('editComments', 4, 20, $comments) . "
                            </div>
                        </div>
                        <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                            <input class='btn btn-primary' type='submit' name='saveCourseDescription' value='" . q($langAdd) . "'>
                            <a class='btn btn-default' href='index.php?course=$course_code'>$langCancel</a>
                        </div>
                        </div>
                  </fieldset>
                </form>
            </div>
        </div>
    </div>";


$head_content .= <<<hCont
<script type="text/javascript">
/* <![CDATA[ */

    $(document).on('change', '#typSel', function (e) {
        //console.log(e);
        //alert($(this).children(':selected').text());
        $('#titleSel').val( $(this).children(':selected').text() );
    });

/* ]]> */
</script>
hCont;
draw($tool_content, 2, null, $head_content);
